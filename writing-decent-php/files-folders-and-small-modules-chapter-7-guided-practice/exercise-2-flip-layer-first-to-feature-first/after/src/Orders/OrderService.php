<?php
declare(strict_types=1);

namespace App\Orders;

use App\Notifications\NotificationService;
use App\Orders\Persistence\OrderRepository;

final class OrderService
{
    public function __construct(
        private OrderRepository     $repository,
        private NotificationService $notifications,
    ) {}

    public function placeOrder(int $customerId, int $totalInPence): Order
    {
        $order = new Order($customerId, $totalInPence);
        $this->repository->save($order);

        $this->notifications->send("order.placed:{$order->id}");

        return $order;
    }
}
