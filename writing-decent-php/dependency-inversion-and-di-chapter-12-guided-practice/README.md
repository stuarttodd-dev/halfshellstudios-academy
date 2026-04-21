# Chapter 12 — Dependency inversion and DI without a framework (guided practice)

> Three exercises to take DI from idea to muscle memory: surface what
> the class is secretly using, draw the abstraction at the level of the
> *question*, and assemble the whole graph in one named place.

For each exercise: refactor without looking at the solution, add one
test that runs in milliseconds with no network or DB, then compare with
the provided solution.

Run with PHP 8.2+ (no Composer required):

```bash
php exercise-1-surface-hidden-dependencies/starter.php
php exercise-1-surface-hidden-dependencies/solution.php
php exercise-2-interface-at-the-right-boundary/starter.php
php exercise-2-interface-at-the-right-boundary/solution.php
php exercise-3-write-the-composition-root/starter.php
php exercise-3-write-the-composition-root/solution.php
php exercise-3-write-the-composition-root/test.php
```

---

## Exercise 1 — surface hidden dependencies (`ScheduleNewsletter`)

> This class secretly depends on three globals. Make them explicit
> constructor parameters.

### Smells

- The constructor signature is **empty** — the class lies about what it
  needs to run.
- Four hidden dependencies in five lines of body: the wall clock,
  `TenantContext::current()`, `Queue::push`, `Logger::info`.
- "Testing" it requires reaching into globals (`TenantContext::$current`,
  `Queue::$jobs`, `Logger::$entries`) and accepting that the recorded
  timestamp will be different on every run.

### What the brief says, and what the solution adds

The brief lists three globals; we counted four — `new DateTimeImmutable()`
is also a hidden dependency on the system clock. So the solution
introduces **four** ports, not three:

| Port | What it answers | Production adapter | Test double |
|------|-----------------|--------------------|-------------|
| `Clock` | "What time is it?" | `SystemClock` | `FixedClock` |
| `TenantProvider` | "Whose request am I serving?" | `ContextTenantProvider` | `StaticTenantProvider` |
| `JobQueue` | "Push this job onto the queue." | `GlobalQueueAdapter` | `InMemoryJobQueue` |
| `EventLogger` | "Record this event." | `StaticLoggerAdapter` | `RecordingEventLogger` |

Notice the abstractions are written from the **caller's** point of view
— `Clock::now()`, `JobQueue::push()`. We do not invert *"the thing
that lets us call static methods on `Queue`"*; we invert *"the operation
we actually need"*. Same lesson as Exercise 2, applied to globals.

### Before

```php
final class ScheduleNewsletter
{
    public function schedule(string $subject, string $body): void
    {
        $when   = (new DateTimeImmutable())->modify('+1 hour');
        $tenant = TenantContext::current();
        Queue::push('newsletter', [/* ... */]);
        Logger::info("scheduled newsletter for {$when->format('c')}");
    }
}
```

### After

```php
final class ScheduleNewsletter
{
    public function __construct(
        private Clock          $clock,
        private TenantProvider $tenants,
        private JobQueue       $queue,
        private EventLogger    $logger,
    ) {}

    public function schedule(string $subject, string $body): void
    {
        $when   = $this->clock->now()->modify('+1 hour');
        $tenant = $this->tenants->current();
        $this->queue->push('newsletter', [/* ... */]);
        $this->logger->info("scheduled newsletter for {$when->format('c')}");
    }
}
```

### Side-by-side test

| Starter (with hidden deps) | Solution (with injected deps) |
|----------------------------|-------------------------------|
| Reset `Queue::$jobs`, `Logger::$entries` | Construct `InMemoryJobQueue`, `RecordingEventLogger` |
| Set `TenantContext::$current = new Tenant(42)` | Pass `new StaticTenantProvider(new Tenant(42))` |
| `assert($jobWhen >= $before && $jobWhen <= $after)` | `assert($job['payload']['when'] === '2026-04-20T13:00:00+00:00')` |
| Range assertion (because the clock keeps moving) | **Exact** assertion |

The test goes from "approximately correct, with global setup" to "exactly
correct, with no globals".

Files: [`exercise-1-surface-hidden-dependencies/`](exercise-1-surface-hidden-dependencies).

---

## Exercise 2 — interface at the right boundary (`ConvertCurrency`)

> Refactor this so HTTP-fetching the rate goes through an interface
> you can fake in tests.

### Smells

- The class hits the network inline. There is no way to test it without
  either a live HTTP endpoint or a stream-wrapper hack.
- Even the **right** answer to "convert £10 from GBP to EUR" is
  obscured by the HTTP plumbing.

### Where to put the interface

Three plausible boundaries — only one is right:

| Boundary | What it abstracts | Verdict |
|----------|-------------------|---------|
| `HttpClient` | "GET a URL" | Too low. Tests now have to know about URLs and status codes. |
| `JsonHttpClient` | "GET a URL and decode JSON" | Still too low. The API's URL template and JSON shape leak into the use case. |
| **`ExchangeRateProvider`** | "Give me a rate from X to Y" | **Right.** The use case asks the question it actually has. |

> **Rule of thumb:** an interface lives at the level of the *question
> being asked*, not the *primitive being used*.

### Before

```php
final class ConvertCurrency
{
    public function convert(int $amountInPence, string $from, string $to): int
    {
        $rate = json_decode(file_get_contents("https://api.example.com/rate/{$from}/{$to}"))->rate;
        return (int) round($amountInPence * $rate);
    }
}
```

