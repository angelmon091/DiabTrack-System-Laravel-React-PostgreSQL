<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Clase RegisteredUserController
 *
 * Gestiona el registro de nuevos usuarios.
 * Maneja la vista de formulario y la lógica de creación de cuentas.
 */
class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro de nuevos usuarios.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Procesa la solicitud de registro de un nuevo usuario.
     *
     * Valida los datos recibidos (nombre, correo, contraseña) y crea una nueva cuenta.
     * Si es exitoso, inicia sesión en la aplicación y redirige al usuario al onboarding.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
