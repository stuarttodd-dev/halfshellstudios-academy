<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (app()->isLocal()) {
    Route::get('/_exercise/login', function () {
        $user = User::query()->where('email', 'test@example.com')->first();
        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
        Auth::login($user);

        return 'Logged in for exercise (local only). Now POST to /checkout.';
    });
}
