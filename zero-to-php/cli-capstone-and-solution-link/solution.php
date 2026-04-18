<?php
declare(strict_types=1);

$tasks = [];

$tasks[] = ['id' => 1, 'title' => 'Buy milk', 'done' => false];
$tasks[0]['done'] = true;
$tasks[] = ['id' => 2, 'title' => 'Write recap', 'done' => false];
$tasks = array_values(array_filter($tasks, fn(array $task): bool => $task['id'] !== 1));

echo "Remaining tasks: " . count($tasks) . "\n";
echo "Next improvement: add automated tests for commands.\n";
