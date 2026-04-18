# Chapter 16.8 — Basket add items

Basic solution for `basket-add-items-and-session-structure`.

```php
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
```

## Solution walkthrough

`addToBasket()` enforces one canonical basket item shape and merges duplicate products by incrementing `qty`.  
This avoids duplicate rows for the same `product_id`.

## How to test

1. Wire `addToBasket()` into your add action.
2. Add the same product twice in one session.
3. Confirm there is one basket row for that product and `qty` increments to `2`.

← [Zero to PHP](../README.md)
