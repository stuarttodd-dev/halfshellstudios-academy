<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Doc-only exercise — see SOLUTION.md. Optional: add a ShouldQueue job and dispatch from a route in your app.
Route::get('/chapter-15', fn () => response()->json(['message' => 'See SOLUTION.md for queue runbook.']));
