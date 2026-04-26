# Chapter 12 — Exercise: Strategy pattern + service container

**Course page:** [Build a strategy system bound in the container](http://127.0.0.1:38080/learn/sections/chapter-12-service-container-and-providers/exercise-build-strategy-system)

## Files

- `config/pricing.php` — `driver` key from `PRICING_STRATEGY` env (read only in config, not in domain code).
- `app/Contracts/DiscountStrategy.php`
- `app/Services/Pricing/*Strategy.php` — `Fixed`, `Percentage`, `None`
- `AppServiceProvider::register` binding (see `files/app/Providers/AppServiceProvider-register-snippet.php`)
- `routes/pricing-demo.php` — `GET /pricing-demo?subtotal=10000`
- `tests/Feature/PricingStrategyTest.php`

## Run

```bash
php artisan test --filter=PricingStrategyTest
```
