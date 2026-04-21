# Chapter 8 guided practice — errors, results, and boundaries

Three exercises about distinguishing **domain failures** (an account
isn't found, a balance is too low, a customer doesn't exist) from
**system failures** (the database is down, the disk is full), and
choosing the right shape — exception, result, or value — for each.

- **Exercise 1** — replace catch-all `\Exception` with named domain
  exceptions and translate them to specific HTTP statuses at the
  controller boundary.
- **Exercise 2** — drop a `catch (\Throwable)` blanket: catch only the
  named failures the controller knows how to translate, and let
  system failures bubble up to the framework's top-level handler.
- **Exercise 3** — kill `false`-as-sentinel in a repository by
  splitting one method into two: `byEmailOrNull` and `byEmailOrFail`,
  and update each call site to use the one that fits its situation.

A note on diffs in this chapter: unlike the earlier chapters,
**Exercises 1 and 2 deliberately change observable behaviour**. That
is the entire point — error responses become more honest. Each
solution's driver prints the new responses so the change is visible.
Exercise 3 is a pure restructure and its starter/solution outputs are
byte-for-byte identical.

## Exercise 1 — name the failures

The use case throws raw `\Exception` for three different things, and
the controller (or a hypothetical caller) has no way to tell them
apart without parsing the message.

### Smells in the starter

- **Three failure modes, one type.** "From account missing", "to
  account missing", and "insufficient balance" all surface as
  `\Exception`. The only thing distinguishing them is the message —
  exactly the kind of thing you should not switch on in code.
- **No data on the exception.** `'Insufficient balance'` is a string;
  it doesn't say *which* account, *what* balance, *what* was
  requested. The boundary has nothing useful to render.
- **Boundary collapses everything to 500.** Because the controller
  catches `\Throwable`, every failure looks like a server error to the
  client — even the ones that are clearly the client's fault (404,
  422). The HTTP semantics are lost.

### What the refactor buys

- Two **named domain exceptions** — `AccountNotFoundException` and
  `InsufficientBalanceException` — each carrying the data the
  boundary needs (which account, available, requested) on typed,
  public properties.
- The use case throws domain exceptions directly using PHP 8's
  `?? throw` shorthand, keeping the happy path the visual main line.
- A **translation layer** in the controller that maps each named
  exception to a specific status:
  - `AccountNotFoundException` → `404 account_not_found`
  - `InsufficientBalanceException` → `422 insufficient_balance`
- **Anything not caught propagates.** A `RuntimeException` from the
  database client is a real bug — it should reach the framework's
  top-level handler, not be silently swallowed as a generic 500.
- Tests can now assert on the **exception class**, not the message
  string — refactoring the wording becomes safe.

### Behaviour change (visible in the driver output)

| Scenario | Starter | Solution |
| --- | --- | --- |
| happy | `200 {"status":"ok"}` | `200 {"status":"ok"}` |
| from missing | `500 {"error":"From account not found"}` | `404 {"error":"account_not_found","role":"source","account_id":9}` |
| to missing | `500 {"error":"To account not found"}` | `404 {"error":"account_not_found","role":"destination","account_id":9}` |
| insufficient balance | `500 {"error":"Insufficient balance"}` | `422 {"error":"insufficient_balance","account_id":2,"available":500,"requested":2000}` |

### Before

```php
final class TransferFunds
{
    public function transfer(int $fromAccount, int $toAccount, int $amountInPence): void
    {
        $from = $this->accounts->byId($fromAccount);
        if ($from === null) { throw new \Exception('From account not found'); }
        $to = $this->accounts->byId($toAccount);
        if ($to === null)   { throw new \Exception('To account not found'); }
        if ($from->balanceInPence < $amountInPence) {
            throw new \Exception('Insufficient balance');
        }
        $this->accounts->debit($from, $amountInPence);
        $this->accounts->credit($to, $amountInPence);
    }
}
```

### After

