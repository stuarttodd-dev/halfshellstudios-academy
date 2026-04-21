<?php
declare(strict_types=1);

/**
 * Every comment in this class is one of these three failure modes:
 *
 *   - WRONG:      "VAT is 17.5%"  — the constant is 1.20 (20% VAT).
 *                 The comment rotted when the rate changed and nobody
 *                 updated it. Now the comment and the code contradict.
 *   - REDUNDANT:  "Calculate the total price", "Loop through items",
 *                 "Apply VAT" — these re-read the code as English.
 *                 Delete without loss.
 *   - MISPLACED:  "Return the total" sits above `sleep(1)`, not above
 *                 the return. Either it migrated when somebody
 *                 inserted the sleep, or it was always wrong.
 *
 * Meanwhile the ONE thing a future reader would want to know — *why*
 * is there a `sleep(1)` in a price calculator? — is completely
 * undocumented.
 *
 * Comments are worth the space they take when they tell you *why*.
 * Everything else belongs in a better name or a deleted line.
 */
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

/* ---------- driver ---------- */

$items = [
    ['price' => 500, 'qty' => 2],
    ['price' => 300, 'qty' => 1],
];

$calculator = new PriceCalculator();

echo "total (pence): " . $calculator->total($items) . "\n";
