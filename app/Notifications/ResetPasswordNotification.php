<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Crea una nueva notificación para restablecer la contraseña.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Define los canales utilizados para entregar la notificación.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construye el contenido del correo de recuperación.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(Lang::get('DiabTrack - Restablecer Contraseña'))
            ->view('emails.reset-password', [
                'url' => $url,
                'name' => $notifiable->name,
            ]);
    }
}
