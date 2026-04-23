# Chapter 25 — Interpreter (guided practice)

Interpreter represents domain rules (or queries) as a tree of small
classes that all answer one question — `evaluate`, `matches`,
`isSatisfiedBy`. It pays off when the rule space is open and rules are
composed at runtime. The trap is reaching for a grammar to express two
fixed `&&`s.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Search query DSL | `tag:php AND (level:beginner OR level:intermediate)` | **Interpreter fits** — leaves + combinators + tiny parser |
| 2 — Two fixed checks | `total > 100 && customer.isPremium` | **Trap.** An `if` is fine |
| 3 — Cart pricing rules | Multiple campaigns, choose the best | **Interpreter fits** — `OrderTotalAtLeast`, `IsStudent`, `IsMember`, `IsFirstOrder`, `AllOf`, `AnyOf` |

---

## Exercise 1 — Search query DSL

```php
$tree = (new SearchQueryParser())->parse('tag:php AND ( level:beginner OR level:intermediate )');
$matches = array_filter($articles, fn ($a) => $tree->matches($a));
```

Leaves: `TagExpr`, `LevelExpr`. Combinators: `AndExpr`, `OrExpr`. The
parser is tiny because the grammar is tiny — that is the right
ratio.

---

## Exercise 2 — Fixed conditions (the trap)

### Verdict — Interpreter is the wrong answer

Two checks joined by AND are an `if` statement. A grammar with leaf
classes and combinators would only obscure the rule.

If the rule grows ("marketing want to compose discount campaigns"),
that is a *different* shape — see Specification (chapter 27) and the
non-trap exercise 3 below.

---

## Exercise 3 — Cart pricing rules

```php
$campaigns = [
    new Discount('Over £50',       5,  new OrderTotalAtLeast(5_000)),
    new Discount('Student member', 10, new AllOf(new IsStudent(), new IsMember())),
    new Discount('First order',    15, new IsFirstOrder()),
];
bestApplicable($campaigns, $cart);
```

New rules = new leaf classes. Composition by tree models marketing's
mental model accurately.

---

## Chapter rubric

For each non-trap exercise:

- a single interface with `evaluate(context)` (or domain equivalent)
- one leaf class per operand and combinator
- composition as trees in code (or via a parser)
- per-leaf and per-combinator tests

For the trap: explain that two fixed `&&`s do not warrant a grammar.

---

## How to run

```bash
cd php-design-patterns/interpreter-chapter-25-guided-practice
php exercise-1-search-dsl/solution.php
php exercise-2-fixed-conditions/solution.php
php exercise-3-cart-rules/solution.php
```
