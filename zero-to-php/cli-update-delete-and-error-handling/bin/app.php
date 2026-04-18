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

function saveTasks(array $tasks): void
{
    $dir = dirname(STORAGE_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents(STORAGE_PATH, json_encode($tasks, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}

function findTaskIndex(array $tasks, int $id): int
{
    foreach ($tasks as $index => $task) {
        if ((int) $task['id'] === $id) {
            return $index;
        }
    }
    return -1;
}

$command = $argv[1] ?? '';
$tasks = loadTasks();

if ($command === 'done') {
    if (!ctype_digit($argv[2] ?? '')) {
        fwrite(STDERR, "Error: done requires a numeric task id.\n");
        exit(1);
    }
    $id = (int) $argv[2];
    $index = findTaskIndex($tasks, $id);
    if ($index < 0) {
        fwrite(STDERR, "Task {$id} not found.\n");
        exit(1);
    }
    $tasks[$index]['done'] = true;
    saveTasks($tasks);
    echo "Marked #{$id} as done.\n";
    exit(0);
}

if ($command === 'delete') {
    if (!ctype_digit($argv[2] ?? '')) {
        fwrite(STDERR, "Error: delete requires a numeric task id.\n");
        exit(1);
    }
    $id = (int) $argv[2];
    $before = count($tasks);
    $tasks = array_values(array_filter($tasks, static fn(array $task): bool => (int) $task['id'] !== $id));
    if (count($tasks) === $before) {
        fwrite(STDERR, "Task {$id} not found.\n");
        exit(1);
    }
    saveTasks($tasks);
    echo "Deleted #{$id}.\n";
    exit(0);
}

echo "Usage: php bin/app.php [done <id>|delete <id>]\n";
exit(1);
