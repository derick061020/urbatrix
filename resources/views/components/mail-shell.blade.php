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
  body { margin: 0; padding: 0; background-color: #EFEDE8; }
  a { text-decoration: none; }
</style>
</head>
<body style="margin:0;padding:0;background-color:#EFEDE8;font-family:'Inter',Helvetica,Arial,sans-serif;">

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#EFEDE8;">
  <tr>
    <td align="center" style="padding:24px 16px;">
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px;width:100%;">

        <!-- HEADER -->
        <tr>
          <td style="background-color:#0b1c0a;padding:20px 36px 18px 36px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <div style="font-size:14px;font-weight:600;color:#F1EDE3;letter-spacing:0.22em;text-transform:uppercase;line-height:1;">{{ $c['brand'] }}</div>
                  <div style="font-size:8px;font-weight:400;color:rgba(241,237,227,0.35);letter-spacing:0.14em;text-transform:uppercase;margin-top:4px;">{{ $c['group'] }}</div>
                </td>
                @if($docLabel)
                <td align="right" style="vertical-align:middle;">
                  <div style="font-size:8px;font-weight:500;color:rgba(241,237,227,0.25);letter-spacing:0.2em;text-transform:uppercase;">{{ $docLabel }}</div>
                </td>
                @endif
              </tr>
            </table>
          </td>
        </tr>

        <!-- GOLD BAR -->
        <tr><td style="background-color:#B8962E;height:2px;font-size:0;line-height:0;">&nbsp;</td></tr>

        <!-- HERO -->
        <tr>
          <td style="background-color:#0f2710;padding:26px 36px 22px 36px;">
            @if($eyebrow)<p style="margin:0 0 7px 0;font-size:9px;font-weight:500;color:rgba(241,237,227,0.4);letter-spacing:0.24em;text-transform:uppercase;">{{ $eyebrow }}</p>@endif
            <p style="margin:0;font-size:22px;font-weight:300;color:#F1EDE3;letter-spacing:-0.02em;line-height:1.15;">{!! $title !!}</p>
          </td>
        </tr>

        {{-- CONTENT --}}
        {{ $slot }}

        <!-- SIGNATURE -->
        <tr>
          <td style="background-color:#f7f6f3;padding:20px 36px;border-top:1px solid #e8e7e3;">
            <p style="margin:0 0 2px 0;font-size:11px;color:#4a4a46;">Un cordial saludo,</p>
            <p style="margin:0 0 2px 0;font-size:12px;font-weight:600;color:#1a1a18;">Equipo {{ $c['group'] }}</p>
            <p style="margin:0;font-size:10px;color:#8a8a84;">{{ $c['support_email'] }} · {{ $c['phone'] }}</p>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background-color:#0b1c0a;padding:16px 36px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <p style="margin:0;font-size:10px;font-weight:500;color:rgba(241,237,227,0.4);letter-spacing:0.18em;text-transform:uppercase;">{{ $c['brand'] }} · {{ $c['group'] }}</p>
                  <p style="margin:4px 0 0 0;font-size:10px;color:rgba(241,237,227,0.2);">{{ $c['location'] }}</p>
                </td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-size:9px;color:rgba(241,237,227,0.15);letter-spacing:0.1em;">{{ $c['website'] }}</p>
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
