<?php
declare(strict_types=1);

function loadItems(string $path): array {
    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    if (!is_string($json) || $json === '') {
        return [];
    }

    return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
}

function saveItems(string $path, array $items): void {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($path, json_encode($items, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}

function nextId(array $items): int {
    return $items === [] ? 1 : (max(array_column($items, 'id')) + 1);
}

$path = __DIR__ . '/storage/items.json';
$items = loadItems($path);
$items[] = ['id' => nextId($items), 'name' => 'Tee', 'price' => 1999];
saveItems($path, $items);

echo count($items) . "\n";
