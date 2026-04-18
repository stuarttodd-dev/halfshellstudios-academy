# Chapter 16.18 — CRUD capstone

Basic capstone checklist for `crud-capstone-and-solution-link`.

- List/create/edit/delete flows work end-to-end.
- Server-side validation blocks invalid writes.
- Persistence logic is extracted from route handlers.
- Failure paths return clear status/messages.

Reference: [Zero to PHP - CRUD build](https://github.com/stuartp-dev/zero-to-php-crud-build)

## Solution walkthrough

This capstone joins all CRUD pieces: stable routing, form validation, edit/delete safety, and extracted persistence.  
The result is a complete beginner CRUD app with predictable behavior.

## How to test

1. From this folder, start the app:
   ```bash
   php -S 127.0.0.1:8018 -t public
   ```
2. Open `http://127.0.0.1:8018` and run full CRUD flow:
   - create task
   - edit task
   - delete task
3. Submit invalid create/edit values and confirm inline validation blocks writes.
4. Try editing a missing id (`/?action=edit&id=999`) and confirm not-found behavior.
5. Confirm persistence by checking `storage/tasks.json` and reloading the app.

← [Zero to PHP](../README.md)
