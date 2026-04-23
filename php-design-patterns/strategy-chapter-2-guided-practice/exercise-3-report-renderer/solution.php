<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * Strategy fits cleanly here. Each format has its own non-trivial rendering
 * algorithm (CSV escaping, JSON serialisation, HTML escaping + tags) — the
 * branches are *behaviour*, not data. We extract one class per format and
 * test each in isolation, with no other format constructed.
 */

interface RowsRenderer
{
    /** @param list<array<int|string, scalar>> $rows */
    public function render(array $rows): string;
}

final class CsvRenderer implements RowsRenderer
{
    public function render(array $rows): string
    {
        $output = '';
        foreach ($rows as $row) {
            $output .= implode(',', array_map(static fn ($v) => (string) $v, $row)) . "\n";
        }
        return $output;
    }
}

final class JsonRenderer implements RowsRenderer
{
    public function render(array $rows): string
    {
        return json_encode($rows, JSON_THROW_ON_ERROR);
    }
}

final class HtmlRenderer implements RowsRenderer
{
    public function render(array $rows): string
    {
        $html = '<table>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</table>';
    }
}

final class ReportRenderer
{
    /** @param array<string, RowsRenderer> $renderers */
    public function __construct(private readonly array $renderers) {}

    public static function default(): self
    {
        return new self([
            'csv'  => new CsvRenderer(),
            'json' => new JsonRenderer(),
            'html' => new HtmlRenderer(),
        ]);
    }

    public function render(string $format, array $rows): string
    {
        if (!isset($this->renderers[$format])) {
            throw new \RuntimeException("Unknown format: {$format}");
        }
        return $this->renderers[$format]->render($rows);
    }
}

// ---- assertions -------------------------------------------------------------

$rows = [['a', 'b'], ['c', 'd']];

// Each strategy is independently testable — no need to construct the others.
pdp_assert_eq("a,b\nc,d\n",                          (new CsvRenderer())->render($rows),  'CSV in isolation');
pdp_assert_eq('[["a","b"],["c","d"]]',               (new JsonRenderer())->render($rows), 'JSON in isolation');
pdp_assert_eq('<table><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>',
                                                     (new HtmlRenderer())->render($rows), 'HTML in isolation');

// HTML escapes hostile cells.
pdp_assert_eq('<table><tr><td>&lt;script&gt;</td></tr></table>',
              (new HtmlRenderer())->render([['<script>']]), 'HTML escapes cells');

// The picker delegates correctly.
$report = ReportRenderer::default();
pdp_assert_eq("a,b\nc,d\n", $report->render('csv', $rows), 'picker -> csv');
pdp_assert_throws(\RuntimeException::class, fn () => $report->render('xml', $rows), 'picker rejects unknown formats');

pdp_done();
