<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class DatabaseConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $user,
        public readonly string $password,
        /** @var array<string, mixed> */
        public readonly array $options,
        public readonly ?string $sslCa,
        public readonly bool $sslVerify,
        public readonly int $connectTimeout,
    ) {}

    public static function builder(): DatabaseConfigBuilder
    {
        return new DatabaseConfigBuilder();
    }
}

/**
 * Builder. Methods are named after CONCERNS (credentials, ssl), not
 * raw setters, so call sites read like recipes.
 */
final class DatabaseConfigBuilder
{
    private string $host = '';
    private int $port = 3306;
    private string $database = '';
    private string $user = '';
    private string $password = '';
    /** @var array<string, mixed> */
    private array $options = [];
    private ?string $sslCa = null;
    private bool $sslVerify = true;
    private int $connectTimeout = 10;

    public function host(string $host, int $port = 3306): self
    {
        $this->host = $host;
        $this->port = $port;
        return $this;
    }

    public function database(string $name): self { $this->database = $name; return $this; }

    public function credentials(string $user, string $password): self
    {
        $this->user = $user;
        $this->password = $password;
        return $this;
    }

    public function withSsl(string $caPath, bool $verify = true): self
    {
        $this->sslCa = $caPath;
        $this->sslVerify = $verify;
        return $this;
    }

    public function connectTimeout(int $seconds): self
    {
        if ($seconds <= 0) throw new \InvalidArgumentException('connectTimeout must be > 0');
        $this->connectTimeout = $seconds;
        return $this;
    }

    /** @param array<string, mixed> $options */
    public function options(array $options): self { $this->options = $options; return $this; }

    public function build(): DatabaseConfig
    {
        if ($this->host === '')     throw new \LogicException('host is required');
        if ($this->database === '') throw new \LogicException('database is required');
        if ($this->user === '')     throw new \LogicException('credentials are required');

        return new DatabaseConfig(
            host: $this->host,
            port: $this->port,
            database: $this->database,
            user: $this->user,
            password: $this->password,
            options: $this->options,
            sslCa: $this->sslCa,
            sslVerify: $this->sslVerify,
            connectTimeout: $this->connectTimeout,
        );
    }
}

// ---- assertions -------------------------------------------------------------

$config = DatabaseConfig::builder()
    ->host('db.internal', port: 3307)
    ->database('app')
    ->credentials('app_user', 's3cret')
    ->withSsl('/etc/ssl/ca.pem', verify: true)
    ->connectTimeout(5)
    ->build();

pdp_assert_eq('db.internal', $config->host, 'host');
pdp_assert_eq(3307,          $config->port, 'port');
pdp_assert_eq('app',         $config->database, 'database');
pdp_assert_eq('app_user',    $config->user, 'user');
pdp_assert_eq('s3cret',      $config->password, 'password');
pdp_assert_eq('/etc/ssl/ca.pem', $config->sslCa, 'sslCa');
pdp_assert_eq(true,          $config->sslVerify, 'sslVerify');
pdp_assert_eq(5,             $config->connectTimeout, 'connectTimeout');

// Validation in build()
pdp_assert_throws(\LogicException::class, fn () => DatabaseConfig::builder()->build(), 'missing host raises');
pdp_assert_throws(\LogicException::class, fn () => DatabaseConfig::builder()->host('h')->build(), 'missing database raises');
pdp_assert_throws(\LogicException::class, fn () => DatabaseConfig::builder()->host('h')->database('d')->build(), 'missing credentials raises');
pdp_assert_throws(\InvalidArgumentException::class, fn () => DatabaseConfig::builder()->connectTimeout(0), 'invalid timeout raises');

pdp_done();
