<?php
declare(strict_types=1);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    echo json_encode(['message' => 'Send POST / with JSON body {name, price}'], JSON_THROW_ON_ERROR);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => ['code' => 'invalid_json', 'message' => 'Body must be valid JSON']], JSON_THROW_ON_ERROR);
    exit;
}

$name = trim((string) ($payload['name'] ?? ''));
$price = $payload['price'] ?? null;

if ($name === '' || !is_int($price) || $price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => ['code' => 'validation_failed', 'message' => 'name and positive integer price are required']], JSON_THROW_ON_ERROR);
    exit;
}

http_response_code(201);
echo json_encode(['item' => ['id' => 1, 'name' => $name, 'price' => $price]], JSON_THROW_ON_ERROR);
