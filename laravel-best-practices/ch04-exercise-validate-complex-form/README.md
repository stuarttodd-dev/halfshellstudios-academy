# Chapter 4 — Exercise: validate a complex checkout form

**Course page:** [Build a robust validation boundary for a complex checkout form](https://laravel.learnio.dev/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — you **must** run `db:seed` (see [Run the app](#run-the-app)) so `User` and `Product` rows exist. `POST /checkout` and `/_exercise/login` follow the same **local** pattern as ch3.

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

`db:seed` is **safe to run more than once** (it won’t try to create a second `test@example.com` user or duplicate the demo product). If you need a clean slate, use `php artisan migrate:fresh --force` then `db:seed` as above.

## What’s in the app

Under **`laravel/`**: `StoreCheckoutRequest`, `CheckoutController`, `routes/checkout.php` (required from `routes/solution.php`), plus user + product seeding in `DatabaseSeeder` for manual checks.

### Lesson acceptance (course)

- **Form request:** `StoreCheckoutRequest` enforces the lesson rules (array items, `exists:products,id`, email, `prepareForValidation` if the course added it) — prove with **422** on bad data.
- **Happy path:** authed `POST` returns **201** and an echo of what you will persist in the real app (`received` payload in this sample).
- **Auth boundary:** unauthenticated `POST` is **403** from `authorize()`.

**If the seeded `product_id` is not `1`:** from `ch04-…/laravel` run `php artisan tinker --execute="print_r(\\App\\Models\\Product::pluck('id')->all());"` and use a listed id in the `curl` body.

---

## How to test everything

**Unauthenticated `GET`:** open **`/exercise`** in the **browser** (step 1). **`POST /checkout`** (and the dev login cookie) need **`curl`** or a REST client — there is no browser form for the JSON happy path in this sample. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8004`. The [Run the app](#run-the-app) block already runs `db:seed` so you have a **user** and at least one **product** (check `id` in DB or assume product id **1** from seeder). `POST /checkout` is **CSRF-exempt** in `bootstrap/app.php`.

| Step | What |
| ---- | ---- |
| 0 | Migrated + seeded, server on **8004** |
| 1 | Health → `ok` |
| 2 | Local login → session cookie `cj` |
| 3 | Authed, valid body → **201** JSON with `received` |
| 4 | Validation errors → **422** (bad email, etc.) |
| 5 | Guest (no session) on checkout → **403** (not authenticated) |

**1 — Health**

In the browser, open **`http://127.0.0.1:8004/exercise`**. Expect **`ok`**.

*Optional (terminal):* `curl -sS "http://127.0.0.1:8004/exercise"`

**2 — Login (local only) — same cookie file for next requests**

```bash
curl -sS -c cj -b cj "http://127.0.0.1:8004/_exercise/login"
```

**3 — Happy path checkout** (default seed creates a product; first id is **usually `1`**. If not, [see the lesson block above](#lesson-acceptance-course) for a one-liner to print ids)

```bash
curl -sS -X POST -b cj "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"Alex","email":"alex@example.com","account_type":"personal","items":[{"product_id":1,"quantity":2}]}'
```

Expect: **201** and JSON like `{"received":{...}}`.

**4 — Validation (422) — bad email**

```bash
curl -sS -X POST -b cj "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"A","email":"not-an-email","account_type":"personal","items":[{"product_id":1,"quantity":1}]}' -i
```

Expect: **422** and `errors` for `email` (or similar).

**5 — `exists:products,id` — invalid `product_id`**

```bash
curl -sS -X POST -b cj "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"Alex","email":"a@a.com","account_type":"personal","items":[{"product_id":99999,"quantity":1}]}' -i
```

Expect: **422** for `items.0.product_id` (or equivalent).

**6 — Guest (no cookie)**

```bash
curl -sS -X POST "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"A","email":"a@a.com","account_type":"personal","items":[{"product_id":1,"quantity":1}]}' -i
```

Expect: **403** (FormRequest `authorize` requires a user).

**7 — Code**

- `app/Http/Requests/StoreCheckoutRequest.php` — rules, `prepareForValidation`, `authorize`.
- `app/Http/Controllers/CheckoutController.php` — returns `201` with `received`.

**8 — Routes**

```bash
cd ch04-exercise-validate-complex-form/laravel && php artisan route:list --path=checkout
```
