<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Notifications;

use Mailer;

final class ReceiptMailer
{
    public function emailFor(int $orderId): void
    {
        Mailer::send("receipt-for-order-{$orderId}@example.com", "Receipt for order #{$orderId}");
    }
}
