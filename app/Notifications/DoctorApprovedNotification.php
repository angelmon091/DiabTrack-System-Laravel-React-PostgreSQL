<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorApprovedNotification extends Notification
{
    use Queueable;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('DiabTrack - Perfil médico aprobado')
            ->view('emails.doctor-approved', [
                'name' => $notifiable->name,
                'dashboardUrl' => route('doctor.dashboard'),
            ]);
    }
}
