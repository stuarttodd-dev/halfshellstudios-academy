<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiometricLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['field-notes.lock_enabled' => true]);
        session(['lock_enabled' => true, 'unlocked' => false]);
    }

    public function test_note_routes_redirect_to_unlock_when_locked(): void
    {
        Note::query()->create(['title' => 'Secret', 'body' => 'Hidden content']);

        $this->get(route('notes.index'))
            ->assertRedirect(route('locked', ['redirect' => route('notes.index')]));
    }

    public function test_unlocked_session_allows_note_access(): void
    {
        session(['unlocked' => true]);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('No notes yet', false);
    }

    public function test_settings_remain_accessible_while_locked(): void
    {
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('Biometric lock', false);
    }

    public function test_settings_can_disable_lock(): void
    {
        $this->post(route('settings.lock'), ['lock_enabled' => '0'])
            ->assertRedirect(route('settings'))
            ->assertSessionHas('status');

        $this->get(route('notes.index'))
            ->assertOk();
    }

    public function test_locked_screen_renders_retry(): void
    {
        $this->get(route('locked'))
            ->assertOk()
            ->assertSee('Unlock to continue', false)
            ->assertSee('Try again', false);
    }
}
