<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida los límites permitidos para los signos vitales registrados.
 */
class VitalSignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'glucose_level' => ['required', 'integer', 'min:20', 'max:600'],
            'systolic' => ['nullable', 'integer', 'min:40', 'max:250'],
            'diastolic' => ['nullable', 'integer', 'min:30', 'max:150'],
            'heart_rate' => ['nullable', 'integer', 'min:30', 'max:220'],
            'weight' => ['nullable', 'numeric', 'min:20', 'max:350'],
            'hba1c' => ['nullable', 'numeric', 'min:3', 'max:15'],
            'measurement_moment' => ['required', 'string', 'in:Ayunas,Antes de Comer,Después de Comer,Al Dormir'],
            'stress_level' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'glucose_level.min' => __('Nivel de glucosa inválido.'),
            'hba1c.max' => __('El valor de HbA1c parece demasiado alto.'),
        ];
    }
}
