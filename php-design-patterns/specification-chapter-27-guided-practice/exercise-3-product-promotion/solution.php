<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Product
{
    public function __construct(
        public readonly string $sku,
        public readonly string $category,
        public readonly int $stock,
        public readonly int $priceInPence,
        public readonly bool $featured,
    ) {}
}

interface ProductSpec
{
    public function isSatisfiedBy(Product $p): bool;
}

abstract class CompositeProductSpec implements ProductSpec
{
    public function and(ProductSpec $other): ProductSpec { return new AndSpec($this, $other); }
    public function or(ProductSpec $other): ProductSpec  { return new OrSpec($this, $other); }
    public function not(): ProductSpec                   { return new NotSpec($this); }
}

final class CategoryIs extends CompositeProductSpec
{
    public function __construct(public readonly string $category) {}
    public function isSatisfiedBy(Product $p): bool { return $p->category === $this->category; }
}

final class InStock extends CompositeProductSpec
{
    public function isSatisfiedBy(Product $p): bool { return $p->stock > 0; }
}

final class PriceAtMost extends CompositeProductSpec
{
    public function __construct(public readonly int $maxPriceInPence) {}
    public function isSatisfiedBy(Product $p): bool { return $p->priceInPence <= $this->maxPriceInPence; }
}

final class IsFeatured extends CompositeProductSpec
{
    public function isSatisfiedBy(Product $p): bool { return $p->featured; }
}

final class AndSpec extends CompositeProductSpec
{
    public function __construct(private readonly ProductSpec $a, private readonly ProductSpec $b) {}
    public function isSatisfiedBy(Product $p): bool { return $this->a->isSatisfiedBy($p) && $this->b->isSatisfiedBy($p); }
}

final class OrSpec extends CompositeProductSpec
{
    public function __construct(private readonly ProductSpec $a, private readonly ProductSpec $b) {}
    public function isSatisfiedBy(Product $p): bool { return $this->a->isSatisfiedBy($p) || $this->b->isSatisfiedBy($p); }
}

final class NotSpec extends CompositeProductSpec
{
    public function __construct(private readonly ProductSpec $inner) {}
    public function isSatisfiedBy(Product $p): bool { return !$this->inner->isSatisfiedBy($p); }
}

// ---- two callsites that share specs ----------------------------------------

/**
 * Selection: pick products eligible for a promotion banner.
 *
 * @param list<Product> $products
 * @return list<Product>
 */
function eligibleForBanner(array $products, ProductSpec $spec): array
{
    return array_values(array_filter($products, static fn (Product $p) => $spec->isSatisfiedBy($p)));
}

/**
 * Validation: refuse to add a product to a promotion if it doesn't qualify.
 */
function assertEligibleForPromotion(Product $p, ProductSpec $spec): void
{
    if (!$spec->isSatisfiedBy($p)) {
        throw new \DomainException("{$p->sku} does not qualify for the promotion");
    }
}

// ---- assertions -------------------------------------------------------------

$catalogue = [
    new Product('A', 'shoes',     5, 4_000, true),
    new Product('B', 'shoes',     0, 3_000, true),  // out of stock
    new Product('C', 'jackets',  10, 9_000, false),
    new Product('D', 'shoes',    20, 1_500, false),
];

$summerSale = (new CategoryIs('shoes'))
    ->and(new InStock())
    ->and(new PriceAtMost(5_000));

$banner = eligibleForBanner($catalogue, $summerSale);
pdp_assert_eq(['A', 'D'], array_map(static fn (Product $p) => $p->sku, $banner), 'shop selection: shoes in stock <= £50');

// reuse same spec at the *validation* boundary
assertEligibleForPromotion(new Product('E', 'shoes', 4, 2_500, false), $summerSale);

pdp_assert_throws(
    \DomainException::class,
    static fn () => assertEligibleForPromotion(new Product('F', 'shoes', 0, 2_500, true), $summerSale),
    'out-of-stock product rejected by validation'
);

// composed with NOT
$nonFeatured = (new IsFeatured())->not();
$nonFeaturedSkus = array_map(static fn (Product $p) => $p->sku, eligibleForBanner($catalogue, $nonFeatured));
pdp_assert_eq(['C', 'D'], $nonFeaturedSkus, 'NOT featured');

pdp_done();
