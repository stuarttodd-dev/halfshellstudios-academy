# Chapter 16.15 — CRUD edit flow

Basic solution for `crud-edit-flow-and-prefilled-form`.

```php
<?php
declare(strict_types=1);

function findById(array $rows, int $id): ?array {
    foreach ($rows as $row) {
        if ($row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

function updateRow(array &$rows, int $id, array $payload): bool {
    foreach ($rows as &$row) {
        if ($row['id'] === $id) {
            $row = array_merge($row, $payload);
            return true;
        }
    }
    return false;
}
```

Use `findById()` to prefill edit form and return 404 for missing ids.

## Solution walkthrough

`findById()` retrieves a single record for prefilled edit fields.  
`updateRow()` applies validated changes in place and returns success/failure so route handlers can respond cleanly.

## How to test

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8024 -t public
   ```
2. Open `http://127.0.0.1:8024/?id=1` and confirm the form is prefilled.
3. Submit updated values and confirm success message.
4. Open `http://127.0.0.1:8024/?id=999` and confirm not-found behavior.

← [Zero to PHP](../README.md)
