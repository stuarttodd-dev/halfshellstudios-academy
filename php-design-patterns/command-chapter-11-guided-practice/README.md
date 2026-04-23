# Chapter 11 — Command (guided practice)

Command captures an **intent to change state** as a value object,
plus a separate handler. Once you have a bus, cross-cutting concerns
(auth, transactions, audit, logging) become composable middleware.
The trap is wrapping a *query* in command machinery.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Register user | Direct service call inside the controller | **Command fits** — `RegisterUserCommand` + handler + audit middleware |
| 2 — Get customer by id | A read | **Trap.** Queries are not Commands — keep the method call |
| 3 — Cancel subscription | Auth + tx + audit tangled inside the controller | **Command fits** — handler is bare logic; cross-cutting moves to middleware; same command from HTTP / CLI / queue |

---

## Exercise 1 — Register user

### Before

```php
final class RegistrationController
{
    public function __construct(private UserService $users) {}
    public function register(Request $r): Response
    {
        $user = $this->users->register($r->email, $r->password, $r->locale);
        return new Response(['id' => $user->id], 201);
    }
}
```

### After

```php
final class RegisterUserCommand   { /* email, password, locale */ }
final class RegisterUserHandler   { public function __invoke(RegisterUserCommand $cmd): object { /* ... */ } }
final class AuditMiddleware       { public function __invoke(object $cmd, callable $next): mixed { /* log + next */ } }

$bus = new InMemoryCommandBus();
$bus->register(RegisterUserCommand::class, new RegisterUserHandler($users));
$bus->pipe(new AuditMiddleware());

final class RegistrationController
{
    public function __construct(private CommandBus $bus) {}
    public function register(string $email, string $password, string $locale): object
    {
        return $this->bus->dispatch(new RegisterUserCommand($email, $password, $locale));
    }
}
```

### What the refactor buys

- **Command is data**, immutable, easy to validate, easy to log,
  cheap to test.
- **Handler is bare logic** — no HTTP, no transport.
- **Audit middleware** runs once and covers every command, forever —
  not per use case.

---

## Exercise 2 — Get customer by id (the trap)

### Verdict — Command is the wrong answer

The starter is a **query**, not a command:

```php
public function show(int $id): Response { return new Response($this->customers->find($id)); }
```

Rules of thumb (CQRS-friendly):

- **Commands** alter state, return little (an id, void), and benefit
  from audit / authorisation / transaction middleware.
- **Queries** read state, return data, are pure within the
  transaction, and benefit from caching — not from audit middleware.

Forcing a query through a command bus:

- changes the return type to `mixed` (the bus is generic);
- makes call sites read worse;
- subjects every read to middleware that should not apply.

The right shape: keep the query as a method call on the repository (or
a small `CustomerQuery` if it grows). If you want a CQRS-shaped read
side, that is a **Query bus**, not a Command bus.

---

## Exercise 3 — Cancel subscription

### Before

```php
public function cancel(int $subscriptionId, Request $r): Response
{
    if (! auth()->user()->can('subscriptions.cancel')) abort(403);
    DB::beginTransaction();
    try {
        $sub = Subscription::find($subscriptionId);
        $sub->cancel($r->input('reason'));
        $sub->save();
        AuditLog::record('subscription.cancelled', $sub->id);
        DB::commit();
        return response()->json($sub);
    } catch (\Throwable $e) { DB::rollBack(); throw $e; }
}
```

### After

```php
final class CancelSubscriptionCommand { /* subscriptionId, userId, reason */ }
final class CancelSubscriptionHandler { /* find, cancel, save */ }

$bus = new InMemoryCommandBus();
$bus->register(CancelSubscriptionCommand::class, new CancelSubscriptionHandler($subscriptions));
$bus->pipe(new AuditMiddleware());            // outermost — records EVERY attempt
$bus->pipe(new AuthorizationMiddleware($checker));
$bus->pipe(new TransactionalMiddleware());    // innermost — only wraps successful auths

// Same command, three transports:
$controller = new SubscriptionController($bus);
$controller->cancel(userId: 42, subId: 1, reason: 'too expensive');     // HTTP
$bus->dispatch(new CancelSubscriptionCommand(2, 42, 'cli'));            // artisan command
$bus->dispatch(new CancelSubscriptionCommand(3, 42, 'queue'));          // queue worker
```

### What the refactor buys

- **Three concerns extracted** from the controller:
  authorisation, transactions, audit — each is one class, used by
  every command.
- **The handler is bare business logic.** It cannot be told the wrong
  answer about authorisation, because authorisation has already
  happened by the time it runs.
- **Same command from any transport.** HTTP, CLI, queue worker — they
  all build the same `CancelSubscriptionCommand` and dispatch it to
  the same bus. The handler does not know which one called it.
- **Pinned middleware order.** Audit is outermost (records every
  attempt, including refused ones). Auth runs before tx (so an
  unauthorised attempt does not begin a transaction). Tx wraps only
  successful auths.

---

## Chapter rubric

For each non-trap exercise:

- command that is an immutable value object describing the action and its inputs
- handler separate from the command, with its own dependencies
- bus or dispatcher that maps command classes to handlers
- middleware where appropriate (auth, transaction, audit)
- same command dispatchable from multiple transports

For the trap: explain why queries don't belong on a command bus.

---

## How to run

```bash
cd php-design-patterns/command-chapter-11-guided-practice
php exercise-1-register-user/solution.php
php exercise-2-get-customer/solution.php
php exercise-3-cancel-subscription/solution.php
```
