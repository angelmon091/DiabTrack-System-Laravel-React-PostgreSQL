<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Clase NewPasswordController
 *
 * Gestiona el restablecimiento de contraseña de un usuario.
 * Maneja la vista de formulario y la lógica de guardado de la nueva contraseña.
 */
class NewPasswordController extends Controller
{
    /**
     * Muestra la vista de restablecimiento de contraseña.
     *
     * Se accede a esta vista cuando el usuario ha solicitado un restablecimiento
     * y se le proporciona un token único.
     */
    public function create(Request $request): InertiaResponse
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email', ''),
            'passwordStoreUrl' => route('password.store', absolute: false),
        ]);
    }

    /**
     * Procesa la solicitud de nuevo restablecimiento de contraseña.
     *
     * Valida los datos recibidos (token, email, nueva contraseña) e intenta
     * restablecer la contraseña del usuario. Si es exitoso, redirige al login;
     * de lo contrario, devuelve los errores.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Aquí intentaremos restablecer la contraseña del usuario. Si tiene éxito, actualizaremos
        // la contraseña en un modelo de usuario real y la persistiremos en la base de datos.
        // De lo contrario, analizaremos el error y devolveremos la respuesta.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Si la contraseña se restableció correctamente, redirigiremos al usuario de regreso a
        // la vista autenticada de inicio de la aplicación. Si hay un error, podemos
        // redirigirlos de regreso de donde vinieron con su mensaje de error.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
