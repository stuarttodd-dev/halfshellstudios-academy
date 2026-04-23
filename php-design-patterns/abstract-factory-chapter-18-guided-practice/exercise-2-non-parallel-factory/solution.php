<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — the families aren't parallel.
 *
 * The original interface:
 *
 *   interface ReportFactory {
 *       public function pdfRenderer():   PdfRenderer;
 *       public function csvRenderer():   CsvRenderer;
 *       public function excelRenderer(): ExcelRenderer; // some factories THROW
 *   }
 *
 * Smells:
 *   - "throws on this method" means the interface is broader than reality;
 *     callers have to know which methods are real on which factory.
 *   - PDF, CSV, Excel are *unrelated* products — they happen to render
 *     reports but they don't form a coherent family that varies together.
 *   - Every consumer of `ReportFactory` is implicitly forced to depend on
 *     all three types, even when it only needs one.
 *
 * The right move:
 *   (a) split into separate Factory Methods (one per renderer type),
 *       each chosen independently at the wiring layer, OR
 *   (b) drop the factory entirely and inject the concrete renderer the
 *       caller actually wants.
 *
 * Below we model option (a): three small factories, each focused.
 */

interface PdfRenderer   { public function render(array $rows): string; }
interface CsvRenderer   { public function render(array $rows): string; }
interface ExcelRenderer { public function render(array $rows): string; }

interface PdfRendererFactory   { public function make(): PdfRenderer;   }
interface CsvRendererFactory   { public function make(): CsvRenderer;   }
interface ExcelRendererFactory { public function make(): ExcelRenderer; }

final class TcpdfRenderer implements PdfRenderer { public function render(array $rows): string { return 'pdf:' . count($rows); } }
final class StandardCsvRenderer implements CsvRenderer { public function render(array $rows): string { return 'csv:' . count($rows); } }
final class PhpSpreadsheetRenderer implements ExcelRenderer { public function render(array $rows): string { return 'xlsx:' . count($rows); } }

final class TcpdfFactory implements PdfRendererFactory { public function make(): PdfRenderer { return new TcpdfRenderer(); } }
final class StandardCsvFactory implements CsvRendererFactory { public function make(): CsvRenderer { return new StandardCsvRenderer(); } }
final class PhpSpreadsheetFactory implements ExcelRendererFactory { public function make(): ExcelRenderer { return new PhpSpreadsheetRenderer(); } }

/** A consumer that only needs PDF declares only that dependency. */
final class PdfReportController
{
    public function __construct(private readonly PdfRendererFactory $factory) {}
    public function export(array $rows): string { return $this->factory->make()->render($rows); }
}

// ---- assertions -------------------------------------------------------------

$rows = [['a' => 1], ['a' => 2]];

pdp_assert_eq('pdf:2',  (new PdfReportController(new TcpdfFactory()))->export($rows), 'pdf consumer needs only the pdf factory');
pdp_assert_eq('csv:2',  (new StandardCsvFactory())->make()->render($rows), 'csv standalone');
pdp_assert_eq('xlsx:2', (new PhpSpreadsheetFactory())->make()->render($rows), 'xlsx standalone');

pdp_done('Abstract Factory was the wrong shape — the families are not parallel. See the comment block.');
