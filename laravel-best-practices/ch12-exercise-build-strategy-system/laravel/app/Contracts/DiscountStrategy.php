<?php

namespace App\Contracts;

interface DiscountStrategy
{
    public function apply(int $subtotalPence): int;
}
