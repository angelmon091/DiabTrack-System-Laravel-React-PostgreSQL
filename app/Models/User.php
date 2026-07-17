<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailCodeNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * Modelo de usuario del sistema.
 *
 * Representa a los usuarios del sistema, incluyendo pacientes y administradores.
 * Contiene información básica del usuario y relaciones con sus perfiles, roles y actividades.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'is_admin' => false,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'avatar',
        'provider',
        'timezone',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtiene los atributos que deben ser convertidos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function patientProfile()
    {
        return $this->hasOne(PatientProfile::class);
    }

    public function caregiverProfile()
    {
        return $this->hasOne(CaregiverProfile::class);
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    /**
     * Consejos de salud diarios del paciente.
     */
    public function dailyTips()
    {
        return $this->hasMany(DailyTip::class, 'user_id');
    }

    /**
     * Pacientes vinculados a este cuidador/médico.
     */
    public function linkedPatients()
    {
        return $this->belongsToMany(User::class, 'patient_links', 'linked_user_id', 'patient_id')
            ->wherePivot('status', 'active')
            ->withPivot('relationship');
    }

    /**
     * Cuidadores/médicos vinculados a este paciente.
     */
    public function linkedCarers()
    {
        return $this->belongsToMany(User::class, 'patient_links', 'patient_id', 'linked_user_id')
            ->wherePivot('status', 'active')
            ->withPivot('relationship');
    }

    public function isPatient(): bool
    {
        return $this->hasRole('paciente');
    }

    public function isCaregiver(): bool
    {
        return $this->hasRole('cuidador');
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('médico');
    }

    /**
     * Verifica si el usuario ya completó el onboarding.
     */
    public function hasCompletedOnboarding(): bool
    {
        return $this->roles()->exists();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return (bool) ($this->is_admin ?? false);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'symptom_user')
            ->withPivot('logged_at')
            ->withTimestamps();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function nutritionLogs()
    {
        return $this->hasMany(NutritionLog::class);
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function emailVerificationCode()
    {
        return $this->hasOne(EmailVerificationCode::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $code = (string) random_int(100000, 999999);

        $this->emailVerificationCode()->updateOrCreate([], [
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        $this->notify(new VerifyEmailCodeNotification($code));
    }

    /**
     * Envía la notificación de restablecimiento de contraseña.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
