# Runnable Laravel app (PHP to Laravel course)

This directory contains a normal **Laravel 13** app (`artisan`, `composer.json`, etc.).

- **In the repo:** each exercise’s code lives under `chNN-exercise-*/laravel/`. **Edit and run that folder** — it is the source of truth for the solution.
- **`_laravel-skeleton/`** (this tree when not inside a chapter) is a **template** for new exercises or framework upgrades — see [**Maintaining this folder**](../README.md#maintaining-this-folder) in `laravel-best-practices/README.md`.

**Learners:** open the **README.md** next to this `laravel` folder (one level up) for **how to run and test** this chapter. Global setup: **`laravel-best-practices/README.md`** (repository root for that folder). The **README in the parent of this `laravel` directory** is the chapter’s run & test guide.

## Quick install (from this `laravel/` directory)

```bash
cp -n .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # if missing
php artisan migrate
```

**Serve** on this chapter’s port (**8000 + chapter number**; e.g. chapter 5 → **8005** — see the parent **README** or `laravel-best-practices/README.md`). From the chapter’s `laravel/`: `php artisan serve --host=127.0.0.1 --port=<port>` (see [Setup one chapter app](../README.md#setup-one-chapter-app) in the parent `laravel-best-practices/README.md`).

## Customisations in the course template

- Base `app/Http/Controllers/Controller.php` uses `AuthorizesRequests` where needed.
- PHP **^8.3** for Laravel 13.

## Laravel

[laravel.com/docs](https://laravel.com/docs)
