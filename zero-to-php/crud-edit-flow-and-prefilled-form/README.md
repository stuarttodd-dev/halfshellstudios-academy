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

1. Open edit page for an existing id and confirm form prefill.
2. Submit valid updates and confirm values change on list/detail.
3. Open edit page for missing id and confirm 404/not-found behavior.

← [Zero to PHP](../README.md)
