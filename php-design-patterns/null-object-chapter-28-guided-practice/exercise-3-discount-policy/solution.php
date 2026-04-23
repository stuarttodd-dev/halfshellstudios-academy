<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface DiscountPolicy
{
    public function apply(int $totalInPence): int;
}

final class PercentageDiscount implements DiscountPolicy
{
    public function __construct(public readonly int $percent) {}
    public function apply(int $totalInPence): int
    {
        return (int) round($totalInPence * (100 - $this->percent) / 100);
    }
}

final class FlatDiscount implements DiscountPolicy
{
    public function __construct(public readonly int $offInPence) {}
    public function apply(int $totalInPence): int
    {
        return max(0, $totalInPence - $this->offInPence);
    }
}

/**
 * Null Object: the "no discount" policy. Lets Cart unconditionally
 * call `$policy->apply($total)` without "did the customer have one?"
 * branches.
 */
final class NoDiscount implements DiscountPolicy
{
    public function apply(int $totalInPence): int { return $totalInPence; }
}

final class Cart
{
    public function __construct(
        public readonly int $subtotalInPence,
        private readonly DiscountPolicy $policy = new NoDiscount(),
    ) {}

    public function total(): int { return $this->policy->apply($this->subtotalInPence); }
}

// ---- assertions -------------------------------------------------------------

pdp_assert_eq(10_000, (new Cart(10_000))->total(), 'no discount = subtotal');
pdp_assert_eq(9_000,  (new Cart(10_000, new PercentageDiscount(10)))->total(), '10% off');
pdp_assert_eq(9_500,  (new Cart(10_000, new FlatDiscount(500)))->total(),       '£5 flat off');
pdp_assert_eq(0,      (new Cart(300, new FlatDiscount(500)))->total(),          'flat discount cannot go negative');

// the value of the pattern is structural: Cart::total() is a single
// delegation to $this->policy->apply($subtotal) — no branching on
// "did this cart have a discount?".

pdp_done();
