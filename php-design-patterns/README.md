# PHP design patterns — guided practice solutions

Reference solutions for the **PHP design patterns** course on Half Shell
Studios Academy. One folder per chapter. Each folder contains:

- a `README.md` with the brief, "before/after" snippets, smell analysis,
  and a verdict on the deliberate **trap** exercise (the one where the
  pattern is *not* the right answer);
- a `solution.php` per exercise, self-contained and runnable with
  `php solution.php`. Each file ends with a small assertion block that
  exercises the solution; running the file with no flags is the test.

Every chapter follows the same shape:

| Position | Flavour | Verdict |
| --- | --- | --- |
| Exercise 1 | Apply the pattern to a realistic case | Pattern fits |
| Exercise 2 | Deliberate trap | Pattern does **not** fit — the README says why |
| Exercise 3 | Apply the pattern to a more demanding case | Pattern fits |

The trap is load-bearing: half of "knowing a pattern" is recognising
when not to use it.

## Chapters

| # | Pattern | Folder |
| --- | --- | --- |
| 2 | Strategy | [strategy-chapter-2-guided-practice](strategy-chapter-2-guided-practice/) |
| 3 | Factory Method | [factory-method-chapter-3-guided-practice](factory-method-chapter-3-guided-practice/) |
| 4 | Adapter | [adapter-chapter-4-guided-practice](adapter-chapter-4-guided-practice/) |
| 5 | Decorator | [decorator-chapter-5-guided-practice](decorator-chapter-5-guided-practice/) |
| 6 | Facade | [facade-chapter-6-guided-practice](facade-chapter-6-guided-practice/) |
| 7 | Repository | [repository-chapter-7-guided-practice](repository-chapter-7-guided-practice/) |
| 8 | Builder | [builder-chapter-8-guided-practice](builder-chapter-8-guided-practice/) |
| 9 | Singleton (and alternatives) | [singleton-chapter-9-guided-practice](singleton-chapter-9-guided-practice/) |
| 10 | Observer | [observer-chapter-10-guided-practice](observer-chapter-10-guided-practice/) |
| 11 | Command | [command-chapter-11-guided-practice](command-chapter-11-guided-practice/) |
| 12 | Pipeline / Middleware | [pipeline-middleware-chapter-12-guided-practice](pipeline-middleware-chapter-12-guided-practice/) |
| 13 | Chain of Responsibility | [chain-of-responsibility-chapter-13-guided-practice](chain-of-responsibility-chapter-13-guided-practice/) |
| 14 | Template Method | [template-method-chapter-14-guided-practice](template-method-chapter-14-guided-practice/) |
| 15 | State | [state-chapter-15-guided-practice](state-chapter-15-guided-practice/) |
| 16 | Composite | [composite-chapter-16-guided-practice](composite-chapter-16-guided-practice/) |
| 17 | Proxy | [proxy-chapter-17-guided-practice](proxy-chapter-17-guided-practice/) |
| 18 | Abstract Factory | [abstract-factory-chapter-18-guided-practice](abstract-factory-chapter-18-guided-practice/) |
| 19 | Bridge | [bridge-chapter-19-guided-practice](bridge-chapter-19-guided-practice/) |
| 20 | Flyweight | [flyweight-chapter-20-guided-practice](flyweight-chapter-20-guided-practice/) |
| 21 | Iterator | [iterator-chapter-21-guided-practice](iterator-chapter-21-guided-practice/) |
| 22 | Mediator | [mediator-chapter-22-guided-practice](mediator-chapter-22-guided-practice/) |
| 23 | Memento | [memento-chapter-23-guided-practice](memento-chapter-23-guided-practice/) |
| 24 | Visitor | [visitor-chapter-24-guided-practice](visitor-chapter-24-guided-practice/) |
| 25 | Interpreter | [interpreter-chapter-25-guided-practice](interpreter-chapter-25-guided-practice/) |
| 26 | Prototype | [prototype-chapter-26-guided-practice](prototype-chapter-26-guided-practice/) |
| 27 | Specification | [specification-chapter-27-guided-practice](specification-chapter-27-guided-practice/) |
| 28 | Null Object | [null-object-chapter-28-guided-practice](null-object-chapter-28-guided-practice/) |

## How to run

```bash
cd php-design-patterns/<chapter-folder>/<exercise-folder>
php solution.php
```

A successful run prints `PASS: ...` for each assertion and finishes
with `All assertions passed.`. PHP's `assert()` is used in the
strict-throw mode (`zend.assertions=1, assert.exception=1`) so any
failure raises `AssertionError` and exits non-zero.

← [Half Shell Studios Academy](../README.md)
