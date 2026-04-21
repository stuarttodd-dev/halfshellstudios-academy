<?php
declare(strict_types=1);

namespace App\Models;

final class Order
{
    public ?int $id = null;

    public function __construct(public readonly int $customerId, public readonly int $totalInPence) {}
}
