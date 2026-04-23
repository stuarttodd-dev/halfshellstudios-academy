<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: Observer is the WRONG answer here.
 *
 * The starter:
 *
 *   $result = $this->gateway->charge($order->customer, $order->totalInPence);
 *   $this->orders->markPaid($order->id, $result->transactionId);
 *   return $result;
 *
 * is **NOT fan-out**. It is two steps of one atomic operation:
 *
 *   1) authorise + capture money at the gateway,
 *   2) record that capture against the order.
 *
 * Both steps must happen, or the money is taken and the order is not
 * marked paid (or vice versa) — i.e. the system lies. They are NOT
 * independent reactions to "a payment occurred"; they are the
 * payment.
 *
 * Forcing this into Observer makes things worse:
 *
 *   - "fire-and-forget" subscribers cannot easily roll back if one
 *     step fails;
 *   - the contract becomes "we charged you, then *eventually* the
 *     order may be marked paid";
 *   - error handling, ordering, and transactions become spread across
 *     subscribers no caller can read in one place.
 *
 * The right shape: keep the imperative use case. If we want to react
 * to "a payment was captured" with secondary work (send a receipt,
 * update analytics, fan out to a CRM), THAT is when an event makes
 * sense — *after* the atomic operation completes successfully.
 */

interface PaymentGateway { public function charge(int $customerId, int $amountInPence): object; }
interface OrderRepository { public function markPaid(int $orderId, string $transactionId): void; }

final class FakeGateway implements PaymentGateway
{
    public function charge(int $customerId, int $amountInPence): object
    {
        if ($amountInPence <= 0) throw new \DomainException('cannot charge non-positive amount');
        return (object) ['transactionId' => "tx_{$customerId}_{$amountInPence}", 'amountInPence' => $amountInPence];
    }
}

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var array<int, string> */
    public array $paid = [];
    public function markPaid(int $orderId, string $transactionId): void { $this->paid[$orderId] = $transactionId; }
}

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderRepository $orders,
    ) {}

    public function charge(object $order): object
    {
        $result = $this->gateway->charge($order->customerId, $order->totalInPence);
        $this->orders->markPaid($order->id, $result->transactionId);
        return $result;
    }
}

// ---- assertions -------------------------------------------------------------

$gateway = new FakeGateway();
$orders  = new InMemoryOrderRepository();

$result = (new PaymentService($gateway, $orders))->charge(
    (object) ['id' => 1, 'customerId' => 42, 'totalInPence' => 5000]
);

pdp_assert_eq('tx_42_5000', $result->transactionId, 'gateway returned the expected tx id');
pdp_assert_eq('tx_42_5000', $orders->paid[1] ?? null, 'order was marked paid with the same tx id');

// And the failure mode that justifies keeping it imperative: a gateway
// failure must NOT result in an order silently marked paid.
$failingGateway = new class implements PaymentGateway {
    public function charge(int $customerId, int $amountInPence): object { throw new \RuntimeException('declined'); }
};
$orders2 = new InMemoryOrderRepository();
pdp_assert_throws(
    \RuntimeException::class,
    fn () => (new PaymentService($failingGateway, $orders2))->charge((object) ['id' => 9, 'customerId' => 1, 'totalInPence' => 100]),
    'gateway failure propagates',
);
pdp_assert_eq([], $orders2->paid, 'no order was marked paid when the gateway failed');

pdp_done('(Observer was the wrong answer — see the comment block.)');
