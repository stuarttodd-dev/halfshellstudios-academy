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

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8025 -t public
   ```
2. Open `http://127.0.0.1:8025`, click delete on a row, and confirm the browser confirmation prompt.
3. Confirm the row is removed and feedback message is shown.
4. Reload and confirm session-backed list remains updated.

← [Zero to PHP](../README.md)
