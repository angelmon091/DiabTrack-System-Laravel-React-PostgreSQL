<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la selección de síntomas enviada por el paciente.
 */
class SymptomLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symptoms' => ['required', 'array', 'min:1'],
            'symptoms.*' => ['integer', 'exists:symptoms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'symptoms.required' => __('Selecciona al menos un síntoma.'),
            'symptoms.min' => __('Debes seleccionar al menos un síntoma.'),
        ];
    }
}
