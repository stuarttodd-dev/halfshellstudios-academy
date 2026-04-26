<?php

namespace App\Services\Pricing;

use App\Contracts\DiscountStrategy;

class PercentageDiscountStrategy implements DiscountStrategy
{
    public function __construct(private readonly int $percentOff) {}

    public function apply(int $subtotalPence): int
    {
        return (int) round($subtotalPence * (100 - $this->percentOff) / 100);
    }
}
