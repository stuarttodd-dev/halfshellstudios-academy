# Chapter 2 guided practice — naming things well

Reference solutions for the three rename-only refactors in **Naming variables,
functions, classes, and files well → Chapter 2 guided practice**
(`/learn/sections/chapter-naming-things-well/chapter-2-guided-practice`).

The whole exercise is a no-op for behaviour: only names, constants, file
location, and signatures change. A good naming refactor reads like a different
piece of code while running like the same one.

## Exercises

| # | Exercise                                          | Starter | Solution |
| - | ------------------------------------------------- | ------- | -------- |
| 1 | Variable, boolean, and function names             | [`exercise-1-.../starter.php`](exercise-1-variable-boolean-and-function-names/starter.php) | [`exercise-1-.../solution.php`](exercise-1-variable-boolean-and-function-names/solution.php) |
| 2 | Class, namespace, and file location               | [`src/Helpers/OrderMgr.php`](exercise-2-class-namespace-and-file-location/src/Helpers/OrderMgr.php) | [`src/Billing/OrderTotalCalculator.php`](exercise-2-class-namespace-and-file-location/src/Billing/OrderTotalCalculator.php) |
| 3 | Domain language and collection names              | [`exercise-3-.../starter.php`](exercise-3-domain-language-and-collection-names/starter.php) | [`exercise-3-.../solution.php`](exercise-3-domain-language-and-collection-names/solution.php) |

## Exercise 1 — variable, boolean, and function names

**Brief:** Rename every variable, boolean, and function so the names alone tell
the story. Extract the magic number into a named constant and name the unit on
the amount. Keep the business logic, the return shape, and the lack of classes.

**Smells in the starter:**

- Function name `process` says nothing about *what* it processes or *why*.
- Single-letter locals (`$o`, `$c`, `$t`, `$f`, `$e`) force the reader to keep
  a mental key.
- Magic number `1.2` (VAT multiplier) and `100` (promotion threshold) and
  `18` (adult age) are unlabelled.
- The status string `'a'` is a code without a name.
- The unit on the monetary amount is not stated anywhere.
- The trailing `if (...) { return true; } return false;` is a long-winded way
  of saying `return ...;`.

**Before:**

```php
function process(array $o, array $c): bool
{
    $t = $o['amt'] * 1.2;
    $f = $c['vip'] && $c['stat'] === 'a' && $c['age'] >= 18;
    $e = $c['eml'] !== '' && filter_var($c['eml'], FILTER_VALIDATE_EMAIL);

    if ($t > 100 && $f && $e) {
        return true;
    }

    return false;
}
```

**After:**

```php
const VAT_MULTIPLIER             = 1.2;
const PROMOTION_MIN_TOTAL_POUNDS = 100;
const ADULT_AGE_YEARS            = 18;
const CUSTOMER_STATUS_ACTIVE     = 'a';

function isOrderEligibleForPromotion(array $order, array $customer): bool
{
    $orderTotalIncludingVatPounds = $order['amt'] * VAT_MULTIPLIER;

    $isActiveAdultVipCustomer = $customer['vip']
        && $customer['stat'] === CUSTOMER_STATUS_ACTIVE
        && $customer['age'] >= ADULT_AGE_YEARS;

    $emailAddress        = $customer['eml'];
    $hasContactableEmail = $emailAddress !== ''
        && filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false;

    return $orderTotalIncludingVatPounds > PROMOTION_MIN_TOTAL_POUNDS
        && $isActiveAdultVipCustomer
        && $hasContactableEmail;
}
```

A reader can now answer *"what does this function decide?"* from the signature
alone, and *"under what conditions does it return true?"* from the four named
booleans without re-reading any expression.

The cryptic input keys (`amt`, `vip`, `stat`, `age`, `eml`) are deliberately
left alone because they are part of the external contract callers already
depend on — renaming the local variables and the function buys all of the
clarity at none of the change-surface cost.

Run it: `php exercise-1-variable-boolean-and-function-names/solution.php`

## Exercise 2 — class, namespace, and file location

**Brief:** The starter lives at `src/Helpers/OrderMgr.php` in `App\Helpers`.
Rename the class, choose a better namespace and folder, and give every method
a name that matches its real behaviour.

**Smells in the starter:**

- `OrderMgr` — `Mgr` is a placeholder; the class is not a "manager", it is the
  thing that calculates an order's VAT-inclusive total.
- `App\Helpers` — `Helpers` is a junk-drawer namespace. Billing-shaped code
  belongs under a billing-shaped namespace.
- `getOrder()` lies — it also writes a `last_viewed` timestamp. A `get*`
  method should not have side effects.
- `calc()` is a placeholder. It hides what is being calculated and in what
  unit.
- `doStuff()` is the worst kind of placeholder — it tells the reader nothing.
- The magic number `1.2` reappears, unlabelled.

**Before — `src/Helpers/OrderMgr.php`:**

```php
namespace App\Helpers;

final class OrderMgr
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOrder(int $id): array
    {
        $row = $this->db->query("SELECT * FROM orders WHERE id = $id")[0];
        $this->db->update('orders', ['id' => $id, 'last_viewed' => time()]);

        return $row;
    }

    public function calc(array $order): float
    {
        $t = 0;
        foreach ($order['items'] as $i) {
            $t += $i['price'] * $i['qty'];
        }

        return $t * 1.2;
    }

    public function doStuff(array $order): array
    {
        $order['total']     = $this->calc($order);
        $order['processed'] = true;

        return $order;
    }
}
```

