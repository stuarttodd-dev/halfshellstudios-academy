<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * Sync vs async — explicit choice per subscriber.
 *
 *   - search index update   -> SYNC.   Search results lying about
 *     stock for even a few seconds is a checkout-conversion problem.
 *   - cache invalidation    -> SYNC.   The cache MUST be coherent
 *     with the just-written stock level on the next request.
 *   - low-stock alert       -> QUEUED. The merchant doesn't need
 *     this in the request path; a few seconds latency is fine.
 *
 * We model "queued" as appending to an InMemoryJobQueue and asserting
 * the request path stayed fast. In production the queue is Redis /
 * SQS / Beanstalkd; the boundary is the same.
 */

interface InventoryRepository { public function adjust(int $productId, int $delta): int; }
interface SearchIndexClient   { public function updateStockLevel(int $productId, int $level): void; }
interface CacheStore          { public function forget(string $key): void; }
interface LowStockAlerter     { public function notify(int $productId, int $level): void; }

final class FakeInventoryRepository implements InventoryRepository
{
    /** @var array<int, int> */
    public array $levels = [];
    public function adjust(int $productId, int $delta): int { return $this->levels[$productId] = ($this->levels[$productId] ?? 0) + $delta; }
}
final class RecordingSearchClient implements SearchIndexClient { public array $updates = []; public function updateStockLevel(int $productId, int $level): void { $this->updates[$productId] = $level; } }
final class RecordingCacheStore   implements CacheStore         { public array $forgotten = []; public function forget(string $key): void { $this->forgotten[] = $key; } }
final class RecordingAlerter      implements LowStockAlerter    { public array $alerts = []; public function notify(int $productId, int $level): void { $this->alerts[] = compact('productId', 'level'); } }

/** Immutable event. */
final class StockLevelChanged
{
    public function __construct(
        public readonly int $productId,
        public readonly int $newLevel,
    ) {}
}

interface EventDispatcher { public function dispatch(object $event): void; }

final class InMemoryEventDispatcher implements EventDispatcher
{
    /** @var array<class-string, list<callable(object): void>> */
    private array $sync = [];
    /** @var array<class-string, list<callable(object): void>> */
    private array $async = [];

    /** @var list<callable(): void> */
    private array $jobQueue = [];

    public function subscribeSync(string $eventClass, callable $sub): void  { $this->sync[$eventClass][]  = $sub; }
    public function subscribeAsync(string $eventClass, callable $sub): void { $this->async[$eventClass][] = $sub; }

    public function dispatch(object $event): void
    {
        foreach ($this->sync[$event::class] ?? [] as $sub) $sub($event);
        foreach ($this->async[$event::class] ?? [] as $sub) $this->jobQueue[] = static fn () => $sub($event);
    }

    /** Drain the queue (would be the worker process in production). */
    public function runQueuedJobs(): int
    {
        $count = 0;
        while ($job = array_shift($this->jobQueue)) { $job(); $count++; }
        return $count;
    }
    public function pendingJobCount(): int { return count($this->jobQueue); }
}

/** Subscribers — one per reaction. */
final class UpdateSearchIndexOnStockChange
{
    public function __construct(private readonly SearchIndexClient $search) {}
    public function __invoke(StockLevelChanged $event): void { $this->search->updateStockLevel($event->productId, $event->newLevel); }
}
final class InvalidateCacheOnStockChange
{
    public function __construct(private readonly CacheStore $cache) {}
    public function __invoke(StockLevelChanged $event): void { $this->cache->forget("product:{$event->productId}"); }
}
final class AlertOnLowStock
{
    public function __construct(private readonly LowStockAlerter $alerter, private readonly int $threshold = 5) {}
    public function __invoke(StockLevelChanged $event): void
    {
        if ($event->newLevel < $this->threshold) $this->alerter->notify($event->productId, $event->newLevel);
    }
}

final class InventoryService
{
    public function __construct(
        private readonly InventoryRepository $inventory,
        private readonly EventDispatcher $events,
    ) {}

    public function adjust(int $productId, int $delta): void
    {
        $newLevel = $this->inventory->adjust($productId, $delta);
        $this->events->dispatch(new StockLevelChanged($productId, $newLevel));
    }
}

// ---- assertions -------------------------------------------------------------

$inventory = new FakeInventoryRepository();
$search    = new RecordingSearchClient();
$cache     = new RecordingCacheStore();
$alerter   = new RecordingAlerter();

$dispatcher = new InMemoryEventDispatcher();
$dispatcher->subscribeSync (StockLevelChanged::class, new UpdateSearchIndexOnStockChange($search));
$dispatcher->subscribeSync (StockLevelChanged::class, new InvalidateCacheOnStockChange($cache));
$dispatcher->subscribeAsync(StockLevelChanged::class, new AlertOnLowStock($alerter, threshold: 5));

$service = new InventoryService($inventory, $dispatcher);

// Adjust to a high level: sync subscribers run; async subscriber is queued but won't alert.
$service->adjust(productId: 100, delta: 20);
pdp_assert_eq([100 => 20], $search->updates,    'search updated synchronously');
pdp_assert_eq(['product:100'], $cache->forgotten, 'cache invalidated synchronously');
pdp_assert_eq([],   $alerter->alerts,            'no alert (level 20 >= threshold 5)');
pdp_assert_eq(1,    $dispatcher->pendingJobCount(), 'one job queued (the alerter)');

$dispatcher->runQueuedJobs();
pdp_assert_eq([], $alerter->alerts, 'alerter still silent after running the queue (level still high)');

// Adjust down to a low level: alerter should fire when the queue runs.
$service->adjust(productId: 100, delta: -17); // -> 3
pdp_assert_eq([100 => 3], $search->updates,    'search updated again');
pdp_assert_eq([],         $alerter->alerts,    'alerter does NOT run synchronously');
pdp_assert_eq(1, $dispatcher->runQueuedJobs(), 'one queued job ran');
pdp_assert_eq([['productId' => 100, 'level' => 3]], $alerter->alerts, 'alerter fired from the queue when level dropped below 5');

pdp_done();
