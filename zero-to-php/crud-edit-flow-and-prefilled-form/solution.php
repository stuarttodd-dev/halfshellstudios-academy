<?php
declare(strict_types=1);

function findById(array $rows, int $id): ?array {
    foreach ($rows as $row) {
        if ($row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

function updateRow(array &$rows, int $id, array $payload): bool {
    foreach ($rows as &$row) {
        if ($row['id'] === $id) {
            $row = array_merge($row, $payload);
            return true;
        }
    }
    return false;
}

$rows = [
    ['id' => 1, 'title' => 'A', 'status' => 'todo'],
    ['id' => 2, 'title' => 'B', 'status' => 'doing'],
];

$record = findById($rows, 2);
$updated = updateRow($rows, 2, ['status' => 'done']);

echo (($record !== null) && $updated) ? "ok\n" : "error\n";
