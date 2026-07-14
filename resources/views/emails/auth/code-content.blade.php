{{-- Contenido interno del correo de código de verificación (se envuelve en emails.crm.wrapper). --}}
@php
    $name    = $name ?? '';
    $code    = $code ?? '';
    $heading = $heading ?? 'Tu código de verificación';
    $intro   = $intro ?? 'Usa el siguiente código para continuar.';
    $minutes = $minutes ?? 60;
@endphp

<!-- HERO -->
<tr>
  <td style="background-color:#053330;padding:26px 36px 22px 36px;">
    <p style="margin:0 0 7px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;font-weight:500;color:rgba(255,255,255,0.4);letter-spacing:0.24em;text-transform:uppercase;">{{ __('Verificación de seguridad') }}</p>
    <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:22px;font-weight:300;color:#ffffff;letter-spacing:-0.02em;line-height:1.15;">{!! $heading !!}</p>
  </td>
</tr>

<!-- BODY -->
<tr>
  <td style="background-color:#ffffff;padding:32px 36px 8px 36px;">
    @if($name)
      <p style="margin:0 0 6px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#525866;line-height:1.6;">{{ __('Hola') }} <strong style="color:#171717;font-weight:600;">{{ $name }}</strong>,</p>
    @endif
    <p style="margin:0 0 24px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#525866;line-height:1.65;">{{ $intro }}</p>

    <!-- CODE BOX -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
      <tr>
        <td align="center" style="background-color:#074540;padding:22px 16px;border-radius:10px;">
          <p style="margin:0 0 8px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;font-weight:500;color:#f97066;letter-spacing:0.2em;text-transform:uppercase;">{{ __('Tu código') }}</p>
          <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:38px;font-weight:600;color:#ffffff;letter-spacing:0.42em;line-height:1;padding-left:0.42em;">{{ $code }}</p>
        </td>
      </tr>
    </table>

    <p style="margin:20px 0 0 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:12px;color:#99a0ae;line-height:1.6;">{{ __('Este código caduca en') }} <strong style="color:#525866;">{{ $minutes }} minutos</strong>{{ __('. Si no solicitaste esto, ignora este mensaje y tu cuenta seguirá segura.') }}</p>
  </td>
</tr>

<!-- SPACER -->
<tr><td style="background-color:#ffffff;padding:0 36px 28px 36px;font-size:0;line-height:0;">&nbsp;</td></tr>
