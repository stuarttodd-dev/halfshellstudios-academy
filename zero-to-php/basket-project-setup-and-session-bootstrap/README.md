# Chapter 16.7 — Basket setup/session

Basic solution for `basket-project-setup-and-session-bootstrap`.

```php
<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['basket']) || !is_array($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$basketCount = array_sum(array_map(static fn(array $row): int => (int) $row['qty'], $_SESSION['basket']));
echo "Basket items: {$basketCount}";
```

## Solution walkthrough

The app starts a session once, initializes `$_SESSION['basket']` to a safe array, and derives a basket count from stored quantities.  
This creates a stable session boundary for the next basket lessons.

## How to test

1. Add the snippet to your web app entry point (for example, `public/index.php`).
2. Start PHP built-in server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
3. Load the page multiple times and confirm basket count persists within the same browser session.

← [Zero to PHP](../README.md)
