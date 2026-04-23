<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface HttpClient
{
    public function get(string $url): string;
}

interface Clock
{
    public function nowMs(): int;
}

interface Metrics
{
    public function record(string $name, int $valueMs): void;
}

/** Test double — a programmable clock that advances by fixed amounts. */
final class FixedClock implements Clock
{
    public function __construct(private int $tMs = 0) {}
    public function advance(int $ms): void { $this->tMs += $ms; }
    public function nowMs(): int { return $this->tMs; }
}

final class RecordingMetrics implements Metrics
{
    /** @var list<array{name:string,valueMs:int}> */
    public array $records = [];
    public function record(string $name, int $valueMs): void { $this->records[] = compact('name', 'valueMs'); }
}

/**
 * Real-world subject. We make it programmable so the test can choose
 * how many failures it experiences before succeeding.
 */
final class FlakyHttpClient implements HttpClient
{
    public int $callCount = 0;
    /** @param Clock $clock advanced on each call to simulate latency */
    public function __construct(
        private readonly Clock $clock,
        private readonly int $perCallLatencyMs,
        private readonly int $failuresBeforeSuccess = 0,
    ) {}

    public function get(string $url): string
    {
        $this->callCount++;
        $this->clock->nowMs(); // touch for determinism
        if ($this->clock instanceof FixedClock) $this->clock->advance($this->perCallLatencyMs);
        if ($this->callCount <= $this->failuresBeforeSuccess) {
            throw new \RuntimeException("transient failure ({$this->callCount})");
        }
        return "body-of:{$url}";
    }
}

/** Decorator: records the duration of each call to a metrics service. */
final class TimingHttpClient implements HttpClient
{
    public function __construct(
        private readonly HttpClient $inner,
        private readonly Clock $clock,
        private readonly Metrics $metrics,
        private readonly string $metricName = 'http.get',
    ) {}

    public function get(string $url): string
    {
        $start = $this->clock->nowMs();
        try {
            return $this->inner->get($url);
        } finally {
            $this->metrics->record($this->metricName, $this->clock->nowMs() - $start);
        }
    }
}

/** Decorator: retries up to $maxAttempts on RuntimeException. */
final class RetryingHttpClient implements HttpClient
{
    public function __construct(
        private readonly HttpClient $inner,
        private readonly int $maxAttempts = 3,
    ) {}

    public function get(string $url): string
    {
        $attempt = 0;
        while (true) {
            $attempt++;
            try {
                return $this->inner->get($url);
            } catch (\RuntimeException $e) {
                if ($attempt >= $this->maxAttempts) throw $e;
            }
        }
    }
}

// ---- assertions -------------------------------------------------------------

// (1) Each decorator is testable with a fake inner.
//     Just timing + a fast inner = single record.
$clock = new FixedClock();
$inner = new FlakyHttpClient($clock, perCallLatencyMs: 50);
$metrics = new RecordingMetrics();
$timed = new TimingHttpClient($inner, $clock, $metrics);
$timed->get('https://x');
pdp_assert_eq([['name' => 'http.get', 'valueMs' => 50]], $metrics->records, 'timing records the inner duration');

//     Just retry + a flaky inner = success after retries.
$clock = new FixedClock();
$inner = new FlakyHttpClient($clock, perCallLatencyMs: 50, failuresBeforeSuccess: 2);
$retried = new RetryingHttpClient($inner, maxAttempts: 3);
pdp_assert_eq('body-of:https://x', $retried->get('https://x'), 'retry recovers after two transient failures');
pdp_assert_eq(3, $inner->callCount, 'retry made exactly three attempts (2 failures + 1 success)');

// (2) ORDER MATTERS. timing(retry(inner)) measures total time including retries.
$clock = new FixedClock();
$inner = new FlakyHttpClient($clock, perCallLatencyMs: 50, failuresBeforeSuccess: 2);
$metrics = new RecordingMetrics();
$stack = new TimingHttpClient(new RetryingHttpClient($inner, maxAttempts: 3), $clock, $metrics);
pdp_assert_eq('body-of:https://x', $stack->get('https://x'), 'timing(retry(inner)) returns the eventual success body');
pdp_assert_eq([['name' => 'http.get', 'valueMs' => 150]], $metrics->records, 'timing(retry) records 50+50+50 = total time INCLUDING retries');

// (3) Inverted order: retry(timing(inner)) records each attempt separately and sums to less.
$clock = new FixedClock();
$inner = new FlakyHttpClient($clock, perCallLatencyMs: 50, failuresBeforeSuccess: 2);
$metrics = new RecordingMetrics();
$stack = new RetryingHttpClient(new TimingHttpClient($inner, $clock, $metrics), maxAttempts: 3);
$stack->get('https://x');
pdp_assert_eq(3, count($metrics->records), 'retry(timing(inner)) records each attempt separately');
pdp_assert_eq([50, 50, 50], array_column($metrics->records, 'valueMs'), 'each attempt is timed individually');

pdp_done();
