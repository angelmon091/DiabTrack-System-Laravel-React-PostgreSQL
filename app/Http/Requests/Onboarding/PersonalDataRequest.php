<?php

namespace App\Http\Requests\Onboarding;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Clase PersonalDataRequest
 *
 * Reglas de validación para los datos personales del usuario.
 * Asegura que los datos personales sean válidos antes de ser procesados.
 */
class PersonalDataRequest extends FormRequest
{
    private const MONTHS = [
        'Enero' => 1,
        'Febrero' => 2,
        'Marzo' => 3,
        'Abril' => 4,
        'Mayo' => 5,
        'Junio' => 6,
        'Julio' => 7,
        'Agosto' => 8,
        'Septiembre' => 9,
        'Octubre' => 10,
        'Noviembre' => 11,
        'Diciembre' => 12,
    ];

    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Solo usuarios autenticados llegan aquí vía middleware
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'birth_day' => ['required', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['required', 'string', Rule::in(array_keys(self::MONTHS))],
            'birth_year' => ['required', 'integer', 'min:1920', 'max:'.now()->subYears(18)->year],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'diabetes_type' => ['required', 'string', Rule::in(array_keys(config('diabtrack.glycemic_conditions')))],
            'weight' => ['required', 'numeric', 'min:20', 'max:300'],
            'height' => ['required', 'numeric', 'min:50', 'max:250'],
            'gender' => ['required', 'string', 'in:Masculino,Femenino'],
        ];
    }

    /**
     * Obtiene mensajes personalizados para errores de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weight.min' => __('El peso debe ser al menos 20kg.'),
            'weight.max' => __('El peso no puede exceder los 300kg.'),
            'height.min' => __('La estatura debe ser al menos 50cm.'),
            'height.max' => __('La estatura no puede exceder los 250cm.'),
            'gender.in' => __('Seleccione un género válido.'),
            'birth_date.date_format' => __('La fecha de nacimiento no es válida.'),
            'birth_date.before_or_equal' => __('DiabTrack requiere que el paciente tenga al menos 18 años.'),
            'birth_year.max' => __('DiabTrack requiere que el paciente tenga al menos 18 años.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $month = self::MONTHS[$this->input('birth_month')] ?? 0;
        $year = (int) $this->input('birth_year');
        $day = (int) $this->input('birth_day');

        $birthDate = checkdate($month, $day, $year)
            ? Carbon::create($year, $month, $day)->toDateString()
            : 'invalid-date';

        $this->merge(['birth_date' => $birthDate]);
    }
}
