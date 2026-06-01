<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Projectmata\MobileBiometrics\Facades\Biometrics;

#[Layout('layouts.locked')]
class UnlockGate extends Component
{
    public bool $failed = false;

    public function mount(): void
    {
        if (session('unlocked')) {
            $this->redirect($this->intendedUrl());

            return;
        }

        $this->requestUnlock();
    }

    public function requestUnlock(): void
    {
        $this->failed = false;

        $result = Biometrics::authenticate(
            reason: 'Unlock Field Notes to view your notes',
            title: 'Unlock Field Notes',
            subtitle: 'Use Face ID, Touch ID, or your device passcode',
        );

        if (($result['success'] ?? false) === true) {
            session(['unlocked' => true]);
            $this->redirect($this->intendedUrl());

            return;
        }

        $this->failed = true;
    }

    private function intendedUrl(): string
    {
        $redirect = request()->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
            return $redirect;
        }

        return route('notes.index');
    }

    public function render()
    {
        return view('livewire.unlock-gate');
    }
}
