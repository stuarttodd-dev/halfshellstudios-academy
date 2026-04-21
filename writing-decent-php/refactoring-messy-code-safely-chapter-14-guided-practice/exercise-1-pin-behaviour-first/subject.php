<?php
declare(strict_types=1);

/**
 * The function as it lives in the codebase today. Untouched.
 *
 * Smells you can already name without changing a line:
 *   - one-letter variables (`$i`)
 *   - single-line `if`/`foreach` bodies
 *   - magic numbers `1.2` and `0.9`
 *   - arithmetic, business rules, AND formatting jammed together
 *   - returns a string, so the only seam is "the whole rendered text"
 *
 * Resist refactoring. The first job is to **pin** the current behaviour
 * with a characterisation test (see `characterisation_test.php`). Only
 * then is it safe to rename or restructure anything.
 */
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
