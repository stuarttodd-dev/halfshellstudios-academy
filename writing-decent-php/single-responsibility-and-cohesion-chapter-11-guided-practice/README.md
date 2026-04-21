# Chapter 11 — Single responsibility and cohesion (guided practice)

> Three exercises to put cohesion thinking into your fingers. The point
> is not "every class does one thing"; it is **every class is held
> together by one reason to change** — and you can see that reason at a
> glance.

For each exercise: apply the cohesion checklist *before* refactoring,
identify the seam, then refactor along it. Compare with the solution and
notice differences vs mistakes.

### The cohesion checklist used in these solutions

1. **Audience.** Who calls this? If two callers come from two teams with
   two release cadences, it is two classes pretending to be one.
2. **Reason to change.** List the reasons this code would change. One
   reason → one class. Two reasons → two classes.
3. **Field reach.** Does the class touch all of its fields, or only some
   per method? Methods that touch a different subset of fields are a
   second class trying to escape.
4. **Dependency reach.** Does every method use every collaborator? If
   half the methods ignore half the constructor arguments, the class is
   too fat.
5. **Name.** If you can only name the class with a vague noun
   (`Manager`, `Helpers`, `Service`), the seam has not been found yet.

Run with PHP 8.2+ (no Composer required):

```bash
php exercise-1-split-a-god-class/starter.php
php exercise-1-split-a-god-class/solution.php
php exercise-2-pull-rule-onto-entity/starter.php
php exercise-2-pull-rule-onto-entity/solution.php
php exercise-3-convert-fake-class/starter.php
php exercise-3-convert-fake-class/solution.php
```

---

## Exercise 1 — split a god class (`OrderManager`)

> Refactor this `OrderManager` into focused classes. Decide audiences and
> natural seams *before* touching the code.

### Smells

- One class with **seven** unrelated methods and **seven** dependencies —
  every team that touches an order touches this file.
- The constructor signature already gives the game away: `repo`,
  `customers`, `mailer`, `stripe`, `carrier`, `search`, `crm`. No method
  uses more than four of them.
- Two methods that do not change for the same reason already live next
  to each other (`refund` is driven by the payments team; `exportMonthlyCsv`
  is driven by finance; `syncToCrm` is driven by RevOps).

### Audiences and seams (decided BEFORE coding)

| Audience | Use cases | Collaborators |
|----------|-----------|---------------|
| Customer-facing lifecycle | `PlaceOrder`, `CancelOrder`, `RefundOrder` | orders, customers, mailer (+ stripe for refund) |
| Warehouse / fulfilment | `ShipOrder` | orders, customers, mailer, carrier |
| Search / read-side | `ReindexOrderForSearch` | orders, search |
| Reporting / finance | `ExportMonthlyOrdersCsv` | orders only |
| Integrations | `SyncOrderToCrm` | orders, crm |

A thinner cluster (e.g. `OrderLifecycleService` wrapping place / cancel /
refund) is tempting but would re-create the audience problem at half
scale — cancellations and refunds change for different reasons (SLA
policy vs payments policy). So we keep them apart.

### Before

```php
final class OrderManager
{
    public function __construct(
        public InMemoryOrderRepository $orders,
        public FakeCustomerDirectory   $customers,
        public RecordingMailer         $mailer,
        public FakeStripe              $stripe,
        public FakeShippingCarrier     $carrier,
        public FakeSearchIndex         $search,
        public FakeCrmClient           $crm,
    ) {}

    public function placeOrder(array $r): Order { /* ... */ }
    public function cancel(int $id): void { /* ... */ }
    public function refund(int $id, int $amount): void { /* ... */ }
    public function ship(int $id): void { /* ... */ }
    public function reindexSearch(int $id): void { /* ... */ }
    public function exportMonthlyCsv(int $month): string { /* ... */ }
    public function syncToCrm(int $id): void { /* ... */ }
}
```

### After

```php
final class PlaceOrder              { /* uses: orders, customers, mailer */ }
final class CancelOrder             { /* uses: orders, customers, mailer */ }
final class RefundOrder             { /* uses: orders, customers, mailer, stripe */ }
final class ShipOrder               { /* uses: orders, customers, mailer, carrier */ }
final class ReindexOrderForSearch   { /* uses: orders, search */ }
final class ExportMonthlyOrdersCsv  { /* uses: orders */ }
final class SyncOrderToCrm          { /* uses: orders, crm */ }
```

### What the refactor buys

- Each constructor declares **only** the collaborators that class
  actually uses — that is the cohesion test.
- Changes for the warehouse team, the payments team, and the finance
  team no longer share a file or a deploy blast radius.
- The class name is the verb. You no longer have to read the body to
  know what it does.

Files: [`exercise-1-split-a-god-class/`](exercise-1-split-a-god-class).

---

## Exercise 2 — pull a rule onto the entity (`Subscription`)

> Move the rule from the caller onto `Subscription`. Aim for one method
> with a clear name.

### Smells

- The caller is reaching into three of `Subscription`'s fields and
  AND'ing them together — the rule belongs to the subscription, not
  the caller.
- `new DateTimeImmutable('+7 days')` makes the answer **depend on the
  wall clock**. Two callers (controller + cron) get different results
  if you run them at midnight versus midday.
- The literal `'+7 days'` is a policy hidden inside an expression. Read
  the call site and you would never know it exists.

### Pressure review

