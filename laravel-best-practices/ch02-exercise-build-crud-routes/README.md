# Chapter 2 — Exercise: build CRUD routes

**Course page:** [Build a complete product routing surface](http://127.0.0.1:38080/learn/sections/chapter-2-routing-controllers-request/exercise-build-crud-routes)

## What this folder contains

A minimal `Product` resource: migration, model, `ProductController`, and `routes/web.php` fragment. Merge the route group into your app’s `routes/web.php` (or `api.php` if you prefer JSON-only; the course example uses web-style URLs).

## Apply the solution

1. `php artisan make:model Product -m` (or copy the migration from `files/`).
2. Copy `files/database/migrations/*_create_products_table.php` into `database/migrations/`.
3. Copy `files/app/Models/Product.php` and `files/app/Http/Controllers/ProductController.php`.
4. Add the `Route::prefix('products')` group from `files/routes/products.php` into `routes/web.php` (e.g. `require` or paste).
5. `php artisan migrate` then `php artisan route:list --path=products -v`.
6. Hit the `curl` examples from the lesson (adjust host/port).

## Notes

- `whereNumber('product')` keeps non-numeric segments from being passed into binding; `GET /products/not-a-number` should 404.
- `PATCH` and `DELETE` are separate registrations so verb intent stays explicit (REST-style).
