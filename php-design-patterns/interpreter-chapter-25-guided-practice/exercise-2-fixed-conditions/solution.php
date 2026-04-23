<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — two fixed conditions don't need a grammar.
 *
 * Interpreter pays its rent when:
 *   - users write expressions you parse at runtime,
 *   - the rule space is open and grows with new combinators,
 *   - composition by tree is genuinely the natural representation.
 *
 * `if ($order->total > 100 && $order->customer->isPremium)` is none of
 * those: it is two fixed checks, joined by AND, evaluated in code.
 * Wrapping it in `OrderTotalAtLeast`, `IsPremium`, and `AndExpr` would
 * make readers chase classes to learn what an `if` statement already
 * tells them.
 *
 * For the same brief grown into "premium discount campaigns that
 * marketing want to compose", see Specification (chapter 27).
 */

final class Customer
{
    public function __construct(public readonly bool $isPremium) {}
}

final class Order
{
    public function __construct(
        public readonly int $totalInPence,
        public readonly Customer $customer,
    ) {}
}

function premiumDiscountApplies(Order $order): bool
{
    return $order->totalInPence > 10_000 && $order->customer->isPremium;
}

pdp_assert_true(premiumDiscountApplies(new Order(20_000, new Customer(true))),  'premium spending -> applies');
pdp_assert_true(!premiumDiscountApplies(new Order(20_000, new Customer(false))), 'non-premium -> no');
pdp_assert_true(!premiumDiscountApplies(new Order(5_000,  new Customer(true))),  'small order -> no');

pdp_done('Interpreter was the wrong answer here — see the comment block.');
