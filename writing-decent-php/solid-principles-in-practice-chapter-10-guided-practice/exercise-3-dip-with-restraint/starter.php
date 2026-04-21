<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

$_ENV['STRIPE_KEY'] = 'sk_test_starter';

DB::reset();
DB::table('orders')->insert(['id' => 42, 'stripe_id' => 'ch_orig_42']);

final class IssueRefund
{
    public function refund(int $orderId, int $amount): void
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_KEY'));
        $stripe->refunds->create(['amount' => $amount, 'charge' => DB::table('orders')->find($orderId)->stripe_id]);
        DB::table('refunds')->insert(['order_id' => $orderId, 'amount' => $amount]);
    }
}

(new IssueRefund())->refund(orderId: 42, amount: 1500);

printf("refunds row -> %s\n", json_encode(DB::$tables['refunds']));
