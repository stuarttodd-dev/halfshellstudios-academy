<?php

declare(strict_types=1);

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
