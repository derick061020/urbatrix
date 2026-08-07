{{--
    Layout compartido de los correos del CRM (BAHÍA MAR).
    El chrome (header con logo, acento rojo, banda de firma y footer) vive aquí;
    cada plantilla aporta solo su contenido interno (hero + cuerpo) ya sustituido.

    Variables esperadas:
      $docLabel  string  Etiqueta de documento, ej. "Comprobante · E-11"
      $content   string  HTML interno ya renderizado (hero + cuerpo)
      $preheader string  (opcional) texto de preencabezado
--}}
@php
    $brand    = config('company.brand', 'BAHÍA MAR');
    $group    = config('company.group', 'BAHÍA MAR');
    $location = config('company.location', 'Cap Cana, Punta Cana, República Dominicana');
    $email    = config('company.support_email', 'hello@landmasscapital.com');
    $phone    = config('company.phone', '+1 849 499 2578');
    $website   = config('company.website', 'landmasscapital.com');
    $docLabel  = $docLabel ?? '';
    $preheader = $preheader ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $brand }} · {{ $group }}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap');
  body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
  body { margin: 0; padding: 0; background-color: #ededed; }
  a { text-decoration: none; }
</style>
</head>
<body style="margin:0;padding:0;background-color:#ededed;font-family:'Inter',Helvetica,Arial,sans-serif;">
@if($preheader)
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">{{ $preheader }}</div>
@endif

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#ededed;">
  <tr>
    <td align="center" style="padding:24px 16px;">
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(10,13,20,0.06);">

        <!-- HEADER -->
        <tr>
          <td style="background-color:#074540;padding:22px 36px 20px 36px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                      <td style="vertical-align:middle;padding-right:10px;">
                        <img src="{{ asset('images/brand/logo-mark-white.png') }}" width="26" height="26" alt="" style="display:block;width:26px;height:26px;">
                      </td>
                      <td style="vertical-align:middle;">
                        <div style="font-family:'Inter Tight','Inter',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#ffffff;letter-spacing:0.24em;text-transform:uppercase;line-height:1;">{{ $brand }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
                <td align="right" style="vertical-align:middle;">
                  <div style="font-family:'Inter',Helvetica,Arial,sans-serif;font-size:8px;font-weight:500;color:rgba(255,255,255,0.25);letter-spacing:0.2em;text-transform:uppercase;">{{ $docLabel }}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- ACCENT BAR — filo claro de marca entre el header y el hero -->
        <tr><td style="background-color:#e8f0ef;height:3px;font-size:0;line-height:0;">&nbsp;</td></tr>

        {{-- ── CONTENIDO DE LA PLANTILLA ── --}}
        {!! $content !!}

        <!-- SIGNATURE -->
        <tr>
          <td style="background-color:#f5f7fa;padding:20px 36px;border-top:1px solid #eaecf0;">
            <p style="margin:0 0 2px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:11px;color:#525866;">{{ __('Un cordial saludo,') }}</p>
            <p style="margin:0 0 2px 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:12px;font-weight:600;color:#171717;">Equipo {{ $group }}</p>
            <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:10px;color:#99a0ae;">{{ $email }} · {{ $phone }}</p>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background-color:#074540;padding:16px 36px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:10px;font-weight:500;color:rgba(255,255,255,0.4);letter-spacing:0.18em;text-transform:uppercase;">{{ $brand }}</p>
                  <p style="margin:4px 0 0 0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:10px;color:rgba(255,255,255,0.2);">{{ $location }}</p>
                </td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-family:'Inter',Helvetica,Arial,sans-serif;font-size:9px;color:rgba(255,255,255,0.15);letter-spacing:0.1em;">{{ $website }}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
