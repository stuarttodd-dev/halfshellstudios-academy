<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Audiences and natural seams (decided BEFORE touching the code):
 *
 *   - Customer-facing lifecycle:
 *       PlaceOrder, CancelOrder, RefundOrder
 *       (each touches: orders, customers, mailer, +/- stripe)
 *   - Warehouse / fulfilment:
 *       ShipOrder
 *       (touches: orders, customers, mailer, carrier)
 *   - Search / read-side:
 *       ReindexOrderForSearch
 *       (touches: orders, search)
 *   - Reporting / finance:
 *       ExportMonthlyOrdersCsv
 *       (touches: orders only)
 *   - Integrations:
 *       SyncOrderToCrm
 *       (touches: orders, crm)
 *
 * The split is *one class per use case*, named by the verb. A thinner
 * cluster ("OrderLifecycleService" wrapping place/cancel/refund) is
 * tempting but would re-create the audience problem at half scale —
 * cancellations and refunds change for different reasons (SLA policy
 * vs payments policy). So we keep them apart.
 *
 * Notice each class declares only the collaborators it actually uses.
 * That is the cohesion test.
 */

/* ---------- customer-facing lifecycle ---------- */

final class PlaceOrder
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeCustomerDirectory   $customers,
        private RecordingMailer         $mailer,
    ) {}

    /** @param array{customer_id: int, lines: list<array{sku: string, qty: int, unit_price: int}>} $r */
    public function place(array $r): Order
    {
        $order = $this->orders->create($r['customer_id'], $r['lines']);
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} placed");
        return $order;
    }
}

final class CancelOrder
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeCustomerDirectory   $customers,
        private RecordingMailer         $mailer,
    ) {}

    public function cancel(int $id): void
    {
        $order = $this->orders->find($id);
        $order->status = 'cancelled';
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} cancelled");
    }
}

final class RefundOrder
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeCustomerDirectory   $customers,
        private RecordingMailer         $mailer,
        private FakeStripe              $stripe,
    ) {}

    public function refund(int $id, int $amount): void
    {
        $order = $this->orders->find($id);
        $this->stripe->refund($order->id, $amount);
        $order->refundedInPence += $amount;
        $email = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} refunded {$amount}p");
    }
}

/* ---------- warehouse / fulfilment ---------- */

final class ShipOrder
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeCustomerDirectory   $customers,
        private RecordingMailer         $mailer,
        private FakeShippingCarrier     $carrier,
    ) {}

    public function ship(int $id): void
    {
        $order    = $this->orders->find($id);
        $tracking = $this->carrier->dispatch($order);
        $order->shippedTrackingId = $tracking;
        $order->status            = 'shipped';
        $email                    = $this->customers->emailFor($order->customerId);
        $this->mailer->send($email, "Order #{$order->id} shipped (tracking {$tracking})");
    }
}

/* ---------- search / read-side ---------- */

final class ReindexOrderForSearch
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeSearchIndex         $search,
    ) {}

    public function reindex(int $id): void
    {
        $this->search->reindex($this->orders->find($id));
    }
}

/* ---------- reporting / finance ---------- */

final class ExportMonthlyOrdersCsv
{
    public function __construct(private InMemoryOrderRepository $orders) {}

    public function forMonth(int $month): string
    {
        $rows = ["id,customer_id,total_pence,status"];
        foreach ($this->orders->orders as $order) {
            $rows[] = sprintf('%d,%d,%d,%s', $order->id, $order->customerId, $order->totalInPence(), $order->status);
        }
        return implode("\n", $rows) . "\n[month={$month}]";
    }
}

/* ---------- integrations ---------- */

final class SyncOrderToCrm
{
    public function __construct(
        private InMemoryOrderRepository $orders,
        private FakeCrmClient           $crm,
    ) {}

    public function sync(int $id): void
    {
        $this->crm->syncOrder($this->orders->find($id));
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$repo      = new InMemoryOrderRepository();
$customers = new FakeCustomerDirectory();
$mailer    = new RecordingMailer();
$stripe    = new FakeStripe();
$carrier   = new FakeShippingCarrier();
$search    = new FakeSearchIndex();
$crm       = new FakeCrmClient();

$place   = new PlaceOrder($repo, $customers, $mailer);
$cancel  = new CancelOrder($repo, $customers, $mailer);
$refund  = new RefundOrder($repo, $customers, $mailer, $stripe);
$ship    = new ShipOrder($repo, $customers, $mailer, $carrier);
$reindex = new ReindexOrderForSearch($repo, $search);
$export  = new ExportMonthlyOrdersCsv($repo);
$sync    = new SyncOrderToCrm($repo, $crm);

$order = $place->place([
    'customer_id' => 9001,
    'lines'       => [['sku' => 'A1', 'qty' => 2, 'unit_price' => 500], ['sku' => 'B2', 'qty' => 1, 'unit_price' => 250]],
]);
$ship->ship($order->id);
$refund->refund($order->id, 250);
$reindex->reindex($order->id);
$sync->sync($order->id);
$cancel->cancel($order->id);

echo "mailer: " . json_encode($mailer->sent) . "\n";
echo "stripe.refunds: " . json_encode($stripe->refunds) . "\n";
echo "carrier.shipped: " . json_encode($carrier->shipped) . "\n";
echo "search.reindexed: " . json_encode($search->reindexed) . "\n";
echo "crm.synced: " . json_encode($crm->synced) . "\n";
echo "csv:\n" . $export->forMonth(month: 4) . "\n";
