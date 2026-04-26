# Chapter 10 — Exercise: authentication (web) + feature tests

**Course page:** [Build a coherent authentication layer](https://laravel.learnio.dev/learn/sections/chapter-10-authentication/exercise-build-auth-system)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — for **`php artisan test`**, you must be in **`ch10-…/laravel`** with `vendor/` from `composer install` ([Run the app](#run-the-app)). CSRF is **relaxed** on auth routes in this sample only ([read why](../README.md#csrf-in-exercise-apps)).

## Run the app

From `laravel-best-practices/`:

```bash
cd ch10-exercise-build-auth-system
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8010
```


## What’s in the app

Under **`laravel/`**: `routes/auth.php` (guest + auth groups, throttle on login), `AuthController`, form requests, Blade `auth/login` + `auth/register` + `dashboard`, `tests/Feature/AuthenticationTest.php`, merged via `routes/solution.php`.

### Lesson acceptance (course)

- **Register + login** work with the lesson’s **Form request** validation; **logout** ends the session; **throttle** on auth attempts is observable when you try it from the browser.
- **Feature tests** in `tests/Feature/AuthenticationTest.php` cover guest redirect, success paths, and wrong password (run them below).

---

## How to test everything

**Port:** `8010`. In this exercise app, `register`, `login`, and `logout` are **excluded from CSRF** in `bootstrap/app.php` so you can run the full flow with `curl` and a **cookie jar** (still use real CSRF in your own projects).

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8010** |
| 1 | `/exercise` → `ok` |
| 2 | `POST /register` — **302** to `/dashboard` (use `-L` to follow) — session established |
| 3 | `GET /dashboard` with same cookies — **200** (HTML) |
| 4 | `POST /logout` — session cleared, **302** to `/login` |
| 5 | `POST /login` with the same user — back to dashboard |
| 6 | Bad login — error flash / **422** or redirect with errors (see test suite) |
| 7 | `php artisan test --filter=AuthenticationTest` — green |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8010/exercise"
```

**2 — Register a new user (form body; unique email + `password` / `password_confirmation` ≥ 8 chars)**

```bash
E="learner+$(date +%s)@example.com"
curl -sS -c cj -b cj -L -o /dev/null -w "HTTP:%{http_code} final_url:%{url_effective}\n" \
  -X POST "http://127.0.0.1:8010/register" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "name=Learner&email=$E&password=password1&password_confirmation=password1"
```

**3 — Dashboard (must send cookies from step 2)**

```bash
curl -sS -b cj "http://127.0.0.1:8010/dashboard" | head -c 400
```

**4 — Logout**

```bash
curl -sS -c cj -b cj -L -o /dev/null -w "HTTP:%{http_code} final_url:%{url_effective}\n" \
  -X POST "http://127.0.0.1:8010/logout"
```

**5 — Login again** (use the **same** `email=…` you registered; if you opened a new shell, set `E` first or paste the address)

```bash
curl -sS -c cj -b cj -L -o /dev/null -w "HTTP:%{http_code} final_url:%{url_effective}\n" \
  -X POST "http://127.0.0.1:8010/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "email=$E&password=password1"
```

**6 — Browser (UX)** — open `http://127.0.0.1:8010/register` and `…/login` to exercise Blade forms and throttling in the course.

**7 — Tests**

```bash
cd ch10-exercise-build-auth-system/laravel && php artisan test --filter=AuthenticationTest
```
