<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/billing', BillingController::class)->name('billing');
    Route::post('/billing/plan', [PlanController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('billing.plan.update');
});
