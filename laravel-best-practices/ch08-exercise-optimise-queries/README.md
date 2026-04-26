# Chapter 8 — Exercise: optimise queries

**Course page:** [Tighten a slow list endpoint and prove the win with query counts](http://127.0.0.1:38080/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries)

## Run the app

Seeded orders + users make the admin list meaningful — run **`php artisan db:seed --force`** after migrate.

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch08-exercise-optimise-queries`**, port **8008**, and **seed**.

## What’s in the app

Under **`laravel/`**: `Order` model, `orders` migration, `AdminOrderController` (filtered query + `with` + limit), optional `MonthlyRevenueReportController`, index migration for `status`/`created_at`, routes under `routes/solution.php`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **List endpoint:** open the admin orders route from `routes/solution.php` (or `php artisan route:list --path=admin`) — list should load without loading every row into memory.
3. **Query count:** enable `DB::listen` in `AppServiceProvider` (temporarily) or use Debugbar/Telescope; compare to the “load everything” anti-pattern in the lesson.
4. **EXPLAIN (optional):** on MySQL/Postgres in a real environment, `EXPLAIN` the indexed query; SQLite in this repo is for convenience only.
5. **Revenue report:** if implemented, hit the report route and confirm aggregate SQL shape.
