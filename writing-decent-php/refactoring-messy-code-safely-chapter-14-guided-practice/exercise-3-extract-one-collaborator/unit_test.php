<?php
declare(strict_types=1);

require_once __DIR__ . '/subject.php';

/**
 * Now that totals live behind a seam (`InvoiceTotalsCalculator`) we
 * can probe each rule directly — without parsing rendered invoice
 * text. This is the *bonus* of extracting one collaborator: a unit
 * test that names the policy, instead of an end-to-end test that
 * implies it.
 *
 * If you ever want to change "VAT first then discount" to "discount
 * first then VAT" — or to fix it as a bug — this test file is where
 * the change conversation happens, not in the formatter.
 */

/**
 * Numeric comparison with a tiny tolerance for floats.
 *
 * The calculator returns `int|float`. Anything involving the GB VAT or
 * discount multipliers triggers IEEE-754 imprecision, e.g. `6 * 1.2`
 * evaluates to `7.199999999999999`. The characterisation test in
 * `characterisation_test.php` does not catch this because PHP's
 * default float-to-string conversion rounds it back to `"7.2"`. Here
 * — where we are testing the calculator on its own terms — we want
 * to acknowledge the imprecision honestly without pretending it does
 * not exist.
 *
 * The motivated next refactor is a `Money` value object that stores
 * pence as `int`. That is a separate small step (Ex3 was about
 * extracting *one* collaborator).
 */
function assert_eq(int|float $expected, int|float $actual, string $label): void
{
    $matches = is_int($expected) && is_int($actual)
        ? $expected === $actual
        : abs((float) $expected - (float) $actual) < 1e-9;

    if (! $matches) {
        echo "  [{$label}] FAIL — expected " . json_encode($expected) . ", got " . json_encode($actual) . "\n";
        exit(1);
    }
    echo "  [{$label}] ok\n";
}

$totals = new InvoiceTotalsCalculator();

assert_eq(
    expected: 6,
    actual:   $totals->totalFor(['country' => 'FR', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]]),
    label:    'sums line subtotals when no rules apply',
);

assert_eq(
    expected: 7.2,
    actual:   $totals->totalFor(['country' => 'GB', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]]),
    label:    'GB applies 20% VAT (6 -> 7.2)',
);

assert_eq(
    expected: 5.4,
    actual:   $totals->totalFor(['country' => 'IE', 'discount' => 'NEW10', 'items' => [['name' => 'Tea', 'price' => 3, 'qty' => 2]]]),
    label:    'discount alone applies 10% off (6 -> 5.4)',
);

assert_eq(
    expected: 11.88,
    actual:   $totals->totalFor([
        'country'  => 'GB',
        'discount' => 'NEW10',
        'items'    => [
            ['name' => 'Tea',  'price' => 3, 'qty' => 2],
            ['name' => 'Cake', 'price' => 5, 'qty' => 1],
        ],
    ]),
    label:    'GB + discount stack (VAT first then discount): 11 -> 13.2 -> 11.88',
);

assert_eq(
    expected: 0,
    actual:   $totals->totalFor(['country' => 'GB', 'items' => []]),
    label:    'empty items totals to 0 even with GB',
);

echo "unit test: PASS (5/5 totals rules)\n";
