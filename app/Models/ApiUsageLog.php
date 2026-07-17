<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa la trazabilidad de cada consumo realizado a los proveedores de inteligencia artificial.
 */
class ApiUsageLog extends Model
{
    protected $fillable = [
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'estimated_cost_usd',
        'daily_tip_id',
        'patient_id',
    ];

    protected $casts = [
        'estimated_cost_usd' => 'decimal:6',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
    ];

    // Cost per million tokens (USD)
    private const RATES = [
        'anthropic' => ['input' => 0.80,  'output' => 4.00],
        'gemini' => ['input' => 0.075, 'output' => 0.30],
    ];

    public static function calculateCost(string $provider, int $inputTokens, int $outputTokens): float
    {
        $rates = self::RATES[$provider] ?? self::RATES['anthropic'];

        return ($inputTokens / 1_000_000 * $rates['input'])
             + ($outputTokens / 1_000_000 * $rates['output']);
    }

    public function dailyTip(): BelongsTo
    {
        return $this->belongsTo(DailyTip::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
