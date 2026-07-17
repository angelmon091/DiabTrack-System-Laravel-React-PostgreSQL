<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Clase PasswordController
 *
 * Gestiona la actualización de la contraseña del usuario.
 * Maneja la lógica de validación y guardado de la nueva contraseña.
 */
class PasswordController extends Controller
{
    /**
     * Actualiza la contraseña del usuario.
     *
     * Valida la contraseña actual y la nueva contraseña, y actualiza el registro
     * en la base de datos si las credenciales son correctas.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
