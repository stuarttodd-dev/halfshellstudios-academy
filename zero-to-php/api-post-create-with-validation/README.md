# Chapter 16.21 — API create + validation

Basic solution for `api-post-create-with-validation`.

```php
<?php
declare(strict_types=1);

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$name = trim((string) ($payload['name'] ?? ''));
$price = $payload['price'] ?? null;

if ($name === '' || !is_int($price) || $price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => ['code' => 'validation_failed', 'message' => 'name and positive integer price are required']]);
    exit;
}

http_response_code(201);
echo json_encode(['item' => ['id' => 1, 'name' => $name, 'price' => $price]]);
```

## Solution walkthrough

The endpoint reads JSON from `php://input`, validates `name` and `price`, and returns `400` on invalid input.  
Valid payloads return `201` with created item data.

## How to test

1. Serve the endpoint locally.
2. Run:
   ```bash
   curl -i -X POST http://127.0.0.1:8000/api/items -H "Content-Type: application/json" -d '{"name":"Tee","price":1999}'
   curl -i -X POST http://127.0.0.1:8000/api/items -H "Content-Type: application/json" -d '{"name":"","price":0}'
   ```
3. Confirm valid request returns `201` and invalid request returns `400`.

← [Zero to PHP](../README.md)
