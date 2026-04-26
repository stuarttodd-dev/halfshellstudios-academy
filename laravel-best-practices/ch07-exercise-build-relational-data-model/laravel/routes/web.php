<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response(
        'Laravel exercise app — use the routes for this chapter (see ../README). ' .
        'GET /exercise for a quick health check.'
    );
});

Route::get('/exercise', fn () => 'ok');

if (file_exists(__DIR__.'/solution.php')) {
    require __DIR__.'/solution.php';
}
