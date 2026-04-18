<?php
declare(strict_types=1);

function updateQuantity(array &$basket, int $productId, int $qty): bool {
    if ($qty < 1) {
        return false;
    }

    foreach ($basket as &$item) {
        if ($item['product_id'] === $productId) {
            $item['qty'] = $qty;
            return true;
        }
    }

    return false;
}

function removeItem(array &$basket, int $productId): void {
    $basket = array_values(array_filter($basket, fn(array $item): bool => $item['product_id'] !== $productId));
}

$basket = [
    ['product_id' => 10, 'name' => 'Mug', 'price_cents' => 1299, 'qty' => 1],
    ['product_id' => 11, 'name' => 'Tee', 'price_cents' => 1999, 'qty' => 1],
];
updateQuantity($basket, 10, 3);
removeItem($basket, 11);

echo $basket[0]['qty'] . "\n";
