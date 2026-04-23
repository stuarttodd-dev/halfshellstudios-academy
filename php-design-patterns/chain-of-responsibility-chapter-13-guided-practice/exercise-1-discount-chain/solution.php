<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class Customer
{
    public function __construct(public readonly string $id, public readonly bool $vip = false) {}
    public function isVip(): bool { return $this->vip; }
}

final class Coupon { public function __construct(public readonly int $discountInPence) {} }

final class Order
{
    public function __construct(
        public readonly Customer $customer,
        public readonly int $totalInPence,
        public readonly ?Coupon $coupon = null,
    ) {}
    public function hasCoupon(): bool { return $this->coupon !== null; }
}

interface DiscountHandler
{
    public function handle(Order $order, callable $next): int;
}

final class DiscountChain
{
    /** @param list<DiscountHandler> $handlers */
    public function __construct(private readonly array $handlers) {}

    public function calculate(Order $order): int
    {
        $terminal = static fn (Order $o): int => 0;
        $next = $terminal;
        foreach (array_reverse($this->handlers) as $h) {
            $current = $next;
            $next = static fn (Order $o): int => $h->handle($o, $current);
        }
        return $next($order);
    }
}

final class VipDiscount implements DiscountHandler
{
    public function handle(Order $order, callable $next): int
    {
        return $order->customer->isVip() ? (int) ($order->totalInPence * 20 / 100) : $next($order);
    }
}

final class BulkDiscount implements DiscountHandler
{
    public function __construct(private readonly int $thresholdInPence = 100_000) {}
    public function handle(Order $order, callable $next): int
    {
        return $order->totalInPence > $this->thresholdInPence
            ? (int) ($order->totalInPence * 10 / 100)
            : $next($order);
    }
}

final class CouponDiscount implements DiscountHandler
{
    public function handle(Order $order, callable $next): int
    {
        return $order->hasCoupon() ? $order->coupon->discountInPence : $next($order);
    }
}

// ---- assertions -------------------------------------------------------------

$chain = new DiscountChain([new VipDiscount(), new BulkDiscount(), new CouponDiscount()]);

$vip = new Customer('1', vip: true);
$normal = new Customer('2');

pdp_assert_eq(2_000, $chain->calculate(new Order($vip, totalInPence: 10_000)), 'vip wins (20%)');
pdp_assert_eq(15_000, $chain->calculate(new Order($normal, totalInPence: 150_000)), 'bulk wins (10% > 100k)');
pdp_assert_eq(500, $chain->calculate(new Order($normal, totalInPence: 5_000, coupon: new Coupon(500))), 'coupon used');
pdp_assert_eq(0, $chain->calculate(new Order($normal, totalInPence: 5_000)), 'no rule applied -> 0');

// each handler is independently testable
pdp_assert_eq(2_000, (new VipDiscount())->handle(new Order($vip, 10_000), static fn () => 999), 'vip handler in isolation');
pdp_assert_eq(999, (new VipDiscount())->handle(new Order($normal, 10_000), static fn () => 999), 'vip handler delegates when not vip');

pdp_done();
