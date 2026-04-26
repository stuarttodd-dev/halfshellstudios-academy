# `_laravel-skeleton` (Half Shell Studios Academy)

This directory is a **Laravel 13** app used as a **template**. It is **not** the place learners run the course from day to day.

## How it fits the repo

- [`php scripts/materialize_laravel_apps.php`](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/laravel-best-practices/scripts/materialize_laravel_apps.php) (run from the `laravel-best-practices/` directory) copies this folder to each `chNN-exercise-*/laravel/`, merges that chapter’s `files/` on top, and wires `routes/web.php` to `routes/solution.php`. *Source-trees* are explained in the [parent README #source-trees](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/laravel-best-practices/README.md#source-trees-files-and-laravel).
- **Learners and instructors:** use the [Laravel best practices README](https://github.com/stuarttodd-dev/halfshellstudios-academy/blob/main/laravel-best-practices/README.md) (prerequisites, **per-chapter `cd` table**, `composer` / `.env` / `migrate` / `serve` for every exercise). In a local clone, from a path like `ch02-exercise-…/laravel/`, that file is at `../../README.md`.

## Working on the skeleton (maintainers)

1. `cd` to **this** directory (`_laravel-skeleton`).

2. Install dependencies and boot the app like any Laravel project:

   ```bash
   cp -n .env.example .env
   composer install
   php artisan key:generate
   touch database/database.sqlite   # if the file is missing; materialize also creates it in chapter apps
   php artisan migrate
   php artisan serve
   ```

3. `vendor/` and `node_modules/` are **not** committed; clone/fresh checkouts need `composer install` (and `npm install` only if you work on the Vite frontend).

4. After changing the skeleton, from `laravel-best-practices/` run `php scripts/materialize_laravel_apps.php`, then `composer install` in any `ch*-exercise-*/laravel` apps you use.

## Customisations in this copy

- Base [`app/Http/Controllers/Controller.php`](app/Http/Controllers/Controller.php) uses `AuthorizesRequests` so course controllers can call `$this->authorize()` (e.g. authorisation chapter).
- `composer.json` requires **PHP `^8.3`** to match [Laravel 13](https://laravel.com/docs).

## Laravel itself

Laravel is MIT-licenced; full framework docs, community, and security policy: [laravel.com/docs](https://laravel.com/docs).
