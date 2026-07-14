@php $c = config('company'); @endphp
<x-mail-shell
    eyebrow="Comprobante de pago · {{ $d['proyecto'] }}"
    title="Tu comprobante está <span style='font-weight:600;'>{{ __('listo') }}</span>"
    doc-label="Comprobante · E-11">

    <!-- SALUDO + INTRO -->
    <tr>
      <td style="background-color:#ffffff;padding:32px 36px 24px 36px;">
        <p style="margin:0 0 6px 0;font-size:13px;color:#525866;line-height:1.6;">Estimado/a <strong style="color:#171717;font-weight:600;">{{ $d['nombre_cliente'] }}</strong>,</p>
        <p style="margin:0;font-size:13px;color:#525866;line-height:1.65;">{{ __('Confirmamos la recepción de tu pago del') }} <strong style="color:#171717;">{{ $d['fecha_pago'] }}</strong> {{ __('para tu') }} <strong style="color:#171717;">Unidad {{ $d['unidad'] }}</strong> en <strong style="color:#171717;">{{ $d['proyecto'] }}</strong>{{ __('. A continuación encontrarás el resumen de la operación.') }}</p>
      </td>
    </tr>

    <!-- SECTION LABEL -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 10px 36px;">
        <p style="margin:0;font-size:9px;font-weight:600;color:#99a0ae;letter-spacing:0.22em;text-transform:uppercase;">{{ __('Resumen del pago') }}</p>
      </td>
    </tr>

    <!-- SUMMARY TABLE -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 0 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top:1px solid #eaecf0;">
          <tr>
            <td style="width:50%;padding:13px 16px 13px 0;border-bottom:1px solid #eaecf0;vertical-align:top;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Unidad') }}</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#171717;">{{ $d['unidad'] }}</p>
            </td>
            <td style="width:50%;padding:13px 0 13px 16px;border-bottom:1px solid #eaecf0;vertical-align:top;border-left:1px solid #eaecf0;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Concepto') }}</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#171717;">{{ $d['concepto_pago'] }}</p>
            </td>
          </tr>
          <tr>
            <td style="width:50%;padding:13px 16px 13px 0;border-bottom:1px solid #eaecf0;vertical-align:top;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Método') }}</p>
              <p style="margin:0;font-size:12px;font-weight:500;color:#171717;">{{ $d['metodo_pago'] }}</p>
            </td>
            <td style="width:50%;padding:13px 0 13px 16px;border-bottom:1px solid #eaecf0;vertical-align:top;border-left:1px solid #eaecf0;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Referencia') }}</p>
              <p style="margin:0;font-size:12px;font-weight:500;color:#171717;">{{ $d['referencia'] }}</p>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="background-color:#074540;padding:18px 16px;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#f97066;letter-spacing:0.14em;text-transform:uppercase;">Monto recibido · {{ $d['fecha_pago'] }}</p>
              <p style="margin:0;font-size:26px;font-weight:600;color:#ffffff;letter-spacing:-0.01em;">{{ $d['moneda'] }} {{ $d['monto'] }}</p>
              <p style="margin:5px 0 0 0;font-size:9px;font-style:italic;color:rgba(255,255,255,0.38);">{{ $d['monto_en_letras'] }}</p>
            </td>
          </tr>
          <tr>
            <td style="width:50%;padding:12px 16px 12px 0;border-bottom:1px solid #eaecf0;vertical-align:top;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.12em;text-transform:uppercase;">{{ __('Total pagado a la fecha') }}</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#171717;">{{ $d['moneda'] }} {{ $d['total_pagado'] }}</p>
            </td>
            <td style="width:50%;padding:12px 0 12px 16px;border-bottom:1px solid #eaecf0;vertical-align:top;border-left:1px solid #eaecf0;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.12em;text-transform:uppercase;">{{ __('Saldo pendiente') }}</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#171717;">{{ $d['moneda'] }} {{ $d['saldo_pendiente'] }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- CTA -->
    <tr>
      <td style="background-color:#ffffff;padding:24px 36px 8px 36px;">
        <p style="margin:0 0 12px 0;font-size:12px;color:#525866;line-height:1.65;">{{ __('El comprobante completo en PDF está disponible en tu portal. Guárdalo como respaldo oficial de tu pago.') }}</p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="background-color:#074540;">
              <a href="{{ $d['link_comprobante'] }}" style="display:inline-block;padding:11px 24px;font-size:11px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.1em;text-transform:uppercase;">{{ __('Descargar comprobante PDF') }}</a>
            </td>
            <td style="width:16px;"></td>
            <td style="vertical-align:middle;">
              <a href="{{ $d['link_portal'] }}" style="font-size:11px;color:#99a0ae;text-decoration:underline;">{{ __('Ver mi portal') }}</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- ADVISOR NOTE -->
    <tr>
      <td style="background-color:#ffffff;padding:20px 36px 32px 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td style="padding:0 0 0 20px;border-left:2px solid #074540;">
              <p style="margin:0 0 2px 0;font-size:12px;color:#525866;line-height:1.6;">{{ __('Para cualquier consulta sobre este pago escríbenos a') }} <strong style="color:#171717;font-weight:600;">{{ $c['support_email'] }}</strong>@if($d['nombre_asesor'] !== '—') o contacta a tu asesor <strong style="color:#171717;font-weight:600;">{{ $d['nombre_asesor'] }}</strong>@endif.</p>
              <p style="margin:4px 0 0 0;font-size:12px;color:#525866;">{{ __('Gracias por tu confianza.') }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

</x-mail-shell>
