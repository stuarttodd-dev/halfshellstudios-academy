<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MobileHomeTest extends TestCase
{
    public function test_home_renders_mobile_splash(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('It works on mobile', false);
    }
}
