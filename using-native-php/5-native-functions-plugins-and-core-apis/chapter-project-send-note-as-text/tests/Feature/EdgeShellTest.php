<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class EdgeShellTest extends TestCase
{
    public function test_home_renders_note_placeholders_in_edge_layout(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Transformer pad inspection', false)
            ->assertSee('class="pb-20"', false);
    }

    public function test_compose_route_renders_share_form(): void
    {
        $this->get(route('compose'))
            ->assertOk()
            ->assertSee('Send as text', false);
    }

    public function test_settings_route_renders_app_info(): void
    {
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('Biometric lock', false);
    }

    public function test_named_routes_are_registered(): void
    {
        $this->assertSame(url('/'), route('home'));
        $this->assertStringContainsString('/compose', route('compose'));
        $this->assertStringContainsString('/settings', route('settings'));
    }
}
