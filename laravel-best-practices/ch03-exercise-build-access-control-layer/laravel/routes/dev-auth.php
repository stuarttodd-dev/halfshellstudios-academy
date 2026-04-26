<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

if (app()->isLocal()) {
    Route::get('/_exercise/login', function () {
        $u = User::query()->updateOrCreate(
            ['email' => 'subscribed@example.com'],
            [
                'name' => 'Subscribed',
                'password' => Hash::make('password'),
                'is_subscribed' => true,
                'email_verified_at' => now(),
            ]
        );
        Auth::login($u);

        return 'Logged in (local). Try GET /dashboard or GET /billing';
    });
}
