<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Paste this block into routes/web.php (or require this file from web.php).

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/{product}', [ProductController::class, 'show'])
        ->whereNumber('product')
        ->name('show');
    Route::patch('/{product}', [ProductController::class, 'update'])
        ->whereNumber('product')
        ->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])
        ->whereNumber('product')
        ->name('destroy');
});
