# `_laravel-skeleton` (Half Shell Studios Academy)

Laravel 13 app used as a **template** for each `chNN-exercise-*/laravel/`. **Course setup, `files/` vs `laravel/`, the materialize script, and a per-chapter `cd` table** all live in **`laravel-best-practices/README.md`** in this repository — work from there, not this folder in isolation. After editing the skeleton, run `php scripts/materialize_laravel_apps.php` with your shell’s working directory set to `laravel-best-practices/`.

## Working on the skeleton

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

3. `vendor/` and `node_modules/` are **not** committed; clone/fresh checkouts need `composer install` (and `npm install` only if you work on the Vite frontend). After you change this tree, rematerialise (see intro) and run `composer install` again in any `ch*/laravel` apps you use.

## Customisations in this copy

- Base [`app/Http/Controllers/Controller.php`](app/Http/Controllers/Controller.php) uses `AuthorizesRequests` so course controllers can call `$this->authorize()` (e.g. authorisation chapter).
- `composer.json` requires **PHP `^8.3`** to match [Laravel 13](https://laravel.com/docs).

## Laravel itself

Laravel is MIT-licenced; full framework docs, community, and security policy: [laravel.com/docs](https://laravel.com/docs).
