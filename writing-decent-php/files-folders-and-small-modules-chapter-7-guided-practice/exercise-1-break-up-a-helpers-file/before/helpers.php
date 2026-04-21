<?php
declare(strict_types=1);

require_once __DIR__ . '/stubs.php';

function gbp(int $pence): string
{
    return '£' . number_format($pence / 100, 2);
}

function emailReceipt(int $orderId): void
{
    Mailer::send("receipt-for-order-{$orderId}@example.com", "Receipt for order #{$orderId}");
}

function exportCsv(array $rows, string $path): void
{
    $handle = fopen($path, 'w');
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);
}

function isAdmin(int $userId): bool
{
    return Db::fetchValue("SELECT is_admin FROM users WHERE id = {$userId}") === true;
}

function logAudit(string $message, array $context = []): void
{
    AuditStore::append($message, $context);
}

function calcVat(int $netInPence, string $country): int
{
    return match ($country) {
        'GB', 'IE' => (int) round($netInPence * 0.20),
        'DE'       => (int) round($netInPence * 0.19),
        'FR'       => (int) round($netInPence * 0.20),
        default    => 0,
    };
}
