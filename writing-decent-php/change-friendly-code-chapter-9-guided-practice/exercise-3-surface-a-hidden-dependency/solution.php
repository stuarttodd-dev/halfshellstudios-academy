<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * The three previously-hidden dependencies, each behind a tiny interface:
 *  - Clock — replaces `time()`
 *  - DelayConfig — replaces `config('reporting.delay_seconds')`
 *  - Logger (instance, not the static facade) — replaces `Logger::log`
 *
 * In production you would inject the system implementations via your DI
 * container; in tests you inject the deterministic ones below.
 */
interface Clock
{
    public function now(): int;
}

interface DelayConfig
{
    public function delaySeconds(): int;
}

interface ReportLogger
{
    public function log(string $message): void;
}

interface JobsRepository
{
    /** @param array<string, mixed> $values */
    public function insert(array $values): void;
}

/* ---------- production wiring (still backed by the globals) ---------- */

final class SystemClock implements Clock
{
    public function now(): int { return time(); }
}

final class GlobalConfigDelayConfig implements DelayConfig
{
    public function delaySeconds(): int { return (int) config('reporting.delay_seconds'); }
}

final class StaticLoggerAdapter implements ReportLogger
{
    public function log(string $message): void { Logger::log($message); }
}

final class JobsTableRepository implements JobsRepository
{
    public function insert(array $values): void
    {
        DB::table('jobs')->insert($values);
    }
}

/* ---------- test wiring (no global state) ---------- */

final class FixedClock implements Clock
{
    public function __construct(private int $now) {}
    public function now(): int { return $this->now; }
}

final class StaticDelayConfig implements DelayConfig
{
    public function __construct(private int $seconds) {}
    public function delaySeconds(): int { return $this->seconds; }
}

final class RecordingLogger implements ReportLogger
{
    /** @var list<string> */
    public array $messages = [];

    public function log(string $message): void { $this->messages[] = $message; }
}

final class InMemoryJobsRepository implements JobsRepository
{
    /** @var list<array<string, mixed>> */
    public array $inserted = [];

    public function insert(array $values): void { $this->inserted[] = $values; }
}

/* ---------- the use case, with every dependency declared ---------- */

final class ScheduleReport
{
    public function __construct(
        private Clock          $clock,
        private DelayConfig    $config,
        private ReportLogger   $logger,
        private JobsRepository $jobs,
    ) {}

    public function schedule(int $reportId): void
    {
        $runAt = $this->clock->now() + $this->config->delaySeconds();

        $this->jobs->insert(['report_id' => $reportId, 'run_at' => $runAt]);

        $this->logger->log("scheduled report {$reportId} at {$runAt}");
    }
}

/* ---------- one-line test, no global state ---------- */

$clock  = new FixedClock(now: 1_700_000_000);
$config = new StaticDelayConfig(seconds: 60);
$logger = new RecordingLogger();
$jobs   = new InMemoryJobsRepository();

(new ScheduleReport($clock, $config, $logger, $jobs))->schedule(reportId: 42);

$ok =
       $jobs->inserted   === [['report_id' => 42, 'run_at' => 1_700_000_060]]
    && $logger->messages === ['scheduled report 42 at 1700000060'];

echo $ok ? "solution test: PASS (no \$GLOBALS, no static reset, exact assertions)\n"
        : "solution test: FAIL\n";

echo "  inserts: ", json_encode($jobs->inserted), "\n";
echo "  logs:    ", json_encode($logger->messages), "\n";
