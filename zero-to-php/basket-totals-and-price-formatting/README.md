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

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8020 -t public
   ```
2. Open `http://127.0.0.1:8020`.
3. Confirm item count and subtotal are shown, and money output is formatted currency.

← [Zero to PHP](../README.md)
