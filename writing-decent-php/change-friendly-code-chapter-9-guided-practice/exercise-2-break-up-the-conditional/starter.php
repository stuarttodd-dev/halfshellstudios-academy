<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

function taxFor(Order $o): int
{
    if ($o->country === 'GB') return (int) round($o->net * 0.20);
    if ($o->country === 'IE') return (int) round($o->net * 0.23);
    if (in_array($o->country, ['DE', 'FR', 'NL'])) return (int) round($o->net * 0.19);
    if (in_array($o->country, ['US', 'CA'])) return 0;

    return (int) round($o->net * 0.10);
}

/* ---------- driver ---------- */

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
    printf("%s × %5dp -> %5dp tax\n", $order->country, $order->net, taxFor($order));
}
