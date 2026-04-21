# Chapter 4 guided practice — reducing nesting

Reference solutions for the three structural refactors in **Reducing nesting
and making code easier to scan → Chapter 4 guided practice**
(`/learn/sections/chapter-reducing-nesting/chapter-4-guided-practice`).

Every refactor is a pure restructure: same input → same return value → same
side effects. Only the *shape* of the control flow changes.

## Exercises

| # | Exercise                                          | Starter | Solution |
| - | ------------------------------------------------- | ------- | -------- |
| 1 | Flatten an arrow-shaped function                  | [`exercise-1-.../starter.php`](exercise-1-flatten-an-arrow-shaped-function/starter.php) | [`exercise-1-.../solution.php`](exercise-1-flatten-an-arrow-shaped-function/solution.php) |
| 2 | Turn an `if`/`elseif` chain into the right shape  | [`exercise-2-.../starter.php`](exercise-2-if-elseif-into-match/starter.php) | [`exercise-2-.../solution.php`](exercise-2-if-elseif-into-match/solution.php) |
| 3 | Keep the happy path visible as a function grows   | [`exercise-3-.../starter.php`](exercise-3-keep-the-happy-path-visible/starter.php) | [`exercise-3-.../solution.php`](exercise-3-keep-the-happy-path-visible/solution.php) |

## Exercise 1 — flatten an arrow-shaped function

**Brief:** `billingAddressLineFor()` is the textbook pyramid: five conditions
nested five levels deep, the actual work hidden at the bottom, and the same
`return null` repeated at every layer. Flatten it with guard clauses so the
work lives at the leftmost indentation level.

**Smells in the starter:**

- Pyramid indentation; the real work is six tabs in.
- Five nested `if`s wrapping one piece of business logic.
- Four near-identical `return null` paths — duplicated outcomes that drift
  the moment one of them needs to change.
- Repeated long property paths (`$customer->billingAddress->...`) make the
  reader hold a mental address.

**Before:**

```php
function billingAddressLineFor(?Customer $customer): ?string
{
    if ($customer !== null) {
        if ($customer->isActive()) {
            if ($customer->billingAddress !== null) {
                if ($customer->billingAddress->line1 !== '') {
                    if ($customer->billingAddress->postcode !== '') {
                        return strtoupper(trim($customer->billingAddress->line1))
                            . ', '
                            . strtoupper(trim($customer->billingAddress->postcode));
                    }

                    return null;
                }

                return null;
            }

            return null;
        }

        return null;
    }

    return null;
}
```

**After:**

```php
function billingAddressLineFor(?Customer $customer): ?string
{
    if ($customer === null) {
        return null;
    }

    if (! $customer->isActive()) {
        return null;
    }

    $billingAddress = $customer->billingAddress;
    if ($billingAddress === null) {
        return null;
    }

    if ($billingAddress->line1 === '' || $billingAddress->postcode === '') {
        return null;
    }

    return strtoupper(trim($billingAddress->line1))
        . ', '
        . strtoupper(trim($billingAddress->postcode));
}
```

What the refactor buys:

- **The preconditions read as a checklist at the top.** Each guard says
  "we shouldn't be here" and bails. The body that runs *after* every guard
  is the happy path.
- **Four `return null` paths collapse into three.** Two equivalent string
  emptiness checks merge into one combined guard with `||` — they share a
  reason ("the address is incomplete") and a fix.
- **One named local `$billingAddress`** removes three repetitions of the
  long property path and makes the body easier to scan.
- **Indentation goes from five levels to one** — the eye no longer has to
  track which `}` closes which `if`.

Run it: `php exercise-1-flatten-an-arrow-shaped-function/solution.php` — the
six scenarios print identical results to the starter.

## Exercise 2 — turn an `if`/`elseif` chain into the right shape

**Brief:** `ShippingService::rateFor()` dispatches on `$carrier` through a
nested `if`/`elseif` chain. The `royal_mail` arm has its own weight-band
logic. Turn the dispatch into a `match` and pull the weight-band logic out
into its own private helper.

**Smells in the starter:**

- An `if`/`elseif`/`else` chain whose only job is to map one of five strings
  to an outcome — `match` is the precise tool for that.
- The `royal_mail` arm has *its own* nested `if`/`elseif`/`else` for weight
  bands, so the dispatch table is hidden behind a sub-table.
- A four-level deep `else { return 1200; }` that means *"non-GB Royal Mail"*
  reads worse than a guard would.
- The `else throw` branch is harder to spot than `default => throw` would be.

**Before:**

