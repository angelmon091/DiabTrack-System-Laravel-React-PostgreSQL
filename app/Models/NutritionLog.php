<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo NutritionLog
 *
 * Almacena el historial de ingesta de alimentos del usuario, centrándose en el
 * conteo de carbohidratos y la relación con la medicación tomada.
 */
class NutritionLog extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     *
     * - meal_type: Tipo de comida (desayuno, almuerzo, cena, snack).
     * - carbs_grams: Cantidad de carbohidratos consumidos (crucial para diabéticos).
     * - food_categories: Arreglo de tipos de alimentos consumidos (frutas, lácteos, etc).
     * - medication_taken: Indica si se administró insulina o fármacos tras la comida.
     */
    protected $fillable = [
        'user_id',
        'meal_type',
        'carbs_grams',
        'consumed_at',
        'food_categories',
        'medication_taken',
        'medication_dose',
    ];

    /**
     * Atributos que deben ser convertidos (cast) a tipos nativos.
     */
    protected $casts = [
        'food_categories' => 'array',
    ];

    /**
     * Obtiene el usuario que registró esta información nutricional.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar registros por usuario.
     */
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener registros del día en curso.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Método de arranque del modelo.
     */
    protected static function booted()
    {
        static::saved(function ($nutritionLog) {
            Cache::forget("dashboard_metrics_{$nutritionLog->user_id}_v2");
        });

        static::deleted(function ($nutritionLog) {
            Cache::forget("dashboard_metrics_{$nutritionLog->user_id}_v2");
        });
    }
}
