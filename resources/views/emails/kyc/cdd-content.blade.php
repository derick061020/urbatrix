{{-- Contenido del aviso de CDD (se envuelve en emails.crm.wrapper). Ver App\Mail\CddMail. --}}
@php $kind = $kind ?? 'request'; $isInfo = $kind === 'info'; $note = trim((string) ($note ?? '')); @endphp

<tr>
  <td style="background-color:#0f2710;padding:26px 36px 22px 36px;">
    <p style="margin:0 0 7px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;font-weight:500;color:rgba(241,237,227,0.4);letter-spacing:0.24em;text-transform:uppercase;">{{ __('Conozca a su cliente (KYC)') }}</p>
    <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:22px;font-weight:300;color:#F1EDE3;letter-spacing:-0.02em;line-height:1.15;">
      @if($kind === 'cleared') {{ __('Ya puedes') }} <strong style="font-weight:700;">{{ __('completar tu reserva') }}</strong>
      @elseif($kind === 'approved') {{ __('Verificación KYC') }} <strong style="font-weight:700;">{{ __('aprobada') }}</strong>
      @elseif($kind === 'rejected') {{ __('Resultado de tu') }} <strong style="font-weight:700;">{{ __('verificación KYC') }}</strong>
      @elseif($isInfo) {{ __('Necesitamos') }} <strong style="font-weight:700;">{{ __('información adicional') }}</strong>
      @else {{ __('Completa tu') }} <strong style="font-weight:700;">{{ __('formulario KYC') }}</strong> @endif
    </p>
  </td>
</tr>
<tr>
  <td style="background-color:#ffffff;padding:32px 36px 8px 36px;">
    <p style="margin:0 0 6px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#4a4a46;line-height:1.6;">{{ __('Hola') }} <strong style="color:#1a1a18;font-weight:600;">{{ $name }}</strong>,</p>
    <p style="margin:0 0 16px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#6a6a64;line-height:1.65;">
      @if($kind === 'cleared')
        {{ __('Hemos completado la verificación previa de tu identidad. Ya puedes pagar el depósito de tu reserva: la mantenemos 48 horas.') }}
      @elseif($kind === 'approved')
        {{ __('El equipo de cumplimiento ha revisado tu formulario «Conozca a su cliente» y lo ha aprobado. El siguiente paso es la aprobación final de la venta y la firma del contrato de compraventa; tu asesor te escribirá.') }}
      @elseif($kind === 'rejected')
        {{ __('El equipo de cumplimiento ha revisado tu formulario «Conozca a su cliente» y, con la normativa del Banco Central de Aruba, no podemos continuar con la compra. Si tienes cualquier duda, escríbenos.') }}
      @elseif($isInfo)
        {{ __('Hemos revisado tu formulario «Conozca a su cliente» y el equipo de cumplimiento necesita información o documentación adicional para continuar con tu compra.') }}
      @else
        {{ __('La normativa del Banco Central de Aruba nos obliga a verificar la identidad de cada comprador, el origen de los fondos y el método de pago. Rellena tu formulario «Conozca a su cliente» y adjunta los documentos desde tu portal.') }}
      @endif
    </p>
    @if($note !== '')
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin:0 0 22px 0;">
        <tr><td style="background-color:#f7f5ef;border:1px solid #e7e2d6;border-radius:10px;padding:14px 16px;">
          <p style="margin:0 0 4px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:10px;font-weight:600;color:#6a6a64;letter-spacing:0.08em;text-transform:uppercase;">{{ __('Lo que necesitamos') }}</p>
          <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;color:#4a4a46;line-height:1.6;">{{ $note }}</p>
        </td></tr>
      </table>
    @endif
    @if($url)
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
        <td align="center" style="padding:4px 0 24px 0;">
          <a href="{{ $url }}" target="_blank" style="display:inline-block;background-color:#0b1c0a;color:#F1EDE3;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:0.02em;text-decoration:none;padding:14px 34px;border-radius:10px;">{{ $kind === 'cleared' ? __('Pagar mi reserva') : (in_array($kind, ['approved', 'rejected'], true) ? __('Ir a mi portal') : __('Ir a mi formulario')) }}</a>
        </td>
      </tr></table>
    @endif
  </td>
</tr>
