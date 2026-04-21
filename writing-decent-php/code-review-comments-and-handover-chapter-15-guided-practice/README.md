# Chapter 15 — Code review, comments, documentation, and handover (guided practice)

Three exercises on the **artefacts around the code**: the comments we
leave inside it, the ADRs we leave beside it, and the PR descriptions
and commit messages we leave behind when we ship it.

The discipline of this chapter is: *treat every written artefact as if
you are writing it for a new joiner six months from now*. If it does
not help them, delete it. If the thing they need is missing, write it.

---

## Exercise 1 — Clean up the comments

A `PriceCalculator` class sprinkled with the three classic comment
failure modes: wrong, redundant, and misplaced. Meanwhile, the one
genuinely surprising line (`sleep(1)`) has no explanation at all.

### Brief

> Take this code and remove or replace any comments that no longer
> earn their place. Where the *why* is missing, add a short comment
> that captures it.

### Before

```php
class PriceCalculator
{
    // VAT is 17.5%
    private const VAT = 1.20;

    // Calculate the total price
    public function total(array $items): int
    {
        $total = 0;
        // Loop through items
        foreach ($items as $item) {
            $total += $item['price'] * $item['qty'];
        }
        // Apply VAT
        $total = (int) round($total * self::VAT);
        // Return the total
        sleep(1);
        return $total;
    }
}
```

### Smells

| Line | Smell | Why it is bad |
| --- | --- | --- |
| `// VAT is 17.5%` above `const VAT = 1.20` | **Wrong / rotted** | The constant says 20% VAT. The comment rotted when the rate changed and nobody updated it. Now the comment and the code contradict — and the comment is winning the first-impression contest. |
| `// Calculate the total price` on a method called `total()` | **Redundant** | Re-reads the method name in English. |
| `// Loop through items` on a `foreach` | **Redundant** | Re-reads the keyword. |
| `// Apply VAT` on `$total = round($total * self::VAT)` | **Redundant** | Re-reads the expression. |
| `// Return the total` above `sleep(1)` | **Misplaced** | The comment and the code it sits above disagree. Whether it migrated or was always wrong, it is noise either way. |
| `sleep(1)` itself | **Missing *why*** | This is the only line a reader would actually want context for, and there is none. |

### After

```php
final class PriceCalculator
{
    private const VAT_MULTIPLIER_INCLUDING_20_PERCENT = 1.20;

    /** @param list<array{price: int, qty: int}> $items */
    public function total(array $items): int
    {
        $totalInPence = 0;
        foreach ($items as $item) {
            $totalInPence += $item['price'] * $item['qty'];
        }
        $totalInPence = (int) round($totalInPence * self::VAT_MULTIPLIER_INCLUDING_20_PERCENT);

        // Legacy upstream pricing service rate-limits callers to 1 req/sec;
        // removing this sleep causes intermittent 429s. See ADR-022 for the
        // plan to replace it with a proper token-bucket client.
        sleep(1);

        return $totalInPence;
    }
}
```

### What the refactor buys

- **No lies.** The rotten "17.5%" comment is gone. The constant's
  name (`VAT_MULTIPLIER_INCLUDING_20_PERCENT`) now carries the
  information the comment was trying to carry, and it cannot rot
  without the code changing too.
- **No noise.** The redundant "loop through items" / "apply VAT" /
  "return the total" comments are gone. The remaining code is now a
  short, linear sentence.
- **One comment that earns its keep.** The `sleep(1)` is the only
  non-obvious line, and it is now the only commented line. The
  comment explains *why*, not *what*, and it points the reader at
  the ADR that documents the plan.
- **Signal restored.** Because there are fewer comments, the one
  that remains is now worth reading. In the starter, the reader's
  eye had already glossed over four noise comments before reaching
  the interesting one.

### How to run

```bash
cd writing-decent-php/code-review-comments-and-handover-chapter-15-guided-practice/exercise-1-clean-up-the-comments
php starter.php
php solution.php

diff <(php starter.php) <(php solution.php)  # identical
```

---

## Exercise 2 — Write an ADR

### Brief

> Pick a real decision from a project you know (or invent one —
> Stripe vs PayPal, repository pattern vs active record, monolith
> vs microservices) and write an ADR for it using the template from
> 15.9.

### The template (from section 15.9)

