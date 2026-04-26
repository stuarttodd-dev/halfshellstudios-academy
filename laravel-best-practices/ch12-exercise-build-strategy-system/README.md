# Chapter 12 — Exercise: Strategy pattern + service container

**Course page:** [Build a strategy system bound in the container](http://127.0.0.1:38080/learn/sections/chapter-12-service-container-and-providers/exercise-build-strategy-system)

## Run the app

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch12-exercise-build-strategy-system`** and port **8012**.

## What’s in the app

Under **`laravel/`**: `config/pricing.php`, `App\Contracts\DiscountStrategy`, `App\Services\Pricing\*` strategies, `AppServiceProvider` binding, `routes/pricing-demo.php` → `GET /pricing-demo?subtotal=10000`, `tests/Feature/PricingStrategyTest.php`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Automated:** `php artisan test --filter=PricingStrategyTest` — config driver + container swap behaviour.
3. **HTTP:** [http://127.0.0.1:8012/pricing-demo?subtotal=10000](http://127.0.0.1:8012/pricing-demo?subtotal=10000) — response should follow the selected strategy; toggle `PRICING_STRATEGY` in `.env` and clear config cache if you use one (`php artisan config:clear`).
