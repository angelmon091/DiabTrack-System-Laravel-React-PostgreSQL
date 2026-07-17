<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Almacena de forma protegida el código temporal de verificación de correo.
 */
class EmailVerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'code_hash',
        'attempts',
        'expires_at',
        'sent_at',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
