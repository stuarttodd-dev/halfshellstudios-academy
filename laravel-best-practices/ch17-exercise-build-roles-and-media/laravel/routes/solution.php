<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Install spatie/laravel-permission and medialibrary in this app — see SOLUTION.md.
Route::get('/chapter-17', fn () => response()->json(['message' => 'Spatie capstone: see SOLUTION.md.']));
