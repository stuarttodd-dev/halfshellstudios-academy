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

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8021 -t public
   ```
2. Open `http://127.0.0.1:8021` and submit checkout with invalid values.
3. Confirm validation errors for required name/email format and empty basket.
4. Submit valid values and confirm checkout success message and basket clear behavior.

← [Zero to PHP](../README.md)
