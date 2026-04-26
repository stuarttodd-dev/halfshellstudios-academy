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

**Browser (recommended for all GETs):** Nothing here blocks a normal browser. Open **`/exercise`**, then **`/_exercise/login`** (local only — it logs you in and shows a short message), then **`/dashboard`** and **`/billing`** in the same tab/session. For **`POST /billing/plan`**, throttling, and the **copy-pastable** checks that need a **cookie file**, use the **`curl`** blocks below. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8003`. `POST` to `/billing/plan` is **CSRF-exempt** in this app’s `bootstrap/app.php` so the `curl` examples work without a token; in a real app you would submit a form with CSRF.

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

In the browser, open **`http://127.0.0.1:8003/exercise`**. Expect **`ok`**.

*Optional (terminal):* `curl -sS "http://127.0.0.1:8003/exercise"`

**2 — Dev login (same browser session as the steps above)** — in the browser, open **`http://127.0.0.1:8003/_exercise/login`**. You should see text that you are logged in (local). Then continue with steps 3–4 **without** closing the tab.

*Cookie-jar / `curl` path (if you are not using the browser for GETs):* you **must** use `-c cj` so the session is **written**; later commands use `-b cj`. The path `cj` is **relative to your current directory**; or use e.g. `-c /tmp/ch03.cj -b /tmp/ch03.cj` consistently. If a later request says **`Unauthenticated`**, re-run the login.

```bash
curl -sS -c cj -b cj "http://127.0.0.1:8003/_exercise/login"
```

**2b — All-in-one (terminal: login + one POST, avoids a missing cookie file)**

```bash
curl -sS -c cj -b cj "http://127.0.0.1:8003/_exercise/login" >/dev/null && \
curl -sS -X POST -b cj "http://127.0.0.1:8003/billing/plan" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"plan":"premium"}'
```

**3 — Dashboard (requires auth)** — in the same browser **after** step 2, open **`http://127.0.0.1:8003/dashboard`**.

*Optional (curl):* `curl -sS -b cj "http://127.0.0.1:8003/dashboard"` (if using `cj` from the terminal)

**4 — Billing (requires `subscribed` user)** — in the browser, open **`http://127.0.0.1:8003/billing`**.

*Optional (curl):* `curl -sS -b cj "http://127.0.0.1:8003/billing"`

**5 — Change plan (POST — `curl` or API client)**

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
