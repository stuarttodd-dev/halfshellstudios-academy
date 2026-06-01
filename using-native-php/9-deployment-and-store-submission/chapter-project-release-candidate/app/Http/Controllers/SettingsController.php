<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function show(): View
    {
        return view('settings', [
            'lockEnabled' => (bool) session(
                'lock_enabled',
                config('field-notes.lock_enabled'),
            ),
            'appVersion' => config('nativephp.version', '—'),
            'versionCode' => config('nativephp.version_code', '—'),
            'privacyPolicyUrl' => config('field-notes.privacy_policy_url'),
        ]);
    }

    public function updateLock(): RedirectResponse
    {
        $enabled = request()->boolean('lock_enabled');

        session(['lock_enabled' => $enabled]);

        if ($enabled) {
            session()->forget('unlocked');
        } else {
            session(['unlocked' => true]);
        }

        return redirect()
            ->route('settings')
            ->with('status', $enabled
                ? 'Biometric lock enabled. Notes require unlock on next visit.'
                : 'Biometric lock disabled.');
    }
}
