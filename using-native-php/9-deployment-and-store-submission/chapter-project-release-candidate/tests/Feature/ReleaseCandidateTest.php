<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseCandidateTest extends TestCase
{
    use RefreshDatabase;
    public function test_release_version_env_keys_are_documented(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertIsString($example);
        $this->assertStringContainsString('NATIVEPHP_APP_VERSION=1.0.0', $example);
        $this->assertStringContainsString('NATIVEPHP_APP_VERSION_CODE=1', $example);
        $this->assertStringContainsString('FIELD_NOTES_PRIVACY_POLICY_URL=', $example);
    }

    public function test_settings_shows_release_metadata(): void
    {
        config([
            'nativephp.version' => '1.0.0',
            'nativephp.version_code' => 1,
            'field-notes.privacy_policy_url' => 'https://example.com/privacy',
        ]);

        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('1.0.0', false)
            ->assertSee('View', false);
    }
}
