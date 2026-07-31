<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\EmailChangeAlert;
use App\Mail\VerifyEmailChange;
use App\Models\EmailChangeRequest;
use App\Models\PatientLink;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Clase ProfileController
 *
 * Gestiona la visualización y edición del perfil del usuario.
 * Permite actualizar la información personal y eliminar la cuenta.
 */
class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil.
     */
    public function edit(Request $request): InertiaResponse
    {
        $user = $request->user()->loadMissing('patientProfile');
        $linkedUsers = $user->isPatient()
            ? $user->linkedCarers()->with('roles')->get()->map(function (User $linkedUser) {
                $isDoctor = $linkedUser->roles->contains('name', 'médico');

                return [
                    'id' => $linkedUser->id,
                    'name' => $linkedUser->name,
                    'email' => $linkedUser->email,
                    'avatarUrl' => $linkedUser->avatar
                        ? (str_starts_with($linkedUser->avatar, 'http') ? $linkedUser->avatar : asset('storage/'.$linkedUser->avatar))
                        : null,
                    'roleLabel' => $isDoctor ? 'Médico' : 'Cuidador',
                    'unlinkUrl' => route('profile.unlink', $linkedUser, absolute: false),
                ];
            })->values()
            : collect();

        $pendingEmailChange = EmailChangeRequest::where('user_id', $user->id)->first();

        return Inertia::render('Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone ?? 'America/Monterrey',
                'avatarUrl' => $user->avatar
                    ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/'.$user->avatar))
                    : null,
                'gender' => strtolower($user->patientProfile?->gender ?? ''),
            ],
            'updateUrl' => route('profile.update', absolute: false),
            'passwordUrl' => route('password.update', absolute: false),
            'destroyUrl' => route('profile.destroy', absolute: false),
            'linkedUsers' => $linkedUsers,
            'pendingEmailChange' => $pendingEmailChange ? [
                'newEmail' => $pendingEmailChange->new_email,
                'expiresAt' => $pendingEmailChange->expires_at->toISOString(),
            ] : null,
            'timezones' => [
                ['value' => 'America/Monterrey', 'label' => 'Monterrey (GMT-6)'],
                ['value' => 'America/Mexico_City', 'label' => 'Ciudad de México (GMT-6)'],
                ['value' => 'America/Tijuana', 'label' => 'Tijuana (GMT-7)'],
                ['value' => 'America/Hermosillo', 'label' => 'Hermosillo (GMT-7)'],
                ['value' => 'America/Bogota', 'label' => 'Bogotá / Colombia (GMT-5)'],
                ['value' => 'America/Santiago', 'label' => 'Santiago / Chile (GMT-4)'],
                ['value' => 'America/Buenos_Aires', 'label' => 'Buenos Aires / Argentina (GMT-3)'],
                ['value' => 'America/New_York', 'label' => 'New York (GMT-5)'],
                ['value' => 'America/Los_Angeles', 'label' => 'Los Angeles (GMT-8)'],
                ['value' => 'UTC', 'label' => 'Coordinated Universal Time (UTC)'],
            ],
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldEmail = $user->email;
        $newEmail = $request->validated()['email'];
        $status = 'profile-updated';

        $user->fill($request->except(['email', 'avatar']));

        if ($oldEmail !== $newEmail) {
            // Validar contraseña obligatoriamente para cambio de email
            if (! $request->current_password) {
                return Redirect::route('profile.edit')->withErrors(['current_password' => 'Se requiere la contraseña actual para cambiar el correo electrónico.'])->withInput();
            }

            // Eliminar solicitudes previas
            EmailChangeRequest::where('user_id', $user->id)->delete();

            $token = Str::random(64);
            EmailChangeRequest::create([
                'user_id' => $user->id,
                'new_email' => $newEmail,
                'token' => $token,
                'expires_at' => now()->addMinutes(30),
            ]);

            // Enviar alerta al correo ACTUAL
            Mail::to($oldEmail)->send(new EmailChangeAlert($user, $newEmail));

            // Enviar verificación al NUEVO correo
            Mail::to($newEmail)->send(new VerifyEmailChange($user, $token, $newEmail));

            $status = 'email-change-requested';
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatars/'.$user->id.'_'.time().'.jpg';

            if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }

            try {
                if (function_exists('imagejpeg') && (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng') || function_exists('imagecreatefromwebp'))) {
                    $manager = new ImageManager(new Driver);
                    $image = $manager->decode($file);
                    $encoded = $image->cover(150, 150)->encode(new JpegEncoder(80));
                    Storage::disk('public')->put($filename, $encoded->toString());
                } else {
                    // Si no hay soporte completo para imágenes, guardar original
                    Storage::disk('public')->putFileAs('avatars', $file, basename($filename));
                }
            } catch (\Throwable $e) {
                // En caso de cualquier error (incluyendo Error), guardar original como fallback
                Storage::disk('public')->putFileAs('avatars', $file, basename($filename));
            }

            $user->avatar = $filename;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', $status);
    }

    /**
     * Verifica y aplica el cambio de correo electrónico.
     */
    public function verifyEmail(Request $request, $token): RedirectResponse
    {
        $changeRequest = EmailChangeRequest::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $changeRequest) {
            return Redirect::route('profile.edit')->with('error', 'El enlace de verificación ha expirado o es inválido.');
        }

        $user = $changeRequest->user;
        $user->email = $changeRequest->new_email;
        $user->email_verified_at = now();
        $user->save();

        $changeRequest->delete();

        return Redirect::route('profile.edit')->with('status', 'email-updated');
    }

    /**
     * Elimina la cuenta del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function unlinkCarer(Request $request, User $linkedUser): RedirectResponse
    {
        PatientLink::where('patient_id', Auth::id())
            ->where('linked_user_id', $linkedUser->id)
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'Vínculo eliminado correctamente.');
    }
}
