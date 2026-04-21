<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\OrderService;

final class OrderController
{
    public function __construct(private OrderService $service) {}

    public function store(int $customerId, int $totalInPence): int
    {
        return $this->service->placeOrder($customerId, $totalInPence)->id;
    }
}
