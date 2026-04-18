<?php
declare(strict_types=1);

function basketTotals(array $basket): array {
    $itemCount = 0;
    $subtotalCents = 0;

    foreach ($basket as $item) {
        $qty = (int) $item['qty'];
        $itemCount += $qty;
        $subtotalCents += ((int) $item['price_cents']) * $qty;
    }

    return ['item_count' => $itemCount, 'subtotal_cents' => $subtotalCents];
}

function money(int $cents): string {
    return '$' . number_format($cents / 100, 2);
}

$basket = [
    ['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 2],
    ['product_id' => 11, 'name' => 'Tee', 'price_cents' => 1999, 'qty' => 1],
];
$totals = basketTotals($basket);

echo $totals['item_count'] . "\n";
echo money($totals['subtotal_cents']) . "\n";
