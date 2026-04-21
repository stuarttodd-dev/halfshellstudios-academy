<?php
declare(strict_types=1);

namespace App\Invoicing\Http;

use App\Invoicing\InvoiceService;

final class InvoiceController
{
    public function __construct(private InvoiceService $service) {}

    public function create(int $orderId): int
    {
        return $this->service->issueFor($orderId)->id;
    }
}
