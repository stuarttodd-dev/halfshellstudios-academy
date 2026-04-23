# Chapter 20 — Flyweight (guided practice)

Flyweight splits objects into **intrinsic** state (immutable, shared)
and **extrinsic** state (per-instance), with a registry that returns
the *same* shared object every time. It pays off at scale and only
at scale. The trap is reaching for it when there are tens of objects,
not millions.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Forum posts | Author profile data duplicated in every post | **Flyweight fits** — `AuthorRegistry` returns shared `AuthorProfile` |
| 2 — 50 settings | App config | **Trap.** Saving negligible at this cardinality |
| 3 — Game enemies | Type stats duplicated across thousands of enemies | **Flyweight fits** — `EnemyTypeRegistry` |

---

## Exercise 1 — Forum posts with author profile

```php
final class AuthorProfile { /* intrinsic — id, name, avatar, title */ }
final class AuthorRegistry {
    public function get(string $authorId): AuthorProfile { return $this->cache[$authorId] ??= $this->repo->find($authorId); }
}
final class Post {
    public function __construct(public AuthorProfile $author, public string $body) {}
}
```

Tests assert *identity* (`$a === $b`), not just equality, plus a
serialised-byte comparison showing the saving.

---

## Exercise 2 — Settings (the trap)

### Verdict — Flyweight is the wrong answer

50 settings ≠ a duplication problem. The registry/factory/extrinsic-vs-
intrinsic ceremony costs more than the bytes it would save. Plain
objects are fine; a `Setting` is small and there is no duplication to
deduplicate.

---

## Exercise 3 — Game enemies

```php
final class EnemyType { /* intrinsic — maxHp, attack, defence, sprite, sound */ }
final class Enemy {
    public int $hp;
    public function __construct(public EnemyType $type, public int $x, public int $y) {
        $this->hp = $type->maxHp;
    }
}
```

3 shared `EnemyType` objects power 1000 enemies. Damage is per-instance
(`$enemy->hp`); type stats are shared.

---

## Chapter rubric

For each non-trap exercise:

- a clear split of intrinsic (shared, immutable) and extrinsic (per-instance) state
- a registry/factory that returns shared instances
- tests that assert *identity* of shared instances
- a measurement (byte count) showing the saving

For the trap: explain why the cardinality is too small to bother.

---

## How to run

```bash
cd php-design-patterns/flyweight-chapter-20-guided-practice
php exercise-1-author-profile/solution.php
php exercise-2-settings/solution.php
php exercise-3-game-enemies/solution.php
```
