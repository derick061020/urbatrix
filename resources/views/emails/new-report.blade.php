@php
    $c = config('company');
    $shellEyebrow = "Avances de obra · " . $proyecto;
    $shellTitle = "Nuevo reporte <span style='font-weight:600;'>{{ __('disponible') }}</span>";
@endphp
<x-mail-shell
    :eyebrow="$shellEyebrow"
    :title="$shellTitle"
    doc-label="Reporte · E-12">

    <!-- INTRO -->
    <tr>
      <td style="background-color:#ffffff;padding:32px 36px 18px 36px;">
        <p style="margin:0 0 6px 0;font-size:13px;color:#525866;line-height:1.6;">Estimado/a <strong style="color:#171717;font-weight:600;">{{ $nombreCliente }}</strong>,</p>
        <p style="margin:0;font-size:13px;color:#525866;line-height:1.65;">{{ __('Hemos subido un nuevo reporte de avances de obra a tu portal. Encontrarás fotografías actualizadas, el estado de los hitos de construcción y el porcentaje de avance vigente de tu') }} <strong style="color:#171717;">Unidad {{ $unidad }}</strong> en <strong style="color:#171717;">{{ $proyecto }}</strong>.</p>
      </td>
    </tr>

    <!-- SECTION LABEL -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 10px 36px;">
        <p style="margin:0;font-size:9px;font-weight:600;color:#99a0ae;letter-spacing:0.22em;text-transform:uppercase;">{{ __('Resumen del reporte') }}</p>
      </td>
    </tr>

    <!-- SUMMARY -->
    <tr>
      <td style="background-color:#ffffff;padding:0 36px 0 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top:1px solid #eaecf0;">
          <tr>
            <td style="width:50%;padding:13px 16px 13px 0;border-bottom:1px solid #eaecf0;vertical-align:top;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Período') }}</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#171717;">{{ $report->period }}</p>
            </td>
            <td style="width:50%;padding:13px 0 13px 16px;border-bottom:1px solid #eaecf0;vertical-align:top;border-left:1px solid #eaecf0;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.14em;text-transform:uppercase;">{{ __('Unidad') }}</p>
              <p style="margin:0;font-size:15px;font-weight:300;color:#171717;">{{ $unidad }}</p>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="background-color:#171717;padding:18px 16px;">
              <p style="margin:0 0 4px 0;font-size:9px;font-weight:500;color:#f97066;letter-spacing:0.14em;text-transform:uppercase;">Avance actual · {{ $report->period }}</p>
              <p style="margin:0;font-size:26px;font-weight:600;color:#ffffff;letter-spacing:-0.01em;">{{ $report->overall_progress }}%</p>
              <p style="margin:5px 0 0 0;font-size:10px;color:rgba(255,255,255,0.45);">completado · entrega estimada {{ $report->estimated_delivery ?: 'Q4 2026' }}</p>
            </td>
          </tr>
          <tr>
            <td style="width:50%;padding:12px 16px 12px 0;border-bottom:1px solid #eaecf0;vertical-align:top;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.12em;text-transform:uppercase;">{{ __('Nuevas fotos') }}</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#171717;">{{ $numFotos }}</p>
            </td>
            <td style="width:50%;padding:12px 0 12px 16px;border-bottom:1px solid #eaecf0;vertical-align:top;border-left:1px solid #eaecf0;">
              <p style="margin:0 0 3px 0;font-size:9px;font-weight:500;color:#99a0ae;letter-spacing:0.12em;text-transform:uppercase;">{{ __('Hitos actualizados') }}</p>
              <p style="margin:0;font-size:14px;font-weight:600;color:#171717;">{{ $hitosActualizados }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- CTA -->
    <tr>
      <td style="background-color:#ffffff;padding:24px 36px 8px 36px;">
        <p style="margin:0 0 12px 0;font-size:12px;color:#525866;line-height:1.65;">{{ __('Accede al reporte completo — galería fotográfica, detalle de hitos y documentos adjuntos — directamente en tu portal.') }}</p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
          <tr><td style="background-color:#e11019;">
            <a href="{{ $linkPortal }}" style="display:inline-block;padding:11px 24px;font-size:11px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.1em;text-transform:uppercase;">{{ __('Ver reporte completo') }}</a>
          </td></tr>
        </table>
      </td>
    </tr>

    <!-- ADVISOR -->
    <tr>
      <td style="background-color:#ffffff;padding:20px 36px 32px 36px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td style="padding:0 0 0 20px;border-left:2px solid #e11019;">
              @php $asesor = $nombreAsesor ? ', tu asesor '.e($nombreAsesor).',' : ''; @endphp
              <p style="margin:0;font-size:12px;color:#525866;line-height:1.6;">Ante cualquier consulta sobre el reporte{!! $asesor !!} estamos a tu disposición en <strong style="color:#171717;font-weight:600;">{{ $c['support_email'] }}</strong> · {{ $c['phone'] }}.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

</x-mail-shell>
