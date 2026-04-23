# Chapter 6 — Facade (guided practice)

A Facade hides a noisy subsystem behind one or two domain-named
methods. The trap is wrapping something that **already** is one.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — PDF generation | Controller knows TCPDF setup line by line | **Facade fits** — `InvoicePdfGenerator::generate(Invoice): string` |
| 2 — Cache layer | Caller already uses Laravel's `Cache::remember(...)` | **Trap.** `Cache` *is* the facade. Wrapping it is relabelling |
| 3 — Search and indexing | Controller knows ES indices, body shape, hits traversal | **Facade fits** — `ArticleSearch::search(string): array` of IDs |

---

## Exercise 1 — PDF generation facade

### Before

The `InvoiceController::download()` knows `SetCreator`, `SetAuthor`,
`SetTitle`, `SetMargins`, `SetFont`, `AddPage`, and `writeHTMLCell` —
all to render an invoice PDF. The controller's job (HTTP) and the
PDF library's job (typesetting) are tangled.

### After

```php
final class InvoicePdfGenerator { public function generate(object $invoice): string { /* TCPDF setup */ } }

final class InvoiceController
{
    public function __construct(private InvoicePdfGenerator $generator) {}
    public function download(object $invoice): string { return $this->generator->generate($invoice); }
}
```

Two-line controller. Facade owns the subsystem. Both are independently
testable.

### What the refactor buys

- The controller has one reason to change (HTTP concerns).
- TCPDF setup lives in **one** file. A change to margin policy is one
  edit in one place — never sprinkled across controllers.
- The facade is unit-testable end-to-end: call `generate(invoice)`,
  inspect the recorded TCPDF call sequence in the test.

---

## Exercise 2 — Cache layer (the trap)

### Before

```php
$value = Cache::remember('users.42', 600, fn () => User::find(42));
```

### Verdict — Facade is the wrong answer

`Cache::remember(...)` **is** a facade — that is exactly what the
Laravel `Cache` accessor presents in front of multiple drivers,
locks, expiry, and serialisation. Adding `MyCacheFacade::remember(...)`
that calls `Cache::remember(...)` is a **rename**: it does not
hide a subsystem, it does not shrink the API, it does not raise the
level of abstraction. It only adds an indirection step.

When does a wrapper earn its place?

- **Domain language.** `UserCache::forget(int $userId)` is more
  specific than `Cache::forget("users.{$userId}")`. That is no longer
  "a facade over the cache"; it is a *use-case service that uses the
  cache*. Same building blocks, different intent.
- **Decoupling.** A `CacheStore` interface in front of the Laravel
  facade decouples the use case from a global. That is **DI through a
  domain interface**, not Facade.

For "I want a one-line cache call", the answer is "call the facade
that already exists". The included `solution.php` shows the *correct*
pattern (a domain `UserCache` interface) — not a relabelling.

---

## Exercise 3 — Search and indexing facade

### Before

```php
class SearchController
{
    public function search(Request $r): Response
    {
        $query = ['index' => 'articles', 'body' => ['query' => ['match' => ['title' => $r->q]], 'size' => 10]];
        $client = ClientBuilder::create()->setHosts([env('ES_HOST')])->build();
        $hits = $client->search($query)['hits']['hits'];
        return view('search', ['articles' => array_map(fn ($h) => Article::find($h['_id']), $hits)]);
    }
}
```

### After

```php
interface ArticleSearch { public function search(string $query): array; /* list<int> */ }

final class ElasticArticleSearch implements ArticleSearch { /* index name, body shape, hits traversal */ }

final class SearchController
{
    public function __construct(private ArticleSearch $search) {}
    public function search(string $q): array { return $this->search->search($q); }
}
```

### What the refactor buys

- The controller is a two-line method.
- Index name, request body shape, response traversal — all ES
  vocabulary — live in one file (`ElasticArticleSearch`).
- Adding pagination, fuzziness, or boosting lands inside the facade,
  invisible to the controller.

---

## Chapter rubric

For each non-trap exercise:

- facade interface with one or two operation-named methods (no option arrays)
- implementation hiding all subsystem wiring inside
- callers depending on the facade with no subsystem vocabulary
- focused tests for the facade (correct subsystem use) and the caller (depends on the facade)

For the trap: explain why wrapping an existing facade is relabelling
and what would actually earn its place (domain-language services, DI
through a thin domain interface).

---

## How to run

```bash
cd php-design-patterns/facade-chapter-6-guided-practice
php exercise-1-pdf-facade/solution.php
php exercise-2-cache-layer/solution.php
php exercise-3-search-facade/solution.php
```
