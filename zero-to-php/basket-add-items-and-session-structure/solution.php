<?php
declare(strict_types=1);

function addToBasket(array &$basket, array $product): void {
    foreach ($basket as &$item) {
        if ($item['product_id'] === $product['product_id']) {
            $item['qty']++;
            return;
        }
    }

    $basket[] = [
        'product_id' => $product['product_id'],
        'name' => $product['name'],
        'price_cents' => $product['price_cents'],
        'qty' => 1,
    ];
}

$basket = [];
addToBasket($basket, ['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299]);
addToBasket($basket, ['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299]);

echo $basket[0]['qty'] . "\n";
