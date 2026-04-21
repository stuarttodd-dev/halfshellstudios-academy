<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Each region's policy is a tiny class behind one interface. Adding a
 * new region is *one new line in the registry's map* (when the rate is
 * a flat percentage of net) or *one new class plus one new line* (when
 * the rule itself is genuinely different — say a region that compounds
 * VAT and an environmental levy).
 */
interface VatPolicy
{
    public function taxFor(int $netInPence): int;
}

/**
 * Covers every flat-rate region in the system today (which is most of
 * them). One class, parameterised by a rate; no copy-paste per country.
 */
final class FlatRateVatPolicy implements VatPolicy
{
    public function __construct(private float $rate) {}

    public function taxFor(int $netInPence): int
    {
        return (int) round($netInPence * $this->rate);
    }
}

/**
 * VAT-exempt regions. A separate class (rather than `FlatRateVatPolicy(0.0)`)
 * because "we deliberately do not collect VAT here" is a different domain
 * statement to "the rate happens to be zero today" — and tax authorities
 * audit on intent, not arithmetic.
 */
final class ZeroVatPolicy implements VatPolicy
{
    public function taxFor(int $netInPence): int
    {
        return 0;
    }
}

/**
 * Single source of truth for "country code → policy". The fallback handles
 * any region we have not yet codified — one place to find it, one place to
 * change it. Adding a region is a one-line edit here (or, for a wholly
 * new policy shape, one new class + one new line).
 */
final class VatPolicyRegistry
{
    /** @var array<string, VatPolicy> */
    private array $policiesByCountry;

    public function __construct(private VatPolicy $fallback)
    {
        $gbVat        = new FlatRateVatPolicy(0.20);
        $standardEuVat = new FlatRateVatPolicy(0.19);
        $ieVat        = new FlatRateVatPolicy(0.23);
        $exempt       = new ZeroVatPolicy();

        $this->policiesByCountry = [
            'GB' => $gbVat,
            'IE' => $ieVat,
            'DE' => $standardEuVat,
            'FR' => $standardEuVat,
            'NL' => $standardEuVat,
            'US' => $exempt,
            'CA' => $exempt,
        ];
    }

    public function for(string $country): VatPolicy
    {
        return $this->policiesByCountry[$country] ?? $this->fallback;
    }
}

function taxFor(Order $o, VatPolicyRegistry $policies): int
{
    return $policies->for($o->country)->taxFor($o->net);
}

/* ---------- driver (identical to starter.php) ---------- */

$policies = new VatPolicyRegistry(fallback: new FlatRateVatPolicy(0.10));

$cases = [
    new Order(country: 'GB', net: 10_000),
    new Order(country: 'IE', net: 10_000),
    new Order(country: 'DE', net: 10_000),
    new Order(country: 'FR', net: 10_000),
    new Order(country: 'NL', net: 10_000),
    new Order(country: 'US', net: 10_000),
    new Order(country: 'CA', net: 10_000),
    new Order(country: 'JP', net: 10_000),
    new Order(country: 'XX', net: 10_000),
    new Order(country: 'GB', net: 1),
    new Order(country: 'IE', net: 333),
];

foreach ($cases as $order) {
    printf("%s × %5dp -> %5dp tax\n", $order->country, $order->net, taxFor($order, $policies));
}
