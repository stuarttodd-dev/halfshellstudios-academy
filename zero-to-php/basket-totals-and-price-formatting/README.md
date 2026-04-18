# Chapter 16.10 — Basket totals

Basic solution for `basket-totals-and-price-formatting`.

```php
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
```

## Solution walkthrough

Totals are calculated only in integer cents to avoid float rounding issues.  
`money()` formats values for display at the view layer.

## How to test

1. Build a basket with known values (for example 1999 x2 and 1299 x1).
2. Call `basketTotals()` and verify subtotal cents are correct.
3. Render with `money()` and confirm formatted values like `$52.97`.

← [Zero to PHP](../README.md)
