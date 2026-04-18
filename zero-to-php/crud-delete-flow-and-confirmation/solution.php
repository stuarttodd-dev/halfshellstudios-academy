<?php
declare(strict_types=1);

function deleteById(array &$rows, int $id): bool {
    $before = count($rows);
    $rows = array_values(array_filter($rows, fn(array $row): bool => $row['id'] !== $id));
    return count($rows) < $before;
}

$rows = [
    ['id' => 1, 'title' => 'A'],
    ['id' => 2, 'title' => 'B'],
];

$deleted = deleteById($rows, 1);
echo $deleted ? "deleted\n" : "not_found\n";
