# Chapter 16.4 — CLI list and show

Basic solution for `cli-list-and-detail-commands`.

```php
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
            echo json_encode($task, JSON_PRETTY_PRINT) . "\n";
            return;
        }
    }
    echo "Task {$id} not found.\n";
}
```

## Solution walkthrough

`listTasks()` prints stable lines with id, title, and status for each task.  
`showTask()` finds one task by id and prints details or a clear not-found message.

## How to test

1. From this folder, run:
   ```bash
   php bin/app.php list
   php bin/app.php show 1
   php bin/app.php show 999
   ```
2. Confirm `list` prints id/title/status lines from `storage/tasks.json`.
3. Confirm `show` returns task JSON for an existing id and a readable error for missing ids.

← [Zero to PHP](../README.md)
