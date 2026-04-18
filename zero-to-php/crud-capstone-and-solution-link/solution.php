<?php
declare(strict_types=1);

$rows = [];
$rows[] = ['id' => 1, 'title' => 'Plan sprint', 'status' => 'todo'];
$rows[0]['status'] = 'doing';
$rows[0]['status'] = 'done';
$rows = array_values(array_filter($rows, fn(array $row): bool => $row['id'] !== 1));

echo "records=" . count($rows) . "\n";
echo "ux_improvement=add_success_flash_message\n";
