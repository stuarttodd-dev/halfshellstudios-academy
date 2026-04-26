<?php

namespace Tests\Feature;

use App\Contracts\DiscountStrategy;
use App\Services\Pricing\NoDiscountStrategy;
use Tests\TestCase;

class PricingStrategyTest extends TestCase
{
    public function test_config_switches_outcome_for_same_input(): void
    {
        config(['pricing.driver' => 'none']);
        $a = $this->getJson('/pricing-demo?subtotal=10000')->json('total_pence');
        config(['pricing.driver' => 'fixed', 'pricing.fixed_off_pence' => 1000]);
        $b = $this->getJson('/pricing-demo?subtotal=10000')->json('total_pence');
        $this->assertSame(10_000, $a);
        $this->assertSame(9_000, $b);
    }

    public function test_container_instance_swap(): void
    {
        $this->app->instance(DiscountStrategy::class, new NoDiscountStrategy);
        $out = $this->getJson('/pricing-demo?subtotal=5000')->json('total_pence');
        $this->assertSame(5_000, $out);
    }
}
