<?php
declare(strict_types=1);

/**
 * Step 2 of the refactor: rename only.
 *
 * What changed:
 *   - `$total`     -> `$runningTotal`              (it is mutated in place — the name now says so)
 *   - `$i`         -> `$item`                       (one-letter loop var ⇒ named loop var)
 *   - `$lines`     -> `$invoiceLines`               (says what kind of "lines" we mean)
 *   - magic `1.2`  -> `const GB_VAT_MULTIPLIER`     (declares the policy out loud)
 *   - magic `0.9`  -> `const DISCOUNT_MULTIPLIER`   (ditto)
 *   - the `Total: ...` literal stays as-is to match observable output
 *
 * What deliberately did NOT change:
 *   - the structure of the code (one function, two foreach passes, two ifs)
 *   - the order of operations (VAT first, discount second — bug or feature, we are
 *     pinning it; restructuring waits for Exercise 3)
 *   - the function signature (`array $order` -> `string`)
 *
 * The characterisation test from Exercise 1 still passes byte-for-byte
 * (see `characterisation_test.php`). That is the rule for a rename
 * step: you should not have to update a single test assertion.
 */

const GB_VAT_MULTIPLIER  = 1.2;   // 20% VAT charged on GB orders
const DISCOUNT_MULTIPLIER = 0.9;  // 10% off when a discount code is present

function generateInvoice(array $order): string
{
    $runningTotal = 0;
    foreach ($order['items'] as $item) {
        $runningTotal += $item['price'] * $item['qty'];
    }

    if ($order['country'] === 'GB') {
        $runningTotal *= GB_VAT_MULTIPLIER;
    }
    if (! empty($order['discount'])) {
        $runningTotal *= DISCOUNT_MULTIPLIER;
    }

    $invoiceLines = ["Invoice #" . $order['id']];
    foreach ($order['items'] as $item) {
        $invoiceLines[] = "{$item['name']}: {$item['price']} x {$item['qty']}";
    }
    $invoiceLines[] = "Total: $runningTotal";

    return implode("\n", $invoiceLines);
}
