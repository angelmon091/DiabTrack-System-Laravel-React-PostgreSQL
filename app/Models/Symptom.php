<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Symptom
 *
 * Representa los síntomas que pueden ser registrados por los usuarios.
 * Permite llevar un registro de los síntomas experimentados y su frecuencia.
 */
class Symptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'symptom_user')
            ->withPivot('logged_at')
            ->withTimestamps();
    }
}
