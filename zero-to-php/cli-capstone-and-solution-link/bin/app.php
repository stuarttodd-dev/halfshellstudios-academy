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

function printUsage(): void
{
    echo "Usage:\n";
    echo "  php bin/app.php list\n";
    echo "  php bin/app.php add \"Task title\"\n";
    echo "  php bin/app.php show <id>\n";
    echo "  php bin/app.php done <id>\n";
    echo "  php bin/app.php delete <id>\n";
}

function nextId(array $tasks): int
{
    if ($tasks === []) {
        return 1;
    }

    return max(array_column($tasks, 'id')) + 1;
}

function findTaskIndexById(array $tasks, int $id): int
{
    foreach ($tasks as $index => $task) {
        if ((int) ($task['id'] ?? 0) === $id) {
            return $index;
        }
    }

    return -1;
}

$command = $argv[1] ?? '';
$tasks = loadTasks();

switch ($command) {
    case 'list':
        if ($tasks === []) {
            echo "No tasks yet.\n";
            exit(0);
        }

        foreach ($tasks as $task) {
            $status = !empty($task['done']) ? 'done' : 'todo';
            echo "#{$task['id']} {$task['title']} [{$status}]\n";
        }
        exit(0);

    case 'add':
        $title = trim(implode(' ', array_slice($argv, 2)));
        if ($title === '') {
            fwrite(STDERR, "Error: add requires a non-empty task.\n");
            exit(1);
        }

        $task = ['id' => nextId($tasks), 'title' => $title, 'done' => false];
        $tasks[] = $task;
        saveTasks($tasks);
        echo "Added #{$task['id']}: {$task['title']}\n";
        exit(0);

    case 'show':
        if (!ctype_digit($argv[2] ?? '')) {
            fwrite(STDERR, "Error: show requires a numeric task id.\n");
            exit(1);
        }

        $id = (int) $argv[2];
        $index = findTaskIndexById($tasks, $id);
        if ($index < 0) {
            fwrite(STDERR, "Task {$id} not found.\n");
            exit(1);
        }

        echo json_encode($tasks[$index], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
        exit(0);

    case 'done':
        if (!ctype_digit($argv[2] ?? '')) {
            fwrite(STDERR, "Error: done requires a numeric task id.\n");
            exit(1);
        }

        $id = (int) $argv[2];
        $index = findTaskIndexById($tasks, $id);
        if ($index < 0) {
            fwrite(STDERR, "Task {$id} not found.\n");
            exit(1);
        }

        $tasks[$index]['done'] = true;
        saveTasks($tasks);
        echo "Marked #{$id} as done.\n";
        exit(0);

    case 'delete':
        if (!ctype_digit($argv[2] ?? '')) {
            fwrite(STDERR, "Error: delete requires a numeric task id.\n");
            exit(1);
        }

        $id = (int) $argv[2];
        $before = count($tasks);
        $tasks = array_values(array_filter($tasks, static fn(array $task): bool => (int) ($task['id'] ?? 0) !== $id));
        if (count($tasks) === $before) {
            fwrite(STDERR, "Task {$id} not found.\n");
            exit(1);
        }

        saveTasks($tasks);
        echo "Deleted #{$id}.\n";
        exit(0);

    default:
        printUsage();
        exit(1);
}
