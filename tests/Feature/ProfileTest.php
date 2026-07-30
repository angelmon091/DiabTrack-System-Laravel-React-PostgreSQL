<?php

namespace Tests\Feature;

use App\Mail\EmailChangeAlert;
use App\Mail\VerifyEmailChange;
use App\Models\EmailChangeRequest;
use App\Models\PatientLink;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->withoutVite();
        $this->actingAs($user)->get('/profile')->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')->where('profile.email', $user->email)
            ->where('updateUrl', '/profile')->where('passwordUrl', '/password')->has('timezones', 10));
    }

    public function test_profile_name_can_be_updated_without_requesting_an_email_change(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_inertia_profile_patch_returns_to_profile_with_flash(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->patch('/profile', ['name' => 'Inertia Profile', 'email' => $user->email, 'timezone' => 'UTC'])
            ->assertRedirect('/profile')->assertSessionHas('status', 'profile-updated');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Inertia Profile', 'timezone' => 'UTC']);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_email_change_sends_alert_and_verification_with_expected_content(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'current@example.test']);
        $newEmail = 'new@example.test';

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name, 'email' => $newEmail, 'current_password' => 'password',
        ])->assertSessionHasNoErrors()->assertSessionHas('status', 'email-change-requested')->assertRedirect('/profile');

        Mail::assertSent(EmailChangeAlert::class, function (EmailChangeAlert $mail) use ($user, $newEmail) {
            return $mail->hasTo($user->email) && $mail->newEmail === $newEmail
                && $mail->envelope()->subject === 'DiabTrack - Aviso de Seguridad: Intento de Cambio de Correo'
                && str_contains($mail->render(), $newEmail);
        });
        Mail::assertSent(VerifyEmailChange::class, function (VerifyEmailChange $mail) use ($newEmail) {
            $html = $mail->render();
            return $mail->hasTo($newEmail) && $mail->newEmail === $newEmail
                && $mail->envelope()->subject === 'DiabTrack - Verificación de Cambio de Correo'
                && str_contains($html, $newEmail) && str_contains($html, $mail->token)
                && str_contains($html, 'Verificar y Cambiar Correo');
        });
        $this->assertDatabaseHas('email_change_requests', ['user_id' => $user->id, 'new_email' => $newEmail]);
        $this->assertSame('current@example.test', $user->refresh()->email);
    }

    public function test_email_change_requires_current_password(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'email' => 'new@example.test'])
            ->assertRedirect('/profile')->assertSessionHasErrors('current_password');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('email_change_requests', 0);
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name, 'email' => $user->email, 'avatar' => UploadedFile::fake()->image('avatar.png', 200, 200),
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertNotNull($user->refresh()->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_patient_profile_exposes_and_can_unlink_linked_people(): void
    {
        $patient = User::factory()->create();
        $carer = User::factory()->create();
        $patient->roles()->attach(Role::firstOrCreate(['name' => 'paciente']));
        $carer->roles()->attach(Role::firstOrCreate(['name' => 'cuidador']));
        PatientProfile::create(['user_id' => $patient->id, 'birth_date' => '1990-01-01', 'gender' => 'Femenino', 'diabetes_type' => 'Tipo 2', 'weight' => 70, 'height' => 165]);
        PatientLink::create(['patient_id' => $patient->id, 'linked_user_id' => $carer->id, 'role' => 'caregiver', 'invite_code' => 'PFQA01', 'status' => 'active']);
        $this->withoutVite();
        $this->actingAs($patient)->get('/profile')->assertInertia(fn (Assert $page) => $page
            ->has('linkedUsers', 1)->where('linkedUsers.0.name', $carer->name)->where('linkedUsers.0.roleLabel', 'Cuidador'));
        $this->actingAs($patient)->delete(route('profile.unlink', $carer))->assertRedirect('/profile');
        $this->assertDatabaseMissing('patient_links', ['patient_id' => $patient->id, 'linked_user_id' => $carer->id]);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
