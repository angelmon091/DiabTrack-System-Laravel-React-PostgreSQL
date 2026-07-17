<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa la información complementaria asociada con un usuario cuidador.
 */
class CaregiverProfile extends Model
{
    protected $fillable = ['user_id', 'gender', 'relationship'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
