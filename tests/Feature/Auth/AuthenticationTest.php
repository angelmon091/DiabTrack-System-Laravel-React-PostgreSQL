<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('loginUrl', '/login')
                ->where('forgotPasswordUrl', '/forgot-password')
                ->where('registerUrl', '/register')
                ->where('googleLoginUrl', '/auth/google/redirect'));
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding.index', absolute: false));
    }

    public function test_users_can_authenticate_when_email_contains_uppercase_letters(): void
    {
        $user = User::factory()->create([
            'email' => 'persona@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'Persona@Example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('onboarding.index', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inertia_login_uses_a_full_page_redirect_to_a_blade_destination(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('X-Inertia', 'true')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/onboarding'));
    }

    public function test_inertia_login_returns_validation_errors_for_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('X-Inertia', 'true')
            ->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $this->assertGuest();
        $response->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
