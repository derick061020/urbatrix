<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

/**
 * Aviso al comprador del proceso de diligencia debida (CDD):
 *  - kind "request": tiene que rellenar su formulario KYC en el portal.
 *  - kind "info":    cumplimiento pide información adicional ($note).
 *  - kind "cleared": verificación previa superada; ya puede pagar la reserva.
 *  - kind "approved" / "rejected": decisión de cumplimiento sobre su KYC.
 */
class CddMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $kind,
        public string $note = '',
        public string $url = '',
    ) {}

    public function envelope(): Envelope
    {
        $project = config('company.project');

        return new Envelope(subject: match ($this->kind) {
            'info'    => 'Necesitamos algo más para tu verificación · ' . $project,
            'cleared' => 'Ya puedes completar tu reserva · ' . $project,
            'approved' => 'Tu verificación KYC está aprobada · ' . $project,
            'rejected' => 'Sobre tu verificación KYC · ' . $project,
            default   => 'Completa tu formulario «Conozca a su cliente» · ' . $project,
        });
    }

    public function content(): Content
    {
        $inner = View::make('emails.kyc.cdd-content', [
            'name' => $this->name, 'kind' => $this->kind, 'note' => $this->note, 'url' => $this->url,
        ])->render();

        $html = View::make('emails.crm.wrapper', [
            'docLabel'  => 'KYC · ' . (['info' => 'Información adicional', 'cleared' => 'Verificación superada', 'approved' => 'Aprobado', 'rejected' => 'Resultado'][$this->kind] ?? 'Formulario'),
            'preheader' => match ($this->kind) {
                'info'    => 'El equipo de cumplimiento necesita información adicional.',
                'cleared' => 'Hemos verificado tu identidad: ya puedes pagar el depósito de tu reserva.',
                'approved' => 'Cumplimiento ha aprobado tu verificación KYC.',
                'rejected' => 'Cumplimiento ha revisado tu verificación KYC.',
                default   => 'Rellena tu formulario KYC en tu portal para continuar con tu compra.',
            },
            'content'   => $inner,
        ])->render();

        return new Content(htmlString: $html);
    }
}
