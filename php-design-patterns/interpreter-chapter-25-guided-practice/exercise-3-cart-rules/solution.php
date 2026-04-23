<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Customer
{
    public function __construct(
        public readonly bool $isStudent = false,
        public readonly bool $isMember = false,
        public readonly bool $isFirstOrder = false,
    ) {}
}

final class Cart
{
    public function __construct(
        public readonly int $totalInPence,
        public readonly Customer $customer,
    ) {}
}

interface CartRule
{
    public function isSatisfiedBy(Cart $cart): bool;
}

final class OrderTotalAtLeast implements CartRule
{
    public function __construct(public readonly int $thresholdInPence) {}
    public function isSatisfiedBy(Cart $cart): bool { return $cart->totalInPence >= $this->thresholdInPence; }
}

final class IsStudent implements CartRule { public function isSatisfiedBy(Cart $c): bool { return $c->customer->isStudent; } }
final class IsMember  implements CartRule { public function isSatisfiedBy(Cart $c): bool { return $c->customer->isMember; } }
final class IsFirstOrder implements CartRule { public function isSatisfiedBy(Cart $c): bool { return $c->customer->isFirstOrder; } }

final class AllOf implements CartRule
{
    /** @var list<CartRule> */
    private array $rules;
    public function __construct(CartRule ...$rules) { $this->rules = array_values($rules); }
    public function isSatisfiedBy(Cart $cart): bool
    {
        foreach ($this->rules as $r) if (!$r->isSatisfiedBy($cart)) return false;
        return true;
    }
}

final class AnyOf implements CartRule
{
    /** @var list<CartRule> */
    private array $rules;
    public function __construct(CartRule ...$rules) { $this->rules = array_values($rules); }
    public function isSatisfiedBy(Cart $cart): bool
    {
        foreach ($this->rules as $r) if ($r->isSatisfiedBy($cart)) return true;
        return false;
    }
}

final class Discount
{
    public function __construct(
        public readonly string $name,
        public readonly int $percent,
        public readonly CartRule $when,
    ) {}
}

/**
 * Returns the largest applicable discount, or null.
 *
 * @param list<Discount> $discounts
 */
function bestApplicable(array $discounts, Cart $cart): ?Discount
{
    $eligible = array_values(array_filter($discounts, static fn (Discount $d) => $d->when->isSatisfiedBy($cart)));
    if ($eligible === []) return null;
    usort($eligible, static fn (Discount $a, Discount $b) => $b->percent <=> $a->percent);
    return $eligible[0];
}

// ---- assertions -------------------------------------------------------------

$campaigns = [
    new Discount('Over £50',         5,  new OrderTotalAtLeast(5_000)),
    new Discount('Student member',   10, new AllOf(new IsStudent(), new IsMember())),
    new Discount('First order',      15, new IsFirstOrder()),
];

// new customer making a £30 first order
$first = bestApplicable($campaigns, new Cart(3_000, new Customer(isFirstOrder: true)));
pdp_assert_eq('First order', $first?->name, 'first order wins (15%)');

// student member spending £80
$studentMember = bestApplicable($campaigns, new Cart(8_000, new Customer(isStudent: true, isMember: true)));
pdp_assert_eq('Student member', $studentMember?->name, 'student member (10%) beats over £50 (5%)');

// regular customer spending £60
$regular = bestApplicable($campaigns, new Cart(6_000, new Customer()));
pdp_assert_eq('Over £50', $regular?->name, 'over £50 only');

// nothing applies
pdp_assert_eq(null, bestApplicable($campaigns, new Cart(2_000, new Customer())), 'no discount');

// per-leaf
pdp_assert_true((new OrderTotalAtLeast(5_000))->isSatisfiedBy(new Cart(5_000, new Customer())), 'threshold inclusive');
pdp_assert_true(!(new OrderTotalAtLeast(5_000))->isSatisfiedBy(new Cart(4_999, new Customer())), 'just under threshold');

pdp_done();
