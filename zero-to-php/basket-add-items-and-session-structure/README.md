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

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8018 -t public
   ```
2. Open `http://127.0.0.1:8018` and click the same product button twice.
3. Confirm basket output has one row for that `product_id` and `qty` increments.

← [Zero to PHP](../README.md)
