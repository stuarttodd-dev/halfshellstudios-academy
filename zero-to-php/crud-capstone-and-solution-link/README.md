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

1. Run through full lifecycle: create, list, edit, and delete.
2. Verify invalid submissions do not write data.
3. Confirm missing-record operations return clear errors and app remains stable.

← [Zero to PHP](../README.md)
