<?php

namespace App\Http\Requests\Tracking;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Centraliza las reglas de autorización y validación para los registros nutricionales.
 */
class NutritionLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meal_type' => ['required', 'string', 'in:desayuno,almuerzo,cena,snack,correccion'],
            'carbs_grams' => ['required', 'integer', 'min:0', 'max:500'],
            'consumed_at' => ['nullable', 'date_format:H:i'],
            'food_categories' => ['nullable', 'array'],
            'food_categories.*' => ['string'],
            'medication_taken' => ['nullable', 'string', 'max:100'],
            'medication_dose' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'meal_type.required' => __('Selecciona el tipo de comida.'),
            'carbs_grams.required' => __('Los carbohidratos son obligatorios.'),
            'carbs_grams.max' => __('El valor de carbohidratos parece demasiado alto.'),
        ];
    }
}
