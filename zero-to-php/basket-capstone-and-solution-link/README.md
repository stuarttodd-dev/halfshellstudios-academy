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

1. Start from an empty session.
2. Add products, update quantity, remove one item, and verify totals after each step.
3. Attempt checkout with invalid data, then valid data, and confirm only valid checkout clears basket.

← [Zero to PHP](../README.md)
