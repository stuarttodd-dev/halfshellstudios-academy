# Chapter 21 — Iterator (guided practice)

Iterator (and PHP's generators / `IteratorAggregate`) hides storage
behind `foreach`, lets callers pull items lazily, and composes cleanly
with filters and maps. The trap is wrapping a three-key array.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Paginated user list | All-at-once load | **Iterator fits** — `IteratorAggregate` yielding page by page |
| 2 — Tiny config | Three keys | **Trap.** `foreach` over the array is enough |
| 3 — Filtered streaming | Read CSV, keep only active rows | **Iterator fits** — generator-based reader + `filter()` helper |

---

## Exercise 1 — Paginated user list

```php
final class UserList implements \IteratorAggregate {
    public function getIterator(): \Generator {
        $offset = 0;
        while ($offset < $this->pager->total()) {
            foreach ($this->pager->page($offset, $this->pageSize) as $u) yield $u;
            $offset += $this->pageSize;
        }
    }
}
```

Tests assert that breaking early (after 5 users) only fetches *one*
page — proof of laziness.

---

## Exercise 2 — Tiny config (the trap)

### Verdict — Iterator is the wrong answer

`['debug' => false, 'env' => 'prod', 'tz' => 'UTC']` already iterates
with `foreach`. Adding `IteratorAggregate` would buy zero behaviour and
add one mental layer for every reader. Save Iterator for storage that
actually benefits from being hidden.

---

## Exercise 3 — Filtered streaming

```php
final class CsvReader {
    public function rows(): \Generator { /* yields associative rows */ }
}
function filter(iterable $items, callable $pred): \Generator {
    foreach ($items as $i) if ($pred($i)) yield $i;
}

foreach (filter($reader->rows(), fn ($r) => $r['status'] === 'active') as $row) { /* ... */ }
```

Tests assert that breaking after `id=2` reads only 3 source lines, not
all of them.

---

## Chapter rubric

For each non-trap exercise:

- `IteratorAggregate` or a method returning a `\Generator`
- no exposed cursors or arrays
- lazy production where the data warrants it
- callers using `foreach` without knowing the storage
- a test that proves laziness (early break reads less)

For the trap: explain why `foreach` over the array suffices.

---

## How to run

```bash
cd php-design-patterns/iterator-chapter-21-guided-practice
php exercise-1-paginated-users/solution.php
php exercise-2-tiny-config/solution.php
php exercise-3-csv-streaming/solution.php
```
