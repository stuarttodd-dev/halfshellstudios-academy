<?php
declare(strict_types=1);

namespace App\OrderFulfilment\Domain;

/**
 * Fulfilment owns its own projection of an order, built from the
 * OrderConfirmedEvent. Notice we copy the data we care about — we do
 * not depend on Placement's Order entity.
 */
final class FulfilmentOrder
{
    /** @param list<array{productReference: string, quantity: int, weightInGrams: int}> $lines */
    public function __construct(
        public readonly int    $orderId,
        public readonly string $shippingAddress,
        public readonly array  $lines,
        public readonly string $status = 'awaiting-label',
    ) {}
}
