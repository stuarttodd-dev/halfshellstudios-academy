# Chapter 12 — Exercise: Strategy pattern + service container

**Course page:** [Build a strategy system bound in the container](https://laravel.learnio.dev/learn/sections/chapter-12-service-container-and-providers/exercise-build-strategy-system)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — `php artisan test` must run from **`ch12-…/laravel`**.

## Run the app

From `laravel-best-practices/`:

```bash
cd ch12-exercise-build-strategy-system
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8012
```


## What’s in the app

Under **`laravel/`**: `config/pricing.php`, `App\Contracts\DiscountStrategy`, `App\Services\Pricing\*` strategies, `AppServiceProvider` binding, `routes/pricing-demo.php` → `GET /pricing-demo?subtotal=10000`, `tests/Feature/PricingStrategyTest.php`.

### Lesson acceptance (course)

- **Contract + strategies:** a `DiscountStrategy` interface and two or more concrete classes under a clear namespace.
- **Container binding** in a provider: changing the default implementation changes the JSON from `/pricing-demo` without editing the controller.
- **Tests** assert the **wired** strategy behaviour the course asked for (run `PricingStrategyTest` below).

---

## How to test everything

**Browser first (optional):** For **GET** routes you can open the same URLs in your browser. If the app has a **login** (or `/_exercise/login`), sign in in the browser and browse—`curl` is only needed for **POST / PUT / PATCH / DELETE**, JSON bodies, or when you want a copy-pastable one-liner. See [Browser vs curl](../README.md#browser-vs-curl).


**Port:** `8012`. The container resolves a **`DiscountStrategy`**; the demo only outputs `subtotal_pence` and `total_pence` from `GET /pricing-demo?subtotal=…`.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8012** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /pricing-demo?subtotal=10000` — JSON with pence fields |
| 3 | Try `subtotal=0` and a **large** value — still **200** JSON (strategy math may clamp or not; compare with lesson) |
| 4 | **Swap binding** in `AppServiceProvider` to another strategy, hit the same URL — `total_pence` should change (the point of the pattern) |
| 5 | `php artisan test --filter=PricingStrategyTest` — green |
| 6 | Read `config/pricing.php` and `app/Services/Pricing/*` for mapping keys → classes |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8012/exercise"
```

**2 — Default strategy**

```bash
curl -sS "http://127.0.0.1:8012/pricing-demo?subtotal=10000"
```

**3 — Edge / comparison**

```bash
curl -sS "http://127.0.0.1:8012/pricing-demo?subtotal=0"
```

**4 — Tests**

```bash
cd ch12-exercise-build-strategy-system/laravel && php artisan test --filter=PricingStrategyTest
```
