# Chapter 10 — A review of SOLID principles (guided practice)

> Three exercises to make SOLID feel like a *reading* skill, not a writing rule.
> The point of this chapter is **judgement** — naming the pressures, ranking
> them, and deciding which ones are worth acting on *today*.

For each exercise: write a one-paragraph **pressure review** before touching
the code, refactor based on that review, then compare with the solution and
check whether your priority order matches.

| Pressure | One-liner |
|----------|-----------|
| **SRP** | The class has more than one reason to change. |
| **OCP** | New variants force edits to the same `if` / `switch`. |
| **LSP** | A subclass weakens or breaks a parent contract. |
| **ISP** | Consumers are forced to depend on methods they do not call. |
| **DIP** | High-level policy depends directly on a low-level concrete. |

Run each exercise with PHP 8.2+ (no Composer required):

```bash
php exercise-1-pressure-review/starter.php
php exercise-1-pressure-review/solution.php
php exercise-2-open-closed-candidate/starter.php
php exercise-2-open-closed-candidate/solution.php
php exercise-3-dip-with-restraint/starter.php
php exercise-3-dip-with-restraint/solution.php
```

Every solution preserves the starter's observable output (`diff`-clean), so
the only thing that changes between runs is the *shape* of the code.

---

## Exercise 1 — pressure review (`CustomerService`)

> For this class, write a one-paragraph review naming the SOLID pressures
> (in priority order) and one specific action per pressure. Then refactor.

### Smells

- One class, three completely unrelated jobs: signing users up, running
  monthly billing, and producing finance exports. Three stakeholders,
  three release cadences, one file.
- Every consumer of `CustomerService` drags Stripe, the mailer, the audit
  log, the user store, **and** the invoice store into its dependency
  graph — even when it just wants to register a user.
- The class name (`CustomerService`) is too vague to disagree with, which
  is exactly why it keeps growing.

### Pressure review (priority order)

1. **SRP.** Three reasons to change — registration logic, billing logic,
   finance reporting — each driven by a different stakeholder. **Action:**
   split into three classes named after the use case.
2. **ISP.** The split removes the "you must depend on Stripe to register a
   user" problem for free, because each class only declares the
   collaborators it actually uses.
3. **DIP / OCP / LSP** do not press hard here — there is no growing
   polymorphism, no missing abstraction, no inheritance tree to worry
   about. Note them and move on.

### Before

```php
final class CustomerService
{
    public function register(array $req): JsonResponse { /* validates, hashes, inserts, mails, audits */ }
    public function chargeMonthly(int $userId): void   { /* loads user, charges Stripe, saves invoice, mails receipt */ }
    public function exportForFinance(int $month): array { /* CSV export of all customers */ }
}
```

### After

```php
final class RegisterCustomer { /* uses: users, mailer, audit */ }
final class ChargeMonthlySubscription { /* uses: users, invoices, stripe, mailer, audit */ }
final class ExportCustomersForFinance { /* uses: users */ }
```

### What the refactor buys

- Each class is named after the **job**, not the noun. Consumers depend on
  the use case they need and nothing more (ISP).
- Billing changes (`ChargeMonthlySubscription`) and registration changes
  (`RegisterCustomer`) no longer share a file, a test, or a deploy
  blast radius (SRP).
- The export class only knows about the user store — the cheapest possible
  dependency footprint.

Files: [`exercise-1-pressure-review/`](exercise-1-pressure-review).

---

## Exercise 2 — open/closed candidate (`vatFor`)

> This pricing chain has grown. Decide whether OCP applies. If yes,
> refactor; if no, write one paragraph explaining why the original is
> still fine.

### Decision

**OCP applies — but in its lightest possible shape.**

Every branch of the original is `(int) round($o->net * <rate>)`. The
variation axis is *one float per region* — there is no per-region
behaviour, only per-region data. A `country => rate` table captures that
exactly. Adding Italy is a one-line edit; nothing inside the calculator
has to change.

### What we deliberately did *not* do

We did **not** introduce a `VatPolicy` interface with a class per region.
That shape is right when at least one region needs genuinely different
behaviour (compounded levies, registration thresholds, partial-rate
items, etc.). Today the registry would just be five classes that each
return `net * rate` — five files paying for ceremony none of them use.

When the first non-flat-rate region arrives, `VatCalculator` is the seam:
that one region gets its own class, the others stay in the table.
**Promote on demand, not on suspicion.**

### Before

```php
function vatFor(Order $o): int
{
    if ($o->country === 'GB') return (int) round($o->net * 0.20);
    if ($o->country === 'IE') return (int) round($o->net * 0.23);
    if ($o->country === 'DE') return (int) round($o->net * 0.19);
    if ($o->country === 'FR') return (int) round($o->net * 0.20);
    if ($o->country === 'ES') return (int) round($o->net * 0.21);
    return 0;
}
```

