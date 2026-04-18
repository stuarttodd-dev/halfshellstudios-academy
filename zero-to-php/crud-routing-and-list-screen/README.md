# Chapter 16.13 — CRUD routing/list

Basic solution for `crud-routing-and-list-screen`.

```php
<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/tasks') {
    echo "List screen";
} elseif ($path === '/tasks/create') {
    echo "Create form";
} elseif ($path === '/tasks/edit') {
    echo "Edit form";
} else {
    http_response_code(404);
    echo "Not found";
}
```

## Solution walkthrough

Routing is handled in one front controller by method/path checks, with clear branches for list/create/edit screens and a 404 fallback.  
This gives a predictable navigation skeleton for later CRUD actions.

## How to test

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8022 -t public
   ```
2. Open `http://127.0.0.1:8022/?action=list`, `?action=create`, and `?action=edit&id=1`.
3. Try an unknown action and confirm fallback/not-found behavior.

← [Zero to PHP](../README.md)
