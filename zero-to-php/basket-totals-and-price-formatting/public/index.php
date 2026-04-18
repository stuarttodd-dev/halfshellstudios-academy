<?php
declare(strict_types=1);

session_start();
$_SESSION['basket'] ??= [
    ['product_id' => 1, 'name' => 'T-Shirt', 'price_cents' => 1999, 'qty' => 2],
    ['product_id' => 2, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 1],
];

function money(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

$itemCount = 0;
$subtotalCents = 0;
foreach ($_SESSION['basket'] as $item) {
    $qty = (int) $item['qty'];
    $itemCount += $qty;
    $subtotalCents += ((int) $item['price_cents']) * $qty;
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Basket Totals</title></head>
<body>
<h1>Basket totals and price formatting</h1>
<p>Item count: <?= $itemCount ?></p>
<p>Subtotal: <?= money($subtotalCents) ?></p>
</body>
</html>
