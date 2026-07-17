<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirige al usuario hacia la página de autenticación del proveedor externo.
     *
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            abort(404);
        }

        try {
            $driver = Socialite::driver($provider);

            // Forzar selector de cuenta en Google para no tomar la sesión automáticamente
            if ($provider === 'google') {
                $driver = $driver->with(['prompt' => 'select_account']);
            }

            return $driver->redirect();
        } catch (\InvalidArgumentException $e) {
            // Error de configuración (ej: falta el client_id)
            return redirect()->route('login')->with('error', 'El servicio de '.ucfirst($provider).' no está configurado correctamente en este momento.');
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'No se pudo conectar con '.ucfirst($provider).'. Inténtalo de nuevo más tarde.');
        }
    }

    /**
     * Obtiene la información del usuario y procesa el retorno del proveedor externo.
     *
     * @param  string  $provider
     * @return RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        if (! in_array($provider, ['google', 'facebook'])) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Hubo un problema al autenticar con '.ucfirst($provider));
        }

        $email = Str::lower(trim((string) $socialUser->getEmail()));

        $user = User::where($provider.'_id', $socialUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            // Actualiza el identificador y el avatar cuando provienen del proveedor o están vacíos.
            $userUpdateData = [];
            if (! $user->avatar || str_starts_with($user->avatar, 'http')) {
                $userUpdateData['avatar'] = $socialUser->getAvatar();
            }

            if (! $user->{$provider.'_id'}) {
                $userUpdateData[$provider.'_id'] = $socialUser->getId();
            }

            // Marca el correo como verificado porque el proveedor externo ya confirmó su propiedad.
            if (! $user->email_verified_at) {
                $userUpdateData['email_verified_at'] = now();
            }

            $user->update($userUpdateData);
            Auth::login($user);
        } else {
            // Registra un usuario nuevo con la información entregada por el proveedor.
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                $provider.'_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
                'email_verified_at' => now(), // Social providers verify emails
            ]);

            Auth::login($user);
        }

        // Si el usuario es administrador, redirigir al panel de administración
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Si no ha completado el onboarding, enviarlo allí
        if (! $user->patientProfile && ! $user->caregiverProfile && ! $user->doctorProfile && ! $user->hasCompletedOnboarding()) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended('/dashboard');
    }
}
