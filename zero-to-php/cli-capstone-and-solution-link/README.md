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

1. Ensure your CLI app includes all commands from lessons 16.1-16.5.
2. Run the command sequence shown in this README from an empty `storage/tasks.json`.
3. Confirm each step behaves correctly and JSON state changes as expected after each command.

← [Zero to PHP](../README.md)
