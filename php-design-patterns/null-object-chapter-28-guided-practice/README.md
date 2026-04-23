# Chapter 28 — Null Object (guided practice)

Null Object is a *behavioural* default for a collaborator interface.
Instead of clients writing `if ($x !== null) { $x->doThing(); }`, you
hand them an object that implements the interface and does nothing
useful (but does it safely). The trap is using it for *queries*,
where "absent" is a real answer the caller needs to handle.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Logger | Service should log when configured, otherwise stay quiet | **Null Object fits** — `NullLogger` |
| 2 — `findUser` returning a "null user" | Lookup that may not exist | **Trap.** Return `?User` |
| 3 — Discount policy | Cart applies a discount; some carts have none | **Null Object fits** — `NoDiscount` |

---

## Exercise 1 — Logger

```php
final class OrderService
{
    public function __construct(private readonly Logger $logger = new NullLogger()) {}
    public function place(string $sku): string {
        $this->logger->info("placing order for {$sku}");
        // ... no `if ($this->logger !== null)` ever appears ...
    }
}
```

The point isn't that `NullLogger` "represents nothing"; it's that
*every other line of OrderService stays simple*.

---

## Exercise 2 — `findUser` (the trap)

### Verdict — Null Object is the wrong answer

A "null user" object lets bugs through. Callers must remember to
sentinel-check (`$user->name === ''`) instead of `=== null`, and the
authentication layer can hand a `NullUser` to code that thinks it has
a real one.

`findUser(): ?User` keeps the fact "we didn't find one" honest.
PHP's nullable types and `??` make the call sites just as short.

---

## Exercise 3 — Discount policy

```php
final class Cart
{
    public function __construct(
        public readonly int $subtotalInPence,
        private readonly DiscountPolicy $policy = new NoDiscount(),
    ) {}
    public function total(): int { return $this->policy->apply($this->subtotalInPence); }
}
```

`Cart::total()` is one line and never branches on "did this cart have
a discount?" — `NoDiscount` answers `apply($x) = $x` and the world
keeps turning.

---

## Chapter rubric

For non-trap exercises:

- a single interface for the collaborator
- a `Null*` implementation that quietly satisfies it
- service code that depends on the interface and never null-checks

For the trap: explain that for *queries*, "absent" is information
the caller must respond to — don't hide it behind a sentinel object.

---

## How to run

```bash
cd php-design-patterns/null-object-chapter-28-guided-practice
php exercise-1-logger/solution.php
php exercise-2-find-user/solution.php
php exercise-3-discount-policy/solution.php
```
