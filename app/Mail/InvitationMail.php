<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

/**
 * Correo de invitación para que un cliente recién registrado por el equipo
 * (desde "Nueva reserva") active su cuenta y cree su contraseña.
 * Reutiliza el layout de marca emails.crm.wrapper.
 */
class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $name      Nombre del destinatario.
     * @param string $actionUrl Enlace de activación (set-password) con token firmado.
     * @param string $unitName  Unidad asociada a la reserva (contexto, opcional).
     * @param int    $days       Días de validez del enlace.
     */
    public function __construct(
        public string $name,
        public string $actionUrl,
        public string $unitName = '',
        public int $days = 7,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te damos la bienvenida — activa tu cuenta · ' . config('company.project'),
        );
    }

    public function content(): Content
    {
        $inner = View::make('emails.auth.invitation-content', [
            'name'      => $this->name,
            'actionUrl' => $this->actionUrl,
            'unitName'  => $this->unitName,
            'days'      => $this->days,
        ])->render();

        $html = View::make('emails.crm.wrapper', [
            'docLabel'  => 'Bienvenida · Activación',
            'preheader' => 'Activa tu cuenta y crea tu contraseña para seguir tu reserva.',
            'content'   => $inner,
        ])->render();

        return new Content(htmlString: $html);
    }
}
