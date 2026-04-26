<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardViewController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register.attempt');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardViewController::class, 'show'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
