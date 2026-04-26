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

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

This chapter is **read-only in the browser**: no `curl` is required. See [Browser vs curl](../README.md#browser-vs-curl) for when other chapters use the terminal.

**Port:** `8005`. [Run the app](#run-the-app) must include **`db:seed`** so `posts` exist for the dashboard.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, seeded, server **8005** |
| 1 | `/exercise` → plain text `ok` |
| 2 | `/dashboard` — HTML dashboard (stats, recent posts) |
| 3 | (Optional) No posts — `@forelse` empty state; fresh DB or truncate `posts` in tinker and reload |

**1 — Health**

In the browser, open [http://127.0.0.1:8005/exercise](http://127.0.0.1:8005/exercise). The page should show **`ok`** (plain text).

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8005/exercise"
```

**2 — Dashboard (Blade)**

In the browser, open [http://127.0.0.1:8005/dashboard](http://127.0.0.1:8005/dashboard). You should see the layout, stats, and recent posts (not a 5xx or blank error page).

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8005/dashboard" | head -c 500
```

— expect leading `<!DOCTYPE` or your layout markers.

**3 — (Optional) Empty `@forelse` branch** — In a throwaway environment, use a DB with **no** `posts` (e.g. `php artisan migrate:fresh` without `db:seed`, or truncate the table in tinker), then reload [http://127.0.0.1:8005/dashboard](http://127.0.0.1:8005/dashboard) and confirm the empty state from the lesson.

**4 — Code**

- `resources/views/dashboard/index.blade.php` — `@forelse` / empty state.
- `app/Http/Controllers/DashboardController.php` — stats + `recentPosts`.

**5 — Optional**

```bash
cd ch05-exercise-build-dashboard-ui/laravel && php artisan view:cache
```
