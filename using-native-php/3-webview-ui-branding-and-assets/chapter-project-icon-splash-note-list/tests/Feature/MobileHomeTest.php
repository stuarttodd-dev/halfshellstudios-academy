<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MobileHomeTest extends TestCase
{
    public function test_home_renders_field_notes_branded_shell(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Field Notes', false)
            ->assertSee('Transformer pad inspection', false);
    }
}
