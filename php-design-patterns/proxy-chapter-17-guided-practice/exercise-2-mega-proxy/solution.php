<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — one proxy, multiple concerns.
 *
 * The "MegaProxy" bundles logging + auth + caching into one class.
 * That makes every change risky (touching a logging line means re-reading
 * auth + cache code), every test cumbersome, and the order of concerns
 * impossible to reason about from the outside.
 *
 * Refactor: one proxy per concern, each owning one decision, composed
 * at the wiring layer in a deliberate order.
 */

interface Service { public function call(string $input): string; }
interface AuthChecker { public function can(string $input): bool; }
interface Logger { public function info(string $msg): void; }

final class RecordingLogger implements Logger
{
    /** @var list<string> */
    public array $messages = [];
    public function info(string $msg): void { $this->messages[] = $msg; }
}

final class AlwaysAllow implements AuthChecker { public function can(string $input): bool { return true; } }
final class DenyEverything implements AuthChecker { public function can(string $input): bool { return false; } }

final class CountingService implements Service
{
    public int $calls = 0;
    public function call(string $input): string { $this->calls++; return "result:{$input}"; }
}

final class Forbidden extends \DomainException {}

/* ---- Three small proxies, one concern each ---- */

final class AuthProxy implements Service
{
    public function __construct(private readonly Service $inner, private readonly AuthChecker $auth) {}
    public function call(string $input): string
    {
        if (!$this->auth->can($input)) throw new Forbidden();
        return $this->inner->call($input);
    }
}

final class CachingProxy implements Service
{
    /** @var array<string,string> */
    private array $cache = [];
    public function __construct(private readonly Service $inner) {}
    public function call(string $input): string
    {
        return $this->cache[$input] ??= $this->inner->call($input);
    }
}

final class LoggingProxy implements Service
{
    public function __construct(private readonly Service $inner, private readonly Logger $logger) {}
    public function call(string $input): string
    {
        $this->logger->info("start: {$input}");
        try {
            $out = $this->inner->call($input);
        } catch (\Throwable $e) {
            $this->logger->info('error: ' . $e::class);
            throw $e;
        }
        $this->logger->info("end: {$input}");
        return $out;
    }
}

// ---- wiring (composition root) ---------------------------------------------

$log = new RecordingLogger();
$service = new LoggingProxy(            // outermost — sees every attempt, including refused ones
    new AuthProxy(
        new CachingProxy(               // innermost — only caches authorised, real results
            new CountingService(),
        ),
        new AlwaysAllow(),
    ),
    $log,
);

// ---- assertions -------------------------------------------------------------

$out1 = $service->call('q');
pdp_assert_eq('result:q', $out1, 'happy path returns real result');

$service->call('q');
$service->call('q');
pdp_assert_true(in_array('start: q', $log->messages, true), 'logger fired');
$counting = (function () use ($service): CountingService {
    // unwrap to verify cache; production code wouldn't do this
    $r = new \ReflectionProperty(LoggingProxy::class, 'inner');
    $auth = $r->getValue($service);
    $r = new \ReflectionProperty(AuthProxy::class, 'inner');
    $cache = $r->getValue($auth);
    $r = new \ReflectionProperty(CachingProxy::class, 'inner');
    return $r->getValue($cache);
})();
pdp_assert_eq(1, $counting->calls, 'caching proxy stopped subsequent calls hitting the inner service');

// failed auth path
$denied = new LoggingProxy(new AuthProxy(new CountingService(), new DenyEverything()), $log);
pdp_assert_throws(Forbidden::class, fn () => $denied->call('x'), 'auth denial bubbles up');

pdp_done('Three small proxies beat one mega-proxy — see the comment block.');
