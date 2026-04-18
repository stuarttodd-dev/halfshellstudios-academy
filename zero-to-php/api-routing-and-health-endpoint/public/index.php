<?php
declare(strict_types=1);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($method === 'GET' && $path === '/health') {
    echo json_encode(['ok' => true], JSON_THROW_ON_ERROR);
    exit;
}

if ($method === 'GET' && $path === '/api/items') {
    echo json_encode(['items' => []], JSON_THROW_ON_ERROR);
    exit;
}

http_response_code(404);
echo json_encode(['error' => ['code' => 'not_found', 'message' => 'Route not found']], JSON_THROW_ON_ERROR);
