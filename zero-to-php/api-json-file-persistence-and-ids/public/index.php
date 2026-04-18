<?php
declare(strict_types=1);

const STORAGE = __DIR__ . '/../storage/items.json';

function loadItems(): array
{
    if (!is_file(STORAGE)) {
        return [];
    }
    $json = file_get_contents(STORAGE);
    if (!is_string($json) || trim($json) === '') {
        return [];
    }
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function saveItems(array $items): void
{
    $dir = dirname(STORAGE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents(STORAGE, json_encode($items, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}

function nextId(array $items): int
{
    if ($items === []) {
        return 1;
    }
    return max(array_column($items, 'id')) + 1;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$items = loadItems();

if ($method === 'GET') {
    echo json_encode(['items' => $items], JSON_THROW_ON_ERROR);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_json'], JSON_THROW_ON_ERROR);
        exit;
    }
    $name = trim((string) ($payload['name'] ?? ''));
    $price = $payload['price'] ?? null;
    if ($name === '' || !is_int($price) || $price <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'validation_failed'], JSON_THROW_ON_ERROR);
        exit;
    }
    $item = ['id' => nextId($items), 'name' => $name, 'price' => $price];
    $items[] = $item;
    saveItems($items);
    http_response_code(201);
    echo json_encode(['item' => $item], JSON_THROW_ON_ERROR);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method_not_allowed'], JSON_THROW_ON_ERROR);
