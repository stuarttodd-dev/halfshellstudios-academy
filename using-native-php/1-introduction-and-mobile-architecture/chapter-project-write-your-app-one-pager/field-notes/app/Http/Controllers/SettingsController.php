<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'appName' => config('app.name'),
            'plugins' => [
                ['package' => 'nativephp/mobile', 'type' => 'official (core)', 'purpose' => 'Embedded runtime, Jump, native:run'],
                ['package' => 'nativephp/mobile-biometrics', 'type' => 'official', 'purpose' => 'Biometric unlock (later chapter)'],
            ],
            'platforms' => [
                ['name' => 'iOS 16+', 'ship' => true],
                ['name' => 'Android 12+ (API 31)', 'ship' => true],
                ['name' => 'Web browser', 'ship' => false, 'note' => 'Dev-only before native:run'],
            ],
        ]);
    }
}