### After

```php
interface ExchangeRateProvider
{
    public function rateFor(string $from, string $to): float;
}

final class ConvertCurrency
{
    public function __construct(private ExchangeRateProvider $rates) {}

    public function convert(int $amountInPence, string $from, string $to): int
    {
        return (int) round($amountInPence * $this->rates->rateFor($from, $to));
    }
}

final class HttpExchangeRateProvider     implements ExchangeRateProvider { /* ... */ }
final class InMemoryExchangeRateProvider implements ExchangeRateProvider { /* ... */ }
```

### What the refactor buys

- The use case is **one line of arithmetic** — easy to unit-test, easy
  to read.
- The HTTP call, the URL template, the JSON shape, the API key — all
  in one adapter. Nobody else has to know.
- Other callers that need a rate (refunds, reporting, dashboards) reuse
  the same port and get the same fake in tests.

Files: [`exercise-2-interface-at-the-right-boundary/`](exercise-2-interface-at-the-right-boundary).

---

## Exercise 3 — write the composition root

> Given the classes in the brief, write the composition root file that
> wires them together for a single web entry point. Keep all `new`
> calls except for value objects in this one file.

### Smells (in `starter.php`)

- Wiring is duplicated across entry points: the web controller and the
  cron command each construct the same object graph from scratch.
- `new \Stripe\StripeClient($_ENV['STRIPE_KEY'])` appears in two
  places. So does `new SmtpReceiptMailer($host, $user, $pass)`.
- Swapping an adapter (PDO → Doctrine, SMTP → SES) means greppping for
  every `new` call and editing them all.

### What a composition root is

A single named file (or container class) that:

1. Reads configuration once.
2. Constructs every adapter and every use case.
3. Hands the assembled object graph back to whichever entry point
   asked for it.

> **Every `new` outside the composition root is a smell** — except for
> value objects (`Money`, `OrderId`, `EmailAddress`), which are part of
> the language of the domain, not part of the wiring.

### Files in the solution

```
exercise-3-write-the-composition-root/
├── support/classes.php          # the use case, ports, adapters (each a separate file in a real project)
├── bootstrap.php                # the production composition root  ← only place with `new` for adapters
├── solution.php                 # the "web entry point" — uses the container, no `new`
├── bootstrap.test.php           # the TEST composition root with deterministic doubles
└── test.php                     # millisecond-fast use-case test
```

### Production composition root — the *only* place adapters get constructed

```php
final class AppContainer
{
    public function placeOrderController(): PlaceOrderController
    {
        return new PlaceOrderController($this->placeOrder());
    }

    public function placeOrder(): PlaceOrder
    {
        return $this->placeOrder ??= new PlaceOrder(
            $this->orderRepository(),
            $this->paymentGateway(),
            $this->receiptMailer(),
        );
    }

    private function orderRepository(): OrderRepository  { return $this->orders   ??= new PdoOrderRepository($this->pdo()); }
    private function paymentGateway(): PaymentGateway    { return $this->payments ??= new StripePaymentGateway($this->stripe()); }
    private function receiptMailer(): ReceiptMailer      { return $this->mailer   ??= new SmtpReceiptMailer(/* ...config... */); }

    private function pdo(): PDO                          { /* lazy-build PDO */ }
    private function stripe(): \Stripe\StripeClient      { /* lazy-build Stripe SDK */ }
}
```

### Web entry point (the *only* `new` is the container itself)

```php
$container  = new AppContainer(config: [/* env */]);
$controller = $container->placeOrderController();
$response   = $controller(['customer_id' => 9001, 'total_pence' => 4500, 'email' => 'a@b.c']);
```

### Test composition root — same shape, deterministic leaves

```php
$container = new TestContainer();
$useCase   = $container->placeOrder();
$useCase->place(['customer_id' => 9001, 'total_pence' => 4500, 'email' => 'a@b.c']);

assert($container->orders->rows   === [1 => ['customer_id' => 9001, 'total_pence' => 4500]]);
assert($container->payments->charges === [['amount' => 4500, 'description' => 'Order #1']]);
assert($container->mailer->sent   === [['to' => 'a@b.c', 'order_id' => 1, 'amount' => 4500]]);
```

The test never instantiates a `PDO`, never opens an SMTP socket, never
talks to Stripe. **The container is what makes wiring test doubles
look like wiring real adapters.** That symmetry is the whole point.

### What the refactor buys

- A single source of truth for "what does this app depend on?". Read
  `bootstrap.php` and you know.
- Swapping adapters is a one-line edit.
- Tests get an identically-shaped container with deterministic
  collaborators — no copy-pasted wiring.
- Frameworks (Laravel's container, Symfony's DI) play exactly this role
  for you. The lesson holds in either direction: wiring lives in **one
  named place**.

Files: [`exercise-3-write-the-composition-root/`](exercise-3-write-the-composition-root).

---

## What ties Chapter 12 together

- **Ex1**: a class that lies about its dependencies cannot be tested
  cheaply. Surface them, then they become parameters you can choose.
- **Ex2**: invert the operation, not the primitive. The interface lives
  at the level of the question (`rateFor`), not the level of the
  transport (`HttpClient`).
- **Ex3**: once everything is injected, *something* still has to
  construct it. The composition root is the one place that does.

The combined picture: **DI is not "use a framework container".** It is
"every collaborator is a parameter, and one named file decides what
goes in them." Frameworks just automate step three.
