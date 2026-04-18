<?php
declare(strict_types=1);

const STORAGE_PATH = __DIR__ . '/../storage/tasks.json';

function loadTasks(): array
{
    if (!is_file(STORAGE_PATH)) {
        return [];
    }

    $json = file_get_contents(STORAGE_PATH);
    if (!is_string($json) || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

$command = $argv[1] ?? '';
$tasks = loadTasks();

if ($command === 'list') {
    if ($tasks === []) {
        echo "No tasks yet.\n";
        exit(0);
    }
    foreach ($tasks as $task) {
        $status = !empty($task['done']) ? 'done' : 'todo';
        echo "#{$task['id']} {$task['title']} [{$status}]\n";
    }
    exit(0);
}

if ($command === 'show') {
    if (!ctype_digit($argv[2] ?? '')) {
        fwrite(STDERR, "Error: show requires a numeric task id.\n");
        exit(1);
    }
    $id = (int) $argv[2];
    foreach ($tasks as $task) {
        if ((int) $task['id'] === $id) {
            echo json_encode($task, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
            exit(0);
        }
    }
    fwrite(STDERR, "Task {$id} not found.\n");
    exit(1);
}

echo "Usage: php bin/app.php [list|show <id>]\n";
exit(1);
