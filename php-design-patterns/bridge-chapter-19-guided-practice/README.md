# Chapter 19 — Bridge (guided practice)

Bridge separates two independent axes of variation so they grow
additively (M + N classes) instead of multiplicatively (M × N
subclasses). The trap is using it when there is only one axis — that's
just polymorphism.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Notification × Channel | `PasswordReset{Email,Sms}`, `TwoFactor{Email,Sms}` (4 classes) | **Bridge fits** — 2 notifications + 3 channels = 5 classes; adding push is +1 |
| 2 — Cash vs card register | One axis only | **Trap.** Plain interface is correct |
| 3 — Reports × Renderers | `Sales{Pdf,Html}`, `Stock{Pdf,Html}` | **Bridge fits** — adding XLSX is one new class for both reports |

---

## Exercise 1 — Notifications × channels

```php
abstract class Notification {
    public function __construct(protected Channel $channel) {}
    final public function send(User $u): void {
        $this->channel->deliver($u, $this->subject(), $this->body($u));
    }
    abstract protected function subject(): string;
    abstract protected function body(User $u): string;
}

interface Channel { public function deliver(User $u, string $subject, string $body): void; }
```

Two notifications + three channels = five classes (and one base).
Without Bridge: 2 × 3 = 6 leaf classes, growing as 2 × 4, 2 × 5, …

---

## Exercise 2 — Register (the trap)

### Verdict — Bridge is the wrong answer

`CashRegister` and `CardRegister` differ along *one* axis: payment
method. Bridge needs two independent axes. Forcing it would mean
inventing a second axis (cashier? receipt format?) that is not in the
brief — design by analogy, not by need. Plain interface +
implementations is correct here.

---

## Exercise 3 — Reports × renderers

```php
abstract class Report {
    public function __construct(protected Renderer $renderer) {}
    final public function output(): string {
        return $this->renderer->render($this->title(), $this->rows());
    }
}

interface Renderer { public function render(string $title, array $rows): string; }
```

Sales × {Pdf, Html} → adding XLSX gives **both** reports an XLSX
output for the price of *one* new class. New report types are
independent: one new `Report` subclass works on every existing
renderer.

---

## Chapter rubric

For each non-trap exercise:

- two clearly named hierarchies (Abstraction + Implementor)
- the abstraction holds the implementor by composition
- neither hierarchy mentions the other's concrete types
- tests per axis (not per combination)

For the trap: explain that Bridge requires two independent axes.

---

## How to run

```bash
cd php-design-patterns/bridge-chapter-19-guided-practice
php exercise-1-notification-channels/solution.php
php exercise-2-register/solution.php
php exercise-3-reports-renderers/solution.php
```
