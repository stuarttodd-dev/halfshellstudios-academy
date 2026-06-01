<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\DevicePushToken;
use Native\Mobile\Events\PushNotification\TokenGenerated;

class StorePushToken
{
    public function handle(TokenGenerated $event): void
    {
        DevicePushToken::query()->updateOrCreate(
            ['token' => $event->token],
            ['platform' => PHP_OS_FAMILY],
        );
    }
}
