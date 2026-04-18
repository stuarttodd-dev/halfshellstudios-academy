# Chapter 16.1 — CLI project setup

Basic solution for `cli-project-setup-and-command-entrypoint`.

```php
<?php
declare(strict_types=1);

$command = $argv[1] ?? '';
$value = trim(implode(' ', array_slice($argv, 2)));

switch ($command) {
    case 'list':
        echo "No tasks yet.\n";
        break;
    case 'add':
        echo $value === '' ? "Usage: php bin/app.php add \"Task\"\n" : "Added: {$value}\n";
        break;
    default:
        echo "Usage: php bin/app.php [list|add \"Task\"]\n";
        exit(1);
}
```

## Solution walkthrough

The script reads `$argv`, routes commands with `switch`, and returns a usage message for missing/unknown commands.  
It keeps one consistent command shape: `list` and `add "Task"`.

## How to test

1. From this folder, run:
   ```bash
   php bin/app.php list
   php bin/app.php add "Buy milk"
   php bin/app.php nope
   ```
2. Confirm `list` and `add` work.
3. Confirm unknown command prints usage and exits non-zero.

← [Zero to PHP](../README.md)
