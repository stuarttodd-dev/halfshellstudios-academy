# Chapter 5 — Exercise: Blade dashboard

**Course page:** [Build a reusable Blade dashboard interface](https://laravel.learnio.dev/learn/sections/chapter-5-blade-and-frontend-choice/exercise-build-dashboard-ui)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — this chapter’s `DatabaseSeeder` creates sample **posts**; do not skip [Run the app](#run-the-app)’s `db:seed`.

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

### Lesson acceptance (course)

- **Layout + components:** a shared layout and reusable Blade **components** for the dashboard (match the lesson naming/structure in your hand-in).
- **Data in the view:** `DashboardController` passes stats / recent content; **`@forelse` empty branch** is visible if there are no posts (test by truncating `posts` or skipping seed in a throwaway DB).

---

## How to test everything

**Port:** `8005`. [Run the app](#run-the-app) must include **`db:seed`** so `posts` exist for the dashboard.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, seeded, server **8005** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /dashboard` returns **200** HTML (stats, recent posts) |
| 3 | (Optional) Fresh DB, no seed — `@forelse` empty state in browser; or truncate `posts` in tinker and reload |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8005/exercise"
```

**2 — Dashboard (Blade HTML)**

```bash
curl -sS "http://127.0.0.1:8005/dashboard" | head -c 500
```

Expect: leading `<!DOCTYPE` or your layout markers — not a 5xx.

**3 — In the browser (recommended for Blade/UX)** — open `http://127.0.0.1:8005/dashboard` and confirm stats and `@forelse` table.

**4 — Code**

- `resources/views/dashboard/index.blade.php` — `@forelse` / empty state.
- `app/Http/Controllers/DashboardController.php` — stats + `recentPosts`.

**5 — Optional**

```bash
cd ch05-exercise-build-dashboard-ui/laravel && php artisan view:cache
```
