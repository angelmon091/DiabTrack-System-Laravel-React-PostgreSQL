<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Centraliza las reglas de autorización y validación para los registros de actividad física.
 */
class ActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'intensity' => ['required', 'string', 'in:baja,media,alta'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'energy_level' => ['nullable', 'string', 'in:muy_baja,baja,normal,alta,muy_alta'],
        ];
    }

    public function messages(): array
    {
        return [
            'activity_type.required' => __('Selecciona un tipo de actividad.'),
            'duration_minutes.required' => __('La duración es obligatoria.'),
            'duration_minutes.min' => __('La duración mínima es 1 minuto.'),
            'intensity.required' => __('Selecciona la intensidad.'),
        ];
    }
}
