<?php
declare(strict_types=1);

header('Content-Type: application/json');

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_THROW_ON_ERROR);
}

function errorPayload(string $code, string $message): array
{
    return ['error' => ['code' => $code, 'message' => $message]];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($method === 'GET' && $path === '/health') {
    respond(['ok' => true], 200);
    exit;
}

if ($method === 'GET' && $path === '/api/items') {
    respond(['items' => [['id' => 1, 'name' => 'T-Shirt', 'price' => 1999]]], 200);
    exit;
}

if ($method === 'POST' && $path === '/api/items') {
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($payload)) {
        respond(errorPayload('invalid_json', 'Request body must be valid JSON.'), 400);
        exit;
    }
    $name = trim((string) ($payload['name'] ?? ''));
    $price = $payload['price'] ?? null;
    if ($name === '' || !is_int($price) || $price <= 0) {
        respond(errorPayload('validation_failed', 'name and positive integer price are required.'), 400);
        exit;
    }
    respond(['item' => ['id' => 2, 'name' => $name, 'price' => $price]], 201);
    exit;
}

respond(errorPayload('not_found', 'Route not found.'), 404);
