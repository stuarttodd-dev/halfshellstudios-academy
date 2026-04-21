<?php
declare(strict_types=1);

require_once __DIR__ . '/subject.php';

/**
 * Unit test for the refactored InvoiceGenerator.
 *
 * Compare this file to `exercise-1-pin-behaviour/characterisation_test.php`
 * — that version had to:
 *
 *   - monkey-patch `sendmail_path` to neuter `mail()`
 *   - write real files to `/tmp` and clean up afterwards
 *   - accept a wall-clock-dependent `period` column
 *   - give up on verifying the mail side-effect entirely
 *
 * None of that is needed here. The generator now collaborates through
 * interfaces, so every side-effect is observable as a recorded call on
 * an in-memory double. The test runs in milliseconds, is fully
 * deterministic, and asserts every side-effect the generator is
 * responsible for.
 *
 * Same two scenarios as the Exercise 1 characterisation test: a
 * premium GB client (10h -> 1500 subtotal + 300 GB VAT = 1800), a
 * basic IE client (5h -> 500 subtotal, no VAT), plus an active
 * client with no sessions that must be skipped and an inactive
 * client that must not even be queried.
 */

function assert_eq(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        echo "FAIL: {$message}\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n";
        exit(1);
    }
}

function test_two_clients_happy_path(): void
{
    $clock  = new FixedClock(new DateTimeImmutable('2026-04-15 09:00:00'));
    $period = BillingPeriod::fromClock($clock);

    $alice = (object) [
        'id' => 1, 'name' => 'Alice Ltd',   'email' => 'billing@alice.example',
        'plan' => 'premium', 'country' => 'GB', 'active' => 1,
    ];
    $bob = (object) [
        'id' => 2, 'name' => 'Bob GmbH',    'email' => 'billing@bob.example',
        'plan' => 'basic',   'country' => 'IE', 'active' => 1,
    ];
    $eve = (object) [
        'id' => 3, 'name' => 'Eve & Co',    'email' => 'billing@eve.example',
        'plan' => 'premium', 'country' => 'GB', 'active' => 1,
    ];
    $charlie = (object) [
        'id' => 4, 'name' => 'Charlie Ltd', 'email' => 'billing@charlie.example',
        'plan' => 'basic',   'country' => 'GB', 'active' => 0,
    ];

    $clients = new InMemoryClientRepository([$alice, $bob, $eve, $charlie]);
    $sessions = new InMemorySessionRepository([
        (object) ['id' => 10, 'client_id' => 1, 'date' => '2026-04-15', 'hours' => 6],
        (object) ['id' => 11, 'client_id' => 1, 'date' => '2026-04-15', 'hours' => 4],
        (object) ['id' => 20, 'client_id' => 2, 'date' => '2026-04-15', 'hours' => 5],
        // Eve intentionally has no sessions -> guard clause must skip her.
        // Charlie is inactive -> repository must not even return him.
    ]);
    $invoices = new InMemoryInvoiceRepository();
    $store    = new RecordingInvoiceStore();
    $notifier = new RecordingInvoiceNotifier();

    $generator = new InvoiceGenerator($clients, $sessions, $invoices, $store, $notifier);

    $generated = $generator->generate($period);

    // Returned ids — identical to Exercise 1's characterisation test.
    assert_eq([1, 2], $generated, 'generate() returns the ids of the invoices that were actually created');

    // Invoice rows — same client_id / total / period as the starter produced.
    assert_eq(
        [
            ['id' => 1, 'client_id' => 1, 'total' => 1800, 'period' => '2026-04'],
            ['id' => 2, 'client_id' => 2, 'total' => 500,  'period' => '2026-04'],
        ],
        $invoices->saved,
        'invoice repository rows (client_id + total + period) match the Exercise 1 characterisation fixtures',
    );

    // Stored artefact bodies — byte-identical to the Exercise 1 PDF fixtures.
    assert_eq(
        [
            1 => "Invoice #1\nClient: Alice Ltd\nHours: 10\nTotal: 1800",
            2 => "Invoice #2\nClient: Bob GmbH\nHours: 5\nTotal: 500",
        ],
        $store->bodies,
        'InvoiceStore received the same bodies the starter wrote to /tmp/invoice-*.pdf',
    );

    // Notifications — one per generated invoice, to the right address,
    // with the right month label. This is the assertion we COULD NOT
    // make in Exercise 1 because `mail()` had no seam to observe.
    assert_eq(
        [
            ['email' => 'billing@alice.example', 'invoice_id' => 1, 'period' => 'April 2026'],
            ['email' => 'billing@bob.example',   'invoice_id' => 2, 'period' => 'April 2026'],
        ],
        $notifier->sent,
        'InvoiceNotifier was called once per generated invoice, with the correct client + period',
    );

    echo "PASS: two-clients happy path\n";
}

function test_no_active_clients_produces_no_invoices(): void
{
    $clock    = new FixedClock(new DateTimeImmutable('2026-04-15'));
    $period   = BillingPeriod::fromClock($clock);
    $inactive = (object) ['id' => 99, 'name' => 'Gone Ltd', 'email' => '', 'plan' => 'basic', 'country' => 'GB', 'active' => 0];

    $generator = new InvoiceGenerator(
        new InMemoryClientRepository([$inactive]),
        new InMemorySessionRepository([]),
        $invoices = new InMemoryInvoiceRepository(),
        $store    = new RecordingInvoiceStore(),
        $notifier = new RecordingInvoiceNotifier(),
    );

    assert_eq([], $generator->generate($period), 'generate() returns an empty list when there are no active clients');
    assert_eq([], $invoices->saved, 'no invoice rows were created');
    assert_eq([], $store->bodies,   'no artefacts were stored');
    assert_eq([], $notifier->sent,  'no notifications were sent');

    echo "PASS: no active clients -> no invoices\n";
}

function test_active_client_with_no_sessions_is_skipped(): void
{
    $clock  = new FixedClock(new DateTimeImmutable('2026-04-15'));
    $period = BillingPeriod::fromClock($clock);
    $eve    = (object) ['id' => 3, 'name' => 'Eve & Co', 'email' => 'billing@eve.example', 'plan' => 'premium', 'country' => 'GB', 'active' => 1];

    $generator = new InvoiceGenerator(
        new InMemoryClientRepository([$eve]),
        new InMemorySessionRepository([]), // active but no sessions
        $invoices = new InMemoryInvoiceRepository(),
        $store    = new RecordingInvoiceStore(),
        $notifier = new RecordingInvoiceNotifier(),
    );

    assert_eq([], $generator->generate($period), 'generate() skips active clients whose sessions list is empty');
    assert_eq([], $invoices->saved, 'no invoice was created for the skipped client');
    assert_eq([], $notifier->sent,  'no notification was sent for the skipped client');

    echo "PASS: active client with zero sessions is skipped (guard clause preserved)\n";
}

test_two_clients_happy_path();
test_no_active_clients_produces_no_invoices();
test_active_client_with_no_sessions_is_skipped();

echo "\nAll unit tests passed.\n";