```php
final class AccountNotFoundException extends \DomainException
{
    public function __construct(public readonly int $accountId, public readonly string $role)
    {
        parent::__construct("{$role} account #{$accountId} not found");
    }
}

final class InsufficientBalanceException extends \DomainException
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $availableInPence,
        public readonly int $requestedInPence,
    ) {
        parent::__construct(
            "Account #{$accountId} has {$availableInPence}p but {$requestedInPence}p was requested"
        );
    }
}

final class TransferFunds
{
    public function transfer(int $fromAccount, int $toAccount, int $amountInPence): void
    {
        $from = $this->accounts->byId($fromAccount)
            ?? throw new AccountNotFoundException($fromAccount, 'source');

        $to = $this->accounts->byId($toAccount)
            ?? throw new AccountNotFoundException($toAccount, 'destination');

        if ($from->balanceInPence < $amountInPence) {
            throw new InsufficientBalanceException(
                accountId:        $from->id,
                availableInPence: $from->balanceInPence,
                requestedInPence: $amountInPence,
            );
        }

        $this->accounts->debit($from, $amountInPence);
        $this->accounts->credit($to, $amountInPence);
    }
}

final class TransferFundsController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->useCase->transfer(/* … */);
        } catch (AccountNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'account_not_found', 'role' => $e->role, 'account_id' => $e->accountId],
                404,
            );
        } catch (InsufficientBalanceException $e) {
            return new JsonResponse(
                ['error' => 'insufficient_balance', 'account_id' => $e->accountId, 'available' => $e->availableInPence, 'requested' => $e->requestedInPence],
                422,
            );
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
```

## Exercise 2 — drop the blanket catch

The starter catches `\Throwable`, logs the message, and returns 500 for
everything. Replace it with named catches and let system failures
propagate to the framework.

### Smells in the starter

- **`catch (\Throwable)` swallows real bugs.** A `RuntimeException`
  about a dead database connection is indistinguishable, from outside,
  from a `CustomerNotFoundException`. Both become "Something went
  wrong" 500s, both get one log line — and the system failure (which
  needs alerting) gets the same treatment as an expected domain
  outcome.
- **Stack trace is gone.** Once the controller turns the exception
  into a string and returns a `JsonResponse`, the framework's
  top-level handler — and your error tracker — never see it.
- **Status semantics are lost.** "Customer not found" is a 404; "order
  already invoiced" is a 409 (conflict); "amount must be > 0" is a
  422. The blanket catch flattens all three to 500.
- **No way for clients to react programmatically.** A retry-on-conflict
  client can't tell a conflict from a server error from a validation
  failure.

### What the refactor buys

- The controller catches **only what it knows how to translate** —
  `CustomerNotFoundException`, `OrderAlreadyInvoicedException`, and
  `InvalidInvoiceInputException`, each mapped to its true status.
- **System failures (`RuntimeException` and friends) propagate.** The
  framework's top-level handler logs them with full stack trace,
  notifies your error tracker, and returns a generic 500. This is
  exactly the separation the chapter is about: the controller knows
  about the *domain*; the framework knows about *transport-level
  failure*.
- The body of the happy path is no longer indented inside a `try`
  block — it's the last thing the controller does.
- Tests can assert on the **status code**, not on the
  "something went wrong" message string.

### Behaviour change (visible in the driver output)

| Scenario | Starter | Solution |
| --- | --- | --- |
| happy | `201 ok` | `201 ok` |
| customer-missing | `500 Something went wrong` | `404 customer_not_found` |
| already-invoiced | `500 Something went wrong` | `409 order_already_invoiced` |
| bad-input | `500 Something went wrong` | `422 invalid_input` |
| system-failure | `500 Something went wrong` *(controller logged)* | `500 Internal Server Error` *(framework top-level handler logged with prefix `top-level:`)* |

That last row is the most important one: the **status code is the same**,
but in the solution the framework saw the exception and could have
notified PagerDuty, attached the full stack trace to Sentry, etc. — none
of which would have happened in the starter, because the controller
swallowed it.

### Before

```php
final class CreateInvoiceController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->createInvoice->handle(/* ... */);
            return new JsonResponse(['status' => 'ok'], 201);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return new JsonResponse(['error' => 'Something went wrong'], 500);
        }
    }
}
```

### After

```php
final class CreateInvoiceController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $invoiceId = $this->createInvoice->handle($request);
        } catch (CustomerNotFoundException $e) {
            return new JsonResponse(['error' => 'customer_not_found',     'message' => $e->getMessage()], 404);
        } catch (OrderAlreadyInvoicedException $e) {
            return new JsonResponse(['error' => 'order_already_invoiced', 'message' => $e->getMessage()], 409);
        } catch (InvalidInvoiceInputException $e) {
            return new JsonResponse(['error' => 'invalid_input',          'message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['status' => 'ok', 'invoice_id' => $invoiceId], 201);
    }
}
```

## Exercise 3 — pick the right return shape

