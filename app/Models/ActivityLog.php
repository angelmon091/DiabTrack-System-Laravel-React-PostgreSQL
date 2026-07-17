<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo ActivityLog
 *
 * Registra la actividad física realizada por el usuario, permitiendo rastrear
 * la duración, el tipo de ejercicio y el impacto en su energía.
 */
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     *
     * - activity_type: Tipo de ejercicio (caminar, correr, yoga, etc).
     * - duration_minutes: Tiempo total invertido en la actividad.
     * - intensity: Nivel de esfuerzo percibido (bajo, medio, alto).
     * - energy_level: Estado de ánimo/energía después de la actividad.
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'duration_minutes',
        'intensity',
        'start_time',
        'end_time',
        'energy_level',
    ];

    /**
     * Obtiene el usuario al que pertenece el registro de actividad.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar actividades por un usuario específico.
     */
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener las actividades registradas el día de hoy.
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
        static::saved(function ($activityLog) {
            Cache::forget("dashboard_metrics_{$activityLog->user_id}_v2");
        });

        static::deleted(function ($activityLog) {
            Cache::forget("dashboard_metrics_{$activityLog->user_id}_v2");
        });
    }
}
