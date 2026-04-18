# Chapter 16.2 — CLI args validation

Basic solution for `cli-args-and-input-validation`.

```php
<?php
declare(strict_types=1);

$command = $argv[1] ?? '';

if ($command === 'add' && trim(implode(' ', array_slice($argv, 2))) === '') {
    fwrite(STDERR, "Error: add requires a non-empty task.\n");
    exit(1);
}

if ($command === 'done' && !ctype_digit($argv[2] ?? '')) {
    fwrite(STDERR, "Error: done requires a numeric task id.\n");
    exit(1);
}

echo "ok\n";
```

## Solution walkthrough

This adds guard clauses before command logic: `add` requires non-empty text and `done` requires a numeric id.  
Validation errors are printed to `STDERR` and exit with status `1`.

## How to test

1. From this folder, run:
   ```bash
   php bin/app.php add "Buy milk"
   php bin/app.php add ""
   php bin/app.php done abc
   php bin/app.php done 2
   ```
2. Confirm valid inputs succeed and invalid inputs fail with clear error messages.
3. Confirm invalid commands exit non-zero.

← [Zero to PHP](../README.md)
