# Chapter 5 — Exercise: Blade dashboard

**Course page:** [Build a reusable Blade dashboard interface](http://127.0.0.1:38080/learn/sections/chapter-5-blade-and-frontend-choice/exercise-build-dashboard-ui)

## What to copy

- `files/resources/views/layouts/app.blade.php` — base layout.
- `files/resources/views/components/stat-card.blade.php` — `$label`, `$value` props.
- `files/resources/views/components/panel.blade.php` — optional `title` + default slot.
- `files/resources/views/dashboard/index.blade.php` — `@forelse` for recent posts.
- `files/app/Http/Controllers/DashboardController.php` — passes `stats` and `recentPosts`.
- `files/routes/dashboard.php` — `GET /dashboard` named `dashboard`.

The exercise assumes a `Post` model exists; adjust the namespace in the controller or swap `Post` for a model you have.

## Run

```bash
php artisan view:cache  # optional
open http://127.0.0.1:8000/dashboard
```

Clear state: ensure `recentPosts` is empty to see the empty branch of `@forelse`.
