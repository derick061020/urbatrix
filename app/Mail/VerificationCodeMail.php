<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

/**
 * Correo de código de verificación de 6 dígitos para registro y restablecimiento
 * de contraseña. Reutiliza el layout de marca emails.crm.wrapper.
 */
class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $code     Código de 6 dígitos.
     * @param string $purpose  'register' | 'reset'
     * @param string $name     Nombre del destinatario (opcional).
     * @param int    $minutes  Minutos de validez del código.
     */
    public function __construct(
        public string $code,
        public string $purpose = 'register',
        public string $name = '',
        public int $minutes = 60,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'reset'
            ? 'Tu código para restablecer la contraseña — ' . config('company.project')
            : 'Verifica tu correo — ' . config('company.project');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $isReset = $this->purpose === 'reset';

        $inner = View::make('emails.auth.code-content', [
            'name'    => $this->name,
            'code'    => $this->code,
            'minutes' => $this->minutes,
            'heading' => $isReset
                ? 'Restablece tu <strong style="font-weight:700;">contraseña</strong>'
                : 'Verifica tu <strong style="font-weight:700;">correo electrónico</strong>',
            'intro'   => $isReset
                ? 'Recibimos una solicitud para restablecer tu contraseña. Ingresa este código para confirmar que eres tú y crear una nueva.'
                : 'Gracias por registrarte. Ingresa este código para verificar tu correo y completar tu registro.',
        ])->render();

        $html = View::make('emails.crm.wrapper', [
            'docLabel'  => $isReset ? 'Seguridad · Contraseña' : 'Seguridad · Registro',
            'preheader' => 'Tu código de verificación: ' . $this->code,
            'content'   => $inner,
        ])->render();

        return new Content(htmlString: $html);
    }
}
