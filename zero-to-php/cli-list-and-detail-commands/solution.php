<?php
declare(strict_types=1);

function listTasks(array $tasks): void {
    foreach ($tasks as $task) {
        echo "#{$task['id']} {$task['title']} [" . ($task['done'] ? 'done' : 'todo') . "]\n";
    }
}

function showTask(array $tasks, int $id): void {
    foreach ($tasks as $task) {
        if ($task['id'] === $id) {
            echo json_encode($task, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
            return;
        }
    }
    echo "Task {$id} not found.\n";
}

$tasks = [
    ['id' => 1, 'title' => 'Buy milk', 'done' => false],
    ['id' => 2, 'title' => 'Ship feature', 'done' => true],
];

$command = $argv[1] ?? 'list';
if ($command === 'show') {
    showTask($tasks, (int) ($argv[2] ?? 0));
} else {
    listTasks($tasks);
}
