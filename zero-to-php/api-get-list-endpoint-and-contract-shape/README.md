# Chapter 16.20 — API list contract

Basic solution for `api-get-list-endpoint-and-contract-shape`.

```php
<?php
declare(strict_types=1);

$items = [
    ['id' => 1, 'name' => 'Tee', 'price' => 1999],
    ['id' => 2, 'name' => 'Mug', 'price' => 1299],
];

header('Content-Type: application/json');
echo json_encode(['items' => $items], JSON_THROW_ON_ERROR);
```

Contract shape stays stable: `{"items":[{"id":...,"name":...,"price":...}]}`.

## Solution walkthrough

The endpoint returns one fixed top-level shape (`items`) and only exposes public fields (`id`, `name`, `price`).  
Keeping this contract stable makes frontend/client parsing reliable.

## How to test

1. From this folder, start the API:
   ```bash
   php -S 127.0.0.1:8028 -t public
   ```
2. Run:
   ```bash
   curl -s http://127.0.0.1:8028
   ```
3. Confirm JSON shape is `{"items":[...]}` with expected item fields.

← [Zero to PHP](../README.md)
