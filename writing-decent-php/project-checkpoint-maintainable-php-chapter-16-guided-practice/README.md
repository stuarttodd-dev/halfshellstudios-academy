# Chapter 16 — Project checkpoint: maintainable PHP (guided practice)

Three exercises operating on the **same starter file** — an end-of-month
`InvoiceGenerator` — walked through the full maintainability pipeline
this course has built up to:

1. **Pin behaviour** with a characterisation test.
2. **Name the rules** hiding inside the procedural soup.
3. **Collaborate, don't query** — push all I/O to the edges.

The three exercises are deliberately cumulative: each folder starts
from where the previous one ended, and the same fixture is used in all
three. Running all three in order is a compressed, honest walk-through
of the entire book applied to one messy class.

---

## The starter (identical in all three exercises)

```php
class InvoiceGenerator
{
    public function generate()
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $clients = DB::select('SELECT * FROM clients WHERE active = 1');

        $generated = [];
        foreach ($clients as $client) {
            $sessions = DB::select('SELECT * FROM sessions WHERE client_id = ? AND date BETWEEN ? AND ?', [$client->id, $start, $end]);
            if (count($sessions) === 0) continue;

            $hours = 0;
            foreach ($sessions as $s) $hours += $s->hours;
            $rate = $client->plan === 'premium' ? 150 : 100;
            $sub = $hours * $rate;
            $vat = $client->country === 'GB' ? round($sub * 0.20) : 0;
            $tot = $sub + $vat;

            $invoiceId = DB::insert('INSERT INTO invoices (client_id, total, period) VALUES (?, ?, ?)', [$client->id, $tot, date('Y-m')]);

            $body = "Invoice #$invoiceId\nClient: {$client->name}\nHours: $hours\nTotal: $tot";
            $pdf = "/tmp/invoice-$invoiceId.pdf";
            file_put_contents($pdf, $body);

            mail($client->email, "Invoice for " . date('F Y'), "Please find your invoice attached.");

            $generated[] = $invoiceId;
        }

        return $generated;
    }
}
```

### Smells, roughly in order of severity

| # | Smell | Why it is bad |
| --- | --- | --- |
| 1 | **Static `DB::` calls** | Hidden global dependency. Untestable without a real database, and invisible to the constructor signature. |
| 2 | **Global `mail()`** | Hidden I/O. No way to assert it was called, no way to replace it with Mailgun/SES, no way to stop it firing in tests. |
| 3 | **Global `file_put_contents` to `/tmp`** | Hidden filesystem dependency; the path is hardcoded; there is no way to capture what got written in a test. |
| 4 | **`date()` called four times** | Wall-clock dependency. Every run is a different month, which means the characterisation test has to hedge. |
| 5 | **Magic numbers and strings** | `150`, `100`, `0.20`, `'premium'`, `'GB'` have no names. They are the policies most likely to change, and they are the least findable. |
| 6 | **One method doing four jobs** | Compute rate → compute VAT → persist invoice → write PDF → send mail. Five responsibilities, one method, no boundaries. |
| 7 | **Return type `array`, parameter list empty** | The method signature carries no information. |
| 8 | **`function generate()` with no type hints** | Weak typing on a public entry point. |

The refactor addresses smells 5–8 in Exercise 2, and smells 1–4 in
Exercise 3. We deliberately do the "name the rules" step *before* the
"pull the collaborators out" step — the rules are cheap to extract and
their tests are cheaper to write, so we get them out of the way first.

---

## Exercise 1 — pin behaviour

### Brief

> Write a characterisation test for one realistic scenario (premium
> GB client with 10 hours, basic IE client with 5 hours).

### Files

- [`exercise-1-pin-behaviour/subject.php`](./exercise-1-pin-behaviour/subject.php) — the starter verbatim, plus an in-memory `DB` stub (the minimum scaffolding to let the class run at all).
- [`exercise-1-pin-behaviour/characterisation_test.php`](./exercise-1-pin-behaviour/characterisation_test.php) — the test.

### What we actually pin

For each scenario:

1. The array of invoice ids returned from `generate()`.
2. The rows written to the in-memory `invoices` table
   (`client_id`, `total`, `period`).
