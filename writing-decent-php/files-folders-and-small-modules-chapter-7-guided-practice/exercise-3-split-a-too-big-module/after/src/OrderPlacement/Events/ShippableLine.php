<?php
declare(strict_types=1);

namespace App\OrderPlacement\Events;

/**
 * An immutable snapshot of one ordered line, in the shape Fulfilment needs.
 * Lives in OrderPlacement because Placement is the publisher: this is part
 * of its outward-facing contract.
 */
final class ShippableLine
{
    public function __construct(
        public readonly string $productReference,
        public readonly int    $quantity,
        public readonly int    $weightInGrams,
    ) {}
}
