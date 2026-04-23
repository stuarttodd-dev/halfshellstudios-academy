<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** A tiny stand-in for TCPDF, with a write history so the test can inspect it. */
final class TCPDF
{
    /** @var list<string> */
    public array $calls = [];
    public function SetCreator(string $c): void   { $this->calls[] = "creator:{$c}"; }
    public function SetAuthor(string $a): void    { $this->calls[] = "author:{$a}"; }
    public function SetTitle(string $t): void     { $this->calls[] = "title:{$t}"; }
    public function SetMargins(int $l, int $t, int $r): void { $this->calls[] = "margins:{$l},{$t},{$r}"; }
    public function SetFont(string $family, string $style, int $size): void { $this->calls[] = "font:{$family}/{$style}/{$size}"; }
    public function AddPage(): void               { $this->calls[] = 'add-page'; }
    public function writeHTMLCell(string $body): void { $this->calls[] = "html:{$body}"; }
    public function Output(string $mode): string  { return "%PDF-bytes(" . count($this->calls) . ")"; }
}

interface InvoicePdfGenerator
{
    public function generate(object $invoice): string;
}

/**
 * Facade: hides the entire TCPDF setup behind one method.
 * Callers pass an Invoice and get bytes back. Done.
 */
final class TcpdfInvoicePdfGenerator implements InvoicePdfGenerator
{
    public function __construct(
        private readonly TCPDF $pdf,
        private readonly string $creator = 'Acme',
        private readonly string $author = 'Acme',
    ) {}

    public function generate(object $invoice): string
    {
        $this->pdf->SetCreator($this->creator);
        $this->pdf->SetAuthor($this->author);
        $this->pdf->SetTitle("Invoice {$invoice->number}");
        $this->pdf->SetMargins(15, 27, 15);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->AddPage();
        $this->pdf->writeHTMLCell($this->renderBody($invoice));
        return $this->pdf->Output('S');
    }

    private function renderBody(object $invoice): string
    {
        return "<h1>Invoice {$invoice->number}</h1><p>Total: £{$invoice->total}</p>";
    }
}

/** Caller is now two lines: ask the facade, return the bytes. */
final class InvoiceController
{
    public function __construct(private readonly InvoicePdfGenerator $generator) {}
    public function download(object $invoice): string
    {
        return $this->generator->generate($invoice);
    }
}

// ---- assertions -------------------------------------------------------------

$invoice = (object) ['number' => 'INV-001', 'total' => 99.50];

// (1) Facade test: it drives the subsystem in the right order, with the right values.
$pdf = new TCPDF();
$gen = new TcpdfInvoicePdfGenerator($pdf);
$bytes = $gen->generate($invoice);
pdp_assert_eq([
    'creator:Acme',
    'author:Acme',
    'title:Invoice INV-001',
    'margins:15,27,15',
    'font:helvetica//10',
    'add-page',
    'html:<h1>Invoice INV-001</h1><p>Total: £99.5</p>',
], $pdf->calls, 'facade drives TCPDF in the right order with the right values');
pdp_assert_eq('%PDF-bytes(7)', $bytes, 'facade returns the subsystem output as the result');

// (2) Caller test: it depends on the facade interface and is trivially testable.
$stub = new class implements InvoicePdfGenerator {
    public function generate(object $invoice): string { return "stub-bytes:{$invoice->number}"; }
};
$controller = new InvoiceController($stub);
pdp_assert_eq('stub-bytes:INV-001', $controller->download($invoice), 'controller depends on the facade only');

pdp_done();
