<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo PatientProfile
 *
 * Contiene la información clínica y biométrica base del usuario, esencial
 * para personalizar las metas de salud y rangos de glucosa.
 */
class PatientProfile extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     *
     * - diabetes_type: Condición glucémica declarada por el usuario.
     * - target_glucose_min/max: Rango glucémico ideal personalizado por el médico.
     * - weight/height: Datos físicos para cálculo de IMC y metabolismo.
     */
    protected $fillable = [
        'user_id',
        'birth_date',
        'diabetes_type',
        'weight',
        'height',
        'gender',
        'target_glucose_min',
        'target_glucose_max',
    ];

    /**
     * Obtiene el usuario dueño de este perfil médico.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
