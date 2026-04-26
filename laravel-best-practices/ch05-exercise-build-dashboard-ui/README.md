# Chapter 5 — Exercise: Blade dashboard

**Course page:** [Build a reusable Blade dashboard interface](https://laravel.learnio.dev/learn/sections/chapter-5-blade-and-frontend-choice/exercise-build-dashboard-ui)

## Run the app

Seed data gives you posts to render on the dashboard.

From `laravel-best-practices/`:

```bash
cd ch05-exercise-build-dashboard-ui
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=127.0.0.1 --port=8005
```


## What’s in the app

Under **`laravel/`**: layout `resources/views/layouts/app.blade.php`, components `stat-card` and `panel`, `dashboard/index.blade.php` with `@forelse`, `DashboardController`, `Post` model + migration, `routes/dashboard.php` included from `routes/solution.php`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Dashboard:** open [http://127.0.0.1:8005/dashboard](http://127.0.0.1:8005/dashboard) — stats and recent posts should render when seed data exists.
3. **Empty state:** clear `posts` (or use a fresh DB without seed) and reload — `@forelse` should show the empty branch.
4. Optional: `php artisan view:cache` to ensure Blade compiles cleanly.
