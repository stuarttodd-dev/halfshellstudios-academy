<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;

final class InvoiceService
{
    public function __construct(
        private InvoiceRepository   $invoices,
        private OrderRepository     $orders,
        private NotificationService $notifications,
    ) {}

    public function issueFor(int $orderId): Invoice
    {
        $order   = $this->orders->find($orderId);
        $invoice = new Invoice($orderId, $order->totalInPence);
        $this->invoices->save($invoice);

        $this->notifications->send("invoice.issued:{$invoice->id}");

        return $invoice;
    }
}
