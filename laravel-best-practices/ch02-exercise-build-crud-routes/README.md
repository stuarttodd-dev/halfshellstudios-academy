# Chapter 2 — Exercise: build CRUD routes

**Course page:** [Build a complete product routing surface](https://laravel.learnio.dev/learn/sections/chapter-2-routing-controllers-request/exercise-build-crud-routes)

**Prerequisites:** [Same as the root chapter setup](../README.md#prerequisites-install-once-on-your-machine) — PHP 8.3+, Composer, SQLite. Work from `laravel-best-practices/`.

## Run the app

From `laravel-best-practices/`:

```bash
cd ch02-exercise-build-crud-routes
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8002
```

## What’s in the app

`Product` model, products migration, `ProductController` (JSON), `routes/products.php` (included from `routes/solution.php`).

`POST` / `PATCH` / `DELETE` are exempt from CSRF in this exercise’s `bootstrap/app.php` so you can test with **`curl`** as below (do **not** copy that to production).

### Lesson acceptance (course)

- **REST map:** list → create (201) → show → update (PATCH) → delete (204) — all for `Product` in JSON.
- **Route model binding + safety:** `GET /products/abc` returns **404** (numeric constraint / `whereNumber` as in the lesson), not 500.
- You can find **`ProductController` + `routes/products.php`** in the app and connect them to the course narrative.

**If you get stuck:** run `cd ch02-exercise-build-crud-routes/laravel && php artisan route:list` — if the `products` routes are missing, `routes/solution.php` may not be required from `web.php` in your copy.

---

## How to test everything

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

**Browser (GET):** The API is not behind auth — open the **GET** URLs in the browser to see `ok`, JSON lists, a single product, or a **404** page (e.g. non-numeric id). Use **`curl`** for **POST / PATCH / DELETE** and for exact status codes. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8002`. Work through the steps in order. Use **separate** terminal tabs if you like: one for `php artisan serve`, one for `curl` where a step is mutating.

### 0 — Preconditions

- [Run the app](#run-the-app) has succeeded (`migrate` creates the `products` table).
- Server is listening on [http://127.0.0.1:8002](http://127.0.0.1:8002).

### 1 — Routes (sanity)

```bash
cd ch02-exercise-build-crud-routes/laravel && php artisan route:list --path=products
```

You should see `GET/POST` on `products`, and `GET/PATCH/DELETE` on `products/{product}`.

### 2 — Health

In the browser, open [http://127.0.0.1:8002/exercise](http://127.0.0.1:8002/exercise). Expect the plain text **`ok`**.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8002/exercise"
```

### 3 — List products (start empty or after you delete all)

In the browser, open [http://127.0.0.1:8002/products](http://127.0.0.1:8002/products). The tab shows **JSON** (`{"data":[]}` or a list) — you may need “View source” or a JSON formatter to read it.

*Optional — run in terminal:*

```bash
curl -sS -H "Accept: application/json" "http://127.0.0.1:8002/products"
```

### 4 — Create a product

```bash
curl -sS -X POST "http://127.0.0.1:8002/products" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Test widget","price":9.99}'
```

Expect: **201** and JSON with `data.id` (an integer). **Note that `id`** as `$ID` in the next steps, or use `1` if you know it is the first row.

### 5 — Show that product (replace `1` with `$ID` if different)

In the browser, open [http://127.0.0.1:8002/products/1](http://127.0.0.1:8002/products/1) (or your real id). Expect **200** and JSON for that product, or the framework’s **404** if the id does not exist.

*Optional — run in terminal:*

```bash
curl -sS -H "Accept: application/json" "http://127.0.0.1:8002/products/1"
```

### 6 — Non-numeric id (route constraint + binding)

In the browser, open [http://127.0.0.1:8002/products/abc](http://127.0.0.1:8002/products/abc). Expect a **404** (non-numeric segment does not match `whereNumber`).

*Optional — run in terminal:*

```bash
curl -sS -i -H "Accept: application/json" "http://127.0.0.1:8002/products/abc"
```

### 7 — Update (PATCH) (replace `1` with `$ID` as needed)

```bash
curl -sS -X PATCH "http://127.0.0.1:8002/products/1" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Updated","price":10.5}'
```

Expect: **200** and the updated `data` object.

### 8 — Delete

```bash
curl -sS -X DELETE "http://127.0.0.1:8002/products/1" -H "Accept: application/json" -i
```

Expect: **204** No Content (or the empty response your controller returns).

### 9 — List again (confirm row gone)

In the browser, re-open [http://127.0.0.1:8002/products](http://127.0.0.1:8002/products). The list should reflect the delete.

*Optional — run in terminal:*

```bash
curl -sS -H "Accept: application/json" "http://127.0.0.1:8002/products"
```

### 10 — Read the implementation (optional but recommended)

- `app/Http/Controllers/ProductController.php` — all verbs.
- `routes/products.php` — `Route::prefix('products')` and `whereNumber('product')`.

**Notes:** `whereNumber('product')` stops `abc` from hitting model binding. `PATCH` and `DELETE` are explicit route definitions (REST-style), not a single `Route::apiResource` shortcut unless you refactored to that in your own copy.
