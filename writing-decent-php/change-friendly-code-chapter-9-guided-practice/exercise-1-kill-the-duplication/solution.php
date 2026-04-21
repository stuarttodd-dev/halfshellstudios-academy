<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * The missing concept: a Money value object that owns the pence ↔ pounds
 * conversion and the formatting choices. Three call sites stop computing
 * `'£' . number_format($pence / 100, 2)` inline and start asking the
 * value object what it should look like.
 *
 * Designing it once in one file means:
 *  - the divisor is named (`PENCE_PER_POUND`)
 *  - the currency symbol lives in one constant
 *  - rounding/precision is decided once
 *  - changing how money looks is a one-line change
 */
final class Money
{
    private const CURRENCY_SYMBOL  = '£';
    private const DECIMAL_PLACES   = 2;
    private const PENCE_PER_POUND  = 100;

    public function __construct(public readonly int $pence) {}

    public static function fromPence(int $pence): self
    {
        return new self($pence);
    }

    public function formatWithSymbol(): string
    {
        return self::CURRENCY_SYMBOL . $this->formatBare();
    }

    public function formatBare(): string
    {
        return number_format($this->pence / self::PENCE_PER_POUND, self::DECIMAL_PLACES);
    }
}

final class OrderController
{
    /** @return array<string, string> */
    public function show(Order $o): array
    {
        return ['total' => Money::fromPence($o->totalInPence)->formatWithSymbol()];
    }
}

final class InvoicePdf
{
    public function render(Invoice $i): string
    {
        return 'Total: ' . Money::fromPence($i->totalInPence)->formatWithSymbol();
    }
}

final class ReportRow
{
    public function csvLine(int $totalInPence): string
    {
        return Money::fromPence($totalInPence)->formatBare();
    }
}

/* ---------- driver (identical to starter.php) ---------- */

$order   = new Order(id: 1, totalInPence: 12_345);
$invoice = new Invoice(id: 99, totalInPence: 99);

echo "controller: ", json_encode((new OrderController())->show($order)), "\n";
echo "pdf:        ", (new InvoicePdf())->render($invoice), "\n";
echo "csv:        ", (new ReportRow())->csvLine(250_075), "\n";
echo "csv zero:   ", (new ReportRow())->csvLine(0), "\n";
