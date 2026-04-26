<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect();
    }

    public function test_user_can_register_and_reach_dashboard(): void
    {
        $response = $this->post(route('register.attempt'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secretsecret')]);
        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'secretsecret',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_does_not_authenticate(): void
    {
        $user = User::factory()->create(['password' => bcrypt('right-password')]);
        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
