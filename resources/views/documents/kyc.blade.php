<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('LANDMASS · KYC — Verificación de Identidad') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<style>
@page { margin: 0; size: A4 portrait; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:      #171717;
  --ink-mid:  #525866;
  --ink-mute: #99a0ae;
  --rule:     #cacfd8;
  --rule-lt:  #eaecf0;
  --green:    #525866;
  --green-bg: #171717;
  --field-bg: #f5f7fa;
}

body {
  font-family: 'Inter', sans-serif;
  background: #f2f5f8;
  color: var(--ink);
  -webkit-font-smoothing: antialiased;
  width: 210mm;
  margin: 0 auto;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

.page {
  width: 210mm;
  min-height: 297mm;
  background: #fff;
  display: flex;
  flex-direction: column;
  page-break-after: always;
  break-after: page;
  margin-bottom: 12px;
}
.page:last-child { margin-bottom: 0; page-break-after: auto; break-after: auto; }

.hdr {
  background: var(--green-bg);
  border-bottom: 3px solid #e11019;
  padding: 16px 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}
.hdr-logo { display: flex; align-items: center; gap: 12px; }
.hdr-iso { width: 28px; height: 28px; flex-shrink: 0; }
.hdr-name { font-size: 14px; font-weight: 600; color: #ffffff; letter-spacing: .2em; }
.hdr-right { text-align: right; }
.hdr-doc { font-size: 7px; font-weight: 500; color: rgba(255,255,255,.35); letter-spacing: .18em; text-transform: uppercase; }
.hdr-ref { font-size: 8.5px; color: rgba(255,255,255,.5); margin-top: 4px; }

.footer {
  background: var(--green-bg);
  padding: 7px 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  margin-top: auto;
}
.footer-l { font-size: 7px; font-weight: 500; color: rgba(255,255,255,.4); letter-spacing: .14em; text-transform: uppercase; }
.footer-c { font-size: 6.5px; color: rgba(255,255,255,.22); }
.footer-r { font-size: 7px; color: rgba(255,255,255,.3); }

.cover-band {
  padding: 28px 44px 22px;
  border-bottom: 1px solid var(--rule-lt);
}
.cover-eyebrow {
  font-size: 7px; font-weight: 500; color: var(--ink-mute);
  letter-spacing: .22em; text-transform: uppercase; margin-bottom: 8px;
  display: flex; align-items: center; gap: 8px;
}
.cover-eyebrow::before {
  content: '';
  display: inline-block;
  width: 12px; height: 1.5px;
  background: var(--green);
  flex-shrink: 0;
}
.cover-name {
  font-size: 28px; font-weight: 200; color: var(--ink);
  letter-spacing: -.02em; line-height: 1.1; margin-bottom: 3px;
}
.cover-name strong { font-weight: 700; }
.cover-sub { font-size: 9px; color: var(--ink-mute); letter-spacing: .08em; }

.cover-meta {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  border-top: 1px solid var(--rule-lt);
  border-left: 1px solid var(--rule-lt);
  margin-top: 20px;
}
.meta-cell {
  border-right: 1px solid var(--rule-lt);
  border-bottom: 1px solid var(--rule-lt);
  padding: 9px 14px;
}
.meta-lbl { font-size: 6.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 4px; }
.meta-val { font-size: 10.5px; font-weight: 600; color: var(--ink); }

.body { padding: 22px 44px 88px; flex: 1; }

.sec { margin-bottom: 22px; }

.sec-title {
  font-size: 6.5px; font-weight: 600; color: var(--ink-mute);
  letter-spacing: .24em; text-transform: uppercase;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--ink);
  margin-bottom: 16px;
  display: flex;
  align-items: flex-end;
  gap: 12px;
}
.sec-num {
  font-size: 20px; font-weight: 700; color: var(--green);
  line-height: 1; letter-spacing: -.03em;
  flex-shrink: 0;
  transform: translateY(2px);
}

.dg {
  display: grid;
  gap: 0;
  border-top: 1px solid var(--rule-lt);
  border-left: 1px solid var(--rule-lt);
  margin-bottom: 14px;
}
.dg.c2 { grid-template-columns: 1fr 1fr; }
.dg.c3 { grid-template-columns: 1fr 1fr 1fr; }
.dg.cdoc { grid-template-columns: 180px 1fr 150px; }

.dc {
  border-right: 1px solid var(--rule-lt);
  border-bottom: 1px solid var(--rule-lt);
  padding: 9px 14px;
}
.dc-lbl {
  font-size: 6.5px; font-weight: 500; color: var(--ink-mute);
  letter-spacing: .12em; text-transform: uppercase; margin-bottom: 4px;
}
.dc-val {
  font-size: 10.5px; font-weight: 600; color: var(--ink);
  line-height: 1.3;
}

.id-img-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-top: 14px;
}
.id-img-box {
  border: 1px dashed var(--rule);
  border-radius: 6px;
  background: var(--field-bg);
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 90px;
  overflow: hidden;
}
.id-img-box img { max-width: 100%; max-height: 220px; object-fit: contain; border-radius: 4px; }
.id-img-icon { width: 28px; height: 28px; opacity: .3; }
.id-img-lbl {
  font-size: 7px; font-weight: 600; color: var(--ink-mute);
  letter-spacing: .16em; text-transform: uppercase;
  text-align: center;
}
.id-img-sub {
  font-size: 6.5px; color: var(--ink-mute);
  letter-spacing: .08em;
  text-align: center;
  margin-top: -6px;
}

