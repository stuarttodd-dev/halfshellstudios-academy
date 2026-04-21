<?php
declare(strict_types=1);

namespace App\OrderFulfilment\Persistence;

use App\OrderFulfilment\Domain\FulfilmentOrder;

final class FulfilmentRepository
{
    /** @var array<int, FulfilmentOrder> */
    private array $store = [];

    public function save(FulfilmentOrder $order): void
    {
        $this->store[$order->orderId] = $order;
    }

    public function find(int $orderId): FulfilmentOrder
    {
        return $this->store[$orderId] ?? throw new \RuntimeException("FulfilmentOrder {$orderId} not found");
    }

    public function count(): int
    {
        return count($this->store);
    }
}
