<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// This exercise is a deploy runbook (SOLUTION.md), not a code exercise.
Route::get('/chapter-14', fn () => response()->json([
    'message' => 'See parent folder SOLUTION.md and ../README in this repo',
]));
