# Chapter 8 — Exercise: optimise queries

**Course page:** [Tighten a slow list endpoint and prove the win with query counts](https://laravel.learnio.dev/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — you **need** [Run the app](#run-the-app) with `db:seed` so `orders` exist.

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

### Lesson acceptance (course)

- **Narrow the query** for the admin list: `select` only needed columns, `with` the relations you need, `limit` / pagination as the lesson says — compare to a “load the world” version.
- **Index migration** (if the course required it) matches your filter / sort column(s).
- **Cache or aggregate** endpoint (here `/reports/monthly-revenue`) should behave as described in the lesson (hit it twice, observe cached behaviour if you wired `Cache`).

---

## How to test everything

**Browser first (optional):** For **GET** routes you can open the same URLs in your browser. If the app has a **login** (or `/_exercise/login`), sign in in the browser and browse—`curl` is only needed for **POST / PUT / PATCH / DELETE**, JSON bodies, or when you want a copy-pastable one-liner. See [Browser vs curl](../README.md#browser-vs-curl).


**Port:** `8008`. Run **`php artisan db:seed`** (included in the Run block if you used it) so **orders** exist.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, seeded, server **8008** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /admin/orders` — JSON list (limited columns, with `user`) |
| 3 | `GET /reports/monthly-revenue` — JSON aggregate (uses cache in controller) |
| 4 | Lesson: compare query count to “load everything” anti-pattern (Debugbar / `DB::listen`) |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8008/exercise"
```

**2 — Admin orders (JSON)**

```bash
curl -sS -H "Accept: application/json" "http://127.0.0.1:8008/admin/orders"
```

**3 — Monthly revenue report**

```bash
curl -sS -H "Accept: application/json" "http://127.0.0.1:8008/reports/monthly-revenue"
```

**4 — Implementation**

- `app/Http/Controllers/AdminOrderController.php` — `select`, `with`, `limit`.
- Migrations for index on `status` + `created_at` (when present).
