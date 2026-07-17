<?php

namespace App\Models;

use App\Services\DashboardMetricsService;
use Illuminate\Database\Eloquent\Model;

class DailyTip extends Model
{
    protected $attributes = [
        'status' => 'approved',
    ];

    protected $fillable = [
        'user_id',
        'tip_text',
        'status',
    ];

    /**
     * Obtiene el paciente (usuario) dueño del consejo.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::saved(function (DailyTip $tip) {
            DashboardMetricsService::forgetUserCache($tip->user_id);
        });

        static::deleted(function (DailyTip $tip) {
            DashboardMetricsService::forgetUserCache($tip->user_id);
        });
    }
}
