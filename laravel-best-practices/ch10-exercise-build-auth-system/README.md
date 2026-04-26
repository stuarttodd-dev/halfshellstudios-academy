# Chapter 10 — Exercise: authentication (web) + feature tests

**Course page:** [Build a coherent authentication layer](https://laravel.learnio.dev/learn/sections/chapter-10-authentication/exercise-build-auth-system)

## Run the app

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch10-exercise-build-auth-system`** and port **8010**.

## What’s in the app

Under **`laravel/`**: `routes/auth.php` (guest + auth groups, throttle on login), `AuthController`, form requests, Blade `auth/login` + `auth/register` + `dashboard`, `tests/Feature/AuthenticationTest.php`, merged via `routes/solution.php`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Feature tests:** `php artisan test --filter=AuthenticationTest` — guest blocked from dashboard, register, login, wrong password, logout paths as defined in the test.
3. **Browser:** visit `/register`, `/login`, then `/dashboard` when authenticated; confirm CSRF and `old('email')` behaviour on failed login.
4. **Throttle:** repeated failed logins should eventually hit 429 (rate limit), per route config.
