# Chapter 16.3 — CLI JSON persistence

Basic solution for `cli-persistent-storage-with-json`.

```php
<?php
declare(strict_types=1);

function loadTasks(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $json = file_get_contents($path);
    return is_string($json) && $json !== '' ? json_decode($json, true, 512, JSON_THROW_ON_ERROR) : [];
}

function saveTasks(string $path, array $tasks): void {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, json_encode($tasks, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
}
```

## Solution walkthrough

`loadTasks()` returns an empty array when storage does not exist and decodes JSON safely when it does.  
`saveTasks()` creates the storage directory if needed and writes pretty JSON for readable persistence.

## How to test

1. Put the helper functions in your CLI app.
2. Add a task, call `saveTasks()`, then run the script again and call `loadTasks()`.
3. Confirm `storage/tasks.json` exists and data survives multiple runs.

← [Zero to PHP](../README.md)
