<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailChange extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public $newEmail;

    public $user;

    /**
     * Crea una nueva instancia del mensaje de verificación.
     */
    public function __construct($user, $token, $newEmail)
    {
        $this->user = $user;
        $this->token = $token;
        $this->newEmail = $newEmail;
    }

    /**
     * Define el asunto y los datos generales del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DiabTrack - Verificación de Cambio de Correo',
        );
    }

    /**
     * Define la vista y el contenido del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email-change',
        );
    }

    /**
     * Devuelve los archivos adjuntos del mensaje, cuando existan.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
