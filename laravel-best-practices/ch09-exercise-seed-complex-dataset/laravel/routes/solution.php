<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/seed-demo', function () {
    return response()->json([
        'message' => 'This chapter is about DatabaseSeeder and factories. Run: php artisan db:seed',
    ]);
});
