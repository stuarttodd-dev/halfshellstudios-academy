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

$command = $argv[1] ?? '';
$tasks = loadTasks();

if ($command === 'add') {
    $title = trim(implode(' ', array_slice($argv, 2)));
    if ($title === '') {
        fwrite(STDERR, "Error: add requires a non-empty task.\n");
        exit(1);
    }

    $tasks[] = ['id' => count($tasks) + 1, 'title' => $title, 'done' => false];
    saveTasks($tasks);
    echo "Added.\n";
    exit(0);
}

if ($command === 'list') {
    if ($tasks === []) {
        echo "No tasks yet.\n";
        exit(0);
    }
    foreach ($tasks as $task) {
        echo "#{$task['id']} {$task['title']}\n";
    }
    exit(0);
}

echo "Usage: php bin/app.php [list|add \"Task\"]\n";
exit(1);
