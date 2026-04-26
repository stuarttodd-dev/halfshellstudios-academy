# Chapter 4 — Exercise: validate a complex checkout form

**Course page:** [Build a robust validation boundary for a complex checkout form](https://laravel.learnio.dev/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form)

## Run the app

The checkout `exists:products,id` rule needs **seeded products**.

From `laravel-best-practices/`:

```bash
cd ch04-exercise-validate-complex-form
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=127.0.0.1 --port=8004
```


## What’s in the app

Under **`laravel/`**: `StoreCheckoutRequest`, `CheckoutController`, `routes/checkout.php` (required from `routes/solution.php`), plus user + product seeding in `DatabaseSeeder` for manual checks.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Authenticated POST:** with a valid session / `actingAs` user, `POST /checkout` with a valid `product_id` from the DB and business fields — expect **201/204/redirect** per your implementation.
3. **Validation failures:** invalid payload → **422** with field errors; wrong `product_id` → validation error from `exists:products,id`.
4. **Guest:** requests that should be blocked → **403** or redirect to login, per your `auth` middleware.
5. Optional: add or run a feature test listing guest 403, invalid 422, happy path.
