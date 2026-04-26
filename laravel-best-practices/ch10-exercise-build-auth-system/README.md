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

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

**`GET` / forms:** Open [http://127.0.0.1:8010/exercise](http://127.0.0.1:8010/exercise) in the **browser** (step 1). For **register, login, dashboard, and logout**, prefer the **browser** and the Blade section under **6 — Browser (UX)** below. The **`curl`** blocks are optional: same flow from the **terminal**, with cookie jars and **raw HTTP** teaching. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8010`. In this exercise app, `register`, `login`, and `logout` are **excluded from CSRF** in `bootstrap/app.php` so you can also run the full flow with `curl` and a **cookie jar** (still use real CSRF in your own projects).

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8010** |
| 1 | `/exercise` → `ok` |
| 2 | `POST /register` — **302** to `/dashboard` — session established (follow with a **second** `GET /dashboard` **or** `curl -L` **without** `-X POST`; see **note** below) |
| 3 | `GET /dashboard` with same cookies — **200** (HTML) |
| 4 | `POST /logout` — session cleared, **302** to `/login` (do not combine **`-L`** with **`-X POST`**) |
| 5 | `POST /login` with the same user — back to dashboard (same **`-L`** rule as register) |
| 6 | Bad login — error flash / **422** or redirect with errors (see test suite) |
| 7 | `php artisan test --filter=AuthenticationTest` — green |

**1 — Health**

In the browser, open [http://127.0.0.1:8010/exercise](http://127.0.0.1:8010/exercise). Expect **`ok`**.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8010/exercise"
```

**2 — Register a new user (form body; unique email + `password` / `password_confirmation` ≥ 8 chars)**

`curl` **must not** use **`-X POST` together with `-L`**: `-X` forces the same method on every hop, so a **POST** can hit **`GET` routes** (e.g. `/dashboard`) and return **405**. Use `--data` (or `-d`) without `-X` so the first request is `POST` and **302** hops use **`GET`**, or follow steps **2a** + **2b** (two calls, no `-L`).

In **`application/x-www-form-urlencoded`**, a **`+` in a field value is decoded as a space** (so e.g. `learner+123@…` can fail email validation). Use a different separator (e.g. `learner.$(date +%s)@…`) or encode `+` as `%2B`.

**Option A — one line with follow (`-L`, no `-X`)**

```bash
E="learner.$(date +%s)@example.com"
curl -sS -c cj -b cj -L -o /dev/null -w "HTTP:%{http_code} final_url:%{url_effective}\n" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "name=Learner&email=$E&password=password1&password_confirmation=password1" \
  "http://127.0.0.1:8010/register"
```

**Option B — explicit 302, then `GET` dashboard**

```bash
E="learner.$(date +%s)@example.com"
curl -sS -c cj -b cj -o /dev/null -w "register_status:%{http_code}\n" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "name=Learner&email=$E&password=password1&password_confirmation=password1" \
  "http://127.0.0.1:8010/register"
curl -sS -b cj -o /dev/null -w "dashboard:%{http_code} URL:%{url_effective}\n" "http://127.0.0.1:8010/dashboard"
```

Expect: **register `302`**, **dashboard `200`**.

**3 — Dashboard (after register/login in the browser, or after step 2 with `cj` from `curl`)**

In the **browser** (same session as register/login), open [http://127.0.0.1:8010/dashboard](http://127.0.0.1:8010/dashboard). Or, from the `curl` register flow, run:

```bash
curl -sS -b cj "http://127.0.0.1:8010/dashboard" | head -c 400
```

**4 — Logout** (single `POST` — do **not** add `-L` with `-X POST` or a follow request may hit **GET** routes with `POST`)

```bash
curl -sS -c cj -b cj -o /dev/null -w "HTTP:%{http_code}\n" \
  -X POST "http://127.0.0.1:8010/logout"
```

Expect: **302** to `/login` (or **200** to login page, depending on framework redirect handling—either way, session is cleared).

**5 — Login again** (use the **same** `email=…` you registered; if you opened a new shell, set `E` first or paste the address) — use `-L` **without** `-X POST`, or two steps like register

```bash
curl -sS -c cj -b cj -L -o /dev/null -w "HTTP:%{http_code} final_url:%{url_effective}\n" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "email=$E&password=password1" \
  "http://127.0.0.1:8010/login"
```

**6 — Browser (UX)** — open [http://127.0.0.1:8010/register](http://127.0.0.1:8010/register) and [http://127.0.0.1:8010/login](http://127.0.0.1:8010/login) to exercise Blade forms and throttling in the course.

**7 — Tests**

```bash
cd ch10-exercise-build-auth-system/laravel && php artisan test --filter=AuthenticationTest
```
