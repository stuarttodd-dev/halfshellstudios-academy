<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/* ---- Implementor side: Renderer ---- */

interface Renderer
{
    /** @param list<array<string,mixed>> $rows */
    public function render(string $title, array $rows): string;
}

final class HtmlRenderer implements Renderer
{
    public function render(string $title, array $rows): string
    {
        $lines = ["<h1>{$title}</h1>", '<ul>'];
        foreach ($rows as $r) $lines[] = '<li>' . json_encode($r) . '</li>';
        $lines[] = '</ul>';
        return implode("\n", $lines);
    }
}

final class PdfRenderer implements Renderer
{
    public function render(string $title, array $rows): string
    {
        return "PDF[{$title}](" . count($rows) . " rows)";
    }
}

final class XlsxRenderer implements Renderer
{
    public function render(string $title, array $rows): string
    {
        return "XLSX[{$title}](" . count($rows) . " rows)";
    }
}

/* ---- Abstraction side: Report (holds a Renderer) ---- */

abstract class Report
{
    public function __construct(protected readonly Renderer $renderer) {}

    final public function output(): string
    {
        return $this->renderer->render($this->title(), $this->rows());
    }

    abstract protected function title(): string;
    /** @return list<array<string,mixed>> */
    abstract protected function rows(): array;
}

final class SalesReport extends Report
{
    protected function title(): string { return 'Sales'; }
    protected function rows(): array
    {
        return [['day' => 'mon', 'pence' => 12_300], ['day' => 'tue', 'pence' => 8_500]];
    }
}

final class StockReport extends Report
{
    protected function title(): string { return 'Stock'; }
    protected function rows(): array
    {
        return [['sku' => 'A1', 'qty' => 12], ['sku' => 'A2', 'qty' => 0]];
    }
}

// ---- assertions -------------------------------------------------------------

$html = new HtmlRenderer();
$pdf  = new PdfRenderer();
$xlsx = new XlsxRenderer();

pdp_assert_true(str_contains((new SalesReport($html))->output(), '<h1>Sales</h1>'), 'sales as html');
pdp_assert_eq('PDF[Sales](2 rows)',  (new SalesReport($pdf))->output(),  'sales as pdf');
pdp_assert_eq('PDF[Stock](2 rows)',  (new StockReport($pdf))->output(),  'stock as pdf');

// adding XLSX = ONE new class. Both reports get it for free.
pdp_assert_eq('XLSX[Sales](2 rows)', (new SalesReport($xlsx))->output(), 'sales as xlsx');
pdp_assert_eq('XLSX[Stock](2 rows)', (new StockReport($xlsx))->output(), 'stock as xlsx');

// new report = ONE new class. Every renderer works for it.
final class InventoryAgeReport extends Report
{
    protected function title(): string { return 'Inventory age'; }
    protected function rows(): array { return [['sku' => 'X1', 'days' => 90]]; }
}
pdp_assert_eq('PDF[Inventory age](1 rows)', (new InventoryAgeReport($pdf))->output(), 'new report works on every renderer');

// neither hierarchy mentions the other's concrete types: each Renderer
// implementation only depends on `string $title` and `array $rows`, never
// on a Report subclass — see their definitions above.

pdp_done();
