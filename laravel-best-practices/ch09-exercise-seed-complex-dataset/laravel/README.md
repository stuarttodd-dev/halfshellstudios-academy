# Runnable Laravel app (PHP to Laravel course)

You are inside a directory that contains `artisan` — a normal Laravel 13 app.

- **Named chapter exercise** (e.g. `ch02-exercise-…/laravel/`, `ch01-exercise-hello-laravel-app/laravel/`): read **[this exercise’s `README.md`](../README.md)** (one level up from `laravel/`), and for every chapter’s commands see **`laravel-best-practices/README.md`** at the repository root.
- **`_laravel-skeleton` only** (the template under `laravel-best-practices/_laravel-skeleton/`): maintainers use this tree to refresh all chapter apps; it is **not** the lesson “solution” for [chapter 1 Hello Laravel](http://127.0.0.1:38080/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app). For that mini-project, open **`ch01-exercise-hello-laravel-app/`** instead.

## Working on this app

1. Stay in **this** directory (the one that contains `artisan`).

2. Install dependencies and boot the app:

   ```bash
   cp -n .env.example .env
   composer install
   php artisan key:generate
   touch database/database.sqlite   # if missing; materialise also creates it in chapter apps
   php artisan migrate
   php artisan serve
   ```

3. `vendor/` and `node_modules/` are **not** committed; fresh checkouts need `composer install` (and `npm install` only if you work on the Vite frontend). After changing the **template** in `_laravel-skeleton`, run `php scripts/materialize_laravel_apps.php` with the working directory set to `laravel-best-practices/`, then `composer install` again in affected `ch*/laravel` apps.

## Customisations in the course template

- Base [`app/Http/Controllers/Controller.php`](app/Http/Controllers/Controller.php) uses `AuthorizesRequests` so course controllers can call `$this->authorize()` where needed.
- `composer.json` requires **PHP `^8.3`** to match [Laravel 13](https://laravel.com/docs).

## Laravel itself

Laravel is MIT-licenced; full framework docs: [laravel.com/docs](https://laravel.com/docs).
