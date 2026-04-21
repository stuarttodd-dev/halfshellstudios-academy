<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Decision: OCP applies, but in its lightest possible shape.
 *
 * Every branch in the original is `(int) round($o->net * <rate>)`. The
 * variation axis is one float per region — there is no per-region
 * behaviour, only per-region data. A `country => rate` table captures
 * that exactly. Adding Italy is a one-line edit to the table; nothing
 * inside `VatCalculator` has to change.
 *
 * What we deliberately did NOT do (and why):
 *   - We did not introduce a `VatPolicy` interface with a class per
 *     region. That would be the right shape only when at least one
 *     region needs genuinely different behaviour (compounded levies,
 *     thresholds, registration cut-offs, etc.). Today the registry
 *     would just be five classes that each return `net * rate`, all
 *     paying for ceremony none of them use.
 *   - When the first non-flat-rate region arrives, `VatCalculator`
 *     becomes the seam: one region gets its own class, the others
 *     keep being looked up in the table. Promote-on-demand, not
 *     promote-on-suspicion.
 */
final class VatCalculator
{
    /** @var array<string, float> */
    private const RATES = [
        'GB' => 0.20,
        'IE' => 0.23,
        'DE' => 0.19,
        'FR' => 0.20,
        'ES' => 0.21,
    ];

    public function vatFor(Order $order): int
    {
        $rate = self::RATES[$order->country] ?? 0.0;

        return (int) round($order->net * $rate);
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$orders = [
    new Order(country: 'GB', net: 10_000),
    new Order(country: 'IE', net: 10_000),
    new Order(country: 'DE', net: 10_000),
    new Order(country: 'FR', net: 10_000),
    new Order(country: 'ES', net: 10_000),
    new Order(country: 'NO', net: 10_000),
];

$calculator = new VatCalculator();

foreach ($orders as $order) {
    printf("%s on %d -> %d\n", $order->country, $order->net, $calculator->vatFor($order));
}
