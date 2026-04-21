<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class GenerateOrderSummary
{
    public function run(int $orderId): string
    {
        $order = Order::find($orderId);
        return json_encode([
            'reference' => 'ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'total'     => '£' . number_format($order->totalInPence / 100, 2),
            'items'     => $order->items->count(),
        ]);
    }
}

/* ---------- driver ---------- */

new Order(id: 1,    totalInPence: 1234,    items: new OrderItemCollection([(object) [], (object) []]));
new Order(id: 42,   totalInPence: 99_950,  items: new OrderItemCollection([(object) []]));
new Order(id: 9999, totalInPence: 250_000, items: new OrderItemCollection(array_fill(0, 12, (object) [])));

$useCase = new GenerateOrderSummary();
foreach ([1, 42, 9999] as $id) {
    echo $useCase->run($id), "\n";
}
