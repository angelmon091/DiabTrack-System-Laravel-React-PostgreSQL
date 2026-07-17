<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Clase AdminRoleRequest
 *
 * Reglas de validación para la creación y actualización de roles.
 * Asegura que los datos del rol sean válidos antes de ser procesados.
 */
class AdminRoleRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role') ? $this->route('role')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class)->ignore($roleId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
