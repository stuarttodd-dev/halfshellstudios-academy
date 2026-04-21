<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * DIP with restraint.
 *
 * We invert the dependency that genuinely hurts us — the Stripe SDK —
 * and we deliberately leave the database call alone. See the paragraph
 * below `IssueRefund` for why the bar for extracting an
 * `OrderRepositoryInterface` here is much higher than the bar for
 * extracting `PaymentGateway`.
 */

interface PaymentGateway
{
    public function refund(string $chargeId, int $amountInPence): string;
}

/** Production adapter — wraps the real Stripe SDK. */
final class StripePaymentGateway implements PaymentGateway
{
    public function __construct(private \Stripe\StripeClient $stripe) {}

    public function refund(string $chargeId, int $amountInPence): string
    {
        $refund = $this->stripe->refunds->create([
            'amount' => $amountInPence,
            'charge' => $chargeId,
        ]);

        return $refund->id;
    }
}

/** Recording fake — used by tests instead of hitting the network. */
final class RecordingPaymentGateway implements PaymentGateway
{
    /** @var list<array{charge: string, amount: int}> */
    public array $refunds = [];

    public function refund(string $chargeId, int $amountInPence): string
    {
        $this->refunds[] = ['charge' => $chargeId, 'amount' => $amountInPence];

        return 're_fake_' . count($this->refunds);
    }
}

final class IssueRefund
{
    public function __construct(private PaymentGateway $payments) {}

    public function refund(int $orderId, int $amount): void
    {
        $chargeId = DB::table('orders')->find($orderId)->stripe_id;

        $this->payments->refund($chargeId, $amount);

        DB::table('refunds')->insert(['order_id' => $orderId, 'amount' => $amount]);
    }
}

/*
 * ──────────────────────────────────────────────────────────────────────
 * Why we did NOT also extract `OrderRepositoryInterface` for the DB call
 * ──────────────────────────────────────────────────────────────────────
 *
 * Stripe is a third-party network call that costs money to exercise,
 * cannot run in CI without sandbox credentials, returns its own object
 * graph, and might be replaced by a different processor next year.
 * Inverting it through `PaymentGateway` buys us testability today and
 * substitutability tomorrow. The bar is met.
 *
 * The `orders` and `refunds` tables are ours. The schema was designed
 * by us, lives in our migrations, and is unlikely to be swapped for a
 * different storage engine. Wrapping `DB::table('orders')->find($id)`
 * in `OrderRepositoryInterface` would add a class, an interface, and a
 * test double per repository — and on the day we need it back we will
 * be inverting our own concrete to point at our own concrete. Until
 * the test pain is real, the indirection is friction without payoff.
 *
 * Concretely: extract DIP when the dependency you are inverting (a)
 * is hard to fake in tests, (b) is owned by someone other than us, or
 * (c) has a credible second implementation on the horizon. Stripe
 * scores on all three; `DB::table()` scores on none.
 */

/* ---------- driver — wired with the production adapter so output matches the starter ---------- */

$_ENV['STRIPE_KEY'] = 'sk_test_starter';

DB::reset();
DB::table('orders')->insert(['id' => 42, 'stripe_id' => 'ch_orig_42']);

$gateway = new StripePaymentGateway(new \Stripe\StripeClient(env('STRIPE_KEY')));
(new IssueRefund($gateway))->refund(orderId: 42, amount: 1500);

printf("refunds row -> %s\n", json_encode(DB::$tables['refunds']));
