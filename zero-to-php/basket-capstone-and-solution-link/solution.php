<?php
declare(strict_types=1);

$basket = [];
$basket[] = ['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 1];
$basket[] = ['product_id' => 11, 'name' => 'Tee', 'price_cents' => 1999, 'qty' => 2];
$basket[0]['qty'] = 3;
$basket = array_values(array_filter($basket, fn(array $item): bool => $item['product_id'] !== 11));

$subtotalCents = 0;
foreach ($basket as $item) {
    $subtotalCents += $item['price_cents'] * $item['qty'];
}

echo "items=" . count($basket) . ", subtotal_cents={$subtotalCents}\n";
echo "edge_case=empty_basket_checkout_blocked\n";
