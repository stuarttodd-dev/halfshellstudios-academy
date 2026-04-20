<?php
declare(strict_types=1);

namespace App\Billing;

final class OrderTotalCalculator
{
    private const VAT_MULTIPLIER = 1.2;

    public function __construct(private $database)
    {
    }

    public function findOrderAndTouchLastViewed(int $orderId): array
    {
        $orderRow = $this->database->query("SELECT * FROM orders WHERE id = $orderId")[0];
        $this->database->update('orders', ['id' => $orderId, 'last_viewed' => time()]);

        return $orderRow;
    }

    public function calculateTotalIncludingVatInPounds(array $order): float
    {
        $subtotalPounds = 0.0;

        foreach ($order['items'] as $item) {
            $subtotalPounds += $item['price'] * $item['qty'];
        }

        return $subtotalPounds * self::VAT_MULTIPLIER;
    }

    public function attachTotalAndMarkProcessed(array $order): array
    {
        $order['total']     = $this->calculateTotalIncludingVatInPounds($order);
        $order['processed'] = true;

        return $order;
    }
}
