<?php
declare(strict_types=1);

function markDone(array &$tasks, int $id): bool {
    foreach ($tasks as &$task) {
        if ($task['id'] === $id) {
            $task['done'] = true;
            return true;
        }
    }
    return false;
}

function deleteTask(array &$tasks, int $id): bool {
    $before = count($tasks);
    $tasks = array_values(array_filter($tasks, fn(array $task): bool => $task['id'] !== $id));
    return count($tasks) < $before;
}

$tasks = [
    ['id' => 1, 'title' => 'Buy milk', 'done' => false],
    ['id' => 2, 'title' => 'Ship feature', 'done' => false],
];

$okDone = markDone($tasks, 1);
$okDelete = deleteTask($tasks, 2);

echo ($okDone && $okDelete) ? "ok\n" : "error\n";
