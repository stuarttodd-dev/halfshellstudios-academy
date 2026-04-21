<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * One class, seven jobs, seven dependencies. Every team that touches an
 * order touches this file: customer ops (place, cancel, refund), warehouse
 * (ship), search (reindex), finance (CSV export), and integrations (CRM).
 */
final class OrderManager
{
    public function __construct(
        public InMemoryOrderRepository $orders,
        public FakeCustomerDirectory   $customers,
        public RecordingMailer         $mailer,
        public FakeStripe              $stripe,
        public FakeShippingCarrier     $carrier,
        public FakeSearchIndex         $search,
        public FakeCrmClient           $crm,
    ) {}

    /** @param array{customer_id: int, lines: list<array{sku: string, qty: int, unit_price: int}>} $r */
    public function placeOrder(array $r): Order
    {
        $order = $this->orders->create($r['customer_id'], $r['lines']);
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} placed");
        return $order;
    }

    public function cancel(int $id): void
    {
        $order = $this->orders->find($id);
        $order->status = 'cancelled';
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} cancelled");
    }

    public function refund(int $id, int $amount): void
    {
        $order = $this->orders->find($id);
        $this->stripe->refund($order->id, $amount);
        $order->refundedInPence += $amount;
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} refunded {$amount}p");
    }

    public function ship(int $id): void
    {
        $order = $this->orders->find($id);
        $tracking = $this->carrier->dispatch($order);
        $order->shippedTrackingId = $tracking;
        $order->status = 'shipped';
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} shipped (tracking {$tracking})");
    }

    public function reindexSearch(int $id): void
    {
        $this->search->reindex($this->orders->find($id));
    }

    public function exportMonthlyCsv(int $month): string
    {
        $rows = ["id,customer_id,total_pence,status"];
        foreach ($this->orders->orders as $order) {
            $rows[] = sprintf('%d,%d,%d,%s', $order->id, $order->customerId, $order->totalInPence(), $order->status);
        }
        return implode("\n", $rows) . "\n[month={$month}]";
    }

    public function syncToCrm(int $id): void
    {
        $this->crm->syncOrder($this->orders->find($id));
    }
}

/* ---------- driver ---------- */

$repo      = new InMemoryOrderRepository();
$customers = new FakeCustomerDirectory();
$mailer    = new RecordingMailer();
$stripe    = new FakeStripe();
$carrier   = new FakeShippingCarrier();
$search    = new FakeSearchIndex();
$crm       = new FakeCrmClient();

$mgr = new OrderManager($repo, $customers, $mailer, $stripe, $carrier, $search, $crm);

$order = $mgr->placeOrder([
    'customer_id' => 9001,
    'lines'       => [['sku' => 'A1', 'qty' => 2, 'unit_price' => 500], ['sku' => 'B2', 'qty' => 1, 'unit_price' => 250]],
]);
$mgr->ship($order->id);
$mgr->refund($order->id, 250);
$mgr->reindexSearch($order->id);
$mgr->syncToCrm($order->id);
$mgr->cancel($order->id);

echo "mailer: " . json_encode($mailer->sent) . "\n";
echo "stripe.refunds: " . json_encode($stripe->refunds) . "\n";
echo "carrier.shipped: " . json_encode($carrier->shipped) . "\n";
echo "search.reindexed: " . json_encode($search->reindexed) . "\n";
echo "crm.synced: " . json_encode($crm->synced) . "\n";
echo "csv:\n" . $mgr->exportMonthlyCsv(month: 4) . "\n";