### After

```php
final class VatCalculator
{
    private const RATES = [
        'GB' => 0.20, 'IE' => 0.23, 'DE' => 0.19, 'FR' => 0.20, 'ES' => 0.21,
    ];

    public function vatFor(Order $order): int
    {
        $rate = self::RATES[$order->country] ?? 0.0;
        return (int) round($order->net * $rate);
    }
}
```

### What the refactor buys

- New regions never touch executable code — they touch a data table.
- The "what does VAT depend on?" question now has a one-screen answer.
- The escape hatch for genuinely-different regions is obvious: stop
  reading `RATES`, start dispatching to a class. No premature interface.

Files: [`exercise-2-open-closed-candidate/`](exercise-2-open-closed-candidate).

---

## Exercise 3 — DIP with restraint (`IssueRefund`)

> Refactor this class so the Stripe dependency goes through an injected
> `PaymentGateway` interface. Then resist the temptation to also extract
> `OrderRepositoryInterface` for the `DB::` call — write one paragraph
> explaining why the bar for *that* extraction is higher.

### Smells

- `IssueRefund` constructs the Stripe client inline, reads its API key
  from `env()`, and hits the network the moment you call `refund()`.
- It also reaches into the `DB::` façade for `orders` and `refunds`. Two
  hidden dependencies, one method.
- There is no way to test this class without either real Stripe
  credentials or monkey-patching the SDK.

### Pressure review

1. **DIP (Stripe).** High-level policy ("issue a refund for this order")
   directly depends on a third-party SDK we cannot exercise in CI.
   **Action:** extract a `PaymentGateway` interface and a
   `StripePaymentGateway` adapter; inject it.
2. **DIP (DB) — *intentionally not yet acted on*.** See the paragraph
   below for why the bar is higher here.
3. SRP / OCP / LSP / ISP do not press hard at this size.

### Before

```php
final class IssueRefund
{
    public function refund(int $orderId, int $amount): void
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_KEY'));
        $stripe->refunds->create([
            'amount' => $amount,
            'charge' => DB::table('orders')->find($orderId)->stripe_id,
        ]);
        DB::table('refunds')->insert(['order_id' => $orderId, 'amount' => $amount]);
    }
}
```

### After

```php
interface PaymentGateway
{
    public function refund(string $chargeId, int $amountInPence): string;
}

final class StripePaymentGateway implements PaymentGateway { /* wraps real \Stripe\StripeClient */ }
final class RecordingPaymentGateway implements PaymentGateway { /* test double */ }

final class IssueRefund
{
    public function __construct(private PaymentGateway $payments) {}

    public function refund(int $orderId, int $amount): void
    {
        $chargeId = DB::table('orders')->find($orderId)->stripe_id;
        $this->payments->refund($chargeId, $amount);
        DB::table('refunds')->insert(['order_id' => $orderId, 'amount' => $amount]);
    }
}
```

### Why we did *not* also extract `OrderRepositoryInterface`

Stripe is a third-party network call that costs money to exercise, cannot
run in CI without sandbox credentials, returns its own object graph, and
might be replaced by a different processor next year. Inverting it
through `PaymentGateway` buys us **testability today** and
**substitutability tomorrow**. The bar is met.

The `orders` and `refunds` tables are ours. The schema was designed by us,
lives in our migrations, and is unlikely to be swapped for a different
storage engine. Wrapping `DB::table('orders')->find($id)` in
`OrderRepositoryInterface` would add a class, an interface, and a test
double — and on the day we look at it, we will be inverting our own
concrete to point at our own concrete. Until the test pain is real, the
indirection is friction without payoff.

The rule of thumb that falls out of this:

> **Extract DIP when the dependency you are inverting is (a) hard to fake
> in tests, (b) owned by someone other than us, or (c) has a credible
> second implementation on the horizon. Stripe scores on all three;
> `DB::table()` scores on none.**

Files: [`exercise-3-dip-with-restraint/`](exercise-3-dip-with-restraint).

---

## What ties Chapter 10 together

The three exercises pull in the same direction:

- **Ex1** says: SOLID is a *diagnosis*, not a checklist. Name the pressures,
  rank them, act on the top one.
- **Ex2** says: when you've named OCP, pick the *lightest* fit that resolves
  the pressure — sometimes a data table beats five classes.
- **Ex3** says: DIP is for dependencies that hurt. Inverting things that
  don't hurt is just paying for indirection.

Decent code is not "code that obeys SOLID". It is code where each SOLID
principle is invoked for a *named* reason, in priority order, and stopped
the moment its pressure stops pushing.
