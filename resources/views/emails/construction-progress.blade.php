@php
    $c = config('company');
    $shellTitle = "Novedades de <span style='font-weight:600;'>" . e($proyecto) . "</span>";
@endphp
<x-mail-shell
    eyebrow="Avance de obra · Reporte mensual"
    :title="$shellTitle"
    doc-label="Avance · E-04">

    <!-- INTRO -->
    <tr>
      <td style="background-color:#ffffff;padding:32px 36px 18px 36px;">
        <p style="margin:0 0 6px 0;font-size:13px;color:#4a4a46;line-height:1.6;">Estimado/a <strong style="color:#1a1a18;font-weight:600;">{{ $nombreCliente }}</strong>,</p>
        <p style="margin:0;font-size:13px;color:#6a6a64;line-height:1.65;">Nos complace compartirte las últimas novedades del avance de construcción de tu inversión en <strong style="color:#1a1a18;">{{ $proyecto }}</strong>. Estamos trabajando con el mayor compromiso para entregarte una propiedad de la más alta calidad.</p>
      </td>
    </tr>

    <!-- PERIODO + TITULO -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 10px 36px;">
        <p style="margin:0;font-size:9px;font-weight:600;color:#8a8a84;letter-spacing:0.22em;text-transform:uppercase;">{{ $report->period }} — {{ $report->title }}</p>
      </td>
    </tr>

    <!-- PROGRESO HERO -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 0 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top:1px solid #e8e7e3;">
          <tr>
            <td colspan="2" style="background-color:#0b1c0a;padding:20px 18px;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:rgba(184,150,46,0.7);letter-spacing:0.14em;text-transform:uppercase;">Progreso actual de obra</p>
              <p style="margin:0;font-size:30px;font-weight:600;color:#F1EDE3;letter-spacing:-0.01em;">{{ $report->overall_progress }}<span style="font-size:18px;">%</span> <span style="font-size:11px;font-weight:400;color:rgba(241,237,227,0.5);">completado</span></p>
              <!-- barra -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:12px;background:rgba(241,237,227,0.12);border-radius:3px;">
                <tr><td style="height:6px;font-size:0;line-height:0;background:#B8962E;border-radius:3px;width:{{ max(2,min(100,$report->overall_progress)) }}%;">&nbsp;</td></tr>
              </table>
            </td>
          </tr>
          @if($report->description)
          <tr>
            <td colspan="2" style="padding:14px 0 0 0;">
              <p style="margin:0;font-size:12px;color:#6a6a64;line-height:1.65;">{{ $report->description }}</p>
            </td>
          </tr>
          @endif
          <tr>
            <td style="width:50%;padding:14px 16px 14px 0;border-bottom:1px solid #e8e7e3;border-top:1px solid #e8e7e3;vertical-align:top;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.12em;text-transform:uppercase;">Período</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#1a1a18;">{{ $report->period }}</p>
            </td>
            <td style="width:50%;padding:14px 0 14px 16px;border-bottom:1px solid #e8e7e3;border-top:1px solid #e8e7e3;vertical-align:top;border-left:1px solid #e8e7e3;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#8a8a84;letter-spacing:0.12em;text-transform:uppercase;">Entrega estimada</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#1a1a18;">{{ $report->estimated_delivery ?: 'Q4 2026' }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- CTA -->
    <tr>
      <td style="background-color:#ffffff;padding:24px 36px 8px 36px;">
        <p style="margin:0 0 12px 0;font-size:12px;color:#6a6a64;line-height:1.65;">Accede a la galería fotográfica completa y al detalle de los hitos de construcción en tu portal.</p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
          <tr><td style="background-color:#0b1c0a;">
            <a href="{{ $linkPortal }}" style="display:inline-block;padding:11px 24px;font-size:11px;font-weight:600;color:#F1EDE3;text-decoration:none;letter-spacing:0.1em;text-transform:uppercase;">Ver galería y reporte</a>
          </td></tr>
        </table>
      </td>
    </tr>

    <!-- ADVISOR -->
    <tr>
      <td style="background-color:#ffffff;padding:20px 36px 32px 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td style="padding:0 0 0 20px;border-left:2px solid #B8962E;">
              @php $asesor = $nombreAsesor ? ', tu asesor '.e($nombreAsesor).',' : ''; @endphp
              <p style="margin:0;font-size:12px;color:#6a6a64;line-height:1.6;">Ante cualquier consulta{!! $asesor !!} estamos a tu disposición en <strong style="color:#1a1a18;font-weight:600;">{{ $c['support_email'] }}</strong> · {{ $c['phone'] }}.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

</x-mail-shell>
