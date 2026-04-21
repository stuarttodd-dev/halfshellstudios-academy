<?php
declare(strict_types=1);

namespace App\Invoicing\Persistence;

use App\Invoicing\Invoice;

final class InvoiceRepository
{
    /** @var array<int, Invoice> */
    private static array $store = [];
    private static int   $next  = 5000;

    public function save(Invoice $invoice): void
    {
        $invoice->id              = self::$next++;
        self::$store[$invoice->id] = $invoice;
    }
}
