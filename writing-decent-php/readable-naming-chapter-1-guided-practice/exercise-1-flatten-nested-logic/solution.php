<?php
declare(strict_types=1);

function sendReceiptIfNeeded(array $order, array &$sentEmails): void
{
    if (($order['is_paid'] ?? false) !== true) {
        return;
    }

    $email = (string) ($order['customer_email'] ?? '');
    if ($email === '') {
        return;
    }

    $sentEmails[] = $email;
}

$sent = [];
sendReceiptIfNeeded(['is_paid' => true,  'customer_email' => 'sam@example.com'], $sent);
sendReceiptIfNeeded(['is_paid' => false, 'customer_email' => 'nope@example.com'], $sent);
sendReceiptIfNeeded(['is_paid' => true,  'customer_email' => ''], $sent);

var_export($sent);
echo "\n";
