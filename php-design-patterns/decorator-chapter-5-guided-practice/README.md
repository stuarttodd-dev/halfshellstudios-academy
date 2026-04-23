# Chapter 5 — Decorator (guided practice)

Three places that look like wrapping concerns. Two of them really are;
one of them is a precondition pretending to be one.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Cache + Logging on a `SearchService` | Cross-cutting, behaviour-preserving | **Decorator fits** — stack `Logging( Caching( inner ) )` |
| 2 — Validation on an `OrderProcessor` | Looks cross-cutting; isn't | **Trap.** Validation is a precondition; it belongs at the boundary, in a typed value object |
| 3 — Timing + Retry on an `HttpClient` | Order of decorators changes meaning | **Decorator fits** — and the test pins the order |

The defining property of a Decorator: **same interface in, same
interface out, behaviour-preserving** for the call's contract — only
side-effects (counts, logs, durations) change.

---

## Exercise 1 — Caching + logging on a search service

### Before

Plain `ElasticsearchService implements SearchService` with no
caching, no logging, no observability.

### After

```php
final class CachingSearchService implements SearchService { /* memoise by query */ }
final class LoggingSearchService implements SearchService { /* append to log    */ }

// Composition root:
$service = new LoggingSearchService(
    new CachingSearchService(new ElasticsearchService(/* … */)),
    $log,
);
```

### What the refactor buys

- **One concern per class.** Caching does not know about logging;
  logging does not know about caching.
- **Each decorator is testable with a fake inner.** The cache test
  asserts the inner was hit *once* across two identical queries.
- **Order is explicit at the wiring layer.** Reading the composition
  root is reading the call sequence: every search is logged, even
  cache hits.

---

## Exercise 2 — Validation on an `OrderProcessor` (the trap)

### Before

```php
interface OrderProcessor { public function process(Order $order): void; }
```

### Verdict — Decorator is the wrong answer for validation

A Decorator is **same shape in, same shape out, behaviour-preserving
for the call's contract**: it adds caching, logging, timing, retries —
side-effects that do not depend on what the call *means*.

Validation does not fit:

- it depends on the **semantics** of an Order (required fields,
  invariants, business rules);
- it is a **precondition**, not a wrapping concern — once it fails the
  call should never happen at all.

Two ways this goes wrong if forced into a Decorator:

1. The decorator embeds business rules that already live (or should
   live) inside the Order. Now the rules can disagree.
2. It grows into a "validation framework" that knows about every
   operation it might wrap — a small bureaucracy, not a Decorator.

**Where validation actually belongs**:

- **At the boundary** (HTTP / form request) producing a typed
  `OrderRequest` value object that is *correct by construction*.
- **Inside the domain** as constructor invariants on the Order.

The processor then receives an already-valid object and **cannot be
called incorrectly**. No decorator needed.

---

## Exercise 3 — Timing + retry on an HTTP client

### Before

A `GuzzleHttpClient` with no observability, no resilience.

### After

```php
final class TimingHttpClient   implements HttpClient { /* clock + metrics around the inner call */ }
final class RetryingHttpClient implements HttpClient { /* up to N attempts on RuntimeException  */ }

// Composition root: timing wraps retry, so duration includes retries.
$client = new TimingHttpClient(new RetryingHttpClient(new GuzzleHttpClient(), 3), $clock, $metrics);
```

### Why the order matters (and how the test pins it)

`timing(retry(inner))` records **one** metric — the total wall-clock
time for the eventual success, retries included.

`retry(timing(inner))` records **N** metrics — one per attempt — and
total observability is the sum.

Both are reasonable; they answer different questions. The chapter's
ask is the former (total time including retries), and the test
asserts the single-record outcome of `timing(retry(...))` versus the
three-record outcome of `retry(timing(...))`. The order is now part
of the contract, visible at the composition root and pinned in tests.

---

## Chapter rubric

For each non-trap exercise:

- interface that describes only what the operation does (no concerns leaking in)
- one decorator class per concern, all implementing the interface
- composition root that combines decorators in clear, inspectable order
- focused tests for each decorator with a fake inner
- when order matters, a test that pins the order

For the trap: explain why validation is a precondition, not a wrapper.

---

## How to run

```bash
cd php-design-patterns/decorator-chapter-5-guided-practice
php exercise-1-cache-search-decorator/solution.php
php exercise-2-validation-decorator/solution.php
php exercise-3-timing-and-retry/solution.php
```