3. The body written to the `/tmp/invoice-$id.pdf` file on disk.

### What we explicitly cannot pin yet

- **`mail()` was called with what arguments?** The starter calls the
  global `mail()` directly and we cannot intercept it without a
  PHP extension. The test neuters it with
  `ini_set('sendmail_path', '/bin/true')` so it no-ops, and we
  accept that the mail side-effect is **invisible to us** until
  Exercise 3 replaces it with an `InvoiceNotifier` collaborator.

That caveat is not a flaw in the test — it is the test telling us a
real truth about the starter's design. You cannot cleanly
characterise a unit that reaches out to `mail()`. Exercise 3 is what
buys us that testability.

### Fixture used in all three exercises

| Client | Plan | Country | Active | Sessions this month | Expected invoice |
| --- | --- | --- | --- | --- | --- |
| Alice Ltd  | premium | GB | yes | 6h + 4h = 10h | 10 × £150 = 1500 subtotal, 20% GB VAT = 300, **total 1800** |
| Bob GmbH   | basic   | IE | yes | 5h            | 5 × £100 = 500 subtotal, no VAT, **total 500** |
| Eve & Co   | premium | GB | yes | *(none)*      | **no invoice** — guard clause skips empty sessions |
| Charlie Ltd| basic   | GB | no  | *(any)*       | **no invoice** — not returned by `activeClients()` |

### How to run

```bash
cd writing-decent-php/project-checkpoint-maintainable-php-chapter-16-guided-practice/exercise-1-pin-behaviour
php characterisation_test.php
```

---

## Exercise 2 — name the rules

### Brief

> Extract the rate (`$client->plan === 'premium' ? 150 : 100`) and the
> VAT (`$client->country === 'GB' ? ... : 0`) into named rule classes
> with constants.

### Before

```php
$rate = $client->plan === 'premium' ? 150 : 100;
$sub  = $hours * $rate;
$vat  = $client->country === 'GB' ? round($sub * 0.20) : 0;
$tot  = $sub + $vat;
```

### After

```php
$sub = $hours * $this->hourlyRate->forClient($client);
$vat = $this->vatRule->amountFor($sub, $client);
$tot = $sub + $vat;
```

With the rules themselves living alongside their names:

```php
final class HourlyRate
{
    public const PREMIUM_PLAN        = 'premium';
    public const PREMIUM_RATE_PENCE  = 150;
    public const STANDARD_RATE_PENCE = 100;

    public function forClient(object $client): int
    {
        return $client->plan === self::PREMIUM_PLAN
            ? self::PREMIUM_RATE_PENCE
            : self::STANDARD_RATE_PENCE;
    }
}

final class VatRule
{
    public const VAT_COUNTRY         = 'GB';
    public const GB_VAT_RATE         = 0.20;
    public const NO_VAT_AMOUNT_PENCE = 0;

    public function amountFor(int $subtotal, object $client): int
    {
        if ($client->country !== self::VAT_COUNTRY) {
            return self::NO_VAT_AMOUNT_PENCE;
        }
        return (int) round($subtotal * self::GB_VAT_RATE);
    }
}
```

### What the refactor buys

- **Findable policy.** A change to the standard rate is one file and
  one constant, not a `grep` for the literal `100`.
- **No more magic values in `generate()`.** The generator reads as a
  sequence of named business steps, not as arithmetic on mystery
  numbers.
