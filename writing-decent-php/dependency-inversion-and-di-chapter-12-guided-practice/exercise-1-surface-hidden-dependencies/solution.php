<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Four hidden dependencies → four interfaces → four constructor
 * parameters. The class no longer lies about what it needs.
 *
 * Notice that the abstractions are written from the **caller's** point
 * of view: `Clock::now()`, `TenantProvider::current()`, `JobQueue::push()`,
 * `EventLogger::info()`. We do not invert "the thing that lets us call
 * static methods on `Queue`" — we invert "the operation we actually
 * need". That is the lesson of "interface at the right boundary"
 * applied to globals.
 */

interface Clock
{
    public function now(): DateTimeImmutable;
}

interface TenantProvider
{
    public function current(): Tenant;
}

interface JobQueue
{
    /** @param array<string, mixed> $payload */
    public function push(string $queue, array $payload): void;
}

interface EventLogger
{
    public function info(string $message): void;
}

final class ScheduleNewsletter
{
    public function __construct(
        private Clock          $clock,
        private TenantProvider $tenants,
        private JobQueue       $queue,
        private EventLogger    $logger,
    ) {}

    public function schedule(string $subject, string $body): void
    {
        $when   = $this->clock->now()->modify('+1 hour');
        $tenant = $this->tenants->current();

        $this->queue->push('newsletter', [
            'subject' => $subject,
            'body'    => $body,
            'when'    => $when->format('c'),
            'tenant'  => $tenant->id,
        ]);

        $this->logger->info("scheduled newsletter for {$when->format('c')}");
    }
}

/* ---------- production adapters (used in the composition root) ---------- */

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final class ContextTenantProvider implements TenantProvider
{
    public function current(): Tenant
    {
        return TenantContext::current();
    }
}

final class GlobalQueueAdapter implements JobQueue
{
    /** @param array<string, mixed> $payload */
    public function push(string $queue, array $payload): void
    {
        Queue::push($queue, $payload);
    }
}

final class StaticLoggerAdapter implements EventLogger
{
    public function info(string $message): void
    {
        Logger::info($message);
    }
}

/* ---------- test doubles (used by the millisecond-fast test below) ---------- */

final class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $instant) {}

    public function now(): DateTimeImmutable
    {
        return $this->instant;
    }
}

final class StaticTenantProvider implements TenantProvider
{
    public function __construct(private Tenant $tenant) {}

    public function current(): Tenant
    {
        return $this->tenant;
    }
}

final class InMemoryJobQueue implements JobQueue
{
    /** @var list<array{queue: string, payload: array<string, mixed>}> */
    public array $jobs = [];

    /** @param array<string, mixed> $payload */
    public function push(string $queue, array $payload): void
    {
        $this->jobs[] = ['queue' => $queue, 'payload' => $payload];
    }
}

final class RecordingEventLogger implements EventLogger
{
    /** @var list<string> */
    public array $entries = [];

    public function info(string $message): void
    {
        $this->entries[] = "[info] {$message}";
    }
}

/* ---------- millisecond-fast test — no globals, exact assertions ---------- */

$clock   = new FixedClock(new DateTimeImmutable('2026-04-20T12:00:00+00:00'));
$tenants = new StaticTenantProvider(new Tenant(id: 42));
$queue   = new InMemoryJobQueue();
$logger  = new RecordingEventLogger();

$useCase = new ScheduleNewsletter($clock, $tenants, $queue, $logger);
$useCase->schedule('Hi', 'Welcome aboard');

assert($queue->jobs === [[
    'queue'   => 'newsletter',
    'payload' => [
        'subject' => 'Hi',
        'body'    => 'Welcome aboard',
        'when'    => '2026-04-20T13:00:00+00:00',
        'tenant'  => 42,
    ],
]]);
assert($logger->entries === ['[info] scheduled newsletter for 2026-04-20T13:00:00+00:00']);

echo "solution ran. job: " . json_encode($queue->jobs[0]) . "\n";
echo "solution ran. log: " . json_encode($logger->entries) . "\n";
echo "(notice: 'when' is exactly 2026-04-20T13:00:00+00:00 on every run)\n";
