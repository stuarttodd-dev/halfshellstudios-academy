<?php
declare(strict_types=1);

$items = [
    ['id' => 1, 'name' => 'Tee', 'price' => 1999],
    ['id' => 2, 'name' => 'Mug', 'price' => 1299],
];

$response = [
    'health' => ['ok' => true],
    'items_count' => count($items),
    'next_task' => 'Add auth and rate limiting for production hardening.',
];

header('Content-Type: application/json');
echo json_encode($response, JSON_THROW_ON_ERROR);
