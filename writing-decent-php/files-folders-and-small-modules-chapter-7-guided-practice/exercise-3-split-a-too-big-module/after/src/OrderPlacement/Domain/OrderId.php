<?php
declare(strict_types=1);

namespace App\OrderPlacement\Domain;

final class OrderId
{
    public function __construct(public readonly int $value) {}

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
