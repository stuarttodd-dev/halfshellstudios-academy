# Chapter 8 — Exercise: optimise queries

**Course page:** [Tighten a slow list endpoint and prove the win with query counts](https://laravel.learnio.dev/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries)

## Run the app

Seeded orders + users make the admin list meaningful.

From `laravel-best-practices/`:

```bash
cd ch08-exercise-optimise-queries
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=127.0.0.1 --port=8008
```


## What’s in the app

Under **`laravel/`**: `Order` model, `orders` migration, `AdminOrderController` (filtered query + `with` + limit), optional `MonthlyRevenueReportController`, index migration for `status`/`created_at`, routes under `routes/solution.php`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **List endpoint:** open the admin orders route from `routes/solution.php` (or `php artisan route:list --path=admin`) — list should load without loading every row into memory.
3. **Query count:** enable `DB::listen` in `AppServiceProvider` (temporarily) or use Debugbar/Telescope; compare to the “load everything” anti-pattern in the lesson.
4. **EXPLAIN (optional):** on MySQL/Postgres in a real environment, `EXPLAIN` the indexed query; SQLite in this repo is for convenience only.
5. **Revenue report:** if implemented, hit the report route and confirm aggregate SQL shape.
