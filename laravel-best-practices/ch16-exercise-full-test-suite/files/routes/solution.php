<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/chapter-16', fn () => response()->json(['message' => 'See SOLUTION.md for test / CI hand-in. Run php artisan test in this app.']));
