<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $this->withoutVite();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/ConfirmPassword')
                ->url('/confirm-password')
                ->where('confirmPasswordUrl', '/confirm-password'));
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_inertia_password_confirmation_uses_a_full_page_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('X-Inertia', 'true')
            ->post('/confirm-password', ['password' => 'password']);

        $response->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('dashboard'));
        $response->assertSessionHas('auth.password_confirmed_at');
    }
}
