# Chapter 14 — Template Method (guided practice)

Template Method pins a workflow shape in a base class and lets
subclasses fill in only the variation points. It pays off when several
subclasses share a real multi-step shape (fetch -> transform -> format
-> audit) and differ in one or two steps. The trap is forcing it onto
trivial methods that have no shared shape.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Notifier | Email/SMS notifiers duplicate "log -> deliver -> log" | **Template Method fits** — `Notifier::send` is `final`, subclasses implement `deliver` |
| 2 — Greet/farewell | Two one-line methods | **Trap.** No shared workflow to abstract |
| 3 — Order exporters | xlsx/csv/json all do "fetch -> transform -> format -> audit" | **Template Method fits** — `OrderExporter::export` is `final` |

---

## Exercise 1 — Notifier

### Before

```php
final class EmailNotifier { public function send(User $u, string $msg): void {
    $this->logger->info('sending email');
    $this->mailer->send($u->email, $msg);
    $this->logger->info('email sent');
} }
final class SmsNotifier { public function send(User $u, string $msg): void {
    $this->logger->info('sending sms');
    $this->sms->send($u->phone, $msg);
    $this->logger->info('sms sent');
} }
```

### After

```php
abstract class Notifier {
    final public function send(User $u, string $msg): void {
        $this->logger->info("sending {$this->channelName()}");
        $this->deliver($u, $msg);
        $this->logger->info("{$this->channelName()} sent");
    }
    abstract protected function channelName(): string;
    abstract protected function deliver(User $u, string $msg): void;
}
```

`final send()` makes the workflow non-overridable. Adding a Slack
notifier is one new class with two methods.

---

## Exercise 2 — Greet/farewell (the trap)

### Verdict — Template Method is the wrong answer

There is no workflow here. `greet` and `farewell` are one-line
methods that both happen to take a string and return a string. That is
not a shared shape — it is just a shared signature.

A base class would invent a workflow (`abstract phrase(): string`?) and
force readers to chase inheritance to learn that the answer is
`"Hello, $name!"`. Two trivial methods with no shared *steps* — leave
them.

---

## Exercise 3 — Order exporters

### Before

```php
final class CsvOrderExporter  { public function export(int $userId): string { /* fetch, transform, write csv,  audit */ } }
final class JsonOrderExporter { public function export(int $userId): string { /* fetch, transform, write json, audit */ } }
final class XlsxOrderExporter { public function export(int $userId): string { /* fetch, transform, write xlsx, audit */ } }
```

### After

```php
abstract class OrderExporter {
    final public function export(int $userId): string {
        $orders = $this->orders->ordersFor($userId);
        $rows   = array_map($this->transform(...), $orders);
        $payload = $this->format($rows);
        $this->audit->record(/* ... */);
        return $payload;
    }
    abstract protected function format(array $rows): string;
}
final class CsvOrderExporter extends OrderExporter { /* format() only */ }
final class JsonOrderExporter extends OrderExporter { /* format() only */ }
```

Adding XLSX is one class. The workflow shape — fetch, transform,
format, audit — is impossible to break because `export()` is `final`.

---

## Chapter rubric

For each non-trap exercise:

- a base class with a `final` workflow capturing the shape
- abstract variation points with no defaults
- two or more concrete subclasses each providing only the variation
- tests of the base workflow via an anonymous subclass plus tests per concrete variant

For the trap: explain why there is no shared shape to abstract.

---

## How to run

```bash
cd php-design-patterns/template-method-chapter-14-guided-practice
php exercise-1-notifier/solution.php
php exercise-2-greet-farewell/solution.php
php exercise-3-order-exporter/solution.php
```
