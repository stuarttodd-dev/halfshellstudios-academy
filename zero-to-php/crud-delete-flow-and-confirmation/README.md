# Chapter 16.16 — CRUD delete flow

Basic solution for `crud-delete-flow-and-confirmation`.

```php
<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    http_response_code(400);
    exit('Invalid id');
}
```

After validation and existence check, delete then redirect to list with a flash message.

## Solution walkthrough

Delete is restricted to `POST` so destructive actions are explicit.  
The handler validates id input and only proceeds when the target record exists.

## How to test

1. Attempt delete via `GET` and confirm `405`.
2. Submit `POST` with invalid id and confirm `400`.
3. Submit valid `POST` for existing record and confirm redirect + row removal.

← [Zero to PHP](../README.md)
