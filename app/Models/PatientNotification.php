<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa una notificación interna destinada a un paciente.
 */
class PatientNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'body', 'icon', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public static function createSystem(int $userId, string $title, string $body, string $icon = 'fa-solid fa-link'): self
    {
        return self::create(compact('userId', 'title', 'body', 'icon') + ['user_id' => $userId, 'type' => 'system']);
    }

    public static function createAiReminder(int $userId, string $title, string $body): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'ai_reminder',
            'title' => $title,
            'body' => $body,
            'icon' => 'fa-solid fa-robot',
        ]);
    }
}
