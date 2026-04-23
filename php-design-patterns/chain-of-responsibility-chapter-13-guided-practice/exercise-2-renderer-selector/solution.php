<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — three fixed cases with no expected change is what `match` is for.
 *
 * Chain of Responsibility shines when the *set of handlers* is open, when
 * order matters, or when a handler may decide to pass without firing.
 * `'pdf' | 'csv' | 'html'` is none of those: the choices are exhaustive,
 * exclusive, and stable. CoR would add three handler classes, a chain
 * runner, and a registry — for the pleasure of writing
 * `if ($format === 'pdf') return new PdfRenderer()` distributed across
 * three files instead of one.
 *
 * The right shape is the one we already have: `match`.
 */

interface Renderer { public function render(array $rows): string; }
final class PdfRenderer  implements Renderer { public function render(array $rows): string { return 'pdf:'  . count($rows); } }
final class CsvRenderer  implements Renderer { public function render(array $rows): string { return 'csv:'  . count($rows); } }
final class HtmlRenderer implements Renderer { public function render(array $rows): string { return 'html:' . count($rows); } }

final class RendererSelector
{
    public function pick(string $format): Renderer
    {
        return match ($format) {
            'pdf'  => new PdfRenderer(),
            'csv'  => new CsvRenderer(),
            'html' => new HtmlRenderer(),
            default => throw new \InvalidArgumentException("unknown format: {$format}"),
        };
    }
}

// ---- assertions -------------------------------------------------------------

$selector = new RendererSelector();
pdp_assert_eq('pdf:3',  $selector->pick('pdf')->render([1,2,3]), 'pdf');
pdp_assert_eq('csv:3',  $selector->pick('csv')->render([1,2,3]), 'csv');
pdp_assert_eq('html:3', $selector->pick('html')->render([1,2,3]), 'html');
pdp_assert_throws(\InvalidArgumentException::class, fn () => $selector->pick('xml'), 'unknown format throws');

pdp_done('CoR was the wrong answer here — see the comment block.');
