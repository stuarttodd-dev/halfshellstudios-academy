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

1. Add at least two products to the basket.
2. Update one quantity to a valid number, then try `0` to confirm rejection.
3. Remove one product and confirm it disappears while other rows remain intact.

← [Zero to PHP](../README.md)
