<?php
declare(strict_types=1);

function loadTasks(string $path): array {
    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    if (!is_string($json) || $json === '') {
        return [];
    }

    return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
}

function saveTasks(string $path, array $tasks): void {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($path, json_encode($tasks, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}

$path = __DIR__ . '/storage/tasks.json';
$tasks = loadTasks($path);
$tasks[] = ['id' => count($tasks) + 1, 'title' => 'Sample task', 'done' => false];
saveTasks($path, $tasks);

echo count($tasks) . "\n";
