<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdgeShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_empty_state_without_notes(): void
    {
        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('No notes yet', false);
    }

    public function test_compose_route_renders_create_form(): void
    {
        $this->get(route('notes.create'))
            ->assertOk()
            ->assertSee('Save note', false)
            ->assertSee('name="title"', false);
    }

    public function test_settings_route_renders_app_info(): void
    {
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('Biometric lock', false);
    }

    public function test_named_routes_are_registered(): void
    {
        $note = Note::query()->create(['title' => 'T', 'body' => 'B']);

        $this->assertSame(url('/'), route('notes.index'));
        $this->assertStringContainsString('/notes/create', route('notes.create'));
        $this->assertStringContainsString('/settings', route('settings'));
        $this->assertStringContainsString('/notes/'.$note->id.'/edit', route('notes.edit', $note));
    }
}
