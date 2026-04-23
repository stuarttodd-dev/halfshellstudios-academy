# Chapter 13 — Chain of Responsibility (guided practice)

CoR turns a stack of `if`s into a chain of single-decision handlers,
each free to fire or pass. It pays off when the set of decisions is
open, when ordering matters, or when a custom rule should be slotted in
without touching the others.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Discount calculator | Cascade of pricing rules | **CoR fits** — `VipDiscount`, `BulkDiscount`, `CouponDiscount` |
| 2 — Renderer selector | Three fixed formats | **Trap.** `match` is the right tool |
| 3 — Permission checker | Multiple rules, with module-specific extras | **CoR fits** — base chain + `BillingModuleAccountantRule` slotted in by wiring |

---

## Exercise 1 — Discount chain

### Before

```php
public function calculate(Order $order): int
{
    if ($order->customer->isVip()) return $order->totalInPence * 20 / 100;
    if ($order->total > 100000)    return $order->totalInPence * 10 / 100;
    if ($order->hasCoupon())       return $order->coupon->discountInPence;
    return 0;
}
```

### After

```php
$chain = new DiscountChain([
    new VipDiscount(),
    new BulkDiscount(),
    new CouponDiscount(),
]);
$chain->calculate($order);
```

Each rule is one class, one test, one decision. Adding a new discount
(e.g. `BlackFridayDiscount`) is a new class, no existing edits.

---

## Exercise 2 — Renderer selector (the trap)

### Verdict — CoR is the wrong answer

```php
return match ($format) {
    'pdf'  => new PdfRenderer(),
    'csv'  => new CsvRenderer(),
    'html' => new HtmlRenderer(),
};
```

Three exhaustive, exclusive, stable cases. CoR would scatter the same
information across three handler classes plus a runner — readers would
have to chase the chain to learn that the answer is "one of these
three". `match` is precisely the construct PHP gives you for that.

---

## Exercise 3 — Authorisation chain

### Before

```php
public function check(User $user, string $action, mixed $resource): bool
{
    if ($user->isSuperAdmin()) return true;
    if ($user->isOwnerOf($resource)) return true;
    if ($user->isInTeamWith($resource->ownerId) && $action === 'view') return true;
    if ($user->hasRole('admin') && in_array($action, ['view', 'edit'])) return true;
    return false;
}
```

### After

```php
$baseRules = [new SuperAdminRule(), new OwnerRule(), new TeamViewRule(), new AdminRoleRule()];
$default = new PermissionChain($baseRules);
$billing = new PermissionChain([new BillingModuleAccountantRule(), ...$baseRules]);
```

The billing module gets one extra rule by composition — without
touching `SuperAdminRule`, `OwnerRule`, etc. Each rule is one class
with a single decision.

---

## Chapter rubric

For each non-trap exercise:

- a handler interface with `handle(input, next): result`
- one handler per rule
- a chain runner with a terminal "no" handler
- wiring at the composition root that composes the chain
- a worked example of inserting a custom handler

For the trap: explain why a `match` is clearer than a chain.

---

## How to run

```bash
cd php-design-patterns/chain-of-responsibility-chapter-13-guided-practice
php exercise-1-discount-chain/solution.php
php exercise-2-renderer-selector/solution.php
php exercise-3-permission-chain/solution.php
```
