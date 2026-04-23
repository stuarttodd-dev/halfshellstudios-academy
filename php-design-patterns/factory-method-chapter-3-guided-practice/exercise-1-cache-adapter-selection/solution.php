<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Cache
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): void;
}

final class ArrayCache implements Cache
{
    /** @var array<string, mixed> */
    private array $store = [];
    public function get(string $key): mixed { return $this->store[$key] ?? null; }
    public function set(string $key, mixed $value): void { $this->store[$key] = $value; }
}

/** Stand-in for a real Redis client. Behaves like ArrayCache for the test. */
final class RedisCache implements Cache
{
    /** @var array<string, mixed> */
    private array $store = [];
    public function __construct(public readonly string $host) {}
    public function get(string $key): mixed { return $this->store[$key] ?? null; }
    public function set(string $key, mixed $value): void { $this->store[$key] = $value; }
}

final class FileCache implements Cache
{
    public function __construct(public readonly string $directory) {}
    public function get(string $key): mixed
    {
        $path = "{$this->directory}/" . sha1($key);
        return is_file($path) ? unserialize((string) file_get_contents($path)) : null;
    }
    public function set(string $key, mixed $value): void
    {
        if (!is_dir($this->directory)) mkdir($this->directory, 0700, true);
        file_put_contents("{$this->directory}/" . sha1($key), serialize($value));
    }
}

/**
 * The Factory owns the construction recipes. Callers ask for a driver
 * by name; nobody else knows what `new RedisCache(host: 'localhost')`
 * looks like.
 */
interface CacheFactory
{
    public function make(string $driver): Cache;
}

final class DefaultCacheFactory implements CacheFactory
{
    public function __construct(
        private readonly string $redisHost = 'localhost',
        private readonly string $fileDirectory = '/tmp/cache',
    ) {}

    public function make(string $driver): Cache
    {
        return match ($driver) {
            'redis' => new RedisCache($this->redisHost),
            'file'  => new FileCache($this->fileDirectory),
            'array' => new ArrayCache(),
            default => throw new \RuntimeException("Unknown cache driver: {$driver}"),
        };
    }
}

/** Caller depends on the factory, not on driver names or constructors. */
final class CacheService
{
    public function __construct(private readonly CacheFactory $factory) {}
    public function get(string $key, string $driver): mixed
    {
        return $this->factory->make($driver)->get($key);
    }
}

// ---- assertions -------------------------------------------------------------

$factory = new DefaultCacheFactory(redisHost: 'cache.internal', fileDirectory: sys_get_temp_dir() . '/pdp-cache');

pdp_assert_true($factory->make('array') instanceof ArrayCache, "factory returns ArrayCache for 'array'");
pdp_assert_true($factory->make('redis') instanceof RedisCache, "factory returns RedisCache for 'redis'");
pdp_assert_true($factory->make('file')  instanceof FileCache,  "factory returns FileCache  for 'file'");
pdp_assert_throws(\RuntimeException::class, fn () => $factory->make('memcached'), 'factory rejects unknown drivers');

/** @var RedisCache $redis */
$redis = $factory->make('redis');
pdp_assert_eq('cache.internal', $redis->host, 'factory carries the Redis host config it was constructed with');

// CacheService never touches a `new` call. We hand it a stub factory.
$shared = new ArrayCache();
$shared->set('k', 'v');
$stubFactory = new class ($shared) implements CacheFactory {
    public function __construct(private readonly Cache $shared) {}
    public function make(string $driver): Cache { return $this->shared; }
};
$service = new CacheService($stubFactory);
pdp_assert_eq('v', $service->get('k', 'whatever'), 'CacheService delegates to whatever the factory hands back');

pdp_done();
