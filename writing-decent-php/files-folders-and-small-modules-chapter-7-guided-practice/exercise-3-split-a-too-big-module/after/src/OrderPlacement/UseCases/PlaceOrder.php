<?php
declare(strict_types=1);

namespace App\OrderPlacement\UseCases;

use App\OrderPlacement\Domain\OrderId;
use App\OrderPlacement\Events\EventBus;
use App\OrderPlacement\Events\OrderConfirmedEvent;
use App\OrderPlacement\Events\ShippableLine;

/**
 * The most boiled-down placement use case for the demo. Real Placement
 * would have payment, discounting, VAT, etc. — all encapsulated here, all
 * invisible to Fulfilment.
 */
final class PlaceOrder
{
    private static int $nextOrderId = 1000;

    public function __construct(private EventBus $events) {}

    /** @param list<ShippableLine> $lines */
    public function place(string $shippingAddress, array $lines): OrderId
    {
        $orderId = new OrderId(self::$nextOrderId++);

        $this->events->publish(new OrderConfirmedEvent($orderId, $shippingAddress, $lines));

        return $orderId;
    }
}
