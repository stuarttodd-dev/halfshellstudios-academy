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

1. Add these helpers to your CLI app.
2. Run a sequence:
   ```bash
   php bin/app.php add "A"
   php bin/app.php done 1
   php bin/app.php delete 1
   php bin/app.php done 999
   ```
3. Confirm valid ids mutate state and missing ids return clean failure handling.

← [Zero to PHP](../README.md)
