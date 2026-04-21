<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Four hidden dependencies in five lines:
 *   - the wall clock (`new DateTimeImmutable()`)
 *   - the current tenant (`TenantContext::current()`)
 *   - the job queue (`Queue::push`)
 *   - the logger (`Logger::info`)
 *
 * The constructor signature is empty, so the class lies about what it
 * needs to run. To "test" this you have to set `TenantContext::$current`,
 * inspect `Queue::$jobs`, inspect `Logger::$entries`, and accept that
 * the timestamp will be different on every run.
 */
final class ScheduleNewsletter
{
    public function schedule(string $subject, string $body): void
    {
        $when   = (new DateTimeImmutable())->modify('+1 hour');
        $tenant = TenantContext::current();

        Queue::push('newsletter', [
            'subject' => $subject,
            'body'    => $body,
            'when'    => $when->format('c'),
            'tenant'  => $tenant->id,
        ]);

        Logger::info("scheduled newsletter for {$when->format('c')}");
    }
}

/* ---------- "test" — needs global setup, has range assertions ---------- */

Queue::reset();
Logger::reset();
TenantContext::$current = new Tenant(id: 42);

$before = (new DateTimeImmutable())->modify('+1 hour')->getTimestamp();
(new ScheduleNewsletter())->schedule('Hi', 'Welcome aboard');
$after  = (new DateTimeImmutable())->modify('+1 hour')->getTimestamp();

$job = Queue::$jobs[0];
$jobWhen = (new DateTimeImmutable($job['payload']['when']))->getTimestamp();

assert($job['queue'] === 'newsletter');
assert($job['payload']['tenant'] === 42);
assert($jobWhen >= $before && $jobWhen <= $after, 'when must fall inside the test window');
assert(count(Logger::$entries) === 1);

echo "starter ran. job: " . json_encode(Queue::$jobs[0]) . "\n";
echo "starter ran. log: " . json_encode(Logger::$entries) . "\n";
echo "(notice: 'when' is whatever the wall clock said at runtime)\n";
