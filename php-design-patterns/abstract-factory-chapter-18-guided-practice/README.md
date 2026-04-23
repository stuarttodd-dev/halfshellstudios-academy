# Chapter 18 — Abstract Factory (guided practice)

Abstract Factory is for *families of products that vary together*: pick
the family once at the composition root, and every consumer receives
matching parts. The trap is bundling unrelated products and ending up
with a factory whose methods sometimes throw.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Brand factory | Header/body/footer styles per brand | **Abstract Factory fits** — `BrandFactory` returns matching `Style` objects |
| 2 — Report factory | `pdfRenderer` / `csvRenderer` / `excelRenderer` (some throw) | **Trap.** Split into separate Factory Methods or skip the factory |
| 3 — Database vendor | Connection + query builder + migrator | **Abstract Factory fits** — `MysqlFactory`, `PostgresFactory` |

---

## Exercise 1 — Brand factory

```php
interface BrandFactory {
    public function headerStyle(): Style;
    public function bodyStyle(): Style;
    public function footerStyle(): Style;
}
final class HalfShellBrandFactory implements BrandFactory { /* Inter, dark grey */ }
final class AcademyBrandFactory   implements BrandFactory { /* Lora, navy */ }

new WelcomeEmail(new HalfShellBrandFactory()); // styles always match
```

Adding a third brand is one new factory class — no edits to
`WelcomeEmail`, no risk of mixing fonts across header and body.

---

## Exercise 2 — Non-parallel families (the trap)

### Verdict — Abstract Factory is the wrong shape

`pdfRenderer()`, `csvRenderer()`, `excelRenderer()` are *unrelated*
products — they happen to render reports but they don't form a
coherent family that varies together. A factory whose `excelRenderer()`
sometimes throws is broader than reality, and forces every consumer to
depend on all three product types when they only need one.

Two better shapes:

- **Three small factories** (Factory Method per renderer) chosen
  independently at the wiring layer — used in this solution.
- **No factory at all** — inject the concrete renderer the caller wants.

---

## Exercise 3 — Database vendor

```php
interface DatabaseFactory {
    public function connection():   Connection;
    public function queryBuilder(): QueryBuilder;
    public function migrator():     Migrator;
}
final class MysqlFactory    implements DatabaseFactory { /* always MySQL family */ }
final class PostgresFactory implements DatabaseFactory { /* always Postgres family */ }

new UserService(new MysqlFactory()); // identifiers, placeholders, and connection all match
```

Service classes never `if ($vendor === 'mysql')`. The vendor decision
is made once at the composition root.

---

## Chapter rubric

For each non-trap exercise:

- a factory interface with one method per product type
- one concrete factory per family with all methods implemented honestly
- callers depending only on the factory interface
- a wiring layer that picks the factory once

For the trap: explain why parallel families are a precondition.

---

## How to run

```bash
cd php-design-patterns/abstract-factory-chapter-18-guided-practice
php exercise-1-brand-factory/solution.php
php exercise-2-non-parallel-factory/solution.php
php exercise-3-database-vendor/solution.php
```
