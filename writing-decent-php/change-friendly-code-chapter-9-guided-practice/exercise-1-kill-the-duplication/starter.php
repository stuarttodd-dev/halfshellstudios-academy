<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class OrderController
{
    /** @return array<string, string> */
    public function show(Order $o): array
    {
        return ['total' => '£' . number_format($o->totalInPence / 100, 2)];
    }
}

final class InvoicePdf
{
    public function render(Invoice $i): string
    {
        return 'Total: £' . number_format($i->totalInPence / 100, 2);
    }
}

final class ReportRow
{
    public function csvLine(int $totalInPence): string
    {
        return number_format($totalInPence / 100, 2);
    }
}

/* ---------- driver ---------- */

$order   = new Order(id: 1, totalInPence: 12_345);
$invoice = new Invoice(id: 99, totalInPence: 99);

echo "controller: ", json_encode((new OrderController())->show($order)), "\n";
echo "pdf:        ", (new InvoicePdf())->render($invoice), "\n";
echo "csv:        ", (new ReportRow())->csvLine(250_075), "\n";
echo "csv zero:   ", (new ReportRow())->csvLine(0), "\n";
