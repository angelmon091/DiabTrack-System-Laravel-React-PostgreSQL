<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Role
 *
 * Define los roles dentro del sistema, permitiendo asignar permisos específicos
 * a diferentes tipos de usuarios (ej. Médico, Paciente, Admin).
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
