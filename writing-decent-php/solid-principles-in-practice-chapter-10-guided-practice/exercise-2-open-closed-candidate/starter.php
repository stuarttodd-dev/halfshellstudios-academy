<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

function vatFor(Order $o): int
{
    if ($o->country === 'GB') return (int) round($o->net * 0.20);
    if ($o->country === 'IE') return (int) round($o->net * 0.23);
    if ($o->country === 'DE') return (int) round($o->net * 0.19);
    if ($o->country === 'FR') return (int) round($o->net * 0.20);
    if ($o->country === 'ES') return (int) round($o->net * 0.21);
    return 0;
}

/* ---------- driver ---------- */

$orders = [
    new Order(country: 'GB', net: 10_000),
    new Order(country: 'IE', net: 10_000),
    new Order(country: 'DE', net: 10_000),
    new Order(country: 'FR', net: 10_000),
    new Order(country: 'ES', net: 10_000),
    new Order(country: 'NO', net: 10_000),
];

foreach ($orders as $order) {
    printf("%s on %d -> %d\n", $order->country, $order->net, vatFor($order));
}
