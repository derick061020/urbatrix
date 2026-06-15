{{-- Contenido interno del correo de invitación / activación de cuenta (se envuelve en emails.crm.wrapper). --}}
@php
    $name      = $name ?? '';
    $actionUrl = $actionUrl ?? '#';
    $unitName  = $unitName ?? '';
    $days      = $days ?? 7;
    $project   = config('company.project', 'Makai Residences');
@endphp

<!-- HERO -->
<tr>
  <td style="background-color:#0f2710;padding:26px 36px 22px 36px;">
    <p style="margin:0 0 7px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;font-weight:500;color:rgba(241,237,227,0.4);letter-spacing:0.24em;text-transform:uppercase;">{{ __('Activación de cuenta') }}</p>
    <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:22px;font-weight:300;color:#F1EDE3;letter-spacing:-0.02em;line-height:1.15;">{{ __('Te damos la') }} <strong style="font-weight:700;">{{ __('bienvenida') }}</strong></p>
  </td>
</tr>

<!-- BODY -->
<tr>
  <td style="background-color:#ffffff;padding:32px 36px 8px 36px;">
    @if($name)
      <p style="margin:0 0 6px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#4a4a46;line-height:1.6;">{{ __('Hola') }} <strong style="color:#1a1a18;font-weight:600;">{{ $name }}</strong>,</p>
    @endif
    <p style="margin:0 0 24px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#6a6a64;line-height:1.65;">
      Nuestro equipo ha registrado una reserva a tu nombre@if($unitName) para <strong style="color:#1a1a18;font-weight:600;">{{ $unitName }}</strong>@endif.
      Para acceder a tu portal y dar seguimiento al proceso, solo necesitas activar tu cuenta y crear una contraseña.
    </p>

    <!-- CTA BUTTON -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
      <tr>
        <td align="center" style="padding:4px 0 8px 0;">
          <a href="{{ $actionUrl }}" target="_blank" style="display:inline-block;background-color:#0b1c0a;color:#F1EDE3;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:0.02em;text-decoration:none;padding:14px 34px;border-radius:10px;">{{ __('Activar mi cuenta') }}</a>
        </td>
      </tr>
    </table>

    <p style="margin:20px 0 0 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:12px;color:#8a8a84;line-height:1.6;">{{ __('Este enlace caduca en') }} <strong style="color:#4a4a46;">{{ $days }} días</strong>{{ __('. Si no esperabas esta invitación, puedes ignorar este mensaje de forma segura.') }}</p>

    <p style="margin:18px 0 0 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:11px;color:#a8a8a2;line-height:1.6;">{{ __('¿El botón no funciona? Copia y pega este enlace en tu navegador:') }}<br><span style="color:#6a6a64;word-break:break-all;">{{ $actionUrl }}</span></p>
  </td>
</tr>

<!-- SPACER -->
<tr><td style="background-color:#ffffff;padding:0 36px 28px 36px;font-size:0;line-height:0;">&nbsp;</td></tr>
