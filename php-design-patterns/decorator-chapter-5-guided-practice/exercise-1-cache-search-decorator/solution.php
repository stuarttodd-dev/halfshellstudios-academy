<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface SearchService
{
    /** @return list<string> */
    public function search(string $query): array;
}

/** Real-world subject: pretend this is the Elastic-backed implementation. */
final class FakeBackendSearchService implements SearchService
{
    public int $callCount = 0;
    public function search(string $query): array
    {
        $this->callCount++;
        return ["result for {$query} (call #{$this->callCount})"];
    }
}

/** Decorator: caches by query. Identical interface to the inner. */
final class CachingSearchService implements SearchService
{
    /** @var array<string, list<string>> */
    private array $cache = [];

    public function __construct(private readonly SearchService $inner) {}

    public function search(string $query): array
    {
        return $this->cache[$query] ??= $this->inner->search($query);
    }
}

/** Decorator: logs every search to whichever sink we hand it. */
final class LoggingSearchService implements SearchService
{
    /** @param list<string> $log */
    public function __construct(private readonly SearchService $inner, public array &$log) {}

    public function search(string $query): array
    {
        $this->log[] = "search: {$query}";
        return $this->inner->search($query);
    }
}

// ---- assertions -------------------------------------------------------------

// (1) Caching, in isolation, with a fake inner.
$inner = new FakeBackendSearchService();
$cached = new CachingSearchService($inner);
pdp_assert_eq(['result for php (call #1)'], $cached->search('php'), 'first call hits the inner');
pdp_assert_eq(['result for php (call #1)'], $cached->search('php'), 'second call returns the cached value');
pdp_assert_eq(1, $inner->callCount, 'inner was hit only once');

// (2) Logging, in isolation. Two independent log buffers prove no shared state.
$inner = new FakeBackendSearchService();
$log = [];
$logged = new LoggingSearchService($inner, $log);
$logged->search('foo');
$logged->search('bar');
pdp_assert_eq(['search: foo', 'search: bar'], $log, 'logging captures every call in order');

// (3) The composition root composes them. Order matters.
//     logging( caching( inner ) )  ->  log every CALL, even cache hits
$inner = new FakeBackendSearchService();
$log = [];
$stack = new LoggingSearchService(new CachingSearchService($inner), $log);
$stack->search('php');
$stack->search('php'); // cache hit
$stack->search('go');
pdp_assert_eq(['search: php', 'search: php', 'search: go'], $log, 'logging wraps caching: every call is logged, even cache hits');
pdp_assert_eq(2, $inner->callCount, 'cache short-circuited the second php call');

pdp_done();
