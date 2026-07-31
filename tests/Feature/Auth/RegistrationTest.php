<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->withoutVite();

        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Register')
                ->url('/register')
                ->where('registerUrl', '/register')
                ->where('loginUrl', '/login')
                ->where('googleLoginUrl', '/auth/google/redirect'));
    }

    public function test_login_and_registration_pages_expose_reciprocal_inertia_links(): void
    {
        $this->withoutVite();

        $this->get('/login')->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->url('/login')
            ->where('registerUrl', '/register'));

        $this->get('/register')->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->url('/register')
            ->where('loginUrl', '/login'));
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'Test@Example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertDatabaseHas('email_verification_codes', ['user_id' => $user->id]);
        Notification::assertSentTo($user, VerifyEmailCodeNotification::class);
        $response->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_validation_messages_are_displayed_in_spanish(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'correo-invalido',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'El campo nombre es obligatorio.',
            'email' => 'El campo correo electrónico debe ser una dirección de correo válida.',
            'password' => 'La confirmación de contraseña no coincide.',
        ]);
    }
}
