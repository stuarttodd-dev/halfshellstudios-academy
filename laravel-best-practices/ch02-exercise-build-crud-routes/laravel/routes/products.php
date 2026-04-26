<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Required from routes/solution.php (and mirrored under ../files/ for reference).

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
