# Chapter 14 — Refactoring a messy feature step by step (guided practice)

> Three exercises that operate on the **same** function, in three
> deliberately small steps: pin its behaviour with a characterisation
> test; rename for intention; extract one collaborator. The point is
> not the destination — it is the **rhythm**.

For each step: make the change in a single small commit, confirm the
characterisation test still passes, then compare with the solution and
notice where decisions differ — especially around naming and where to
draw the seam.

Run with PHP 8.2+ (no Composer required):

```bash
php exercise-1-pin-behaviour-first/characterisation_test.php
php exercise-2-rename-first/characterisation_test.php
php exercise-3-extract-one-collaborator/characterisation_test.php
php exercise-3-extract-one-collaborator/unit_test.php
```

The same five-case characterisation test passes byte-for-byte against
all three versions of `subject.php`. Exercise 3 also adds a dedicated
`unit_test.php` for the extracted collaborator.

### The function we are refactoring

```php
function generateInvoice(array $order): string
{
    $total = 0;
    foreach ($order['items'] as $i) $total += $i['price'] * $i['qty'];
    if ($order['country'] === 'GB') $total *= 1.2;
    if (!empty($order['discount'])) $total *= 0.9;
    $lines = ["Invoice #" . $order['id']];
    foreach ($order['items'] as $i) $lines[] = "{$i['name']}: {$i['price']} x {$i['qty']}";
    $lines[] = "Total: $total";
    return implode("\n", $lines);
}
```

Smells you can already name without changing a line: one-letter
variables, single-line `if`/`foreach` bodies, magic `1.2` and `0.9`,
arithmetic + business rules + formatting all jammed together, returns
a string so the only seam is "the whole rendered text".

The sequence below treats those smells in the order that produces the
fewest opportunities to break things.

---

## Exercise 1 — pin behaviour first

> Write **one** characterisation test before changing anything.

### What "one characterisation test" means here

We interpret the brief as one test *function* that pins the function's
observable output for a representative slice of inputs — not "one test
case", which would miss whole branches (GB tax, discount, empty items).

The five cases in `characterisation_cases()`:

| Case | What it pins |
|------|-------------|
| plain non-GB, no discount | the no-rule baseline |
| GB applies 20% VAT | the GB branch in isolation |
| GB + discount stack (VAT first, then 10% off) | the *order* of the two rules |
| empty items still renders header + total | the empty-collection edge case |
| non-GB + discount applies discount only | the discount branch in isolation |

The expected strings were captured by running `generateInvoice()` once
against the original code and copying the output verbatim. **We are
not asserting that this output is correct** — we are asserting that it
does not change while we refactor. That distinction is the whole point.

### What we deliberately did NOT do