1. **Cohesion.** Three predicates that all read subscription fields →
   one method on `Subscription`.
2. **Determinism.** The clock is an *input* to the rule, not a hidden
   global. Pass `DateTimeImmutable $now`.
3. **Hidden constants.** Promote `'+7 days'` to a named class constant
   (`UPGRADE_NOTICE_PERIOD`) sitting next to the policy that uses it.

### Before

```php
if ($subscription->status === 'active' &&
    $subscription->renewsAt > new DateTimeImmutable('+7 days') &&
    $subscription->cancelledAt === null) {
    // eligible for upgrade
}
```

### After

```php
final class Subscription
{
    private const UPGRADE_NOTICE_PERIOD = '+7 days';

    public function isEligibleForUpgrade(DateTimeImmutable $now): bool
    {
        return $this->isActive()
            && ! $this->isCancelled()
            && $this->renewsAt > $now->modify(self::UPGRADE_NOTICE_PERIOD);
    }

    private function isActive(): bool    { return $this->status === 'active'; }
    private function isCancelled(): bool { return $this->cancelledAt !== null; }
}
```

### Behaviour change is the lesson

The starter and solution **deliberately produce different output**
(`[1]` vs `[1, 5]`) when run on most days. The starter is wall-clock
dependent; the solution is deterministic. Reproduce by running both:

```bash
php exercise-2-pull-rule-onto-entity/starter.php
# starter (wall clock):  offers = [1]   ← varies by date

php exercise-2-pull-rule-onto-entity/solution.php
# solution (injected clock): offers = [1,5]   ← always
```

That gap is the *whole point* of the exercise. The rule was buggy and
nobody noticed because the bug only fires on certain days.

### What the refactor buys

- One named, deterministic question on the entity:
  `subscription.isEligibleForUpgrade($now)`.
- The policy and its magic number live together.
- Two callers (controller + cron) get the same answer, every time, for
  the same `$now`.

Files: [`exercise-2-pull-rule-onto-entity/`](exercise-2-pull-rule-onto-entity).

---

## Exercise 3 — convert a fake class (`StringHelpers`)

> Replace this fake class with namespaced functions OR with methods on
> a `Slug` value object. Explain why you chose what you did.

### Smells

- `StringHelpers` is a "fake class": only static methods, no state, no
  invariants. Calling `StringHelpers::slugify($s)` is identical to
  calling a function — the class adds nothing except a typing-cost tax.
- The two methods do not actually generalise to "string helpers"; they
  generalise to "slug operations". The vague name is hiding a real
  domain concept.
- Once `slugify()` returns a `string`, the type system can never tell
  you whether you got the result from `slugify()` or from `$_POST['slug']`
  directly.

### Decision: value object, not namespaced functions

Both options would kill the fake-class smell. The deciding factor is
whether the inputs/outputs have **invariants**. Slugs do:

- lowercase ASCII alphanumerics, hyphen-separated, non-empty after
  normalisation;
- "made unique against a set of taken slugs" is a question only a slug
  can answer — it does not apply to arbitrary strings.

So we promote to `Slug`:

- `Slug::fromTitle(string $title): Slug` is the one place a raw string
  becomes a slug. Validation lives there.
- `Slug::madeUniqueAgainst(array $taken): Slug` returns a new `Slug`,
  so the result is still type-safe.
- A function that takes `Slug $slug` cannot be passed an unnormalised
  user input. The compiler enforces the invariant we used to enforce
  by convention.

Namespaced functions (`Slugs\slugify($s)`, `Slugs\ensureUnique(...)`)
would have been the right call if there were *no* invariant — e.g. a
trim/uppercase utility, or a small `Strings\contains($haystack, $needle)`
shim. Here there is one, so promote.

### Before

```php
final class StringHelpers
{
    public static function slugify(string $s): string { /* ... */ }

    /** @param list<string> $taken */
    public static function ensureUnique(string $base, array $taken): string { /* ... */ }
}
```

### After

```php
final class Slug
{
    private function __construct(public readonly string $value) { /* validates */ }

    public static function fromTitle(string $title): self { /* normalises + validates */ }

    /** @param list<Slug> $taken */
    public function madeUniqueAgainst(array $taken): self { /* returns a Slug */ }

    public function __toString(): string { return $this->value; }
}
```

### What the refactor buys

- A **named domain type** for what was previously a stringly-typed
  concept.
- Validation runs **once**, at construction, at the only seam where a
  raw string becomes a slug. Every consumer downstream gets to assume
  the rules hold.
- Uniqueness is a slug-on-slug operation, not "any string against a
  list of any other strings".
- The collaborators of a slug-using function are obvious from the type
  signature (`function publish(Article $a, Slug $slug): void`).

Files: [`exercise-3-convert-fake-class/`](exercise-3-convert-fake-class).

---

## What ties Chapter 11 together

- **Ex1**: cohesion is a *measurement* of dependency reach. If a method
  uses three of seven dependencies, that method is its own class trying
  to escape.
- **Ex2**: rules that touch only entity fields belong on the entity —
  but the entity should still take its inputs (e.g. the clock) as
  parameters, not pull them from globals.
- **Ex3**: a class with only static methods and no state is not a
  class. Pick one of the two real options: namespaced functions if
  there is no invariant, a value object if there is one.

The common thread: **find the seam, then make the seam visible in the
class name and the constructor signature**. Cohesion is what you have
left after every method, every field, and every dependency points in
the same direction.
