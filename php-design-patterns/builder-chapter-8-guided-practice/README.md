# Chapter 8 — Builder (guided practice)

Builder is for constructors that have **many optional parameters** and
benefit from grouped, semantic call sites. It is not for two-parameter
value objects.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — `DatabaseConfig` | 9 fields, several optional, SSL settings group together | **Builder fits** — semantic methods (`host`, `credentials`, `withSsl`) |
| 2 — `Money` | 2 fields, both required | **Trap.** Use named arguments + static factories (`Money::gbp(500)`) |
| 3 — `SalesReport` | 8 fields, many optional, requires cross-field validation (`from < to`, `groupBy ∈ day/week/month`) | **Builder fits** — and `build()` enforces the invariants |

---

## Exercise 1 — Database config

### Before

```php
new DatabaseConfig('db.internal', 3307, 'app', 'app_user', 's3cret', [], '/etc/ssl/ca.pem', true, 5);
// — what does the 4th argument mean again? was that timeout or port?
```

### After

```php
$config = DatabaseConfig::builder()
    ->host('db.internal', port: 3307)
    ->database('app')
    ->credentials('app_user', 's3cret')
    ->withSsl('/etc/ssl/ca.pem', verify: true)
    ->connectTimeout(5)
    ->build();
```

### What the refactor buys

- **Reads as a recipe.** Each line names a concern.
- **Related parameters travel together.** `credentials(user, password)`
  cannot be set partially; `withSsl(caPath, verify)` ditto.
- **Validation lives in one place — `build()`.** Missing host, missing
  database, missing credentials, invalid timeout: all surface as
  `LogicException` / `InvalidArgumentException` with a useful message.
- **Immutable result.** The `DatabaseConfig` returned has no setters.

---

## Exercise 2 — Money (the trap)

### Before

```php
final class Money { public function __construct(public int $amountInPence, public string $currency) {} }
```

### Verdict — Builder is the wrong answer

Two parameters, both meaningful, both required. Named arguments cover
the rare case (`new Money(amountInPence: 12345, currency: 'JPY')`),
and **static factory methods** carry domain meaning more clearly than
a builder ever could:

```php
Money::gbp(500);   // £5.00
Money::usd(500);   // $5.00
```

A `MoneyBuilder` would add ceremony around two parameters. The right
home for "common Money values" is a static factory, not a builder.

When does this cross the line into Builder territory? When `Money`
gains optional behaviour (rounding modes, currency catalogues, FX
contexts that need to be set together) — and even then a static
factory is usually still better.

---

## Exercise 3 — Sales report

### Before

```php
new SalesReport(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-05-01'),
                ['UK','EU'], null, false, 'week', false, 1000);
// — wait, which boolean was includeRefunds and which was includeUnshipped?
```

### After

```php
$report = SalesReport::builder()
    ->between(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-05-01'))
    ->forRegions(['UK', 'EU'])
    ->groupedBy('week')
    ->excludingRefunds()
    ->minimumOrderValue(1000)
    ->build();
```

`build()` enforces:

- `from < to`;
- `groupBy ∈ {day, week, month}`;
- a date range was actually set.

### What the refactor buys

- The boolean flags become **verbs at the call site**
  (`excludingRefunds()`, `includingUnshipped()`).
- Cross-field validation lives in `build()` — the constructor is
  immutable and uniform; the rules are in one place.
- The call site is a checklist a domain expert can read.

---

## Chapter rubric

For each non-trap exercise:

- builder class with methods named after concerns (not raw setters)
- `build()` that validates and returns an immutable object
- callers reading as recipes (`Thing::builder()->...->build()`)
- tests for valid configurations **and** for expected validation failures

For the trap: explain why named arguments + static factories beat a
builder for two-parameter value objects.

---

## How to run

```bash
cd php-design-patterns/builder-chapter-8-guided-practice
php exercise-1-database-config-builder/solution.php
php exercise-2-money-construction/solution.php
php exercise-3-sales-report-builder/solution.php
```
