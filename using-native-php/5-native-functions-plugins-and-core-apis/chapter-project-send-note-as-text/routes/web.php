<?php

declare(strict_types=1);

use App\Http\Controllers\ComposeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/compose', [ComposeController::class, 'show'])->name('compose');
Route::post('/compose/send', [ComposeController::class, 'send'])->name('compose.send');
Route::get('/settings', fn () => view('settings'))->name('settings');
