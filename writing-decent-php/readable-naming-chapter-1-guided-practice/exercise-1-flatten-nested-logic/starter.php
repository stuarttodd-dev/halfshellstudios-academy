<?php
declare(strict_types=1);

function sendReceiptIfNeeded(array $order, array &$sentEmails): void
{
    if (($order['is_paid'] ?? false) === true) {
        if (($order['customer_email'] ?? '') !== '') {
            $sentEmails[] = $order['customer_email'];
        }
    }
}

$sent = [];
sendReceiptIfNeeded(['is_paid' => true,  'customer_email' => 'sam@example.com'], $sent);
sendReceiptIfNeeded(['is_paid' => false, 'customer_email' => 'nope@example.com'], $sent);

var_export($sent);