```markdown
# ADR-NNN — <short, decision-shaped title>

## Status
<Proposed | Accepted | Superseded by ADR-XYZ> (YYYY-MM)

## Context
<The forces in play: what is happening, what constraints exist,
 what already-made decisions this one has to live inside.>

## Decision
<The choice, stated in one or two short sentences.>

## Consequences
<What follows from choosing this — positive, negative, and neutral.>

## Alternatives considered
<The options that were rejected, and a one-line reason each.>
```

### The ADR written for this exercise

See [`exercise-2-write-an-adr/ADR-001-stripe-over-paypal-for-card-payments.md`](./exercise-2-write-an-adr/ADR-001-stripe-over-paypal-for-card-payments.md).

### What the good version buys

- **A dated record.** The reader knows *when* the decision was made,
  so they know what was and was not known at the time. "We picked
  Stripe in April 2026" ages honestly; "we picked Stripe because it
  is the best one" does not.
- **Context before decision.** A reader who disagrees can check
  their disagreement against the original forces. If those forces
  have changed, the decision is revisitable; if they have not, the
  decision stands.
- **Consequences, including the unhappy ones.** The ADR says out
  loud that we are now coupled to Stripe's fee structure, and at
  what volume that coupling becomes a problem. The ADR is doing the
  job a code comment cannot.
- **Alternatives with reasons.** "We considered Adyen and rejected
  it because of integration cost, not technical fit" is exactly the
  sentence that stops the next team re-running the same evaluation.
- **It outlives the people involved.** That is the only rule for
  when to write an ADR: *if the decision is going to outlive the
  people who made it, write it down*.

---

## Exercise 3 — Improve a PR description

### Brief

> You have shipped this PR. Rewrite the title, description, and
> commit message to follow the chapter's guidance.
>
>     Title:       fix
>     Description: (empty)
>     Commit:      fix bug
>     Diff:        a 3-file change that adds idempotency keys to
>                  order creation

### The starter PR

See [`exercise-3-improve-a-pr-description/BEFORE.md`](./exercise-3-improve-a-pr-description/BEFORE.md)
for the original and an annotated list of every failure mode it
demonstrates.

### The rewrite

See [`exercise-3-improve-a-pr-description/AFTER.md`](./exercise-3-improve-a-pr-description/AFTER.md)
for the rewritten title, description, and commit message, with the
rules applied called out alongside each one.

### The shape of a decent PR description

A reviewer should be able to read the description, skim the diff,
and review the PR in one pass — not reverse-engineer the intent from
the code and hope. That means the description has to answer:

1. **Why** is this needed? (problem statement, incident/ticket link)
2. **What** changes observably? (behaviour, not implementation)
3. **How** is it structured? (ports / adapters / new classes, so the
   reviewer has a mental model before they read the diff)
4. **How was it tested?** (unit / feature / manual / staging)
5. **What is the risk, and how is it contained?** (feature flag,
   rollback plan, transactional boundaries)
6. **What is *out of scope*?** (so reviewers stop asking about
   things you deliberately did not do)
7. **Links.** (ticket, incident, ADR, design doc)

A commit message that lands on `main` needs the same treatment
compressed into three blocks:

- **Subject line**, imperative, ≤72 chars.
- **Body paragraph** explaining *why* and *what*, wrapped at ~72.
- **Footer** with `Closes TICKET`, `Mitigates INC-NNN`, and ADR
  references.

### What the good version buys

- **The reviewer can say yes faster.** They have a `Why` and a
  `How` to check the diff against, so the review is a validation
  exercise, not a discovery exercise.
- **The future archaeologist is not lost.** `git log` shows an
  intention-revealing subject line; `git show` shows the reasoning;
  `git blame` on a specific line drops the reader into a commit
  that already tells the whole story.
- **The next incident is shorter.** If idempotency breaks six
  months from now, the on-call engineer finds this commit and has
  ADR-019, INC-412, CHECK-812, and the design doc in one place.

---

## The unifying idea

Code, comments, ADRs, PR descriptions, and commit messages are all
the same medium: **written explanations for a future reader**. The
rules are the same in all five:

- Delete anything that repeats what is obvious.
- Keep anything that captures *why*.
- Write it so a new joiner, six months from now, can pick it up
  cold and still understand what was decided and why.

Comments that fail this test are noise. PR descriptions that fail
this test cost the reviewer an hour each. ADRs that fail this test
get re-debated next quarter. Getting all five right is how a
codebase stays handover-friendly as the team changes around it.
