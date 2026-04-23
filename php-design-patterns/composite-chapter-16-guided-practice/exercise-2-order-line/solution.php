<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — Order and OrderLine are not recursive.
 *
 * Composite is for tree-shaped data where leaves and composites
 * truly behave the same and callers want to walk them uniformly.
 *
 * `Order` and `OrderLine` differ in *what they are*:
 *   - an `Order` has a customer, a delivery address, a status, a totals method
 *   - an `OrderLine` has a product, a qty, a unit price
 *
 * Callers also treat them differently: nobody asks "give me the
 * customer of an OrderLine" or "give me the qty of an Order". A common
 * `lineable()` interface would be a fiction invented to share an
 * abstraction that nobody actually uses.
 *
 * Two different things that happen to live in the same aggregate are
 * *aggregation*, not Composite.
 */

final class OrderLine
{
    public function __construct(
        public readonly int $qty,
        public readonly int $unitPriceInPence,
    ) {}
    public function totalInPence(): int { return $this->qty * $this->unitPriceInPence; }
}

final class Order
{
    /** @param list<OrderLine> $lines */
    public function __construct(public readonly array $lines = []) {}

    public function totalInPence(): int
    {
        return array_sum(array_map(static fn (OrderLine $l) => $l->totalInPence(), $this->lines));
    }
}

// ---- assertions -------------------------------------------------------------

$order = new Order([
    new OrderLine(qty: 2, unitPriceInPence: 1_000),
    new OrderLine(qty: 1, unitPriceInPence: 500),
]);

pdp_assert_eq(2_500, $order->totalInPence(), 'order total = sum of line totals');
pdp_assert_eq(2_000, $order->lines[0]->totalInPence(), 'line 1 total');
pdp_assert_eq(500,   $order->lines[1]->totalInPence(), 'line 2 total');

pdp_done('Composite was the wrong answer here — see the comment block.');
