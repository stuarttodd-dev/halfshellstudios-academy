# Chapter 9 guided practice — writing code that is easy to change

Three exercises that practise change-readiness on real code shapes:
finding the missing concept hiding inside duplicated lines, replacing a
growing `if`-cascade with an extension point, and pulling hidden
dependencies into the open so they can be replaced for tests.

- **Exercise 1** — extract a `Money` value object that owns the
  pence ↔ pounds conversion and formatting, and replace three inline
  copies of the same incantation.
- **Exercise 2** — replace a `taxFor()` `if`-cascade with a registry
  of `VatPolicy` implementations so that adding a new region is one
  new line in a map (or, for genuinely-different policies, one new
  class plus one new line).
- **Exercise 3** — surface the three hidden dependencies in
  `ScheduleReport` (`time()`, `config()`, static `Logger::log`) by
  introducing tiny interfaces and constructor-injecting them, and
  write a test that runs without setting up any global state.

Every solution preserves observable behaviour — the starter and
solution drivers print identical output (Exercises 1 and 2) or pass
the same assertion (Exercise 3).

## Exercise 1 — kill the duplication

Three classes — `OrderController`, `InvoicePdf`, `ReportRow` — each
spell out `'£' . number_format($pence / 100, 2)` (or a near-cousin)
inline. There is a missing concept: a money type.

### Smells in the starter

- **The same magic appears in three places.** The divisor (`100`),
  the decimal places (`2`), the symbol (`£`) — none of them are
  named. Changing any of them means a hunt.
- **Two of the three are subtly different.** `OrderController` and
  `InvoicePdf` include the `£`; `ReportRow` does not. Whether that
  is intentional or accidental is impossible to tell from the code.
- **A new consumer will copy from the wrong example.** When the
  fourth caller (an email template, say) needs to render a price,
  whoever writes it will copy from whichever of the three they find
  first — and the divergence grows.

### What the refactor buys

- A single **`Money` value object** that owns:
  - the pence → pounds conversion (named `PENCE_PER_POUND`)
  - the decimal places (named `DECIMAL_PLACES`)
  - the currency symbol (named `CURRENCY_SYMBOL`)
- Two formatters with **explicit names** for the two real cases:
  `formatWithSymbol(): "£123.45"` and `formatBare(): "123.45"`. The
  difference between the three call sites is now expressed in code,
  not in the absence of a `'£' .` prefix that you might read past.
- Changing the currency symbol or the decimal places is now a
  one-line change with one place to look.
- The fourth consumer simply asks the value object what it should
  look like — the temptation to copy-paste vanishes.

### Before

```php
final class OrderController
{
    public function show(Order $o): array
    {
        return ['total' => '£' . number_format($o->totalInPence / 100, 2)];
    }
}

final class InvoicePdf
{
    public function render(Invoice $i): string
    {
        return 'Total: £' . number_format($i->totalInPence / 100, 2);
    }
}

final class ReportRow
{
    public function csvLine(int $totalInPence): string
    {
        return number_format($totalInPence / 100, 2);
    }
}
```

### After

```php
final class Money
{
    private const CURRENCY_SYMBOL  = '£';
    private const DECIMAL_PLACES   = 2;
    private const PENCE_PER_POUND  = 100;

    public function __construct(public readonly int $pence) {}

    public static function fromPence(int $pence): self
    {
        return new self($pence);
    }

    public function formatWithSymbol(): string
    {
        return self::CURRENCY_SYMBOL . $this->formatBare();
    }

    public function formatBare(): string
    {
        return number_format($this->pence / self::PENCE_PER_POUND, self::DECIMAL_PLACES);
    }
}

final class OrderController
{
    public function show(Order $o): array
    {
        return ['total' => Money::fromPence($o->totalInPence)->formatWithSymbol()];
    }
}

final class InvoicePdf
{
    public function render(Invoice $i): string
    {
        return 'Total: ' . Money::fromPence($i->totalInPence)->formatWithSymbol();
    }
}

final class ReportRow
{
    public function csvLine(int $totalInPence): string
    {
        return Money::fromPence($totalInPence)->formatBare();
    }
}
```

## Exercise 2 — break up the conditional

`taxFor()` is a five-branch `if`-cascade that grows every time a new
region is added or a rate changes. Replace it with an extension point.

