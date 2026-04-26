# Chapter 8 — Exercise: optimise queries

**Course page:** [Tighten a slow list endpoint and prove the win with query counts](http://127.0.0.1:38080/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries)

## What to copy

- `files/app/Http/Controllers/AdminOrderController.php` — improved `index` (SQL filter, order, limit 50, `with`).
- `files/app/Http/Controllers/MonthlyRevenueReportController.php` — grouped aggregate via `DB::table` (optional part of the lesson).
- `files/database/migrations/0001_01_01_000007_add_index_to_orders_status_created.php` — composite index for `where status = paid` + `order by created_at desc`. Adjust column names to match your `orders` table, then run `EXPLAIN` on the target database.
- `files/app/Models/Order.php` — minimal, with `user()` relation.

Create an `orders` migration in your app if you do not have one yet (`id`, `user_id`, `status`, `total`, `created_at`).

## Measure

Enable `DB::listen` in `AppServiceProvider` during local profiling, or use Telescope/Debugbar, as the lesson suggests. Assert query count dropped versus the `Order::all()` anti-pattern in the course text.
