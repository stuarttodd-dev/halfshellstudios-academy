<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: a Factory is the WRONG answer here.
 *
 * The starter constructs `new PdfRenderer()` exactly once, with zero
 * configuration, with a single concrete type. There is:
 *
 *   - no choice between implementations,
 *   - no construction recipe worth hiding,
 *   - no caller who wants to ask for "a renderer" without saying which.
 *
 * A `PdfRendererFactory::make(): PdfRenderer` is one line that wraps
 * one line. That is not a factory; it is a factory-shaped wrapper.
 *
 * The actual smell is *coupling* (the controller `new`s its
 * collaborator). The right fix is plain dependency injection — give
 * the controller a `PdfRenderer` (or, better, a `Renderer` interface
 * if we ever expect another format). No factory needed.
 */

interface Renderer
{
    public function render(object $invoice): string;
}

final class PdfRenderer implements Renderer
{
    public function render(object $invoice): string
    {
        return "%PDF-1.4 invoice #{$invoice->id}";
    }
}

/** Plain DI: the controller depends on the abstraction, no factory in sight. */
final class InvoiceController
{
    public function __construct(private readonly Renderer $renderer) {}
    public function download(object $invoice): string
    {
        return $this->renderer->render($invoice);
    }
}

// ---- assertions -------------------------------------------------------------

$invoice = (object) ['id' => 42];
$controller = new InvoiceController(new PdfRenderer());

pdp_assert_eq('%PDF-1.4 invoice #42', $controller->download($invoice), 'controller delegates to its injected renderer');

// And demonstrating *why* we did not need a factory: swapping the renderer is
// trivial without one. A factory would have been one extra layer to swap.
final class HtmlPreviewRenderer implements Renderer
{
    public function render(object $invoice): string { return "<h1>Invoice #{$invoice->id}</h1>"; }
}
$preview = new InvoiceController(new HtmlPreviewRenderer());
pdp_assert_eq('<h1>Invoice #42</h1>', $preview->download($invoice), 'a different Renderer just slots in');

pdp_done('(Factory Method was the wrong answer — see the comment block.)');
