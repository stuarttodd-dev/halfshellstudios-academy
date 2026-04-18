# Chapter 16.9 — Basket update/remove

Basic solution for `basket-update-quantities-and-remove-items`.

```php
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
```

## Solution walkthrough

`updateQuantity()` rejects invalid quantities and only updates when the target product exists.  
`removeItem()` filters by `product_id` and reindexes rows so basket structure stays clean.

## How to test

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8019 -t public
   ```
2. Open `http://127.0.0.1:8019`, update quantity for an item, then try quantity `0`.
3. Remove one item and confirm the basket re-renders with remaining rows intact.

← [Zero to PHP](../README.md)
