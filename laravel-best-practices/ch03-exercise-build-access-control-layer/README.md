# Chapter 3 — Exercise: build an access control layer (middleware)

**Course page:** [Build a complete middleware-based access boundary](http://127.0.0.1:38080/learn/sections/chapter-3-middleware/exercise-build-access-control-layer)

## Run the app

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch03-exercise-build-access-control-layer`** and port **8003**.

## What’s in the app

Under **`laravel/`**: `users.is_subscribed` migration, `EnsureUserIsSubscribed` middleware, alias registration in `bootstrap/app.php` (see `bootstrap/middleware-aliases.php` for the snippet shape), dev auth routes (`routes/dev-auth.php`), billing/dashboard/plan controllers, and `routes/solution.php` wiring.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Routes:** `php artisan route:list -v --path=dashboard` and `--path=billing` — confirm middleware stacks.
3. **Behaviour (from the lesson):** guests redirected or blocked from protected areas; unsubscribed user gets **403** on billing; repeated `POST /billing/plan` can return **429** (throttle) per your route definition.
4. Use `curl` or browser + session (or a small feature test with `actingAs`) to assert status codes match the lesson table.

## Prerequisite

This sample assumes **`users.is_subscribed`** — already in the bundled migration under `laravel/database/migrations/`.
