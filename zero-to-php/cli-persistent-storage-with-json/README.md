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

1. From this folder, run:
   ```bash
   php bin/app.php list
   php bin/app.php add "Buy milk"
   php bin/app.php list
   ```
2. Confirm `storage/tasks.json` is created automatically.
3. Re-run `php bin/app.php list` and confirm the saved task persists.

← [Zero to PHP](../README.md)
