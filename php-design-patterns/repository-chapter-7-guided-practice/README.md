# Chapter 7 — Repository (guided practice)

A Repository hides persistence behind a domain-named interface and
returns domain objects, not rows. The trap is the wrapper that
returns ORM models and adds nothing.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Customer over PDO | Service does its own SQL + result-shape handling | **Repository fits** — `CustomerRepository::find(CustomerId): ?Customer` |
| 2 — Eloquent wrapper | Repository wraps `User::find($id)` and returns the Eloquent model | **Trap.** Either delete it (just inject the model) or grow it into a real domain repository |
| 3 — Order query patterns | Service has two non-trivial queries inline | **Repository fits** — methods named after the question (`shippedTotalForMonth`, `mostRecentForCustomer`) |

---

## Exercise 1 — Customer over PDO

### Before

```php
final class WelcomeService
{
    public function __construct(private PDO $db) {}
    public function welcome(int $customerId): void
    {
        $stmt = $this->db->prepare("SELECT email, name FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) throw new RuntimeException('No customer');
        Mail::to($row['email'])->send(new WelcomeEmail($row['name']));
    }
}
```

### After

```php
interface CustomerRepository { public function find(CustomerId $id): ?Customer; }

final class PdoCustomerRepository      implements CustomerRepository { /* SQL + row→Customer */ }
final class InMemoryCustomerRepository implements CustomerRepository { /* tests + dev wiring */ }

final class WelcomeService
{
    public function __construct(private CustomerRepository $customers, private Mailer $mailer) {}
    public function welcome(CustomerId $id): void
    {
        $customer = $this->customers->find($id) ?? throw new CustomerNotFound("...");
        $this->mailer->send($customer->email, 'Welcome', "Hello {$customer->name}!");
    }
}
```

### What the refactor buys

- **Domain types in the signature.** `welcome(CustomerId $id)` cannot
  be passed `$row['id']` from another query.
- **Domain exception.** `CustomerNotFound` is a named, catchable type;
  the framework no longer has to grep error strings.
- **Two implementations.** `InMemoryCustomerRepository` powers tests
  with no DB at all; `PdoCustomerRepository` is the production wiring.
- **Use case has no SQL.** A change to the customer table is one edit
  in one file (the PDO repo).

---

## Exercise 2 — Eloquent wrapper (the trap)

### Before

```php
final class UserRepository
{
    public function find(int $id): ?User { return User::find($id); }
}
```

### Verdict — this Repository does not earn its keep

The wrapper is one method that calls one method. It returns the
Eloquent model. It does not introduce a domain interface, simplify
the call site, decouple from Eloquent, or make testing easier. It
is a layer for the sake of a layer.

Two reasonable resolutions:

1. **Delete it.** Inject the Eloquent model directly. No layer means
   no extra maintenance.
2. **Grow it into a real Repository:** a domain interface returning a
   *domain* `User` (a value object / aggregate, not the Eloquent model),
   with methods named after questions the use case asks (`find`,
   `byEmail`). The Eloquent implementation is one of multiple,
   alongside `InMemory…`.

The right choice depends on whether the use case ever wants to be
tested without Eloquent, or run against a non-Eloquent backend.

The included `solution.php` shows option 2 — what the wrapper should
*become* if you decide to keep it.

---

## Exercise 3 — Order query patterns

### Before

```php
public function totalForMonth(int $year, int $month): int { /* PDO SQL with YEAR()/MONTH()/SUM() */ }
public function lastForCustomer(int $customerId): ?array { /* PDO SQL ORDER BY DESC LIMIT 1, returns array */ }
```

### After

```php
interface OrderRepository
{
    public function shippedTotalForMonth(int $year, int $month): int;
    public function mostRecentForCustomer(CustomerId $customerId): ?Order;
}

final class PdoOrderRepository      implements OrderRepository { /* one SQL per method, returns domain types */ }
final class InMemoryOrderRepository implements OrderRepository { /* PHP loops, returns the same domain types */ }

final class ReportService { public function __construct(private OrderRepository $orders) {} }
```

### Where do the totals belong — repository or `OrderStatistics`?

The repository is the right home for `shippedTotalForMonth` because:

- it is **one efficient SQL question** ("sum of shipped totals for a
  month") that PDO can answer in a single round trip;
- summing in PHP after fetching every shipped row would be
  unacceptably slow for a real shop;
- the method name describes the **question**, not the storage.

A separate `OrderStatistics` class would earn its place for
**compositions** of repository calls — "monthly shipped total minus
refunds, grouped by region, year-over-year". That is logic, not
storage. We keep the door open by reading `OrderStatistics` and seeing
nothing but `OrderRepository` calls and arithmetic.

---

## Chapter rubric

For each non-trap exercise:

- repository interface with methods named in domain terms (no `findByCriteria`)
- a real implementation (PDO) **and** an in-memory implementation
- callers depending on the interface, with no SQL or ORM internals visible
- focused tests using the in-memory implementation; integration tests
  for the real implementation against a database
- domain exceptions for "not found" — never `null` interpreted as failure when failure is meaningful

For the trap: explain why the wrapper does not earn its place and
sketch the two correct moves.

---

## How to run

```bash
cd php-design-patterns/repository-chapter-7-guided-practice
php exercise-1-customer-repository/solution.php
php exercise-2-eloquent-wrapper/solution.php
php exercise-3-order-queries/solution.php
```
