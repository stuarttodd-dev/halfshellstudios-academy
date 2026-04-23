# Chapter 27 — Specification (guided practice)

Specification gives you a single domain predicate type
(`isSatisfiedBy(candidate): bool`) that you can compose with `and`,
`or`, and `not`. The same spec then drives selection (`array_filter`,
repository queries) and validation (guard at write boundaries). The
trap is wrapping a single boolean field in a class.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Customer segments | Marketing want composable VIP segments | **Specification fits** — composable leaves |
| 2 — `$user->isActive` filter | One boolean | **Trap.** A direct closure is fine |
| 3 — Product promotion | Same rule guards both selection AND validation | **Specification fits** — reuse across boundaries |

---

## Exercise 1 — Customer segments

```php
$ukVip = (new InCountry('GB'))
    ->and(new HasAtLeastOrders(10))
    ->and(new LifetimeValueAtLeast(50_000))
    ->and(new IsMarketingOptedIn());
```

Each leaf is a tiny class. New criteria add new leaves, never edits to
existing ones (OCP). The fluent `and`/`or`/`not` lives on a small
`CompositeSpecification` base.

---

## Exercise 2 — Single boolean field (the trap)

### Verdict — Specification is the wrong answer

A single boolean field doesn't need a domain predicate type.
`array_filter($users, fn ($u) => $u->isActive)` is faster to read
*and* write than `(new IsActiveSpecification())->isSatisfiedBy($u)`.

If you genuinely need to compose several user flags into named
segments, that's exercise 1's shape — promote then.

---

## Exercise 3 — Product promotion (selection AND validation)

```php
$summerSale = (new CategoryIs('shoes'))->and(new InStock())->and(new PriceAtMost(5_000));

eligibleForBanner($catalogue, $summerSale);                    // selection
assertEligibleForPromotion($product, $summerSale);             // validation
```

The killer feature of Specification is **reuse across boundaries**:
the rule that decides which products *appear* on the banner is
literally the same object that *guards* manual additions. One source
of truth for "what counts as a sale product".

---

## Chapter rubric

For non-trap exercises:

- one interface (`isSatisfiedBy(candidate): bool`)
- a small `CompositeSpecification` base providing `and`/`or`/`not`
- one leaf class per criterion (immutable, no shared state)
- spec reused at multiple callsites (selection AND validation if
  possible)
- per-leaf and composed tests

For the trap: explain that a single boolean field doesn't need its own
predicate type.

---

## How to run

```bash
cd php-design-patterns/specification-chapter-27-guided-practice
php exercise-1-customer-segments/solution.php
php exercise-2-active-flag/solution.php
php exercise-3-product-promotion/solution.php
```
