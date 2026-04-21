<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly string $country,
        public readonly int    $net,
    ) {}
}
