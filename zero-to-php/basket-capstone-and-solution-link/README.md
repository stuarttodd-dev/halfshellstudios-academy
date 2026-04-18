# Chapter 16.12 — Basket capstone

Basic capstone checklist for `basket-capstone-and-solution-link`.

- Session-backed basket initializes safely.
- Add/update/remove actions work without corrupting state.
- Totals use integer cents and format on render.
- Checkout validates required customer fields and empty basket.

Reference: [Zero to PHP - Basket build](https://github.com/stuartp-dev/zero-to-php-basket-build)

## Solution walkthrough

This capstone combines all basket features into one flow: session bootstrap, add/update/remove, totals, and checkout validation.  
It represents a full mini ecommerce basket journey in plain PHP.

## How to test

1. From this folder, start the app:
   ```bash
   php -S 127.0.0.1:8012 -t public
   ```
2. Open `http://127.0.0.1:8012` and run a full journey:
   - add products
   - update quantity
   - remove an item
   - verify subtotal and item count updates
3. Checkout twice:
   - first with invalid fields (empty name or invalid email) and confirm validation errors
   - then with valid name/email and confirm basket is cleared on success

← [Zero to PHP](../README.md)
