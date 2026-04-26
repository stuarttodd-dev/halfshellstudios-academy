# Chapter 3 — Exercise: build an access control layer (middleware)

**Course page:** [Build a complete middleware-based access boundary](http://127.0.0.1:38080/learn/sections/chapter-3-middleware/exercise-build-access-control-layer)

## Prerequisite

This sample assumes a `users.is_subscribed` boolean. Run the migration in `files/database/migrations/` (merge into your app) or add the column in your own migration.

## Register the alias (Laravel 11+)

In `bootstrap/app.php`, add to the `withMiddleware` callback (see snippet in `files/bootstrap/middleware-aliases.php`).

## Controllers

`DashboardController`, `BillingController`, and `PlanController` are minimal invokable classes returning plain text/JSON so you can verify status codes in tests or with `curl` after authenticating (use `actingAs` in a feature test, or a session in the browser).

## Route file

Copy `files/routes/billing-surface.php` into `routes/web.php` (or `require` it from there).

## Verify

```bash
php artisan route:list -v --path=dashboard
php artisan route:list -v --path=billing
```

Expected behaviour is described in the course lesson: guests redirected or blocked; unsubscribed 403 on billing; throttled 429 on repeated `POST /billing/plan`.
