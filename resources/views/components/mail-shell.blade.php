@props([
    'eyebrow' => '',
    'title'   => '',
    'docLabel' => '',
])
@php $c = config('company'); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $title }} — {{ $c['project'] }}</title>
<style>
  body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
  table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
  img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
  body { margin: 0; padding: 0; background-color: #ededed; }
  a { text-decoration: none; }
</style>
</head>
<body style="margin:0;padding:0;background-color:#ededed;font-family:'Inter',Helvetica,Arial,sans-serif;">

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
                        <div style="font-family:'Inter Tight',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#ffffff;letter-spacing:0.24em;text-transform:uppercase;line-height:1;">{{ $c['brand'] }}</div>
                      </td>
                    </tr>
                  </table>
                </td>
                @if($docLabel)
                <td align="right" style="vertical-align:middle;">
                  <div style="font-size:8px;font-weight:600;color:rgba(255,255,255,0.4);letter-spacing:0.2em;text-transform:uppercase;">{{ $docLabel }}</div>
                </td>
                @endif
              </tr>
            </table>
          </td>
        </tr>

        <!-- RED ACCENT BAR -->
        <tr><td style="background-color:#074540;height:3px;font-size:0;line-height:0;">&nbsp;</td></tr>

        <!-- HERO -->
        <tr>
          <td style="background-color:#053330;padding:28px 36px 24px 36px;">
            @if($eyebrow)<p style="margin:0 0 8px 0;font-size:9px;font-weight:600;color:#074540;letter-spacing:0.24em;text-transform:uppercase;">{{ $eyebrow }}</p>@endif
            <p style="margin:0;font-family:'Inter Tight',Helvetica,Arial,sans-serif;font-size:23px;font-weight:600;color:#ffffff;letter-spacing:-0.02em;line-height:1.18;">{!! $title !!}</p>
          </td>
        </tr>

        {{-- CONTENT --}}
        {{ $slot }}

        <!-- SIGNATURE -->
        <tr>
          <td style="background-color:#f5f7fa;padding:20px 36px;border-top:1px solid #eaecf0;">
            <p style="margin:0 0 2px 0;font-size:11px;color:#525866;">{{ __('Un cordial saludo,') }}</p>
            <p style="margin:0 0 2px 0;font-size:12px;font-weight:600;color:#171717;">{{ __('Equipo') }} {{ $c['group'] }}</p>
            <p style="margin:0;font-size:10px;color:#99a0ae;">{{ $c['support_email'] }} · {{ $c['phone'] }}</p>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background-color:#074540;padding:16px 36px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <p style="margin:0;font-size:10px;font-weight:600;color:rgba(255,255,255,0.55);letter-spacing:0.18em;text-transform:uppercase;">{{ $c['brand'] }}</p>
                  <p style="margin:4px 0 0 0;font-size:10px;color:rgba(255,255,255,0.3);">{{ $c['location'] }}</p>
                </td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-size:9px;color:rgba(255,255,255,0.3);letter-spacing:0.1em;">{{ $c['website'] }}</p>
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
