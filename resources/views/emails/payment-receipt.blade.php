@php $c = config('company'); @endphp
<x-mail-shell
    eyebrow="Comprobante de pago · {{ $d['proyecto'] }}"
    title="Tu comprobante está <span style='font-weight:600;'>listo</span>"
    doc-label="Comprobante · E-11">

    <!-- SALUDO + INTRO -->
    <tr>
      <td style="background-color:#ffffff;padding:32px 36px 24px 36px;">
        <p style="margin:0 0 6px 0;font-size:13px;color:#4a4a46;line-height:1.6;">Estimado/a <strong style="color:#1a1a18;font-weight:600;">{{ $d['nombre_cliente'] }}</strong>,</p>
        <p style="margin:0;font-size:13px;color:#6a6a64;line-height:1.65;">Confirmamos la recepción de tu pago del <strong style="color:#1a1a18;">{{ $d['fecha_pago'] }}</strong> para tu <strong style="color:#1a1a18;">Unidad {{ $d['unidad'] }}</strong> en <strong style="color:#1a1a18;">{{ $d['proyecto'] }}</strong>. A continuación encontrarás el resumen de la operación.</p>
      </td>
    </tr>

    <!-- SECTION LABEL -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 10px 36px;">
        <p style="margin:0;font-size:9px;font-weight:600;color:#8a8a84;letter-spacing:0.22em;text-transform:uppercase;">Resumen del pago</p>
      </td>
    </tr>

    <!-- SUMMARY TABLE -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 0 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top:1px solid #e8e7e3;">
          <tr>
            <td style="width:50%;padding:13px 16px 13px 0;border-bottom:1px solid #e8e7e3;vertical-align:top;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.14em;text-transform:uppercase;">Unidad</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#1a1a18;">{{ $d['unidad'] }}</p>
            </td>
            <td style="width:50%;padding:13px 0 13px 16px;border-bottom:1px solid #e8e7e3;vertical-align:top;border-left:1px solid #e8e7e3;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.14em;text-transform:uppercase;">Concepto</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#1a1a18;">{{ $d['concepto_pago'] }}</p>
            </td>
          </tr>
          <tr>
            <td style="width:50%;padding:13px 16px 13px 0;border-bottom:1px solid #e8e7e3;vertical-align:top;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.14em;text-transform:uppercase;">Método</p>
              <p style="margin:0;font-size:12px;font-weight:500;color:#1a1a18;">{{ $d['metodo_pago'] }}</p>
            </td>
            <td style="width:50%;padding:13px 0 13px 16px;border-bottom:1px solid #e8e7e3;vertical-align:top;border-left:1px solid #e8e7e3;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.14em;text-transform:uppercase;">Referencia</p>
              <p style="margin:0;font-size:12px;font-weight:500;color:#1a1a18;">{{ $d['referencia'] }}</p>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="background-color:#0b1c0a;padding:18px 16px;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:rgba(184,150,46,0.7);letter-spacing:0.14em;text-transform:uppercase;">Monto recibido · {{ $d['fecha_pago'] }}</p>
              <p style="margin:0;font-size:26px;font-weight:600;color:#F1EDE3;letter-spacing:-0.01em;">{{ $d['moneda'] }} {{ $d['monto'] }}</p>
              <p style="margin:5px 0 0 0;font-size:9px;font-style:italic;color:rgba(241,237,227,0.38);">{{ $d['monto_en_letras'] }}</p>
            </td>
          </tr>
          <tr>
            <td style="width:50%;padding:12px 16px 12px 0;border-bottom:1px solid #e8e7e3;vertical-align:top;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.12em;text-transform:uppercase;">Total pagado a la fecha</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#1a1a18;">{{ $d['moneda'] }} {{ $d['total_pagado'] }}</p>
            </td>
            <td style="width:50%;padding:12px 0 12px 16px;border-bottom:1px solid #e8e7e3;vertical-align:top;border-left:1px solid #e8e7e3;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.12em;text-transform:uppercase;">Saldo pendiente</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#1a1a18;">{{ $d['moneda'] }} {{ $d['saldo_pendiente'] }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- CTA -->
    <tr>
      <td style="background-color:#ffffff;padding:24px 36px 8px 36px;">
        <p style="margin:0 0 12px 0;font-size:12px;color:#6a6a64;line-height:1.65;">El comprobante completo en PDF está disponible en tu portal. Guárdalo como respaldo oficial de tu pago.</p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="background-color:#0b1c0a;">
              <a href="{{ $d['link_comprobante'] }}" style="display:inline-block;padding:11px 24px;font-size:11px;font-weight:600;color:#F1EDE3;text-decoration:none;letter-spacing:0.1em;text-transform:uppercase;">Descargar comprobante PDF</a>
            </td>
            <td style="width:16px;"></td>
            <td style="vertical-align:middle;">
              <a href="{{ $d['link_portal'] }}" style="font-size:11px;color:#8a8a84;text-decoration:underline;">Ver mi portal</a>
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
            <td style="padding:0 0 0 20px;border-left:2px solid #B8962E;">
              <p style="margin:0 0 2px 0;font-size:12px;color:#6a6a64;line-height:1.6;">Para cualquier consulta sobre este pago escríbenos a <strong style="color:#1a1a18;font-weight:600;">{{ $c['support_email'] }}</strong>@if($d['nombre_asesor'] !== '—') o contacta a tu asesor <strong style="color:#1a1a18;font-weight:600;">{{ $d['nombre_asesor'] }}</strong>@endif.</p>
              <p style="margin:4px 0 0 0;font-size:12px;color:#6a6a64;">Gracias por tu confianza.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

</x-mail-shell>
