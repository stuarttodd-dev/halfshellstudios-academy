# Chapter 1 guided practice — readable naming

Reference solutions for the three sandbox exercises in **What decent PHP means after fundamentals → Chapter 1 guided practice**
(`/learn/sections/chapter-readable-naming/readable-naming-debugging-narrowing-down-problems-in-this-chapter`).

The lesson asks you to:

1. Flatten nested logic with **early returns / guard clauses**.
2. Remove **hidden dependencies** on `$_ENV` and globals — pass them in explicitly.
3. **Avoid unnecessary abstraction** — replace an over-engineered class hierarchy with a direct solution.

Each solution preserves the original behaviour and optimises for readability and local reasoning.

## Exercises

| # | Exercise                                                         | Starter | Solution |
| - | ---------------------------------------------------------------- | ------- | -------- |
| 1 | Flatten nested logic with early returns                          | [`exercise-1-flatten-nested-logic/starter.php`](exercise-1-flatten-nested-logic/starter.php) | [`exercise-1-flatten-nested-logic/solution.php`](exercise-1-flatten-nested-logic/solution.php) |
| 2 | Remove hidden dependencies (no `$_ENV` / `global`)               | [`exercise-2-remove-hidden-dependencies/starter.php`](exercise-2-remove-hidden-dependencies/starter.php) | [`exercise-2-remove-hidden-dependencies/solution.php`](exercise-2-remove-hidden-dependencies/solution.php) |
| 3 | Avoid unnecessary abstraction (drop the strategy + pipeline)     | [`exercise-3-avoid-unnecessary-abstraction/starter.php`](exercise-3-avoid-unnecessary-abstraction/starter.php) | [`exercise-3-avoid-unnecessary-abstraction/solution.php`](exercise-3-avoid-unnecessary-abstraction/solution.php) |

## Exercise 1 — Flatten nested logic

**Brief:** Refactor `sendReceiptIfNeeded()` to use guard clauses. Keep all existing
outcomes the same for paid/unpaid orders and missing email values.

The starter nests two `if` blocks two levels deep before doing any real work.
The fix is to invert each condition and `return` early, leaving the happy path
flat at the bottom of the function.

```php
function sendReceiptIfNeeded(array $order, array &$sentEmails): void
{
    if (($order['is_paid'] ?? false) !== true) {
        return;
    }

    $email = (string) ($order['customer_email'] ?? '');
    if ($email === '') {
        return;
    }

    $sentEmails[] = $email;
}
```

Why this is "decent PHP":

- The two preconditions are stated up front, each on its own line.
- The single side-effect (`$sentEmails[] = $email`) is the last line — easy to spot.
- No nested braces means there is exactly one scope to reason about.

Run it: `php exercise-1-flatten-nested-logic/solution.php`

## Exercise 2 — Remove hidden dependencies

**Brief:** Refactor `canSendDigest()` so it does not read `$_ENV` or global state
directly. Pass required dependencies as function parameters.

The starter reaches into `$_ENV['MAIL_ENABLED']` and a `global $currentHour`,
which makes the function impossible to test in isolation and easy to break by
accident from anywhere in the codebase. The fix is to make every value the
function needs an explicit parameter.

```php
function canSendDigest(array $user, bool $mailEnabled, int $currentHour): bool
{
    if (! $mailEnabled) {
        return false;
    }

    if (($user['email'] ?? '') === '') {
        return false;
    }

    return $currentHour >= 9;
}

$_ENV['MAIL_ENABLED'] = '1';

$mailEnabled = (($_ENV['MAIL_ENABLED'] ?? '0') === '1');
$currentHour = 9;

var_export(canSendDigest(['email' => 'sam@example.com'], $mailEnabled, $currentHour));
```

Why this is "decent PHP":

- The function signature is now an honest contract: everything it depends on is named.
- Reading `$_ENV` and the clock happens at the **edge** of the program (the entry
  script), not in the middle of business logic.
- It is trivial to unit test — just call it with different argument combinations.

Run it: `php exercise-2-remove-hidden-dependencies/solution.php`

## Exercise 3 — Avoid unnecessary abstraction

**Brief:** Simplify the export flow without changing behaviour: keep only active
users with non-empty email addresses.

The starter introduces a `UserSelectionStrategy` interface, an
`ActiveUserSelectionStrategy` implementation, and a `UserExportPipeline` class —
~30 lines of indirection for what is fundamentally one filter over an array.
There is no current need for multiple selection strategies, so the abstraction
costs more than it earns.

```php
function activeEmails(array $users): array
{
    $emails = [];

    foreach ($users as $user) {
        if (($user['active'] ?? false) !== true) {
            continue;
        }

        $email = (string) ($user['email'] ?? '');
        if ($email === '') {
            continue;
        }

        $emails[] = $email;
    }

    return $emails;
}

var_export(activeEmails([
    ['active' => true,  'email' => 'a@example.com'],
    ['active' => false, 'email' => 'b@example.com'],
    ['active' => true,  'email' => ''],
]));
```

Why this is "decent PHP":

- One small function, one obvious behaviour: filter to active users with an email.
- No interface, no constructor wiring, no second class to read before you can answer
  *"what does this do?"*.
- If a real second strategy ever turns up, you can introduce the abstraction
  **then**, with a concrete reason for it.

Run it: `php exercise-3-avoid-unnecessary-abstraction/solution.php`

## How to run all three

From this folder:

```bash
php exercise-1-flatten-nested-logic/solution.php
php exercise-2-remove-hidden-dependencies/solution.php
php exercise-3-avoid-unnecessary-abstraction/solution.php
```

Each script prints the same observable result as its starter — proof that the
refactor preserved behaviour while improving readability.

← [Writing decent PHP](../README.md)
