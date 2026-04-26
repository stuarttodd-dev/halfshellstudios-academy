# Chapter 3 — Exercise: build an access control layer (middleware)

**Course page:** [Build a complete middleware-based access boundary](https://laravel.learnio.dev/learn/sections/chapter-3-middleware/exercise-build-access-control-layer)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — `/_exercise/login` is **local-only** (`app()->isLocal()`). Keep `APP_ENV=local` in `.env` (default) while following this chapter.

## Run the app

From `laravel-best-practices/`:

```bash
cd ch03-exercise-build-access-control-layer
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8003
```


## What’s in the app

Under **`laravel/`**: `users.is_subscribed` migration, `EnsureUserIsSubscribed` middleware, alias registration in `bootstrap/app.php` (see `bootstrap/middleware-aliases.php` for the snippet shape), dev auth routes (`routes/dev-auth.php`), billing/dashboard/plan controllers, and `routes/solution.php` wiring.

### Lesson acceptance (course)

- **Stacked middleware:** authenticated user can see `/dashboard` after dev login; `/billing` also requires the **`subscribed` alias** and **403** if the user is not subscribed.
- **Plan change:** `POST /billing/plan` with JSON `{"plan":"premium"}` (or `standard`) succeeds with the right session.
- **Throttle** (if you exercise it in the course): many rapid POSTs to `/billing/plan` can return **429** (route uses `throttle:5,1`).

**If you get stuck:** your shell must be **`ch03-exercise-build-access-control-layer/laravel`** when you run `php artisan` ([Run the app](#run-the-app)). If `/_exercise/login` is 404, you are not in `local` or the route is missing from `web.php` → `solution.php`.

---

## How to test everything

**Port:** `8003`. `POST` to `/billing/plan` is **CSRF-exempt** in this app’s `bootstrap/app.php` so the commands below are copy-pasteable with a cookie jar.

`/_exercise/login` only works when `APP_ENV=local` and `APP_DEBUG` is on (default after setup).

| Step | Check |
| ---- | ----- |
| 0 | Migrated; server on **8003** |
| 1 | `/exercise` returns `ok` |
| 2 | Local login creates a **subscribed** user session (cookie) |
| 3 | `GET /dashboard` with that session (auth + HTML/response) |
| 4 | `GET /billing` (auth + `subscribed` middleware) — **200** |
| 5 | `POST /billing/plan` with JSON `plan: premium\|standard` — **200** and JSON `ok` |
| 6 | (Optional) Repeat `POST /billing/plan` in a loop — throttle can return **429** (see `throttle:5,1` on the route) |
| 7 | (Optional) Unsubscribed user → `GET /billing` — **403** (see **7** in commands below) |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8003/exercise"
```

**2 — Dev login (session cookie file `cj`)**

```bash
curl -sS -c cj -b cj "http://127.0.0.1:8003/_exercise/login"
```

**3 — Dashboard (requires auth)**

```bash
curl -sS -b cj "http://127.0.0.1:8003/dashboard"
```

**4 — Billing (requires `subscribed` user)**

```bash
curl -sS -b cj "http://127.0.0.1:8003/billing"
```

**5 — Change plan**

```bash
curl -sS -X POST -b cj "http://127.0.0.1:8003/billing/plan" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"plan":"premium"}'
```

**6 — Throttle (optional)** — run the same POST quickly more than 5 times in one minute; expect **429** after the limit.

**7 — (Optional) `subscribed` → 403** — With the same cookie file `cj` from dev login, flip the user off subscription in the DB, then re-hit billing:

```bash
cd ch03-exercise-build-access-control-layer/laravel && php artisan tinker --execute="\\App\\Models\\User::query()->where('email','subscribed@example.com')->update(['is_subscribed'=>0]);"
curl -sS -i -b cj "http://127.0.0.1:8003/billing"
```

Expect: **403** and message text like *Active subscription required.* Restore the flag before continuing:

```bash
cd ch03-exercise-build-access-control-layer/laravel && php artisan tinker --execute="\\App\\Models\\User::query()->where('email','subscribed@example.com')->update(['is_subscribed'=>1]);"
```

**8 — Code paths**

- `app/Http/Middleware/EnsureUserIsSubscribed.php` — 403 for unsubscribed on billing.
- `bootstrap/app.php` — middleware alias `subscribed` (see `bootstrap/middleware-aliases.php` in your lesson).
- `routes/billing-surface.php` — dashboard vs billing group middleware.

**9 — Route list**

```bash
cd ch03-exercise-build-access-control-layer/laravel && php artisan route:list --path=dashboard
```

## Prerequisite

This sample assumes **`users.is_subscribed`** — already in the bundled migration under `laravel/database/migrations/`.
