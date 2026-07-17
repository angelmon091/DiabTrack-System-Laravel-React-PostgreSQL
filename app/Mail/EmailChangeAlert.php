<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $newEmail;

    /**
     * Crea una nueva instancia del aviso de cambio de correo.
     */
    public function __construct($user, $newEmail)
    {
        $this->user = $user;
        $this->newEmail = $newEmail;
    }

    /**
     * Define el asunto y los datos generales del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DiabTrack - Aviso de Seguridad: Intento de Cambio de Correo',
        );
    }

    /**
     * Define la vista y el contenido del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-alert',
        );
    }
}
