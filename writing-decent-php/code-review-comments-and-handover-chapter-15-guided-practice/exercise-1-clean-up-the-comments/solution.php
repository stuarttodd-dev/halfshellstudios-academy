<?php
declare(strict_types=1);

/**
 * Comment rubric applied:
 *
 *   - If the comment says what the code already says, DELETE it.
 *   - If the comment contradicts the code, DELETE it (the code is
 *     the source of truth; the comment rotted).
 *   - If the code does something surprising, ADD a short comment
 *     saying *why*. That is the only comment worth the paper it is
 *     written on.
 *
 * What that meant for this class:
 *
 *   - Deleted: "Calculate the total price", "Loop through items",
 *     "Apply VAT", "Return the total". Redundant.
 *   - Deleted: "// VAT is 17.5%". Wrong, and even if it were right,
 *     a better name (`VAT_MULTIPLIER_INCLUDING_20_PERCENT`) is the
 *     proper home for that information.
 *   - Added: a short `why` comment on `sleep(1)`, the one genuinely
 *     non-obvious line.
 *   - Renamed: `VAT` -> `VAT_MULTIPLIER_INCLUDING_20_PERCENT`. The
 *     name now carries the information the rotten comment used to.
 */
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

/* ---------- driver (identical observable output to starter.php) ---------- */

$items = [
    ['price' => 500, 'qty' => 2],
    ['price' => 300, 'qty' => 1],
];

$calculator = new PriceCalculator();

echo "total (pence): " . $calculator->total($items) . "\n";