### Smells in the starter

- **Open/closed violation by construction.** Every new region — or
  every rate change — is a code edit *inside the function*, with all
  the merge-conflict and regression risk that brings.
- **Mixed shapes.** Three branches use `===`, one uses `in_array`,
  one uses a default — five regions, three conditional styles, in
  six lines. The reader has to evaluate each branch separately.
- **Implicit "fallback" rule.** `0.10` for everything else is buried
  at the bottom and easy to miss; nobody can tell at a glance which
  countries actually fall into it.
- **Hard to extend without touching the function.** A region with a
  genuinely-different rule (say, two layered taxes) cannot be
  expressed without rewriting the function entirely.

### What the refactor buys

- A small **`VatPolicy` interface** (`taxFor(int $netInPence): int`)
  and two implementations:
  - `FlatRateVatPolicy(float $rate)` — covers every flat-rate region
    (most of them) without one class per country.
  - `ZeroVatPolicy` — separate from `FlatRateVatPolicy(0.0)` because
    "we deliberately don't collect VAT here" is a different domain
    statement to "the rate happens to be zero today".
- A **single registry** mapping country code → policy, with an
  injected `fallback`. Adding a region is now one line; finding "what
  happens for country XX?" is a single lookup.
- A genuinely-different region (say, the future "GB + green levy")
  is **one new class** implementing `VatPolicy` plus one new line in
  the registry. That's the rule of three the chapter is about: each
  axis of variation lives in exactly one place.
- The `taxFor()` function shrinks to a one-line lookup. There is no
  more if-cascade to grow.

### Before

```php
function taxFor(Order $o): int
{
    if ($o->country === 'GB') return (int) round($o->net * 0.20);
    if ($o->country === 'IE') return (int) round($o->net * 0.23);
    if (in_array($o->country, ['DE', 'FR', 'NL'])) return (int) round($o->net * 0.19);
    if (in_array($o->country, ['US', 'CA'])) return 0;
    return (int) round($o->net * 0.10);
}
```

### After

```php
interface VatPolicy
{
    public function taxFor(int $netInPence): int;
}

final class FlatRateVatPolicy implements VatPolicy
{
    public function __construct(private float $rate) {}

    public function taxFor(int $netInPence): int
    {
        return (int) round($netInPence * $this->rate);
    }
}

final class ZeroVatPolicy implements VatPolicy
{
    public function taxFor(int $netInPence): int { return 0; }
}

final class VatPolicyRegistry
{
    /** @var array<string, VatPolicy> */
    private array $policiesByCountry;

    public function __construct(private VatPolicy $fallback)
    {
        $standardEuVat = new FlatRateVatPolicy(0.19);
        $exempt        = new ZeroVatPolicy();

        $this->policiesByCountry = [
            'GB' => new FlatRateVatPolicy(0.20),
            'IE' => new FlatRateVatPolicy(0.23),
            'DE' => $standardEuVat,
            'FR' => $standardEuVat,
            'NL' => $standardEuVat,
            'US' => $exempt,
            'CA' => $exempt,
        ];
    }

    public function for(string $country): VatPolicy
    {
        return $this->policiesByCountry[$country] ?? $this->fallback;
    }
}

function taxFor(Order $o, VatPolicyRegistry $policies): int
{
    return $policies->for($o->country)->taxFor($o->net);
}
```

## Exercise 3 — surface a hidden dependency

`ScheduleReport::schedule()` looks like a pure use case but secretly
talks to `time()`, `config()` and the static `Logger::log` facade.
Pull each one into the open so the use case can be tested without any
global setup.

### Smells in the starter

- **Three hidden dependencies in three lines.** `time()`, `config()`,
  `Logger::log` — none of them appear in the constructor signature,
  so a reader of the class diagram has no idea this code touches
  the clock, the config store, and a logger.
- **Untestable without global setup.** To exercise `schedule()` in a
  test you must (a) populate `$GLOBALS['__config']`, (b) reset
  `Logger::$messages` and `DB::$inserts`, (c) work around the fact
  that `time()` advances between the test setup and the assertion,
  and (d) make assertions on *ranges* rather than exact values.
  Every one of those is friction the next contributor will pay.
