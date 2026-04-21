<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

Mailer::reset();
AuditStore::reset();

$csvPath = sys_get_temp_dir() . '/decent-php-ch7-ex1-before.csv';
@unlink($csvPath);

echo "gbp(12_345)         = ", gbp(12_345), "\n";
echo "gbp(99)             = ", gbp(99), "\n";
echo "calcVat(10000, GB)  = ", calcVat(10_000, 'GB'), "\n";
echo "calcVat(10000, DE)  = ", calcVat(10_000, 'DE'), "\n";
echo "calcVat(10000, US)  = ", calcVat(10_000, 'US'), "\n";

emailReceipt(7);
emailReceipt(11);

logAudit('user.login',  ['user_id' => 1]);
logAudit('order.placed', ['order_id' => 7, 'total_pence' => 12_345]);

echo "isAdmin(1)          = ", isAdmin(1)  ? 'true'  : 'false', "\n";
echo "isAdmin(2)          = ", isAdmin(2)  ? 'true'  : 'false', "\n";
echo "isAdmin(42)         = ", isAdmin(42) ? 'true' : 'false', "\n";

exportCsv([['order_id', 'total'], [7, '£123.45'], [11, '£0.99']], $csvPath);
echo "csv contents:\n", file_get_contents($csvPath);

echo "mailer sent count   = ", count(Mailer::$sent), "\n";
echo "audit entries count = ", count(AuditStore::$entries), "\n";
