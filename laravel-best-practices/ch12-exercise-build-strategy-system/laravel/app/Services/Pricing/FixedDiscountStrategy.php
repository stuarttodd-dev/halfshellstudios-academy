<?php

namespace App\Services\Pricing;

use App\Contracts\DiscountStrategy;

class FixedDiscountStrategy implements DiscountStrategy
{
    public function __construct(private readonly int $offPence) {}

    public function apply(int $subtotalPence): int
    {
        return max(0, $subtotalPence - $this->offPence);
    }
}
