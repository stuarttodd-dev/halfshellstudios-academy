# Chapter 2 — Exercise: build CRUD routes

**Course page:** [Build a complete product routing surface](https://laravel.learnio.dev/learn/sections/chapter-2-routing-controllers-request/exercise-build-crud-routes)

## Run the app

From `laravel-best-practices/`:

```bash
cd ch02-exercise-build-crud-routes
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8002
```


## What’s in the app

The full example lives under **`laravel/`**: `Product` model, products migration, `ProductController`, and `routes/products.php` (included from `routes/solution.php`).

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **List routes:** `php artisan route:list --path=products -v` — you should see the REST-style `products` resource routes.
3. **Route model binding + constraint:** `GET /products/1` (after migrate) should resolve a product; **`GET /products/not-a-number`** should **404** thanks to `whereNumber('product')` on the parameter.
4. **Verbs:** exercise `GET`, `POST`, `PUT`/`PATCH`, `DELETE` with `curl` or an HTTP client as in the lesson (adjust host/port).
5. **Read the code:** open `app/Http/Controllers/ProductController.php` and the `Route::prefix('products')` group in `routes/products.php` and confirm PATCH and DELETE are explicit registrations.

## Notes

- `whereNumber('product')` keeps non-numeric segments from being passed into binding.
- `PATCH` and `DELETE` are separate registrations (REST-style).
