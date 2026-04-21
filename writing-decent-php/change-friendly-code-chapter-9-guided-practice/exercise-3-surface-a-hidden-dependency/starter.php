<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class ScheduleReport
{
    public function schedule(int $reportId): void
    {
        $now    = time();
        $offset = config('reporting.delay_seconds');
        $runAt  = $now + $offset;

        DB::table('jobs')->insert(['report_id' => $reportId, 'run_at' => $runAt]);

        Logger::log("scheduled report {$reportId} at {$runAt}");
    }
}

/* ---------- "test" against the starter ----------
 *
 * Notice everything we have to do to "test" this:
 *  - we cannot freeze time (we have to work around it)
 *  - we have to pre-populate $GLOBALS to fake the config
 *  - we have to reset two static arrays before the call
 *  - we have to assert against ranges, not exact values, because
 *    `time()` keeps moving
 */

DB::reset();
Logger::reset();
$GLOBALS['__config']['reporting.delay_seconds'] = 60;

$before = time();
(new ScheduleReport())->schedule(reportId: 42);
$after = time();

$insert = DB::$inserts[0]['values'] ?? null;

$ok =
       $insert !== null
    && $insert['report_id'] === 42
    && $insert['run_at']   >= $before + 60
    && $insert['run_at']   <= $after  + 60
    && count(Logger::$messages) === 1
    && str_contains(Logger::$messages[0], 'scheduled report 42 at ');

echo $ok ? "starter test: PASS (with global setup, range assertions)\n"
        : "starter test: FAIL\n";

echo "  inserts: ", json_encode(DB::$inserts), "\n";
echo "  logs:    ", json_encode(Logger::$messages), "\n";
