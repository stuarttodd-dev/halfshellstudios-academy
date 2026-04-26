<?php

// Add inside AppServiceProvider::register():

use App\Contracts\DiscountStrategy;
use App\Services\Pricing\FixedDiscountStrategy;
use App\Services\Pricing\NoDiscountStrategy;
use App\Services\Pricing\PercentageDiscountStrategy;
use Illuminate\Support\InvalidArgumentException;

$this->app->bind(DiscountStrategy::class, function ($app) {
    return match (config('pricing.driver')) {
        'none' => new NoDiscountStrategy,
        'fixed' => new FixedDiscountStrategy((int) config('pricing.fixed_off_pence')),
        'percent' => new PercentageDiscountStrategy((int) config('pricing.percent_off')),
        default => throw new InvalidArgumentException('Unknown PRICING_STRATEGY: ' . config('pricing.driver')),
    };
});
