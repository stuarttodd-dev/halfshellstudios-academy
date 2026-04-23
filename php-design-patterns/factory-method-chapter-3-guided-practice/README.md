# Chapter 3 — Factory Method (guided practice)

Three places where construction is the smell. Two of them want a
factory; one of them does not.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Cache adapter selection | Caller `new`s one of three caches based on a string | **Factory fits** — `CacheFactory::make($driver)` owns the recipes |
| 2 — PDF renderer | Caller `new`s the one and only renderer | **Trap.** No choice, no recipe — it's just plain DI of a single collaborator |
| 3 — Logger per channel | Two services each `new` a `FileLogger` with a hard-coded path | **Factory fits** — `LoggerFactory::for($channel)` hides the path map |

---

## Exercise 1 — Cache adapter selection

### Before

```php
final class CacheService
{
    public function get(string $key, string $driver): mixed
    {
        if ($driver === 'redis') return (new RedisCache(new Predis(['host' => 'localhost'])))->get($key);
        if ($driver === 'file')  return (new FileCache('/tmp/cache'))->get($key);
        if ($driver === 'array') return (new ArrayCache())->get($key);
        throw new RuntimeException("Unknown cache driver");
    }
}
```

### After

```php
interface CacheFactory { public function make(string $driver): Cache; }

final class DefaultCacheFactory implements CacheFactory
{
    public function __construct(private string $redisHost = 'localhost', private string $fileDirectory = '/tmp/cache') {}
    public function make(string $driver): Cache
    {
        return match ($driver) {
            'redis' => new RedisCache($this->redisHost),
            'file'  => new FileCache($this->fileDirectory),
            'array' => new ArrayCache(),
            default => throw new RuntimeException("Unknown cache driver: {$driver}"),
        };
    }
}

final class CacheService
{
    public function __construct(private CacheFactory $factory) {}
    public function get(string $key, string $driver): mixed { return $this->factory->make($driver)->get($key); }
}
```

### What the refactor buys

- **One place to look** for "how do we build a cache?" — the factory.
- **Configuration leaves the call site.** The Redis host and the file
  directory live on the factory, set once at the composition root.
- **Testable factory.** `make('redis') instanceof RedisCache`, plus an
  assertion that the Redis host the factory carries was honoured.
- **Testable caller.** `CacheService` can be tested with a stub factory
  that returns whatever the test wants — see the `TestLoggerFactory`
  pattern in Exercise 3 for the same idea.

---

## Exercise 2 — PDF renderer (the trap)

### Before

```php
$pdf = (new PdfRenderer())->render($invoice);
```

### Verdict — Factory Method is the wrong answer

There is **one** type, **no** configuration, and **no** choice. A
`PdfRendererFactory::make(): PdfRenderer` is one line wrapping one
line — pure ceremony.

The actual smell is *coupling*: the controller `new`s its collaborator.
The right fix is plain dependency injection — give the controller a
`Renderer` interface (so a future `HtmlPreviewRenderer` is a swap, not a
rewrite). No factory in sight.

```php
final class InvoiceController
{
    public function __construct(private Renderer $renderer) {}
    public function download(object $invoice): string { return $this->renderer->render($invoice); }
}
```

When does this cross the line into Factory? When the controller would
otherwise have to know **how to choose** between renderers, **with what
configuration**. Until then, DI wins.

---

## Exercise 3 — Logger per channel

### Before

```php
class OrderProcessor    { public function process(Order $o): void   { (new FileLogger('/var/log/orders.log'))->info(...); } }
class PaymentProcessor  { public function process(Payment $p): void { (new FileLogger('/var/log/payments.log'))->info(...); } }
```

### After

```php
interface LoggerFactory { public function for(string $channel): Logger; }

final class DefaultLoggerFactory implements LoggerFactory
{
    public function __construct(private array $channelPaths) {}
    public function for(string $channel): Logger
    {
        if (!isset($this->channelPaths[$channel])) throw new RuntimeException("Unknown log channel: {$channel}");
        return new FileLogger($this->channelPaths[$channel]);
    }
}

final class OrderProcessor   { public function __construct(private LoggerFactory $loggers) {} /* uses ->for('orders')   */ }
final class PaymentProcessor { public function __construct(private LoggerFactory $loggers) {} /* uses ->for('payments') */ }
```

### What the refactor buys

- **Neither processor knows a file path.** That information lives on
  the factory, in one place.
- **Tests use a `TestLoggerFactory`** that hands out `ArrayLogger`
  instances. The processors are exercised end-to-end with no file
  system access.
- **Factory itself is testable** — `for('unknown')` throws; `for('orders')`
  returns a `FileLogger` with the configured path.

---

## Chapter rubric

For each non-trap exercise:

- factory interface with one method named after the question being answered (`make`, `for`)
- default implementation owning the construction recipes
- callers ask for what they want, with no construction logic of their own
- tests for the factory (right type back) **and** for the caller (uses what the factory returns)

For the trap: explain why it is one (DI > Factory when there is no choice and no recipe).

---

## How to run

```bash
cd php-design-patterns/factory-method-chapter-3-guided-practice
php exercise-1-cache-adapter-selection/solution.php
php exercise-2-pdf-renderer/solution.php
php exercise-3-logger-per-channel/solution.php
```