`byEmail()` returns `Customer|false`. That single signature serves
three call sites that want three different things from "not found",
and so every call site has to remember to compare to `false`.

### Smells in the starter

- **`false` as a sentinel.** Confusing for two reasons: (a) `false`
  is not the same as "absent" in the language's mental model — `null`
  is — and (b) `Customer|false` means every consumer must remember to
  do the strict `=== false` check or risk a type error downstream.
- **One method, three opinions.** The profile controller wants
  "missing is an error, render 404". The marketing job wants "missing
  is fine, skip silently". The login flow wants "missing is the
  expected first-time path, fall through to create". Forcing all
  three through the same API means each one carries its own
  defensive `if`.
- **Easy to forget.** A new caller writing
  `$repo->byEmail($email)->name` looks reasonable at a glance, but
  blows up the moment the customer doesn't exist.

### What the refactor buys

- **Two methods, two clear contracts** — and the names *tell you* what
  each one does on absence:
  - `byEmailOrNull(string): ?Customer` — for "missing is fine".
  - `byEmailOrFail(string): Customer` — for "missing is an error",
    raising a typed `CustomerNotFoundException` carrying the email.
- Each call site uses the **one whose semantics match its situation**:
  - The profile controller catches `CustomerNotFoundException` and
    renders 404. Its body reads as a single happy path.
  - The marketing job uses `byEmailOrNull` and skips silently with a
    null guard.
  - The login flow uses `byEmailOrNull` with `?? new Customer(...)` —
    upsert in one expression.
- The repository's signatures now communicate intent **at the type
  level**: the second one is `: Customer`, never null, never false.

### Behaviour preserved

The starter and solution drivers produce **identical output** —
this is a pure restructure. The improvement is in clarity at every
call site, not in observable behaviour.

### Before

```php
final class CustomerRepository
{
    public function byEmail(string $email): Customer|false
    {
        $row = DB::table('customers')->where('email', $email)->first();
        return $row === null ? false : Customer::fromRow($row);
    }
}

// Call site 1
$customer = $this->customers->byEmail($email);
if ($customer === false) {
    return new JsonResponse(['error' => 'customer_not_found'], 404);
}
return new JsonResponse(['id' => $customer->id, 'name' => $customer->name]);

// Call site 2
$customer = $this->customers->byEmail($email);
if ($customer === false)         { return; }
if (! $customer->marketingOptIn) { return; }
$this->mailer->sendCampaignTo($customer);

// Call site 3
$existing = $this->customers->byEmail($email);
if ($existing !== false) {
    return $existing;
}
return new Customer(/* ... */);
```

### After

```php
final class CustomerNotFoundException extends \DomainException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct("Customer with email {$email} not found.");
    }
}

final class CustomerRepository
{
    public function byEmailOrNull(string $email): ?Customer
    {
        $row = DB::table('customers')->where('email', $email)->first();
        return $row === null ? null : Customer::fromRow($row);
    }

    public function byEmailOrFail(string $email): Customer
    {
        return $this->byEmailOrNull($email)
            ?? throw new CustomerNotFoundException($email);
    }
}

// Call site 1 — must exist
try {
    $customer = $this->customers->byEmailOrFail($email);
} catch (CustomerNotFoundException) {
    return new JsonResponse(['error' => 'customer_not_found'], 404);
}
return new JsonResponse(['id' => $customer->id, 'name' => $customer->name]);

// Call site 2 — missing is fine
$customer = $this->customers->byEmailOrNull($email);
if ($customer === null)          { return; }
if (! $customer->marketingOptIn) { return; }
$this->mailer->sendCampaignTo($customer);

// Call site 3 — upsert
return $this->customers->byEmailOrNull($email)
    ?? new Customer(/* ... */);
```

## Running the solutions

Each exercise folder is self-contained and runs with plain PHP — no
Composer, no framework, no database:

```bash
# Exercise 1 — outputs differ on purpose; the README table shows the change
cd writing-decent-php/errors-results-and-boundaries-chapter-8-guided-practice/exercise-1-name-the-failures
php starter.php
php solution.php

# Exercise 2 — same; diff is the lesson
cd ../exercise-2-drop-the-blanket-catch
php starter.php
php solution.php

# Exercise 3 — pure restructure, outputs identical
cd ../exercise-3-pick-the-right-return-shape
diff <(php starter.php) <(php solution.php)   # no output ⇒ behaviour preserved
```
