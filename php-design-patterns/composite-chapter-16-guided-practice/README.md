# Chapter 16 — Composite (guided practice)

Composite gives leaves and groups the same interface so callers can
walk a tree without `instanceof`. It pays off when the data really is
recursive (an expression tree, an org chart, a folder tree). The trap
is bolting it onto two-level "X has many Y" data where the two ends
are nothing alike.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Boolean expression tree | Recursive AND/OR/NOT/Var | **Composite fits** — `Expression` interface, leaves and composites |
| 2 — Order / OrderLine | Two-level aggregation, very different ends | **Trap.** Aggregation, not Composite |
| 3 — Org chart | Departments contain employees and other departments | **Composite fits** — `OrgNode::headcount()` recursive |

---

## Exercise 1 — Boolean expression tree

```php
interface Expression { public function evaluate(array $ctx): bool; }
final class VarExpr implements Expression { /* leaf */ }
final class AndExpr implements Expression { public function __construct(public Expression $left, public Expression $right) {} }
```

The composites hold other `Expression` references — neither leaves nor
composites care which is which.

---

## Exercise 2 — Order / OrderLine (the trap)

### Verdict — Composite is the wrong answer

`Order` and `OrderLine` are not recursive — an order does not contain
an order. They have different fields, different invariants, and
different consumers. A common `Lineable` interface would be a fiction
nobody actually uses. This is plain *aggregation*: `Order` has many
`OrderLine`s. Keep it as two classes.

---

## Exercise 3 — Org chart

```php
interface OrgNode { public function name(): string; public function headcount(): int; }
final class Employee implements OrgNode  { public function headcount(): int { return 1; } }
final class Department implements OrgNode {
    public function headcount(): int {
        return array_sum(array_map(fn (OrgNode $m) => $m->headcount(), $this->members));
    }
}
```

Callers walk the tree via `OrgNode::headcount()` and never need
`instanceof`. Add/remove are kept off the interface — callers
asking "what is your headcount?" should not be able to mutate the
tree.

---

## Chapter rubric

For each non-trap exercise:

- a single interface implemented by both leaves and composites
- group-only methods (`add` / `remove`) kept *off* the interface
  unless a transparent Composite is genuinely required
- callers depending only on the interface
- per-leaf/per-composite tests plus an integration test with a nested
  fixture

For the trap: explain why aggregation is not Composite.

---

## How to run

```bash
cd php-design-patterns/composite-chapter-16-guided-practice
php exercise-1-boolean-expression/solution.php
php exercise-2-order-line/solution.php
php exercise-3-org-chart/solution.php
```
