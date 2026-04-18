<?php
declare(strict_types=1);

header('Content-Type: application/json');

$items = [
    ['id' => 1, 'name' => 'T-Shirt', 'price' => 1999],
    ['id' => 2, 'name' => 'Mug', 'price' => 1299],
];

echo json_encode(['items' => $items], JSON_THROW_ON_ERROR);
