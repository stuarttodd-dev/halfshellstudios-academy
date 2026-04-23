# Chapter 26 — Prototype (guided practice)

Prototype builds new objects by cloning a fully configured template
instead of reconstructing them from scratch. PHP's `clone` keyword
plus a careful `__clone()` for nested objects is usually all you need.
The trap is reaching for it for cheap value objects.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Email templates | Pre-configured templates personalised per recipient | **Prototype fits** — registry + clone |
| 2 — Money | Trivial value object | **Trap.** Use the immutable `with*` idiom |
| 3 — Game level config | Designer wants variants of a pre-built level | **Prototype fits** — needs deep `__clone()` |

---

## Exercise 1 — Email templates

```php
$email = $registry->get('welcome')->withRecipientName('Sam');
```

The registry holds a fully configured template (subject, header,
footer, font, body skeleton). `get()` clones, `with*()` returns a
clone with a single field changed. Originals stay pristine.

---

## Exercise 2 — Money (the trap)

### Verdict — Prototype is the wrong answer

`Money` is two fields. There is nothing to clone *away from*.
`new Money($amount, $currency)` is as cheap as `clone $proto`. The
`with*` idiom on an immutable value type covers the ergonomics with
no extra concept.

---

## Exercise 3 — Game level config

```php
$harder = $registry->spawn('forest');
$harder->enemyStats->health = 75;     // mutates only this variant
```

`LevelConfig` holds a nested `EnemyStats` object — so `__clone()`
performs `clone $this->enemyStats` to keep variants independent.
This is the canonical Prototype use case: a richly populated object
that's expensive (or simply tedious) to rebuild from scratch.

---

## Chapter rubric

For non-trap exercises:

- a registry returning `clone $prototype` (so callers cannot mutate the
  template by accident)
- a custom `__clone()` for any nested mutable objects (deep clone where
  it matters)
- tests that mutate one variant and confirm the original is untouched

For the trap: explain that cheap value types do not benefit from
Prototype.

---

## How to run

```bash
cd php-design-patterns/prototype-chapter-26-guided-practice
php exercise-1-email-templates/solution.php
php exercise-2-money/solution.php
php exercise-3-game-config/solution.php
```
