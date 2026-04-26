<?php

use App\Http\Controllers\PricingDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/pricing-demo', PricingDemoController::class)->name('pricing.demo');