- Did not rename anything (Exercise 2's job).
- Did not extract anything (Exercise 3's job).
- Did not "fix" the magic numbers, the missing types, the float
  imprecision, the bug-or-feature ordering of VAT vs discount, or the
  fact that an empty `$order['discount']` is falsy. **The test pins
  whatever is there.**

### Run

```bash
php exercise-1-pin-behaviour-first/characterisation_test.php
# characterisation test: PASS (5/5 cases pinned)
```

Files: [`exercise-1-pin-behaviour-first/`](exercise-1-pin-behaviour-first).

---

## Exercise 2 — rename first

> Refactor the same function to use intention-revealing names. Do not
> change structure or behaviour. The characterisation test must still
> pass.

### Renames applied

| Was | Now | Why |
|-----|-----|-----|
| `$total` | `$runningTotal` | it is mutated in place — the name now says so |
| `$i` | `$item` | one-letter loop variable → named loop variable |
| `$lines` | `$invoiceLines` | which kind of "lines" do we mean? |
| `1.2` | `const GB_VAT_MULTIPLIER` | declares the policy out loud |
| `0.9` | `const DISCOUNT_MULTIPLIER` | same |

### What deliberately did NOT change

- The structure: still one function, still two `foreach` passes, still
  two `if`s.
- The order of operations: VAT first, discount second. **Bug or
  feature, we are pinning it.** Restructuring waits.
- The function signature.
- The `Total: $runningTotal` interpolation literal — changing it
  would alter observable output.

### The contract of a rename step

> If the characterisation test needs even one assertion updated after
> a rename, the rename moved semantics — that is a bug, not a refactor.

Our `characterisation_test.php` is byte-identical to Exercise 1's. It
still passes against the renamed `subject.php`.

### Run

```bash
php exercise-2-rename-first/characterisation_test.php
# characterisation test: PASS (5/5 cases pinned, post-rename)
```

Files: [`exercise-2-rename-first/`](exercise-2-rename-first).

---

## Exercise 3 — extract one collaborator

> Pick **one** concern (totals or formatting) and extract it into its
> own class. Wire it from the original function. The characterisation
> test must still pass.

### Why we extract totals (and not formatting)

| Candidate | Pro | Con |
|-----------|-----|-----|
| **Totals** | Where the real policy lives (line subtotals + VAT + discount). Highest payoff in testability. Currently *interleaved* with the formatting loop, so untangling makes both halves clearer. | Requires deciding the calculator's interface. |
| Formatting | Separates output from work. | Mostly literal strings — small payoff today, easy second step once totals are out. |

We pick **totals**. After this step, formatting is the obvious next
candidate, but that is a *separate* small step — **the discipline of
this chapter is one move at a time**.

### What we deliberately did NOT do (still)

- Did not introduce a `Money` value object. (Separate refactor — see
  the float-imprecision note below.)
- Did not replace the `if`-cascade with a `VatPolicy` strategy.
  (That is Chapter 9 / Chapter 10 territory; not this step.)
- Did not extract a `InvoiceRenderer`. (Obvious next step; not this
  step.)

### Before (post-rename `generateInvoice` from Ex2)

```php
function generateInvoice(array $order): string
{
    $runningTotal = 0;
    foreach ($order['items'] as $item) {
        $runningTotal += $item['price'] * $item['qty'];
    }
    if ($order['country'] === 'GB')   { $runningTotal *= GB_VAT_MULTIPLIER; }
    if (! empty($order['discount']))  { $runningTotal *= DISCOUNT_MULTIPLIER; }

    $invoiceLines = ["Invoice #" . $order['id']];
    foreach ($order['items'] as $item) {
        $invoiceLines[] = "{$item['name']}: {$item['price']} x {$item['qty']}";
    }
    $invoiceLines[] = "Total: $runningTotal";

    return implode("\n", $invoiceLines);
}
```

### After

```php
final class InvoiceTotalsCalculator
{
    public function totalFor(array $order): int|float
    {
        $runningTotal = 0;
        foreach ($order['items'] as $item) {
            $runningTotal += $item['price'] * $item['qty'];
        }
        if ($order['country'] === 'GB')  { $runningTotal *= GB_VAT_MULTIPLIER; }
        if (! empty($order['discount'])) { $runningTotal *= DISCOUNT_MULTIPLIER; }
        return $runningTotal;
    }
}

function generateInvoice(array $order, ?InvoiceTotalsCalculator $totals = null): string
{
    $totals ??= new InvoiceTotalsCalculator();

    $invoiceLines = ["Invoice #" . $order['id']];
    foreach ($order['items'] as $item) {
        $invoiceLines[] = "{$item['name']}: {$item['price']} x {$item['qty']}";
    }
    $invoiceLines[] = "Total: " . $totals->totalFor($order);

    return implode("\n", $invoiceLines);
}
```

The `?InvoiceTotalsCalculator $totals = null` default keeps every
existing caller working without modification — they keep calling
`generateInvoice($order)` and a calculator is constructed on demand.
The seam is *available* to tests and to the composition root, but not
*forced* on callers.

### The bonus you earn by extracting a collaborator

`unit_test.php` exercises `InvoiceTotalsCalculator` directly. It tests
the rules — "GB applies 20% VAT", "discount alone applies 10% off",
"VAT then discount stack to 11.88" — without parsing rendered invoice
text. **That is what the seam buys you**: a unit test that names the
policy, instead of an end-to-end test that implies it.

### A real bug surfaced by the extraction

The unit test exposes something the characterisation test silently
hides. `6 * 1.2` evaluates to `7.199999999999999` in IEEE 754, but
PHP's default float-to-string conversion rounds it to `"7.2"` — so the
characterisation test (which compares strings) sees `"Total: 7.2"`
and is happy. The unit test compares numbers, sees `7.199999999999999`,
and would fail unless we either tolerate a delta or store money as
integer pence.

The unit test uses a small numeric tolerance and explicitly comments
that **"introduce a `Money` value object that stores pence as `int`"
is the obvious next refactor.** That is the rhythm again: surface the
problem in this step, fix it in the next.

### Run

```bash
php exercise-3-extract-one-collaborator/characterisation_test.php
# characterisation test: PASS (5/5 cases pinned, post-extract)

php exercise-3-extract-one-collaborator/unit_test.php
# unit test: PASS (5/5 totals rules)
```

Files: [`exercise-3-extract-one-collaborator/`](exercise-3-extract-one-collaborator).

---

## What ties Chapter 14 together

The three exercises are the same loop applied three times:

1. **Pin** — capture the current behaviour as a test that you trust.
2. **Move one thing** — rename, extract, restructure. *One* thing.
3. **Re-run the pin** — if it still passes, commit; if not, the move
   was wrong and you revert cheaply.

Refactoring "messy code safely" is not a vibe — it is this loop, run
on a tight cadence, with the discipline to do **only the next step**
even when you can see five further smells. Each step earns the next
step the right to exist by leaving the codebase in a green state.

The natural sequel from where Exercise 3 left off:

> Extract `InvoiceRenderer` (separate small commit).
> Then introduce `Money` (separate small commit).
> Then turn the VAT branch into a `VatPolicy` strategy (separate
> small commit).
>
> Each of those steps starts the same way: re-run the characterisation
> test. End the same way: re-run the characterisation test.
