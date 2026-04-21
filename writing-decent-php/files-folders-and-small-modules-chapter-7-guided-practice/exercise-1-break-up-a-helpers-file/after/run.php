<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use DecentPhp\Ch7\Ex1\Access\AdminCheck;
use DecentPhp\Ch7\Ex1\Audit\AuditLog;
use DecentPhp\Ch7\Ex1\Csv\CsvExporter;
use DecentPhp\Ch7\Ex1\Money\MoneyFormatter;
use DecentPhp\Ch7\Ex1\Notifications\ReceiptMailer;
use DecentPhp\Ch7\Ex1\Tax\VatCalculator;

Mailer::reset();
AuditStore::reset();

$csvPath = sys_get_temp_dir() . '/decent-php-ch7-ex1-after.csv';
@unlink($csvPath);

$receipts = new ReceiptMailer();
$audit    = new AuditLog();
$csv      = new CsvExporter();
$access   = new AdminCheck();

echo "gbp(12_345)         = ", MoneyFormatter::gbp(12_345), "\n";
echo "gbp(99)             = ", MoneyFormatter::gbp(99), "\n";
echo "calcVat(10000, GB)  = ", VatCalculator::calculate(10_000, 'GB'), "\n";
echo "calcVat(10000, DE)  = ", VatCalculator::calculate(10_000, 'DE'), "\n";
echo "calcVat(10000, US)  = ", VatCalculator::calculate(10_000, 'US'), "\n";

$receipts->emailFor(7);
$receipts->emailFor(11);

$audit->record('user.login',  ['user_id' => 1]);
$audit->record('order.placed', ['order_id' => 7, 'total_pence' => 12_345]);

echo "isAdmin(1)          = ", $access->isAdmin(1)  ? 'true'  : 'false', "\n";
echo "isAdmin(2)          = ", $access->isAdmin(2)  ? 'true'  : 'false', "\n";
echo "isAdmin(42)         = ", $access->isAdmin(42) ? 'true' : 'false', "\n";

$csv->export([['order_id', 'total'], [7, '£123.45'], [11, '£0.99']], $csvPath);
echo "csv contents:\n", file_get_contents($csvPath);

echo "mailer sent count   = ", count(Mailer::$sent), "\n";
echo "audit entries count = ", count(AuditStore::$entries), "\n";
