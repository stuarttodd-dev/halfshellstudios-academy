<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\StorePushToken;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Native\Mobile\Events\PushNotification\TokenGenerated;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(TokenGenerated::class, StorePushToken::class);
    }
}
