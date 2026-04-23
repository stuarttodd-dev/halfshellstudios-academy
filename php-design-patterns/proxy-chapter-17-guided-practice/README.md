# Chapter 17 — Proxy (guided practice)

A Proxy implements the same interface as the real thing and adds one
cross-cutting concern: caching, lazy loading, access control, remote
calls. The trap is bundling several concerns into one "mega proxy"
that is no longer a proxy of anything in particular.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Caching price service | Slow API priced per product | **Proxy fits** — `CachingPriceService` with TTL |
| 2 — Mega proxy | One class doing log + auth + cache | **Trap.** Refactor into three small proxies composed at wiring |
| 3 — Lazy report | Expensive constructor doing the fetch | **Proxy fits** — `LazyReport` loads on first `rows()` |

---

## Exercise 1 — Caching price proxy

```php
interface PriceService { public function priceFor(int $productId): Money; }

final class ApiPriceService     implements PriceService { /* slow */ }
final class CachingPriceService implements PriceService { /* TTL via Clock */ }
```

The proxy is interchangeable with the real implementation — callers
depend only on `PriceService`.

---

## Exercise 2 — Mega proxy (the trap)

### Verdict — composition beats bundling

One proxy doing logging + auth + caching couples them and orders them
implicitly. Three small proxies, each owning one concern, can be:

- tested in isolation (auth tests don't need a logger);
- reordered deliberately at the wiring layer;
- removed individually (drop logging in tests, keep caching);
- reused (add logging to a *different* service for free).

```php
$service = new LoggingProxy(            // outermost — sees every attempt, including refused
    new AuthProxy(
        new CachingProxy(
            new RealService(),          // innermost — only authorised reads land here
        ),
        $auth,
    ),
    $logger,
);
```

This is also exactly the Decorator chapter's lesson — single-concern
wrappers compose.

---

## Exercise 3 — Lazy report

### Before

```php
final class Report {
    public array $rows;
    public function __construct(int $userId) {
        $this->rows = $this->repo->fetchAll($userId); // very expensive — runs even if never read
    }
}
```

### After

```php
interface Report { public function rows(): array; public function userId(): int; }

final class LazyReport implements Report {
    private ?array $rows = null;
    public function rows(): array {
        return $this->rows ??= $this->repo->fetchAll($this->userId);
    }
}
```

Existing callers who depend on `Report` keep working. The expensive
fetch only runs when somebody actually reads the rows.

---

## Chapter rubric

For each non-trap exercise:

- a clear interface for the service
- the real implementation and the proxy both implementing the interface
- one cross-cutting concern per proxy
- callers depending only on the interface
- tests of the proxy in isolation plus tests of a caller using a stub of the interface

For the trap: explain why one proxy per concern beats a mega proxy.

---

## How to run

```bash
cd php-design-patterns/proxy-chapter-17-guided-practice
php exercise-1-caching-price-proxy/solution.php
php exercise-2-mega-proxy/solution.php
php exercise-3-lazy-report/solution.php
```
