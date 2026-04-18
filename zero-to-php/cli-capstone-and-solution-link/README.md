# Chapter 16.6 — CLI capstone

Basic capstone checklist for `cli-capstone-and-solution-link`.

- `list`, `show`, `add`, `done`, and `delete` commands run through one CLI entry script.
- Input validation returns clear messages and non-zero exit codes.
- Data persists to `storage/tasks.json`.

Suggested run sequence:

```bash
php bin/app.php add "Buy milk"
php bin/app.php list
php bin/app.php done 1
php bin/app.php show 1
php bin/app.php delete 1
```

Reference: [Zero to PHP - CLI build](https://github.com/stuartp-dev/zero-to-php-cli-build)

## Solution walkthrough

This capstone combines the earlier CLI lessons into one small but complete task app: parsing commands, validating input, persisting JSON, and handling list/show/done/delete flows.  
The command sequence demonstrates a full lifecycle from create to removal.

## How to test

1. From this folder, run:
   ```bash
   php bin/app.php add "Buy milk"
   php bin/app.php add "Write recap"
   php bin/app.php list
   php bin/app.php done 1
   php bin/app.php show 1
   php bin/app.php delete 1
   php bin/app.php list
   ```
2. Confirm invalid inputs fail with non-zero exit codes:
   ```bash
   php bin/app.php done nope
   php bin/app.php add ""
   ```
3. Confirm `storage/tasks.json` persists updates between command runs.

← [Zero to PHP](../README.md)
