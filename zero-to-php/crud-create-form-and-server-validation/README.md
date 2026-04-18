# Chapter 16.14 — CRUD create + validation

Basic solution for `crud-create-form-and-server-validation`.

```php
<?php
declare(strict_types=1);

function validateCreate(array $input): array {
    $errors = [];
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? '');
    $allowedStatus = ['todo', 'doing', 'done'];

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    }
    if (!in_array($status, $allowedStatus, true)) {
        $errors['status'] = 'Status must be todo, doing, or done.';
    }
    return $errors;
}
```

Create record only when no validation errors, then redirect to list.

## Solution walkthrough

`validateCreate()` enforces required `title` and an allowed `status` set.  
Invalid input returns field-level errors so the form can re-render safely without creating a record.

## How to test

1. Submit create form with empty title and invalid status.
2. Confirm the record is not created and errors are shown.
3. Submit valid title/status and confirm record is saved then redirected to list.

← [Zero to PHP](../README.md)
