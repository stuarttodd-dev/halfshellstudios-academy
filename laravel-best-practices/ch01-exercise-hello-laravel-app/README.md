# Chapter 1 (Laravel tour) — Mini project: Hello Laravel app

**Course page:** [mini-project-hello-laravel-app](http://127.0.0.1:38080/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app)

## What this folder contains

- **`files/`** — a small `GET /hello` route and a Blade view (what the tour walks you toward).
- **`laravel/`** — a full runnable app (from [`_laravel-skeleton`](../_laravel-skeleton/) + `files/`), same workflow as the other chapters. See the [parent README](../README.md) for `composer` / `.env` / `migrate` / `serve`.

`_laravel-skeleton` in the repo is only the **shared template**; this folder is the **chapter 1** reference app to link from the course.

## Apply the solution (manual merge)

1. `composer create-project laravel/laravel your-app` (or use the `laravel/` app here after `composer install`).
2. Copy `files/resources/views/hello.blade.php` into `resources/views/`.
3. Register the `GET /hello` route from `files/routes/solution.php` in `routes/web.php` (or use this repo’s `laravel/` layout where `web.php` already loads `solution.php`).

## Try it in this repo

```bash
cd ch01-exercise-hello-laravel-app/laravel
cp -n .env.example .env
composer install
php artisan key:generate
php artisan migrate --force
php artisan serve
# Open http://127.0.0.1:8000/hello
```

`GET /exercise` should still return `ok` (health check from the course template).
