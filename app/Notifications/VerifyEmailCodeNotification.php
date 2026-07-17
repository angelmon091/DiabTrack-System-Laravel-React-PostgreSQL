<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('DiabTrack - Código de verificación')
            ->view('emails.verify-email-code', [
                'code' => $this->code,
                'name' => $notifiable->name,
                'expiresInMinutes' => 10,
            ]);
    }
}
