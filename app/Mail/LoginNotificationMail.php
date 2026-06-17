<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $ip,
        public string $fechaHora,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nuevo inicio de sesión — BovWeight CR');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login_notification');
    }
}
