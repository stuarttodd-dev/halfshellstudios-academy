# Chapter 4 — Exercise: validate a complex checkout form

**Course page:** [Build a robust validation boundary for a complex checkout form](https://laravel.learnio.dev/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form)

## Run the app

The checkout `exists:products,id` rule needs **seeded products** — run **`php artisan db:seed --force`** after migrate.

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch04-exercise-validate-complex-form`**, port **8004**, and **seed** (see the main README’s migrate/seed line).

## What’s in the app

Under **`laravel/`**: `StoreCheckoutRequest`, `CheckoutController`, `routes/checkout.php` (required from `routes/solution.php`), plus user + product seeding in `DatabaseSeeder` for manual checks.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Authenticated POST:** with a valid session / `actingAs` user, `POST /checkout` with a valid `product_id` from the DB and business fields — expect **201/204/redirect** per your implementation.
3. **Validation failures:** invalid payload → **422** with field errors; wrong `product_id` → validation error from `exists:products,id`.
4. **Guest:** requests that should be blocked → **403** or redirect to login, per your `auth` middleware.
5. Optional: add or run a feature test listing guest 403, invalid 422, happy path.
