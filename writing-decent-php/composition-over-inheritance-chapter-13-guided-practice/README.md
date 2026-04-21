# Chapter 13 — Composition over inheritance in app code (guided practice)

> Three exercises to make composition your default reflex. Each one
> takes a piece of code that uses inheritance for the wrong reason
> (sharing helpers, tagging variants, layering behaviour) and replaces
> it with a structure where collaborators are *parameters*.

For each exercise: sketch the new structure before refactoring, write a
test that exercises the composed behaviour, then compare with the
solution and notice where the decisions differ.

Run with PHP 8.2+ (no Composer required):

```bash
php exercise-1-flatten-a-hierarchy/starter.php
php exercise-1-flatten-a-hierarchy/solution.php
php exercise-2-replace-instanceof-with-strategy/starter.php
php exercise-2-replace-instanceof-with-strategy/solution.php
php exercise-3-wrap-behaviour-instead-of-subclassing/starter.php
php exercise-3-wrap-behaviour-instead-of-subclassing/solution.php
```

Every solution preserves the starter's observable output (`diff`-clean
once the explanatory `(notice ...)` lines are stripped), so the only
thing that changes between runs is the *shape* of the code.

---

## Exercise 1 — flatten a hierarchy (`OrderApiController`)

> Refactor a three-level controller hierarchy into a single class with
> collaborators.

### Smells

- Three-level chain: `OrderApiController → AbstractAuthenticatedApiController → AbstractApiController`.
- Two layers of inherited "helpers" — JSON formatting and authentication
  — that the leaf cannot choose, override, or test independently.
- `protected ?User $currentUser` is shared mutable state that flows
  *down* the hierarchy. Reading the leaf requires reading three files.
- "Add an admin endpoint that uses service tokens" turns into "add a
  fourth abstract class".

### Sketch (decided BEFORE coding)

| Concern | Was | Becomes |
|---------|-----|---------|
| JSON formatting | Inherited helper methods on `AbstractApiController` | `JsonResponder` collaborator |
| Authentication | Inherited helper + protected `$currentUser` on `AbstractAuthenticatedApiController` | `RequestAuthenticator` collaborator returning `?User` |
| The actual endpoint | Leaf `OrderApiController extends ...` | `final` `OrderApiController` with **no parent** |

### Before

```php
abstract class AbstractApiController { /* jsonOk, jsonError */ }

abstract class AbstractAuthenticatedApiController extends AbstractApiController
{
    protected ?User $currentUser = null;
    protected function authenticate(Request $r): ?JsonResponse { /* sets $this->currentUser */ }
}

final class OrderApiController extends AbstractAuthenticatedApiController
{
    public function __invoke(Request $r): JsonResponse
    {
        if ($failure = $this->authenticate($r)) { return $failure; }
        return $this->jsonOk(['orders' => $this->orders->ordersFor($this->currentUser->id)]);
    }
}
```

### After

```php
final class JsonResponder        { /* ok(), error() */ }
final class RequestAuthenticator { public function userFor(Request $r): ?User; }

final class OrderApiController
{
    public function __construct(
        private RequestAuthenticator $auth,
        private JsonResponder        $json,
        private InMemoryOrderStore   $orders,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->auth->userFor($request);
        if ($user === null) {
            return $this->json->error('unauthorised', 401);
        }
        return $this->json->ok(['orders' => $this->orders->ordersFor($user->id)]);
    }
}
```

### What the refactor buys

- No parent classes. Read the controller in **one file** and you know
  what it does.
- No shared mutable `$currentUser`. The user is a local variable in
  the one method that needs it.
- `JsonResponder` and `RequestAuthenticator` are directly testable
  without instantiating a controller.
- Every controller picks its own auth/response policy. A new
  service-token-only admin endpoint is one new class with a different
  authenticator — no fourth abstract base needed.

Files: [`exercise-1-flatten-a-hierarchy/`](exercise-1-flatten-a-hierarchy).

---

## Exercise 2 — replace `instanceof` with strategy (`User::welcomeEmail`)

> Eliminate the `instanceof` checks by introducing a `WelcomeTemplate`
> interface and one implementation per audience.

### Smells

- The base class peeks at its own subclass with `$this instanceof X`.
  That is inheritance carrying the *data* of which variant we are,
  while a field would do.
- The subclasses are empty marker types — they exist *only* so the
  base can `instanceof` them.
- Adding a third audience means editing the base class. Closed for
  modification it is not.

### Composition decisions made here

1. Delete the marker subclasses. `User` becomes one concrete class with
   an `Audience` enum field.
2. Audience-specific copy lives in a `WelcomeTemplate` strategy with
   one implementation per audience.
3. A `WelcomeTemplateRegistry` picks the template for a given audience
   and falls back to `GenericWelcomeTemplate` for unmapped values.
4. The "compose a welcome email for a user" workflow lives in a use
   case (`SendWelcomeEmail`), not on the user. **The user stays as
   data; the policy stays as a strategy.**

### Before

```php
abstract class User
{
    public function welcomeEmail(): EmailMessage
    {
        if ($this instanceof CustomerUser) { /* ... */ }
        if ($this instanceof BusinessUser) { /* ... */ }
        return /* generic */;
    }
}
final class CustomerUser extends User {}
final class BusinessUser extends User {}
```

### After

