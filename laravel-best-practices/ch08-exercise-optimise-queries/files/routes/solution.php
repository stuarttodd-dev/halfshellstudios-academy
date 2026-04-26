<?php

declare(strict_types=1);

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\MonthlyRevenueReportController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/orders', [AdminOrderController::class, 'index']);
Route::get('/reports/monthly-revenue', MonthlyRevenueReportController::class);