.status-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 7px;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 20px;
}
.status-tag.pending { background: rgba(82,88,102,.08); color: var(--green); border: 1px solid rgba(82,88,102,.2); }
.status-tag.approved { background: rgba(46,125,50,.1); color: #2e7d32; border: 1px solid rgba(46,125,50,.25); }
.status-tag.rejected { background: rgba(176,0,32,.08); color: #b00020; border: 1px solid rgba(176,0,32,.22); }
.status-tag::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; opacity: .7; }

.declaration {
  border-left: 2px solid var(--rule);
  padding: 9px 14px;
  margin-top: 18px;
  background: var(--field-bg);
}
.declaration-title {
  font-size: 7px; font-weight: 700; color: var(--ink);
  letter-spacing: .12em; text-transform: uppercase; margin-bottom: 5px;
}
.declaration-text { font-size: 8px; color: var(--ink-mid); line-height: 1.7; }

.sig-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  margin-top: 20px;
}
.sig-role { font-size: 6.5px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 3px; }
.sig-name { font-size: 11px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.sig-entity { font-size: 7.5px; color: var(--ink-mute); margin-bottom: 14px; }
.sig-line { border-top: 1px solid var(--ink); padding-top: 5px; height: 50px; }
.sig-label { font-size: 6.5px; color: var(--ink-mute); letter-spacing: .1em; margin-top: 8px; }

@media print {
  body { background: #fff; }
  .page { margin-bottom: 0; }
}
</style>
</head>
<body>

<svg style="display:none">
  <symbol id="iso" viewBox="0 0 87 87" fill="none">
    <image href="{{ asset('images/brand/logo-mark-white.png') }}" x="0" y="0" width="87" height="87" preserveAspectRatio="xMidYMid meet"/>
  </symbol>
</svg>

<div class="page">

  <div class="hdr">
    <div class="hdr-logo">
      <svg class="hdr-iso" viewBox="0 0 87 87" fill="none"><use href="#iso"/></svg>
      <span class="hdr-name">LANDMASS</span>
    </div>
    <div class="hdr-right">
      <div class="hdr-doc">{{ __('KYC · Verificación de Identidad') }}</div>
      <div class="hdr-ref">{{ $proyecto }} · Cap Cana, R.D. · Exp. #{{ $referencia }}</div>
    </div>
  </div>

  <div class="cover-band">
    <div class="cover-eyebrow">{{ __('Conozca a su Cliente') }}</div>
    <div class="cover-name">{{ $comprador_nombre }}</div>
    <div class="cover-sub">Titular de la compra · {{ $proyecto }} · {{ $unidad }}</div>

    <div class="cover-meta">
      <div class="meta-cell">
        <div class="meta-lbl">{{ __('Expediente') }}</div>
        <div class="meta-val">{{ $referencia }}</div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">{{ __('Fecha de llenado') }}</div>
        <div class="meta-val">{{ $fecha_llenado }}</div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">{{ __('Estado KYC') }}</div>
        <div class="meta-val">
          <span class="status-tag {{ $estado_clase }}">{{ $estado }}</span>
        </div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">{{ __('Asesor') }}</div>
        <div class="meta-val">{{ $asesor }}</div>
      </div>
    </div>
  </div>

  <div class="body">

    <div class="sec">
      <div class="sec-title">
        <span class="sec-num">01</span>
        Datos personales
      </div>

      <div class="dg c2">
        <div class="dc">
          <div class="dc-lbl">Nombre(s)</div>
          <div class="dc-val">{{ $nombres }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('Apellidos') }}</div>
          <div class="dc-val">{{ $apellidos }}</div>
        </div>
      </div>

      <div class="dg c3">
        <div class="dc">
          <div class="dc-lbl">{{ __('Fecha de nacimiento') }}</div>
          <div class="dc-val">{{ $fecha_nacimiento }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('Nacionalidad') }}</div>
          <div class="dc-val">{{ $nacionalidad }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('País de residencia') }}</div>
          <div class="dc-val">{{ $pais_residencia }}</div>
        </div>
      </div>
    </div>

    <div class="sec">
      <div class="sec-title">
        <span class="sec-num">02</span>
        Documento de identidad
      </div>

      <div class="dg cdoc">
        <div class="dc">
          <div class="dc-lbl">{{ __('Tipo de documento') }}</div>
          <div class="dc-val">{{ $id_tipo }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('Número de documento') }}</div>
          <div class="dc-val">{{ $id_numero }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('Fecha de expedición') }}</div>
          <div class="dc-val">{{ $id_expedicion }}</div>
        </div>
      </div>

      <div class="id-img-row">
        <div class="id-img-box">
          @if($id_imagen_url)
            <img src="{{ $id_imagen_url }}" alt="{{ __('Documento de identidad') }}">
          @else
            <svg class="id-img-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="16" rx="2"/>
              <circle cx="9" cy="10" r="2"/>
              <path d="M15 8h2M15 12h2M7 16h10"/>
            </svg>
            <div class="id-img-lbl">{{ __('Frente del documento') }}</div>
            <div class="id-img-sub">{{ __('Adjunto en el expediente') }}</div>
          @endif
        </div>
        <div class="id-img-box">
          <svg class="id-img-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M7 9h10M7 13h6M7 17h4"/>
          </svg>
          <div class="id-img-lbl">{{ __('Reverso del documento') }}</div>
          <div class="id-img-sub">{{ __('Adjunto en el expediente') }}</div>
        </div>
      </div>
    </div>

    <div class="sec">
      <div class="sec-title">
        <span class="sec-num">03</span>
        Información de contacto
      </div>

      <div class="dg c2">
        <div class="dc">
          <div class="dc-lbl">{{ __('Teléfono móvil') }}</div>
          <div class="dc-val">{{ $telefono }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">{{ __('Correo electrónico') }}</div>
          <div class="dc-val">{{ $email }}</div>
        </div>
      </div>

      <div class="dg" style="grid-template-columns: 1fr;">
        <div class="dc">
          <div class="dc-lbl">{{ __('Dirección de residencia') }}</div>
          <div class="dc-val">{{ $direccion }}</div>
        </div>
      </div>
    </div>

    <div class="declaration">
      <div class="declaration-title">{{ __('Declaración del titular') }}</div>
      <div class="declaration-text">
        Yo, <strong>{{ $comprador_nombre }}</strong>, declaro bajo juramento que los datos suministrados en el presente formulario son verídicos, completos y exactos al momento de su llenado. Me comprometo a notificar a Duna Development Group cualquier cambio que se produzca en la información aquí registrada. Asimismo, autorizo el tratamiento de mis datos personales conforme a la política de privacidad de la empresa.
      </div>
    </div>

    <div class="sig-row">
      <div class="sig-block">
        <div class="sig-role">{{ __('Titular de la compra') }}</div>
        <div class="sig-name">{{ $comprador_nombre }}</div>
        <div class="sig-entity">Comprador · {{ $proyecto }}</div>
        <div class="sig-line"></div>
        <div class="sig-label">{{ __('Firma · Fecha: _____ / _____ / _________') }}</div>
      </div>
      <div class="sig-block">
        <div class="sig-role">{{ __('Verificado por') }}</div>
        <div class="sig-name">{{ $asesor }}</div>
        <div class="sig-entity">{{ __('Asesor Comercial · Duna Development Group') }}</div>
        <div class="sig-line"></div>
        <div class="sig-label">{{ __('Firma · Sello · Fecha: _____ / _____ / _________') }}</div>
      </div>
    </div>

  </div>

  <div class="footer">
    <div class="footer-l">{{ __('LANDMASS · Confidencial') }}</div>
    <div class="footer-c">{{ __('Este documento contiene información personal protegida. Uso interno exclusivo.') }}</div>
    <div class="footer-r">{{ __('KYC · Pág. 1 de 1') }}</div>
  </div>

</div>

</body>
</html>