```php
public function rateFor(string $carrier, int $weightInGrams, string $countryCode): int
{
    if ($carrier === 'royal_mail') {
        if ($countryCode === 'GB') {
            if ($weightInGrams <= 100) {
                return 165;
            } elseif ($weightInGrams <= 250) {
                return 230;
            } elseif ($weightInGrams <= 1000) {
                return 360;
            } else {
                return 750;
            }
        } else {
            return 1200;
        }
    } elseif ($carrier === 'dpd') {
        return $countryCode === 'GB' ? 800 : 1800;
    } elseif ($carrier === 'fedex') {
        return $countryCode === 'GB' ? 1500 : 2500;
    } elseif ($carrier === 'click_and_collect') {
        return 0;
    } else {
        throw new UnknownCarrierException($carrier);
    }
}
```

**After:**

```php
public function rateFor(string $carrier, int $weightInGrams, string $countryCode): int
{
    return match ($carrier) {
        'royal_mail'        => $this->royalMailRateFor($weightInGrams, $countryCode),
        'dpd'               => $countryCode === 'GB' ? 800  : 1800,
        'fedex'             => $countryCode === 'GB' ? 1500 : 2500,
        'click_and_collect' => 0,
        default             => throw new UnknownCarrierException($carrier),
    };
}

private function royalMailRateFor(int $weightInGrams, string $countryCode): int
{
    if ($countryCode !== 'GB') {
        return 1200;
    }

    return match (true) {
        $weightInGrams <= 100  => 165,
        $weightInGrams <= 250  => 230,
        $weightInGrams <= 1000 => 360,
        default                => 750,
    };
}
```

What the refactor buys:

- **`rateFor()` reads as a dispatch table.** Five lines, one per carrier — a
  reviewer can answer *"what does each carrier cost?"* without parsing
  control flow.
- **`royal_mail`'s extra logic earns its own method.** The carrier with
  enough work to need its own table gets one; everyone else stays one line.
- **`match (true)`** turns the weight-band ladder into an ordered table
  rather than an `if`/`elseif` cascade — the bands and their rates line up
  visually.
- **`default => throw`** keeps the unknown-carrier path inside the dispatch
  expression instead of dangling in a final `else`.

Run it: `php exercise-2-if-elseif-into-match/solution.php` — every rate and
the thrown exception match the starter.

## Exercise 3 — keep the happy path visible as a function grows

**Brief:** `notifyOnThreadComment()` is already five levels deep. A teammate
is about to add two more preconditions to it ("skip if the user has muted
this thread" and "skip if we already notified this user about this comment in
the last hour"). Refactor the existing body so each new precondition is a
single-line, no-extra-nesting change.

**Smells in the starter:**

- Five nested `if`s wrapping the only piece of work the function actually
  does.
- The mailer and audit calls are buried at indentation level 7.
- Adding a sixth precondition would push the work to level 8 and turn the
  diff into the cliff-edge of the screen.
- The test case "author is the reader" is expressed as `! is(...)` *inside*
  the deepest level — easy to miss.

**Before:**

```php
public function notifyOnThreadComment(?User $user, Thread $thread, Comment $comment): void
{
    if ($user !== null) {
        if ($user->isActive()) {
            if ($user->emailVerified()) {
                if ($thread->isOpen()) {
                    if (! $comment->author->is($user)) {
                        $this->mailer->send(/* ... */);
                        $this->audit->record(/* ... */);
                    }
                }
            }
        }
    }
}
```

**After:**

```php
public function notifyOnThreadComment(?User $user, Thread $thread, Comment $comment): void
{
    if ($user === null)              { return; }
    if (! $user->isActive())         { return; }
    if (! $user->emailVerified())    { return; }
    if (! $thread->isOpen())         { return; }
    if ($comment->author->is($user)) { return; }

    // Future preconditions land here as one-liners, e.g.:
    // if ($user->hasMuted($thread))                            { return; }
    // if ($this->recentlyNotified($user, $comment, '1 hour'))  { return; }

    $this->mailer->send(
        $user->email,
        'New comment on ' . $thread->title,
        $this->renderer->renderCommentEmail($thread, $comment),
    );

    $this->audit->record('notification_sent', [
        'user'    => $user->id,
        'thread'  => $thread->id,
        'comment' => $comment->id,
    ]);
}
```

What the refactor buys:

- **Five preconditions are now five aligned guards** at the top of the
  function — the reviewer reads the rule list, then the body.
- **Adding the next two checks is genuinely a one-line change each.** No
  extra nesting, no diff churn around braces.
- **The "author is the reader" check flips from a wrapper to a guard,**
  which matches how a human says it: *"don't notify someone about their own
  comment"*.
- **The mailer and audit calls live at the leftmost level**, so the actual
  side effect of the function is what you see when you scroll to the bottom.

Run it: `php exercise-3-keep-the-happy-path-visible/solution.php` — every
scenario emits the same number of mails and audit records as the starter.

## How to run all three

From this folder:

```bash
php exercise-1-flatten-an-arrow-shaped-function/solution.php
php exercise-2-if-elseif-into-match/solution.php
php exercise-3-keep-the-happy-path-visible/solution.php
```

Each script prints the same observable output as its starter — proof the
restructures changed the shape of the code without changing what it does.

← [Writing decent PHP](../README.md)
