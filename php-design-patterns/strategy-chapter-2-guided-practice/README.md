# Chapter 2 — Strategy (guided practice)

Three branch-heavy snippets. Two of them want Strategy; one of them
does not. Recognising the difference is the chapter.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Tax calculator | One `if` per region, each with its own rule | **Strategy fits** — one class per region's rule, picker on the side |
| 2 — Pricing tiers | A `match` returning four constants | **Trap.** The branches are *data*, not behaviour. Keep the `match` (with an enum) |
| 3 — Report renderer | One `if` per format, each with non-trivial rendering code | **Strategy fits** — one class per format |

Run any solution with `php solution.php`.

---

## Exercise 1 — Tax calculator

### Before

```php
final class TaxCalculator
{
    public function tax(string $region, int $netInPence): int
    {
        if ($region === 'UK') return (int) round($netInPence * 0.20);
        if ($region === 'EU') return (int) round($netInPence * 0.21);
        if ($region === 'US') {
            $state = 'NY';
            return (int) round($netInPence * 0.0875);
        }
        if ($region === 'AU') return (int) round($netInPence * 0.10);
        return 0;
    }
}
```

### After

```php
interface TaxStrategy { public function tax(int $netInPence): int; }

final class UkTax implements TaxStrategy { /* 20% */ }
final class EuTax implements TaxStrategy { /* 21% */ }
final class UsTax implements TaxStrategy { /* NY 8.75% */ }
final class AuTax implements TaxStrategy { /* 10% */ }
final class NoTax implements TaxStrategy { /* 0   */ }

final class TaxCalculator
{
    public function __construct(private array $strategies, private TaxStrategy $fallback) {}
    public function tax(string $region, int $netInPence): int
    {
        return ($this->strategies[$region] ?? $this->fallback)->tax($netInPence);
    }
}
```

### What the refactor buys

- **One reason to open each file.** A change to UK VAT touches `UkTax`
  and `UkTax` only.
- **Independently testable.** The Strategy `UkTax::tax(1000) === 200`
  needs no calculator, no map, no fallback.
- **Open/Closed.** A new `JpTax` is a new file plus one line in the
  picker — no edit to existing rules.
- **Honest about NY.** The hard-coded state is now visibly the
  responsibility of `UsTax`. When a second US state appears, *that*
  class grows a sub-region argument; everything else stays still.

---

## Exercise 2 — Pricing tiers (the trap)

### Before

```php
return match ($tier) {
    'free'  => 0,
    'basic' => 999,
    'pro'   => 2999,
    'team'  => 9999,
};
```

### Verdict — Strategy is the wrong answer

The branches are **constants**, not behaviour. There is no algorithm to
swap, no policy to vary. A `FreePricing` / `BasicPricing` /
`ProPricing` / `TeamPricing` class that returns one number each is
ceremony around four numbers. The right home for "data that varies by
enum-like key" is exactly what we already have: a small data table.

What we *do* fix:

- the implicit string parameter becomes a `Tier` enum, so a typo
  (`'beam'`) is a `ValueError` at the boundary, not an unhandled match
  arm;
- the units make it into the type (`priceInPence`).

That is not Strategy.

When does this cross the line? When pricing **gains behaviour** — per-tier
rate limits, trial windows, currency-specific overrides. Until then,
`match` wins.

---

## Exercise 3 — Report renderer

### Before

```php
if ($format === 'csv') { /* implode + \n loop */ }
if ($format === 'json') return json_encode($rows);
if ($format === 'html') { /* table + tr + td + escape loop */ }
throw new RuntimeException("Unknown format: {$format}");
```

### After

```php
interface RowsRenderer { public function render(array $rows): string; }

final class CsvRenderer  implements RowsRenderer { /* CSV escaping */ }
final class JsonRenderer implements RowsRenderer { /* json_encode  */ }
final class HtmlRenderer implements RowsRenderer { /* htmlspecialchars + table tags */ }

final class ReportRenderer
{
    public function __construct(private array $renderers) {}
    public function render(string $format, array $rows): string { /* picker */ }
}
```

### What the refactor buys

- **CSV escaping, HTML escaping, JSON serialisation each own one
  file.** The CSV renderer cannot accidentally HTML-escape its cells
  the day someone touches the HTML renderer.
- **Bonus from the brief: one unit test per renderer that does not
  construct the others.** `(new HtmlRenderer())->render([['<script>']])`
  proves HTML escapes hostile cells without ever instantiating
  `JsonRenderer`.
- **A new format is a new class** (`PdfRenderer`, `XmlRenderer`) plus
  one entry in the picker — no edit to the existing renderers.

---

## What "done" looks like (chapter rubric)

For each exercise, the solution:

- has an interface named after the operation (`tax`, `render`)
- has one strategy per genuine variation
- has a picker (constructor map / `match`) that is testable on its own
- includes at least one assertion per strategy *and* per picker branch
- explicitly says, in this README and in the file's header, which
  exercise needed Strategy and which did not

---

## How to run

```bash
cd php-design-patterns/strategy-chapter-2-guided-practice
php exercise-1-tax-calculator/solution.php
php exercise-2-pricing-tiers/solution.php
php exercise-3-report-renderer/solution.php
```
