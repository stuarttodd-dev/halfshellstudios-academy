<?php

namespace App\Services\Pricing;

use App\Contracts\DiscountStrategy;

class NoDiscountStrategy implements DiscountStrategy
{
    public function apply(int $subtotalPence): int
    {
        return $subtotalPence;
    }
}