- **Tightly coupled to specific implementations.** Want to log to
  Sentry instead? Want to use Carbon? Want to read config from a
  feature flag? Each one is a code edit inside the use case.

### What the refactor buys

- Three tiny **interfaces** (`Clock`, `DelayConfig`, `ReportLogger`,
  plus `JobsRepository` for the DB call), each with a one-line
  contract. The use case's dependencies are now declared explicitly
  in its constructor — the type system and the diff in version
  control both make them visible.
- Two parallel sets of implementations:
  - **System adapters** — `SystemClock`, `GlobalConfigDelayConfig`,
    `StaticLoggerAdapter`, `JobsTableRepository` — used in production.
  - **Deterministic test doubles** — `FixedClock`, `StaticDelayConfig`,
    `RecordingLogger`, `InMemoryJobsRepository` — used in tests.
- A test that **runs without touching `$GLOBALS`, without resetting
  any static state, with exact-value assertions**, and that fits
  comfortably on a single screen. The `schedule()` body itself is
  unchanged in shape, just substituting `$this->clock->now()` for
  `time()` and so on.
- Swapping any single dependency in the future — Carbon for the clock,
  Sentry for the logger, a feature-flag service for the config — is
  one new class implementing one interface. The use case never changes.

### Side-by-side: the test that pins each version

```php
// Starter test — needs all of this just to run
DB::reset();
Logger::reset();
$GLOBALS['__config']['reporting.delay_seconds'] = 60;

$before = time();
(new ScheduleReport())->schedule(reportId: 42);
$after = time();

$insert = DB::$inserts[0]['values'] ?? null;
$ok =
       $insert['report_id'] === 42
    && $insert['run_at']   >= $before + 60
    && $insert['run_at']   <= $after  + 60      // ← range, because time moves
    && str_contains(Logger::$messages[0], 'scheduled report 42 at ');
```

```php
// Solution test — no global setup, exact assertions
$clock  = new FixedClock(now: 1_700_000_000);
$config = new StaticDelayConfig(seconds: 60);
$logger = new RecordingLogger();
$jobs   = new InMemoryJobsRepository();

(new ScheduleReport($clock, $config, $logger, $jobs))->schedule(reportId: 42);

$ok =
       $jobs->inserted   === [['report_id' => 42, 'run_at' => 1_700_000_060]]
    && $logger->messages === ['scheduled report 42 at 1700000060'];
```

### Before

```php
final class ScheduleReport
{
    public function schedule(int $reportId): void
    {
        $now    = time();
        $offset = config('reporting.delay_seconds');
        $runAt  = $now + $offset;

        DB::table('jobs')->insert(['report_id' => $reportId, 'run_at' => $runAt]);

        Logger::log("scheduled report {$reportId} at {$runAt}");
    }
}
```

### After

```php
interface Clock          { public function now(): int; }
interface DelayConfig    { public function delaySeconds(): int; }
interface ReportLogger   { public function log(string $message): void; }
interface JobsRepository { /** @param array<string, mixed> $values */
                           public function insert(array $values): void; }

final class ScheduleReport
{
    public function __construct(
        private Clock          $clock,
        private DelayConfig    $config,
        private ReportLogger   $logger,
        private JobsRepository $jobs,
    ) {}

    public function schedule(int $reportId): void
    {
        $runAt = $this->clock->now() + $this->config->delaySeconds();

        $this->jobs->insert(['report_id' => $reportId, 'run_at' => $runAt]);

        $this->logger->log("scheduled report {$reportId} at {$runAt}");
    }
}
```

## Running the solutions

Each exercise folder is self-contained and runs with plain PHP — no
Composer, no framework, no database:

```bash
# Exercise 1 — pure restructure, outputs identical
cd writing-decent-php/change-friendly-code-chapter-9-guided-practice/exercise-1-kill-the-duplication
diff <(php starter.php) <(php solution.php)   # no output ⇒ behaviour preserved

# Exercise 2 — also a pure restructure
cd ../exercise-2-break-up-the-conditional
diff <(php starter.php) <(php solution.php)   # no output ⇒ behaviour preserved

# Exercise 3 — runs the embedded "test" against each version
cd ../exercise-3-surface-a-hidden-dependency
php starter.php    # passes WITH global setup and range assertions
php solution.php   # passes with NO global setup and exact assertions
```
