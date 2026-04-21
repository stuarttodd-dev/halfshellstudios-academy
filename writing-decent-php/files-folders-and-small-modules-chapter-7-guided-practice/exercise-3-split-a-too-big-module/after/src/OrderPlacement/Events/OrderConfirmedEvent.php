<?php
declare(strict_types=1);

namespace App\OrderPlacement\Events;

use App\OrderPlacement\Domain\OrderId;

/**
 * The single piece of public surface between OrderPlacement and any other
 * module (most importantly OrderFulfilment). If you want to know what
 * Placement promises to the rest of the system, read this class — it is
 * the contract.
 */
final class OrderConfirmedEvent
{
    /** @param list<ShippableLine> $lines */
    public function __construct(
        public readonly OrderId $orderId,
        public readonly string  $shippingAddress,
        public readonly array   $lines,
    ) {}
}