**After — `src/Billing/OrderTotalCalculator.php`:**

```php
namespace App\Billing;

final class OrderTotalCalculator
{
    private const VAT_MULTIPLIER = 1.2;

    public function __construct(private $database)
    {
    }

    public function findOrderAndTouchLastViewed(int $orderId): array
    {
        $orderRow = $this->database->query("SELECT * FROM orders WHERE id = $orderId")[0];
        $this->database->update('orders', ['id' => $orderId, 'last_viewed' => time()]);

        return $orderRow;
    }

    public function calculateTotalIncludingVatInPounds(array $order): float
    {
        $subtotalPounds = 0.0;

        foreach ($order['items'] as $item) {
            $subtotalPounds += $item['price'] * $item['qty'];
        }

        return $subtotalPounds * self::VAT_MULTIPLIER;
    }

    public function attachTotalAndMarkProcessed(array $order): array
    {
        $order['total']     = $this->calculateTotalIncludingVatInPounds($order);
        $order['processed'] = true;

        return $order;
    }
}
```

What the rename buys you:

- The folder + namespace + class name now agree: `App\Billing\OrderTotalCalculator`
  in `src/Billing/OrderTotalCalculator.php`.
- `findOrderAndTouchLastViewed()` is honest about the side effect — no caller
  will be surprised that this method writes.
- `calculateTotalIncludingVatInPounds()` says *what* and *what unit*.
- `attachTotalAndMarkProcessed()` describes both things it does to the order.

Honest names also make the next refactor obvious: the two methods that touch
the database and the order lifecycle clearly do not belong in a class called
`OrderTotalCalculator`. The brief is rename-only, so we stop there — but the
mismatch is now visible instead of hidden behind `OrderMgr`.

## Exercise 3 — domain language and collection names

**Brief:** Pick one word for the main person-concept (`customer`, `client`, or
`user`) and use only that word everywhere. Name collections plurally with the
right qualifier and loop variables as the singular of their collection.

**Smells in the starter:**

- Three synonyms for the same thing: `client_name`, `customer_id`, `user_email`.
- Abbreviations that hide the concept: `$txs`, `$tplMap`, `$tpl`, `$p`, `$t`.
- `$out`, `$relevant`, `$data` — collection and bucket names that say nothing
  about *what* they hold.
- Loop variables (`$p`, `$t`) that are not the singular of their collections
  (`$people`, `$txs`).

**Before:**

```php
final class ReportingService
{
    public function build(array $people, array $txs, array $tplMap): array
    {
        $out = [];

        foreach ($people as $p) {
            $data = [
                'client_name' => $p['name'],
                'customer_id' => $p['id'],
                'user_email'  => $p['email'],
            ];

            $relevant = [];
            foreach ($txs as $t) {
                if ($t['buyer_id'] === $p['id']) {
                    $relevant[] = $t;
                }
            }

            $tpl = $tplMap[$p['tier']] ?? $tplMap['default'];

            $data['transactions'] = $relevant;
            $data['template']     = $tpl;

            $out[] = $data;
        }

        return $out;
    }
}
```

**After:**

```php
final class CustomerReportBuilder
{
    public function build(array $customers, array $transactions, array $emailTemplatesByTier): array
    {
        $customerReports = [];

        foreach ($customers as $customer) {
            $customerReport = [
                'customer_name'  => $customer['name'],
                'customer_id'    => $customer['id'],
                'customer_email' => $customer['email'],
            ];

            $customerTransactions = [];
            foreach ($transactions as $transaction) {
                if ($transaction['buyer_id'] === $customer['id']) {
                    $customerTransactions[] = $transaction;
                }
            }

            $emailTemplate = $emailTemplatesByTier[$customer['tier']]
                ?? $emailTemplatesByTier['default'];

            $customerReport['transactions'] = $customerTransactions;
            $customerReport['template']     = $emailTemplate;

            $customerReports[] = $customerReport;
        }

        return $customerReports;
    }
}
```

One word — `customer` — is now used consistently across:

- the class name (`CustomerReportBuilder`),
- the input collection (`$customers`) and its loop variable (`$customer`),
- every key in the output (`customer_name`, `customer_id`, `customer_email`),
- the per-customer working bucket (`$customerReport`),
- the filtered sub-collection (`$customerTransactions`).

`$txs` becomes `$transactions` (with `$transaction` as the loop variable),
`$tplMap` becomes `$emailTemplatesByTier` (a name that says both *what* is in
the map and *what the keys mean*), and `$out` becomes `$customerReports` so
the return value is no longer a mystery.

The `buyer_id` field on transactions is left alone — it is part of the data
shape coming in from another system, so renaming it would change behaviour for
the caller, not just for the reader.

Run it: `php exercise-3-domain-language-and-collection-names/solution.php`

## How to run all three

From this folder:

```bash
php exercise-1-variable-boolean-and-function-names/solution.php
php exercise-3-domain-language-and-collection-names/solution.php
```

Exercise 2 is a class definition with no entry script — load it through your
own autoloader, or run a quick syntax check:

```bash
php -l exercise-2-class-namespace-and-file-location/src/Billing/OrderTotalCalculator.php
```

Each runnable solution prints the same observable output as its starter — the
refactor is a no-op for behaviour and a step-change for readability.

← [Writing decent PHP](../README.md)
