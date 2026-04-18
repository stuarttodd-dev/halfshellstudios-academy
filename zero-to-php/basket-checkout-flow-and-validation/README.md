# Chapter 16.11 — Basket checkout

Basic solution for `basket-checkout-flow-and-validation`.

```php
<?php
declare(strict_types=1);

function validateCheckout(string $name, string $email, array $basket): array {
    $errors = [];
    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is invalid.';
    }
    if ($basket === []) {
        $errors['basket'] = 'Basket cannot be empty.';
    }
    return $errors;
}
```

Clear `$_SESSION['basket']` only when `validateCheckout(...)` returns no errors.

## Solution walkthrough

`validateCheckout()` centralizes checkout rules: required name, valid email, and non-empty basket.  
Checkout succeeds only when the returned error array is empty, then basket clear happens after success.

## How to test

1. Submit checkout with empty name, bad email, and empty basket.
2. Confirm each failure produces the expected validation error.
3. Submit valid name/email with basket items and confirm successful checkout then basket clear.

← [Zero to PHP](../README.md)
