<?php
declare(strict_types=1);

namespace App\Invoicing;

final class Invoice
{
    public ?int $id = null;

    public function __construct(public readonly int $orderId, public readonly int $totalInPence) {}
}
