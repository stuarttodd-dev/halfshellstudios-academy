<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly int $id,
        public readonly int $totalInPence,
    ) {}
}

final class Invoice
{
    public function __construct(
        public readonly int $id,
        public readonly int $totalInPence,
    ) {}
}
