<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

/**
 * Correo que se envía al cliente cuando el administrador revisa su KYC:
 *  - status "approved": identidad verificada, sin acción requerida.
 *  - status "rejected": hubo un problema con los documentos y debe volver a subirlos.
 * Reutiliza el layout de marca emails.crm.wrapper.
 */
class KycStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $name      Nombre del destinatario.
     * @param string $status    "approved" | "rejected".
     * @param string $reason    Motivo del rechazo (solo para "rejected"), opcional.
     * @param string $actionUrl Enlace para volver a subir documentos (solo "rejected").
     */
    public function __construct(
        public string $name,
        public string $status,
        public string $reason = '',
        public string $actionUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        $project = config('company.project');

        $subject = $this->status === 'approved'
            ? 'Tu verificación de identidad fue aprobada · ' . $project
            : 'Necesitamos que vuelvas a subir tus documentos · ' . $project;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $isApproved = $this->status === 'approved';

        $inner = View::make('emails.kyc.status-content', [
            'name'      => $this->name,
            'status'    => $this->status,
            'reason'    => $this->reason,
            'actionUrl' => $this->actionUrl,
        ])->render();

        $html = View::make('emails.crm.wrapper', [
            'docLabel'  => $isApproved ? 'KYC · Aprobado' : 'KYC · Acción requerida',
            'preheader' => $isApproved
                ? 'Tu identidad fue verificada correctamente.'
                : 'Hubo un problema con tus documentos. Vuelve a subirlos para continuar.',
            'content'   => $inner,
        ])->render();

        return new Content(htmlString: $html);
    }
}
