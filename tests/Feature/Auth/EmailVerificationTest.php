<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_can_be_verified_with_six_digit_code(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('482913'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        Event::fake();

        $response = $this->actingAs($user)->post(route('verification.code'), [
            'code' => '482913',
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
        $response->assertRedirect(route('onboarding.index'));
    }

    public function test_invalid_verification_code_is_rejected_and_counted(): void
    {
        $user = User::factory()->unverified()->create();
        $verification = EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('482913'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.code'), [
            'code' => '111111',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertSame(1, $verification->fresh()->attempts);
    }

    public function test_expired_verification_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('482913'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
            'sent_at' => now()->subMinutes(11),
        ]);

        $response = $this->actingAs($user)->post(route('verification.code'), [
            'code' => '482913',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_unverified_user_is_redirected_to_code_screen_after_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }
}
