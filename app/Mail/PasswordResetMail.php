<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(
        public readonly Usuario $usuario,
        string $token
    ) {
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'correo' => $usuario->correo,
        ], false));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer contraseña — BovWeight CR',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
        );
    }
}
