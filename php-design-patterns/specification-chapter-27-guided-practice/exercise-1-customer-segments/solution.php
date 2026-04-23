<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Customer
{
    public function __construct(
        public readonly string $name,
        public readonly string $country,
        public readonly int $orderCount,
        public readonly int $lifetimeValueInPence,
        public readonly bool $marketingOptIn,
    ) {}
}

interface Specification
{
    public function isSatisfiedBy(Customer $candidate): bool;
}

abstract class CompositeSpecification implements Specification
{
    public function and(Specification $other): Specification { return new AndSpecification($this, $other); }
    public function or(Specification $other): Specification  { return new OrSpecification($this, $other); }
    public function not(): Specification                     { return new NotSpecification($this); }
}

final class InCountry extends CompositeSpecification
{
    public function __construct(public readonly string $country) {}
    public function isSatisfiedBy(Customer $c): bool { return $c->country === $this->country; }
}

final class HasAtLeastOrders extends CompositeSpecification
{
    public function __construct(public readonly int $threshold) {}
    public function isSatisfiedBy(Customer $c): bool { return $c->orderCount >= $this->threshold; }
}

final class LifetimeValueAtLeast extends CompositeSpecification
{
    public function __construct(public readonly int $thresholdInPence) {}
    public function isSatisfiedBy(Customer $c): bool { return $c->lifetimeValueInPence >= $this->thresholdInPence; }
}

final class IsMarketingOptedIn extends CompositeSpecification
{
    public function isSatisfiedBy(Customer $c): bool { return $c->marketingOptIn; }
}

final class AndSpecification extends CompositeSpecification
{
    public function __construct(private readonly Specification $a, private readonly Specification $b) {}
    public function isSatisfiedBy(Customer $c): bool { return $this->a->isSatisfiedBy($c) && $this->b->isSatisfiedBy($c); }
}

final class OrSpecification extends CompositeSpecification
{
    public function __construct(private readonly Specification $a, private readonly Specification $b) {}
    public function isSatisfiedBy(Customer $c): bool { return $this->a->isSatisfiedBy($c) || $this->b->isSatisfiedBy($c); }
}

final class NotSpecification extends CompositeSpecification
{
    public function __construct(private readonly Specification $inner) {}
    public function isSatisfiedBy(Customer $c): bool { return !$this->inner->isSatisfiedBy($c); }
}

// ---- assertions -------------------------------------------------------------

$customers = [
    new Customer('Alex', 'GB', 12, 80_000, true),
    new Customer('Beth', 'GB', 2,  4_000,  true),
    new Customer('Cara', 'US', 30, 120_000, false),
    new Customer('Dom',  'GB', 0,  0,      false),
];

$ukVip = (new InCountry('GB'))
    ->and(new HasAtLeastOrders(10))
    ->and(new LifetimeValueAtLeast(50_000))
    ->and(new IsMarketingOptedIn());

$picked = array_values(array_filter($customers, static fn (Customer $c) => $ukVip->isSatisfiedBy($c)));
pdp_assert_eq(['Alex'], array_map(static fn (Customer $c) => $c->name, $picked), 'UK VIP segment');

// reuse pieces in another segment: any-country VIP
$globalVip = (new HasAtLeastOrders(10))->and(new LifetimeValueAtLeast(50_000));
$picked2   = array_values(array_filter($customers, static fn (Customer $c) => $globalVip->isSatisfiedBy($c)));
pdp_assert_eq(['Alex', 'Cara'], array_map(static fn (Customer $c) => $c->name, $picked2), 'global VIP segment');

// not-opted-in
$notOptedIn = (new IsMarketingOptedIn())->not();
pdp_assert_eq(['Cara', 'Dom'], array_map(static fn (Customer $c) => $c->name, array_values(array_filter($customers, static fn (Customer $c) => $notOptedIn->isSatisfiedBy($c)))), 'not opted-in');

// per-leaf
pdp_assert_true((new InCountry('GB'))->isSatisfiedBy($customers[0]), 'leaf: country GB');
pdp_assert_true(!(new HasAtLeastOrders(10))->isSatisfiedBy($customers[1]), 'leaf: orders < 10');

pdp_done();
