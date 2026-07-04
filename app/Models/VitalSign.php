<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo VitalSign
 * 
 * Representa los registros de signos vitales de un usuario, incluyendo niveles 
 * de glucosa, presión arterial y hemoglobina glicosilada (HbA1c).
 */
class VitalSign extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     * 
     * - glucose_level: Nivel de azúcar en sangre (mg/dL).
     * - systolic/diastolic: Presión arterial.
     * - hba1c: Promedio de glucosa de los últimos 3 meses (%).
     * - measurement_moment: Momento de la toma (ayunas, después de comer, etc).
     */
    protected $fillable = [
        'user_id',
        'glucose_level',
        'systolic',
        'diastolic',
        'heart_rate',
        'weight',
        'hba1c',
        'measurement_moment',
        'stress_level',
        'notes',
    ];

    /**
     * Obtiene el usuario al que pertenece el registro.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar registros por un usuario específico.
     */
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener solo los registros realizados el día de hoy.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('created_at', \Carbon\Carbon::today());
    }

    /**
     * Rango glucémico objetivo por defecto (ayunas). Si el perfil del paciente
     * conserva estos valores, se considera que el médico NO ha personalizado el
     * rango, por lo que se ignora y se usan los umbrales clínicos por momento.
     */
    public const GLUCOSE_DEFAULT_MIN = 70;
    public const GLUCOSE_DEFAULT_MAX = 130;

    /**
     * Clasifica una lectura de glucosa según el MOMENTO de medición, usando los
     * mismos umbrales clínicos que la IA emplea para generar los tips.
     *
     * Devuelve: 'baja' | 'normal' | 'elevada' | null (si no hay valor).
     *
     * Umbrales clínicos por momento (metas de manejo ADA para diabéticos):
     *  - Ayunas / Antes de Comer : Normal 70–130 | Elevada >130  (meta preprandial ADA 80–130)
     *  - Después de Comer (1–2h) : Normal <180   | Elevada ≥180
     *  - Al Dormir               : Normal 70–150 | Elevada >150
     *  - <70 en cualquier momento → 'baja' (piso universal de hipoglucemia)
     *
     * El rango del médico (target min/max) se interpreta como un rango EN AYUNAS
     * y SOLO se aplica si fue personalizado (distinto de 70/180); de lo contrario
     * se ignora en favor de los umbrales clínicos.
     */
    public static function clasificarGlucosa(?int $valor, ?string $momento, ?int $targetMin = null, ?int $targetMax = null): ?string
    {
        if ($valor === null || $valor <= 0) {
            return null;
        }

        if ($valor < 70) {
            return 'baja';
        }

        $medicoPersonalizo = $targetMin !== null && $targetMax !== null
            && !($targetMin === self::GLUCOSE_DEFAULT_MIN && $targetMax === self::GLUCOSE_DEFAULT_MAX);

        $clasificarPorRangoMedico = function () use ($valor, $targetMin, $targetMax) {
            if ($valor < $targetMin) return 'baja';
            return $valor <= $targetMax ? 'normal' : 'elevada';
        };

        return match ($momento) {
            'Ayunas', 'Antes de Comer' => $medicoPersonalizo ? $clasificarPorRangoMedico() : ($valor <= 130 ? 'normal' : 'elevada'),
            'Después de Comer' => $valor < 180 ? 'normal' : 'elevada',
            'Al Dormir'        => $valor <= 150 ? 'normal' : 'elevada',
            default            => $medicoPersonalizo
                ? $clasificarPorRangoMedico()
                : (($valor >= self::GLUCOSE_DEFAULT_MIN && $valor <= self::GLUCOSE_DEFAULT_MAX) ? 'normal' : 'elevada'),
        };
    }

    /**
     * Mapea el estado clínico ('baja'|'normal'|'elevada') a metadatos de UI
     * (color del tema, texto y icono) para mantener las vistas consistentes.
     */
    public static function glucoseStatusUi(?string $estado): array
    {
        return match ($estado) {
            'elevada' => ['color' => 'danger',  'label' => 'Nivel Elevado', 'badge' => 'Alto',     'icon' => 'fa-triangle-exclamation'],
            'baja'    => ['color' => 'warning', 'label' => 'Nivel Bajo',    'badge' => 'Bajo',     'icon' => 'fa-droplet-slash'],
            'normal'  => ['color' => 'success', 'label' => 'En rango',      'badge' => 'En Rango', 'icon' => 'fa-circle-check'],
            default   => ['color' => 'secondary', 'label' => 'Sin datos',   'badge' => '--',       'icon' => 'fa-circle-question'],
        };
    }

    /**
     * Método de arranque del modelo.
     */
    protected static function booted()
    {
        static::saved(function ($vitalSign) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_metrics_{$vitalSign->user_id}_v2");
        });

        static::deleted(function ($vitalSign) {
            \Illuminate\Support\Facades\Cache::forget("dashboard_metrics_{$vitalSign->user_id}_v2");
        });
    }
}