```php
enum Audience: string { case Customer='customer'; case Business='business'; case Generic='generic'; }

final class User
{
    public function __construct(
        public readonly string   $email,
        public readonly string   $name,
        public readonly Audience $audience,
    ) {}
}

interface WelcomeTemplate                    { public function compose(User $user): EmailMessage; }
final class CustomerWelcomeTemplate implements WelcomeTemplate { /* ... */ }
final class BusinessWelcomeTemplate implements WelcomeTemplate { /* ... */ }
final class GenericWelcomeTemplate  implements WelcomeTemplate { /* ... */ }

final class WelcomeTemplateRegistry { public function for(Audience $a): WelcomeTemplate { /* with default */ } }
final class SendWelcomeEmail        { public function for(User $u): EmailMessage; }
```

### What the refactor buys

- Adding a `Partner` audience: declare an enum case + add one new
  `PartnerWelcomeTemplate` + register it. Nothing else moves.
- The `User` class no longer knows anything about email composition,
  so it is reusable in contexts that have nothing to do with welcome
  flows (auth, billing, exports).
- `WelcomeTemplate` implementations are individually testable by
  passing in a constructed `User`. No subclasses required.
- The default template is a real, named class — not a fall-through
  branch hidden at the bottom of an `instanceof` ladder.

Files: [`exercise-2-replace-instanceof-with-strategy/`](exercise-2-replace-instanceof-with-strategy).

---

## Exercise 3 — wrap behaviour instead of subclassing (`LoggingMailer`)

> `LoggingMailer extends SmtpMailer` overrides every method to call
> `parent::*()` and log around it. Refactor it into a `LoggingMailer`
> wrapper class that takes any `Mailer` as a constructor parameter.

### Smells

- **One transport, one decorator.** Logging an `SesMailer` would mean
  writing `LoggingSesMailer extends SesMailer`. With three transports
  and three cross-cutting concerns (logging, retry, throttling),
  that is *nine* classes — quadratic explosion.
- **Silent gaps.** Add a `sendDelayed()` method to `SmtpMailer` and
  it bypasses logging until somebody remembers to override it in
  `LoggingMailer`. The compiler does not help.
- **Locked signatures.** Anything that takes a `SmtpMailer` parameter
  cannot accept the logging variant — the parameter type is the
  concrete class, not the contract.

### Composition decisions made here

1. Extract a `Mailer` interface — the contract every transport satisfies.
2. `SmtpMailer` (and any future `SesMailer`, `RecordingMailer`,
   `SendgridMailer`) `implements Mailer` as siblings, not as parents
   and children of each other.
3. `LoggingMailer implements Mailer` and takes a `Mailer $inner` in
   its constructor. **One decorator works for every transport.**
4. Decorators stack: `RetryingMailer(LoggingMailer(SmtpMailer(...)))`
   reads top-to-bottom and any layer can be removed by swapping it
   for the inner one.

### Before

```php
class SmtpMailer
{
    public function send(Email $e): void { /* SMTP */ }
    public function sendBulk(array $es): void { foreach ($es as $e) $this->send($e); }
}

final class LoggingMailer extends SmtpMailer
{
    public function send(Email $e): void { /* log; parent::send($e); log; */ }
    public function sendBulk(array $es): void { /* log; parent::sendBulk($es); log; */ }
}
```

### After

```php
interface Mailer
{
    public function send(Email $email): void;
    public function sendBulk(array $emails): void;
}

final class SmtpMailer    implements Mailer { public function __construct(private string $host) {} /* ... */ }
final class LoggingMailer implements Mailer { public function __construct(private Mailer $inner) {} /* ... */ }
```

### A real composition decision: what does `LoggingMailer::sendBulk` do?

The decorator now has two valid choices, each with trade-offs:

| Strategy | Pro | Con |
|----------|-----|-----|
| Iterate inside the decorator and call `$this->send($email)` per item | Per-item logs match what you'd see if the user had called `send()` N times. Cross-cutting concerns wrap consistently. | The inner transport's `sendBulk` (which might use one SMTP connection for the batch) is bypassed. |
| Delegate to `$this->inner->sendBulk($emails)` | Inner's bulk efficiency is preserved. | You only get bulk-level logs; per-item visibility disappears. |

The starter inherited from `SmtpMailer`, so PHP's polymorphic dispatch
silently chose strategy 1 for it (`parent::sendBulk` calls `$this->send`,
which resolves back to the subclass). The solution makes the choice
**explicit** — it iterates and logs each item, with a comment naming
the alternative. *That visibility is the whole point of composition:
the decorator decides what gets wrapped at this layer, not the language.*

### What the refactor buys

- One `LoggingMailer` decorates **every** transport.
- The compiler enforces the contract — add a method to `Mailer` and
  every implementation (transport or decorator) must satisfy it.
- New cross-cutting layers (`RetryingMailer`, `ThrottlingMailer`,
  `MetricsMailer`) are independent decorators that compose in any
  order.
- Tests can swap in `RecordingMailer` or any other `Mailer`
  implementation without touching the consumer.

Files: [`exercise-3-wrap-behaviour-instead-of-subclassing/`](exercise-3-wrap-behaviour-instead-of-subclassing).

---

## What ties Chapter 13 together

- **Ex1**: inheritance for *helper sharing* is just hidden coupling.
  Every helper that lived on a base class is happier as a collaborator
  on the constructor.
- **Ex2**: inheritance for *tagging variants* is `instanceof` waiting
  to happen. A field plus a strategy interface cleans it up and stays
  open to extension.
- **Ex3**: inheritance for *layering behaviour* (template-method-style
  pre/post hooks) is what the decorator pattern is for. Same interface,
  one implementation wraps another, no quadratic explosion.

The combined heuristic: **subclass only when the relationship is "is a
narrower kind of the same thing", not when it is "uses the same
helpers", "shares some fields", or "has the same shape".** Composition
is the default; inheritance is the exception that has to justify itself.
