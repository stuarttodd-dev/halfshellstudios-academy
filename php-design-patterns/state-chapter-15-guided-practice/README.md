# Chapter 15 — State (guided practice)

State turns "string status + a swarm of `if` checks" into one class
per state, each owning the behaviour for every operation in that state.
It earns its keep when several operations behave differently per state
and illegal transitions should be impossible. The trap is forcing it on
a single boolean.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Connection lifecycle | open/send/close gated by string status | **State fits** — `ClosedState`, `ConnectingState`, `OpenState` |
| 2 — Feature toggle | `bool $enabled` + `evaluate()` | **Trap.** A bool is fine |
| 3 — Application form | draft/submitted/under_review/approved/rejected/withdrawn | **State fits** — six states, illegal transitions throw |

---

## Exercise 1 — Connection lifecycle

### State diagram

```
[closed] --open()--> [connecting] --established()--> [open]
   ^                       |                            |
   +-----close()-----------+                            |
   ^                                                    |
   +-----close()----------------------------------------+
```

### Before / After

```php
// before — string status + ifs in every method
public function send(string $msg): void {
    if ($this->status !== 'open') throw new InvalidOp();
    /* ... */
}

// after — each state owns the behaviour
final class ClosedState implements ConnectionState {
    public function send(Connection $c, string $m): void { throw new InvalidConnectionOperation('cannot send while closed'); }
    public function open(Connection $c): void { $c->transitionTo(new ConnectingState()); }
    /* ... */
}
```

---

## Exercise 2 — Feature toggle (the trap)

### Verdict — State is the wrong answer

`evaluate()` returns the same bool either way. There is one operation
and one piece of state. Replacing the bool with two state classes
would buy nothing but indirection. Save State for genuine machines
where each state actually behaves differently across multiple methods.

---

## Exercise 3 — Application form

### State diagram

```
[draft] --submit--> [submitted] --review--> [under_review]
                                              |       |
                                          approve  reject
                                              |       |
                                              v       v
                                         [approved] [rejected]   (terminal)

  Withdraw from any non-terminal state -> [withdrawn]            (terminal)
```

### After

One class per state. An abstract base throws "illegal transition" by
default; each concrete state overrides only the methods that are
actually permitted in that state. This makes illegal transitions
impossible: you cannot `approve()` from `draft` because `DraftState`
does not override `approve()`.

---

## Chapter rubric

For each non-trap exercise:

- one interface with a method per operation
- one class per state implementing all operations
- the context delegates to the current state and exposes a
  `transitionTo()` for state classes to call
- per-state tests plus integration tests that walk the diagram

For the trap: explain why a single bool is the right shape.

---

## How to run

```bash
cd php-design-patterns/state-chapter-15-guided-practice
php exercise-1-connection/solution.php
php exercise-2-feature-toggle/solution.php
php exercise-3-application-form/solution.php
```
