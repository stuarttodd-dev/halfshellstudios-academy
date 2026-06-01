<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\DevicePushToken;
use Livewire\Component;
use Native\Mobile\Attributes\OnNative;
use Native\Mobile\Events\PushNotification\TokenGenerated;
use Native\Mobile\Facades\PushNotifications;

class PushEnrollment extends Component
{
    public ?string $permission = null;

    public ?string $tokenPreview = null;

    public function mount(): void
    {
        $this->permission = PushNotifications::checkPermission();
        $this->tokenPreview = $this->maskToken(
            DevicePushToken::query()->latest('id')->value('token')
        );
    }

    public function enroll(): void
    {
        PushNotifications::enroll();
    }

    #[OnNative(TokenGenerated::class)]
    public function onTokenGenerated(string $token, ?string $id = null): void
    {
        $this->tokenPreview = $this->maskToken($token);
        $this->permission = PushNotifications::checkPermission();
    }

    private function maskToken(?string $token): ?string
    {
        if ($token === null || $token === '') {
            return null;
        }

        if (strlen($token) <= 12) {
            return $token;
        }

        return substr($token, 0, 8).'…'.substr($token, -4);
    }

    public function render()
    {
        return view('livewire.push-enrollment');
    }
}
