<?php
declare(strict_types=1);

require_once __DIR__ . '/subject.php';

/**
 * Characterisation test for the starter InvoiceGenerator.
 *
 * "Pin behaviour" means: whatever this code does today, keep doing it
 * tomorrow. We are not asserting that the behaviour is *correct* — we
 * are asserting that our refactors in Exercises 2 and 3 do not
 * accidentally change it.
 *
 * What we actually pin, for each scenario:
 *
 *   1. The array of invoice ids returned from `generate()`.
 *   2. The rows written to the in-memory `invoices` table
 *      (client_id, total, period).
 *   3. The body written to the "PDF" file on disk.
 *
 * What we explicitly do *not* pin:
 *
 *   - The wall-clock-dependent `period` string (we only pin that it
 *     matches `date('Y-m')` at the moment the test runs, which is what
 *     the starter will produce on any run).
 *   - Whether `mail()` was called. The starter uses the global `mail()`
 *     function and we cannot intercept it without `uopz` or a similar
 *     extension. We neuter it with
 *     `ini_set('sendmail_path', '/bin/true')` so the test does not try
 *     to post anything, and we accept that verifying the mail side-
 *     effect has to wait until Exercise 3 introduces an `InvoiceNotifier`
 *     collaborator we *can* record against.
 *
 * That caveat is not a weakness of the test; it is the test telling us
 * a real truth about the starter's design. You cannot cleanly
 * characterise a unit that reaches out to `mail()` directly. The
 * refactor to a `InvoiceNotifier` interface in Exercise 3 is what buys
 * us that testability.
 */

ini_set('sendmail_path', '/bin/true');

/**
 * @return array<string, array{
 *     clients:  list<object>,
 *     sessions: list<object>,
 *     expected: array{
 *         ids:      list<int>,
 *         invoices: list<array{client_id:int,total:int}>,
 *         pdfs:     array<int, string>,
 *     },
 * }>
 */
function characterisation_cases(): array
{
    // Two realistic scenarios, matching the brief: a premium GB client with
    // 10 hours of sessions, and a basic IE client with 5 hours of sessions.
    // We also seed one active client with zero sessions (Eve), to pin the
    // `if (count($sessions) === 0) continue;` skip branch.

    $alice = (object) [
        'id' => 1, 'name' => 'Alice Ltd',  'email' => 'billing@alice.example',
        'plan' => 'premium', 'country' => 'GB', 'active' => 1,
    ];
    $bob = (object) [
        'id' => 2, 'name' => 'Bob GmbH',   'email' => 'billing@bob.example',
        'plan' => 'basic',   'country' => 'IE', 'active' => 1,
    ];
    $eve = (object) [
        'id' => 3, 'name' => 'Eve & Co',   'email' => 'billing@eve.example',
        'plan' => 'premium', 'country' => 'GB', 'active' => 1,
    ];
    $charlie = (object) [
        'id' => 4, 'name' => 'Charlie Ltd', 'email' => 'billing@charlie.example',
        'plan' => 'basic',   'country' => 'GB', 'active' => 0, // inactive — skipped
    ];

    $currentMonth = date('Y-m');
    $midMonth     = "$currentMonth-15";

    return [
        'premium GB client (10h) + basic IE client (5h), one active-no-sessions client, one inactive client' => [
            'clients'  => [$alice, $bob, $eve, $charlie],
            'sessions' => [
                // Alice — 10 hours split over two sessions, premium GB
                (object) ['id' => 10, 'client_id' => 1, 'date' => $midMonth, 'hours' => 6],
                (object) ['id' => 11, 'client_id' => 1, 'date' => $midMonth, 'hours' => 4],
                // Bob — 5 hours over one session, basic IE
                (object) ['id' => 20, 'client_id' => 2, 'date' => $midMonth, 'hours' => 5],
                // Eve — zero sessions this month (skipped by the guard clause)
            ],
            'expected' => [
                // Alice: 10h * 150/h = 1500 subtotal; GB VAT 20% = 300; total 1800.
                // Bob:   5h  * 100/h = 500  subtotal; non-GB -> no VAT;  total 500.
                'ids'      => [1, 2],
                'invoices' => [
                    ['client_id' => 1, 'total' => 1800],
                    ['client_id' => 2, 'total' => 500],
                ],
                'pdfs' => [
                    1 => "Invoice #1\nClient: Alice Ltd\nHours: 10\nTotal: 1800",
                    2 => "Invoice #2\nClient: Bob GmbH\nHours: 5\nTotal: 500",
                ],
            ],
        ],
    ];
}

function assert_eq(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        $expectedStr = var_export($expected, true);
        $actualStr   = var_export($actual, true);
        echo "FAIL: {$message}\n  expected: {$expectedStr}\n  actual:   {$actualStr}\n";
        exit(1);
    }
}

function run_characterisation_test(): void
{
    $period = date('Y-m');

    foreach (characterisation_cases() as $name => $case) {
        DB::reset();
        DB::$clients  = $case['clients'];
        DB::$sessions = $case['sessions'];

        // Clean any leftover PDFs from previous runs of this test
        foreach ($case['expected']['pdfs'] as $id => $_) {
            @unlink("/tmp/invoice-{$id}.pdf");
        }

        $generator = new InvoiceGenerator();
        $ids       = $generator->generate();

        assert_eq($case['expected']['ids'], $ids, "[{$name}] returned ids");

        // Pin the invoices table
        $invoicesForComparison = array_map(
            static fn (array $row) => ['client_id' => $row['client_id'], 'total' => $row['total']],
            DB::$invoices,
        );
        assert_eq(
            $case['expected']['invoices'],
            $invoicesForComparison,
            "[{$name}] invoices table rows (client_id + total)",
        );

        // Pin the period column separately (wall-clock-sensitive)
        foreach (DB::$invoices as $row) {
            assert_eq($period, $row['period'], "[{$name}] invoice period for id {$row['id']}");
        }

        // Pin the PDF bodies
        foreach ($case['expected']['pdfs'] as $id => $expectedBody) {
            $path = "/tmp/invoice-{$id}.pdf";
            assert_eq(true, is_file($path), "[{$name}] PDF was written at {$path}");
            assert_eq($expectedBody, file_get_contents($path), "[{$name}] PDF body for id {$id}");
            @unlink($path);
        }

        echo "PASS: {$name}\n";
    }
}

run_characterisation_test();

echo "\nAll characterisation cases passed.\n";
