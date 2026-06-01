<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/compose', fn () => view('compose'))->name('compose');
Route::get('/settings', fn () => view('settings'))->name('settings');
