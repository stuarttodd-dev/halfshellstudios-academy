<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/ItemStore.php';

$store = new ItemStore(__DIR__ . '/../storage/items.json');

header('Content-Type: application/json');

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_THROW_ON_ERROR);
}

function errorResponse(string $code, string $message, int $status): void
{
    respond(['error' => ['code' => $code, 'message' => $message]], $status);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

try {
    if ($method === 'GET' && $path === '/health') {
        respond(['ok' => true]);
        exit;
    }

    if ($method === 'GET' && $path === '/api/items') {
        respond(['items' => $store->all()]);
        exit;
    }

    if ($method === 'POST' && $path === '/api/items') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);
        if (!is_array($payload)) {
            errorResponse('invalid_json', 'Request body must be valid JSON.', 400);
            exit;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $price = $payload['price'] ?? null;

        if ($name === '' || !is_int($price) || $price <= 0) {
            errorResponse('validation_failed', 'name must be non-empty and price must be an integer > 0.', 400);
            exit;
        }

        $item = $store->create($name, $price);
        respond(['item' => $item], 201);
        exit;
    }

    errorResponse('not_found', 'Route not found.', 404);
} catch (Throwable $e) {
    errorResponse('server_error', 'Unexpected server error.', 500);
}
