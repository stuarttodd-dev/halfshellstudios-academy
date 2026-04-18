# Chapter 16.5 — CLI done/delete

Basic solution for `cli-update-delete-and-error-handling`.

```php
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
```

## Solution walkthrough

`markDone()` updates a single task and returns `true` only when the id exists.  
`deleteTask()` removes by id, reindexes the array, and returns whether anything was deleted.

## How to test

1. From this folder, run:
   ```bash
   php bin/app.php done 1
   php bin/app.php delete 2
   php bin/app.php done 999
   ```
2. Confirm valid ids mutate `storage/tasks.json`.
3. Confirm missing ids return clean error handling and non-zero exit.

← [Zero to PHP](../README.md)
