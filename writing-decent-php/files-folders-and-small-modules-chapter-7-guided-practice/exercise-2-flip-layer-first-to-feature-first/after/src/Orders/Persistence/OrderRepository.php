<?php
declare(strict_types=1);

namespace App\Orders\Persistence;

use App\Orders\Order;

final class OrderRepository
{
    /** @var array<int, Order> */
    private static array $store = [];
    private static int   $next  = 1000;

    public function save(Order $order): void
    {
        $order->id            = self::$next++;
        self::$store[$order->id] = $order;
    }

    public function find(int $id): Order
    {
        return self::$store[$id] ?? throw new \RuntimeException("Order {$id} not found");
    }
}
