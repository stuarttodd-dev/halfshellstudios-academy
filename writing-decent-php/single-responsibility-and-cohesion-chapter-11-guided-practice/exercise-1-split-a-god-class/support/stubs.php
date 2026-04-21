<?php
declare(strict_types=1);

/* ---------- domain ---------- */

final class Order
{
    /** @param list<array{sku: string, qty: int, unit_price: int}> $lines */
    public function __construct(
        public readonly int    $id,
        public readonly int    $customerId,
        public readonly array  $lines,
        public string          $status,
        public int             $refundedInPence = 0,
        public ?string         $shippedTrackingId = null,
    ) {}

    public function totalInPence(): int
    {
        $total = 0;
        foreach ($this->lines as $line) {
            $total += $line['qty'] * $line['unit_price'];
        }
        return $total;
    }
}

/* ---------- in-memory infrastructure ---------- */

final class InMemoryOrderRepository
{
    /** @var array<int, Order> */
    public array $orders = [];
    private int  $next   = 7000;

    /** @param list<array{sku: string, qty: int, unit_price: int}> $lines */
    public function create(int $customerId, array $lines): Order
    {
        $order = new Order(id: $this->next++, customerId: $customerId, lines: $lines, status: 'placed');
        $this->orders[$order->id] = $order;
        return $order;
    }

    public function find(int $id): Order
    {
        return $this->orders[$id] ?? throw new RuntimeException("Order {$id} not found");
    }
}

final class RecordingMailer
{
    /** @var list<array{to: string, subject: string}> */
    public array $sent = [];

    public function send(string $to, string $subject): void
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject];
    }
}

final class FakeStripe
{
    /** @var list<array{order_id: int, amount: int}> */
    public array $refunds = [];

    public function refund(int $orderId, int $amountInPence): string
    {
        $this->refunds[] = ['order_id' => $orderId, 'amount' => $amountInPence];
        return sprintf('re_%05d', count($this->refunds));
    }
}

final class FakeShippingCarrier
{
    /** @var list<int> */
    public array $shipped = [];

    public function dispatch(Order $order): string
    {
        $this->shipped[] = $order->id;
        return sprintf('TRK%07d', $order->id);
    }
}

final class FakeSearchIndex
{
    /** @var list<int> */
    public array $reindexed = [];

    public function reindex(Order $order): void
    {
        $this->reindexed[] = $order->id;
    }
}

final class FakeCrmClient
{
    /** @var list<int> */
    public array $synced = [];

    public function syncOrder(Order $order): void
    {
        $this->synced[] = $order->id;
    }
}

final class FakeCustomerDirectory
{
    /** @var array<int, string> */
    public array $emails = [
        9001 => 'alice@example.com',
        9002 => 'bob@example.com',
    ];

    public function emailFor(int $customerId): string
    {
        return $this->emails[$customerId] ?? throw new RuntimeException("Unknown customer {$customerId}");
    }
}
