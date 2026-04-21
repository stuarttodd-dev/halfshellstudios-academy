<?php
declare(strict_types=1);

/**
 * Step 3 of the refactor: extract ONE collaborator.
 *
 * The brief asks us to pick one concern (totals OR formatting) and
 * extract it. We pick **totals**, because:
 *
 *   - that is where the real policy lives (line subtotals + VAT +
 *     discount), so isolating it pays the most in testability;
 *   - the totals computation is currently *interleaved* with the
 *     formatting loop, so untangling it makes both halves clearer;
 *   - formatting is mostly literal strings — extracting that next
 *     would be straightforward once totals are out.
 *
 * Critically: this is still **one small step**. We do NOT also:
 *   - introduce a `Money` value object (separate refactor);
 *   - replace the `if`-cascade with a strategy pattern (separate refactor);
 *   - extract formatting into its own renderer (the next obvious step,
 *     but not THIS step).
 *
 * The characterisation test still passes byte-for-byte. The new
 * `InvoiceTotalsCalculator` also gets its own dedicated unit test
 * (see `unit_test.php`) — that is the *bonus* you earn by extracting
 * a collaborator: a seam you can probe directly.
 */

const GB_VAT_MULTIPLIER   = 1.2;
const DISCOUNT_MULTIPLIER = 0.9;

final class InvoiceTotalsCalculator
{
    /** @param array{country: string, items: list<array{name: string, price: int|float, qty: int}>, discount?: string} $order */
    public function totalFor(array $order): int|float
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
