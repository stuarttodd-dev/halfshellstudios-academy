# Chapter 10 — Observer (guided practice)

Observer fits when **several independent reactions** want to happen
when something occurs and the originator should not have to know who
listens. The trap is the case where there is no fan-out — just an
atomic two-step operation.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — User registration | Controller calls mailer + analytics + newsletter inline | **Observer fits** — `UserRegistered` event with three subscribers |
| 2 — Payment processing | Charge gateway, then mark order paid | **Trap.** No fan-out. Two halves of one atomic operation |
| 3 — Inventory updates | Adjust stock; update index, invalidate cache, maybe alert | **Observer fits** — `StockLevelChanged` event, sync vs async per subscriber |

---

## Exercise 1 — User registered

### Before

```php
public function register(Request $r): Response
{
    $user = $this->users->create($r->email, $r->password);
    $this->mailer->send($r->email, new VerifyEmailMail($user));
    $this->analytics->track('user.registered', ['source' => $r->source]);
    $this->newsletter->subscribe($r->email, 'general');
    return new Response('ok');
}
```

### After

```php
final class UserRegistered { public function __construct(public int $userId, public string $email, public string $source) {} }

$dispatcher->subscribe(UserRegistered::class, new SendVerifyEmailOnRegistration($mailer));
$dispatcher->subscribe(UserRegistered::class, new TrackRegistrationOnRegistration($analytics));
$dispatcher->subscribe(UserRegistered::class, new SubscribeToNewsletterOnRegistration($newsletter));

final class RegistrationController
{
    public function __construct(private UserRepository $users, private EventDispatcher $events) {}
    public function register(string $email, string $password, string $source): object
    {
        $user = $this->users->create($email, $password);
        $this->events->dispatch(new UserRegistered($user->id, $email, $source));
        return $user;
    }
}
```

### What the refactor buys

- **Controller depends on what it needs for its real job** (the user
  repo) plus the dispatcher. No mailer, no analytics, no newsletter.
- **Each subscriber owns its own dependency.** A change to the email
  template touches one file.
- **Adding a fourth reaction** (audit log, CRM hook) is a new
  subscriber wired at the composition root — no edit to the
  controller.
- **Three independent test surfaces.** Subscribers are tested by
  invoking them with the event directly. The controller is tested by
  asserting the event was dispatched.

---

## Exercise 2 — Payment processing (the trap)

### Verdict — Observer is the wrong answer

The starter is **two steps of one atomic operation**, not fan-out:

1. authorise + capture money at the gateway,
2. record that capture against the order.

If step 2 fails after step 1 succeeds, the money is taken without the
order being marked paid — the system lies. They are not independent
reactions to "a payment occurred"; they are the payment.

Forcing this into Observer makes things worse:

- fire-and-forget subscribers cannot easily roll back if one step fails;
- the contract becomes "we charged you, then *eventually* the order
  may be marked paid";
- error handling and ordering get scattered across subscribers no
  caller can read in one place.

When does an event become useful here? **After** the atomic operation
succeeds: a `PaymentCaptured` event for receipt sending, analytics,
CRM updates — all genuine fan-out, all OK to fail independently.

---

## Exercise 3 — Inventory updates

### Sync vs async — the deliberate choice

| Subscriber | Mode | Why |
| --- | --- | --- |
| Search index update | **Sync** | Search lying about stock for even seconds is a checkout-conversion problem |
| Cache invalidation | **Sync** | Next request must see the just-written level |
| Low-stock alert | **Async (queued)** | Merchant doesn't need it on the request path; latency is fine |

```php
$dispatcher->subscribeSync (StockLevelChanged::class, new UpdateSearchIndexOnStockChange($search));
$dispatcher->subscribeSync (StockLevelChanged::class, new InvalidateCacheOnStockChange($cache));
$dispatcher->subscribeAsync(StockLevelChanged::class, new AlertOnLowStock($alerter, threshold: 5));
```

The dispatcher we use here records sync subscribers as direct calls
and async subscribers as queued closures. In production, "async"
means a real job queue (Redis / SQS / Beanstalkd) consumed by a
worker — same boundary, different infrastructure.

### What the refactor buys

- **One originator, many reactions.** `InventoryService::adjust(...)`
  no longer knows about search, cache, or alerts.
- **Per-subscriber latency model**, decided at wiring time, visible at
  the composition root.
- **Threshold logic lives in the alerter**, not in the originator.

---

## Chapter rubric

For each non-trap exercise:

- event class that is an immutable value object describing what happened
- one subscriber per reaction, each with its own dependencies
- originator that depends on what's needed for its real job + the dispatcher
- focused tests for the originator (it dispatches the event) and for each subscriber (it does its job when given the event)

For the trap: explain why an atomic two-step operation is not Observer territory.

---

## How to run

```bash
cd php-design-patterns/observer-chapter-10-guided-practice
php exercise-1-user-registered/solution.php
php exercise-2-payment-processing/solution.php
php exercise-3-stock-level-changed/solution.php
```
