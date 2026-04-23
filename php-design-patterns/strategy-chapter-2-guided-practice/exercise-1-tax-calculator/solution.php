<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * Strategy — one class per region's tax rule, plus a registry/picker.
 *
 * The hard-coded `'NY'` in the starter is the interesting design
 * decision. Two reasonable options:
 *
 *   (a) keep it inside `UsTaxStrategy` as the default state — the
 *       calculator's contract is `tax(string $region, int $net): int`,
 *       and the US strategy chooses NY internally;
 *   (b) widen the contract so callers pass a sub-region.
 *
 * Option (b) is the right answer the moment a second US state appears.
 * Until then, (a) preserves the starter's contract and is honest about
 * the current behaviour. We pick (a) and document it loudly.
 */

interface TaxStrategy
{
    /** Tax in pence on a net amount in pence. */
    public function tax(int $netInPence): int;
}

final class UkTax implements TaxStrategy
{
    public function tax(int $netInPence): int { return (int) round($netInPence * 0.20); }
}

final class EuTax implements TaxStrategy
{
    public function tax(int $netInPence): int { return (int) round($netInPence * 0.21); }
}

/**
 * Currently NY-only. When a second US state appears, *this* class is
 * the one that needs to grow a sub-region argument — not the calculator
 * and not the picker. That is the value of the Strategy split.
 */
final class UsTax implements TaxStrategy
{
    public function tax(int $netInPence): int { return (int) round($netInPence * 0.0875); }
}

final class AuTax implements TaxStrategy
{
    public function tax(int $netInPence): int { return (int) round($netInPence * 0.10); }
}

final class NoTax implements TaxStrategy
{
    public function tax(int $netInPence): int { return 0; }
}

final class TaxCalculator
{
    /** @param array<string, TaxStrategy> $strategies */
    public function __construct(private readonly array $strategies, private readonly TaxStrategy $fallback) {}

    public static function default(): self
    {
        return new self(
            strategies: ['UK' => new UkTax(), 'EU' => new EuTax(), 'US' => new UsTax(), 'AU' => new AuTax()],
            fallback:   new NoTax(),
        );
    }

    public function tax(string $region, int $netInPence): int
    {
        return ($this->strategies[$region] ?? $this->fallback)->tax($netInPence);
    }
}

// ---- assertions -------------------------------------------------------------

$calc = TaxCalculator::default();

pdp_assert_eq(2000, $calc->tax('UK', 10000), 'UK = 20%');
pdp_assert_eq(2100, $calc->tax('EU', 10000), 'EU = 21%');
pdp_assert_eq(875,  $calc->tax('US', 10000), 'US (NY default) = 8.75%');
pdp_assert_eq(1000, $calc->tax('AU', 10000), 'AU = 10%');
pdp_assert_eq(0,    $calc->tax('XX', 10000), 'unknown region falls back to NoTax');

// Strategies are independently testable without touching the picker.
pdp_assert_eq(200, (new UkTax())->tax(1000), 'UkTax::tax in isolation');
pdp_assert_eq(0,   (new NoTax())->tax(99999), 'NoTax::tax in isolation');

pdp_done();
