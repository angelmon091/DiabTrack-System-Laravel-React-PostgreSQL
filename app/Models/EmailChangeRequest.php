<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa una solicitud temporal para modificar el correo de acceso.
 */
class EmailChangeRequest extends Model
{
    protected $fillable = ['user_id', 'new_email', 'token', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
