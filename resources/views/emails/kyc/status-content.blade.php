{{-- Contenido interno del correo de estado de KYC (se envuelve en emails.crm.wrapper). --}}
@php
    $name       = $name ?? '';
    $status     = $status ?? 'approved';
    $reason     = trim((string) ($reason ?? ''));
    $actionUrl  = $actionUrl ?? '';
    $isApproved = $status === 'approved';
@endphp

<!-- HERO -->
<tr>
  <td style="background-color:#053330;padding:26px 36px 22px 36px;">
    <p style="margin:0 0 7px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;font-weight:500;color:rgba(255,255,255,0.4);letter-spacing:0.24em;text-transform:uppercase;">{{ __('Verificación de identidad') }}</p>
    @if($isApproved)
      <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:22px;font-weight:300;color:#ffffff;letter-spacing:-0.02em;line-height:1.15;">{{ __('Tu identidad fue') }} <strong style="font-weight:700;">{{ __('aprobada') }}</strong></p>
    @else
      <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:22px;font-weight:300;color:#ffffff;letter-spacing:-0.02em;line-height:1.15;">{{ __('Necesitamos') }} <strong style="font-weight:700;">{{ __('revisar tus documentos') }}</strong></p>
    @endif
  </td>
</tr>

<!-- BODY -->
<tr>
  <td style="background-color:#ffffff;padding:32px 36px 8px 36px;">
    @if($name)
      <p style="margin:0 0 6px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#525866;line-height:1.6;">{{ __('Hola') }} <strong style="color:#171717;font-weight:600;">{{ $name }}</strong>,</p>
    @endif

    @if($isApproved)
      <p style="margin:0 0 24px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#525866;line-height:1.65;">
        ¡Buenas noticias! Hemos revisado y <strong style="color:#171717;font-weight:600;">aprobado</strong> tu verificación de identidad (KYC).
        No necesitas hacer nada más por ahora; te avisaremos de los próximos pasos de tu proceso.
      </p>
    @else
      <p style="margin:0 0 16px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#525866;line-height:1.65;">
        Revisamos los documentos que enviaste para tu verificación de identidad (KYC) y encontramos un inconveniente,
        por lo que necesitamos que <strong style="color:#171717;font-weight:600;">vuelvas a subirlos</strong> para poder continuar.
      </p>

      @if($reason !== '')
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin:0 0 22px 0;">
          <tr>
            <td style="background-color:#fde8e8;border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:14px 16px;">
              <p style="margin:0 0 4px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:10px;font-weight:600;color:#d92d20;letter-spacing:0.08em;text-transform:uppercase;">{{ __('Motivo') }}</p>
              <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#b42318;line-height:1.6;">{{ $reason }}</p>
            </td>
          </tr>
        </table>
      @endif

      @if($actionUrl)
        <!-- CTA BUTTON -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td align="center" style="padding:4px 0 8px 0;">
              <a href="{{ $actionUrl }}" target="_blank" style="display:inline-block;background-color:#074540;color:#ffffff;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:0.02em;text-decoration:none;padding:14px 34px;border-radius:10px;">{{ __('Volver a subir documentos') }}</a>
            </td>
          </tr>
        </table>

        <p style="margin:18px 0 0 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:11px;color:#99a0ae;line-height:1.6;">{{ __('¿El botón no funciona? Copia y pega este enlace en tu navegador:') }}<br><span style="color:#525866;word-break:break-all;">{{ $actionUrl }}</span></p>
      @endif
    @endif
  </td>
</tr>

<!-- SPACER -->
<tr><td style="background-color:#ffffff;padding:0 36px 28px 36px;font-size:0;line-height:0;">&nbsp;</td></tr>