- **Two small things that are independently testable.** `VatRule` can
  grow a table of per-country rates later (Chapter 10's OCP lesson)
  without touching `InvoiceGenerator`.
- **The characterisation test from Exercise 1 passes unchanged.**
  That is the whole point: we renamed, we did not rewrite.

### How to run

```bash
cd writing-decent-php/project-checkpoint-maintainable-php-chapter-16-guided-practice/exercise-2-name-the-rules
php characterisation_test.php
```

---

## Exercise 3 — collaborate, don't query

### Brief

> Replace the static `DB::` calls with `ClientRepository`,
> `SessionRepository`, `InvoiceRepository` interfaces. Replace
> `mail()` and `file_put_contents` with `InvoiceStore` and
> `InvoiceNotifier` interfaces. Compose `InvoiceGenerator` from
> these collaborators.

### Before (Exercise 2)

```php
$clients = DB::select('SELECT * FROM clients WHERE active = 1');
// ...
$invoiceId = DB::insert('INSERT INTO invoices ...', [$client->id, $tot, date('Y-m')]);
file_put_contents("/tmp/invoice-$invoiceId.pdf", $body);
mail($client->email, "Invoice for " . date('F Y'), "Please find your invoice attached.");
```

### After

```php
final class InvoiceGenerator
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly SessionRepository $sessions,
        private readonly InvoiceRepository $invoices,
        private readonly InvoiceStore $store,
        private readonly InvoiceNotifier $notifier,
        private readonly HourlyRate $hourlyRate = new HourlyRate(),
        private readonly VatRule $vatRule = new VatRule(),
    ) {}

    /** @return list<int> */
    public function generate(BillingPeriod $period): array
    {
        $generated = [];
        foreach ($this->clients->activeClients() as $client) {
            $sessions = $this->sessions->forClientBetween($client->id, $period->start, $period->end);
            if ($sessions === []) continue;

            $hours    = $this->totalHours($sessions);
            $subtotal = $hours * $this->hourlyRate->forClient($client);
            $total    = $subtotal + $this->vatRule->amountFor($subtotal, $client);

            $invoiceId = $this->invoices->create($client->id, $total, $period->yearMonth);

            $this->store->store($invoiceId, $this->body($invoiceId, $client, $hours, $total));
            $this->notifier->notify($client, $invoiceId, $period->label);

            $generated[] = $invoiceId;
        }
        return $generated;
    }
    // ...
}
```

Plus five small ports (`ClientRepository`, `SessionRepository`,
`InvoiceRepository`, `InvoiceStore`, `InvoiceNotifier`), a `Clock`,
a `BillingPeriod` value object that owns the four `date()` calls, and
in-memory adapters for each port so the unit test can verify every
side-effect deterministically.

### What the refactor buys

- **No hidden dependencies.** Every collaborator is in the
  constructor signature. A reader can list the class's dependencies
  without reading the body.
- **No global I/O.** The generator no longer calls `DB::`, `mail()`,
  `file_put_contents`, or `date()`. It is now a pure *orchestration*
  class.
- **Observable side-effects.** The Exercise 1 test had to give up on
  verifying mail. The Exercise 3 test asserts the exact arguments to
  `InvoiceNotifier::notify()` — email, invoice id, period label.
- **Fast, deterministic tests.** No filesystem, no SMTP, no DB, no
  wall clock. The unit test in `unit_test.php` runs in milliseconds.
- **Adapter-level change friendliness.** Swapping Postgres for MySQL
  is a new `MysqlClientRepository` class. Swapping `mail()` for
  Mailgun is a new `MailgunInvoiceNotifier` class. `InvoiceGenerator`
  does not change.

### How to run

```bash
cd writing-decent-php/project-checkpoint-maintainable-php-chapter-16-guided-practice/exercise-3-collaborate-dont-query
php unit_test.php
```

---

## The whole-course checkpoint

Each exercise is a compressed application of earlier chapters:

| Step | Chapter(s) the step puts into practice |
| --- | --- |
| Exercise 1 — pin behaviour | **14** (refactor safely behind a characterisation test) |
| Exercise 2 — name the rules | **1–2** (readable naming), **3–4** (functions that do one job, less nesting), **5–6** (data shaping, boundaries), **10.2** (OCP candidates) |
| Exercise 3 — collaborate, don't query | **9** (change-friendly code), **10.3** (DIP), **11** (SRP), **12** (dependency injection), **13** (composition over inheritance) |

By the end of Exercise 3, the `InvoiceGenerator` from the brief has
become: a policy class that orchestrates five ports, applies two
named rules, and returns a list of ids. It reads top-to-bottom,
type-checks under `declare(strict_types=1)`, has no hidden
dependencies, and is fully unit-testable without a database, a mail
server, a filesystem, or a clock.

That is what "maintainable PHP" looks like on a single class. Apply
the same three steps — pin, name, collaborate — to the next rough
file you inherit at work.
