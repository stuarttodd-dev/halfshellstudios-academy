# Chapter 16.19 — API routing + health

Basic solution for `api-routing-and-health-endpoint`.

```php
<?php
declare(strict_types=1);

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($method === 'GET' && $path === '/health') {
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'GET' && $path === '/api/items') {
    echo json_encode(['items' => []]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => ['code' => 'not_found', 'message' => 'Route not found']]);
```

## Solution walkthrough

The handler routes by method/path and always responds with JSON content type.  
It provides `GET /health`, `GET /api/items`, and a consistent JSON 404 error shape.

## How to test

1. Start server with this entry script.
2. Run:
   ```bash
   curl -i http://127.0.0.1:8000/health
   curl -i http://127.0.0.1:8000/api/items
   curl -i http://127.0.0.1:8000/nope
   ```
3. Confirm JSON responses and expected status codes.

← [Zero to PHP](../README.md)
