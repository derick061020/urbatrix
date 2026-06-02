<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Duna · KYC — Verificación de Identidad</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<style>
@page { margin: 0; size: A4 portrait; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:      #1a1a18;
  --ink-mid:  #4a4a46;
  --ink-mute: #8a8a84;
  --rule:     #d8d8d4;
  --rule-lt:  #eeeeea;
  --green:    #4A5E3F;
  --green-bg: #0b1c0a;
  --field-bg: #f7f6f3;
}

body {
  font-family: 'Inter', sans-serif;
  background: #f0efec;
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
  padding: 16px 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}
.hdr-logo { display: flex; align-items: center; gap: 12px; }
.hdr-iso { width: 28px; height: 28px; flex-shrink: 0; }
.hdr-name { font-size: 14px; font-weight: 600; color: #F1EDE3; letter-spacing: .2em; }
.hdr-right { text-align: right; }
.hdr-doc { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.35); letter-spacing: .18em; text-transform: uppercase; }
.hdr-ref { font-size: 8.5px; color: rgba(241,237,227,.5); margin-top: 4px; }

.footer {
  background: var(--green-bg);
  padding: 7px 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  margin-top: auto;
}
.footer-l { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.4); letter-spacing: .14em; text-transform: uppercase; }
.footer-c { font-size: 6.5px; color: rgba(241,237,227,.22); }
.footer-r { font-size: 7px; color: rgba(241,237,227,.3); }

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
.status-tag.pending { background: rgba(74,94,63,.08); color: var(--green); border: 1px solid rgba(74,94,63,.2); }
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
    <path d="M43.2607 0.00439453C48.6922 0.111782 54.1102 0.474765 59.1484 3.27686C64.4825 6.24346 70.3854 8.20656 74.7549 12.7739C78.4586 16.6464 81.5372 20.8815 82.7842 26.2808C83.4698 29.2474 84.3912 32.1577 85.2314 35.0874C86.8379 40.6882 86.9721 46.3025 85.2109 51.8765C82.7876 59.5514 79.7994 66.9715 74.8115 73.3745C73.82 74.6463 72.7314 75.9118 71.4609 76.8716C63.0732 83.2078 53.7484 87.1215 43.2578 86.228L42.2422 86.1235C27.9273 84.4858 16.5973 77.3406 8.55762 65.189C2.30603 55.7422 0.201666 49.0606 0 38.0063C0.0874549 30.1369 3.18353 22.1503 10.4199 15.8647C15.0044 11.8847 19.3436 7.63278 24.9297 5.04541C30.7712 2.34396 36.7436 -0.11974 43.2607 0.00439453ZM44.8242 4.26611C39.423 3.97751 34.5219 4.66552 29.9609 7.64209C25.5445 10.5281 21.0539 13.2969 16.6475 16.1997C8.93708 21.281 4.71592 28.2933 4.6582 37.6294V38.5405C4.83967 40.5875 4.81015 42.6814 5.24707 44.6714C6.92091 52.2825 10.1804 59.1625 15.3027 65.0151C24.734 75.7909 35.957 82.8947 50.8467 80.8745C62.0895 79.3509 71.1376 74.0657 76.2432 63.2397C77.3288 60.9343 78.4818 58.6521 79.4531 56.2964C81.8731 50.4303 83.3318 44.4231 81.5605 38.0503C80.6497 34.7749 79.6177 31.5297 78.8379 28.2241C77.4363 22.2944 74.1935 17.6059 69.5049 13.9614C62.2349 8.31019 54.2015 4.76287 44.8242 4.26611ZM42.9453 9.02881C54.4033 7.93143 62.7993 13.5556 69.5518 21.7104C74.5934 27.798 76.1565 35.399 75.2188 43.3491C74.7045 47.7118 74.0623 52.0677 73.2422 56.3833C72.8657 58.3666 72.2774 60.4776 71.1816 62.1187C65.0241 71.3371 56.0432 76.1362 45.4121 77.6665C39.369 78.5377 33.9851 76.1995 29.3857 72.3335L28.9395 71.9546C25.1651 68.666 21.4613 65.2498 18.0566 61.5786C16.282 59.6658 14.9708 57.1852 13.8818 54.7622C9.85194 45.7886 9.86523 37.1001 15.1455 28.3647C18.9099 22.1397 24.1062 17.4751 29.373 12.8306C33.2215 9.43776 38.1658 8.98854 42.9453 9.02881ZM50.3965 12.8374C46.7867 11.8375 42.7331 12.1224 38.915 12.3774C36.4144 12.5453 33.6012 13.1429 31.5947 14.5288C26.0188 18.3847 21.004 22.9049 18.0664 29.3647C16.0329 33.8449 13.4415 38.3657 14.46 43.3257C15.428 48.0407 16.8094 53.1116 19.542 56.897C23.2022 61.9709 28.0723 66.2764 32.9189 70.3101C36.6463 73.4108 41.4393 75.25 46.333 73.9146C51.499 72.5017 56.6758 70.6627 61.3848 68.1323C66.5977 65.3301 70.365 60.8163 71.2725 54.561C71.9547 49.8629 72.4227 45.1279 72.9873 40.4097C73.2562 34.9733 71.4846 29.9527 68.7051 25.6841C64.4062 19.083 57.8582 14.9046 50.3965 12.8374ZM37.9707 17.8882C44.192 17.311 50.2788 18.1127 56.2178 20.1597C63.6458 22.7169 69.3697 27.4391 68.7109 39.1411C68.8857 45.8997 66.8455 53.1682 63.8945 60.2222C63.5719 60.994 63.1688 61.8569 62.5537 62.3569C59.3405 64.9644 56.1408 67.6226 52.7461 69.9751C48.7094 72.7704 44.3332 71.7127 40.2227 70.1724C33.6552 67.7146 28.3275 63.6004 24.7588 57.4146L24.415 56.8101C23.4302 55.0113 22.3478 53.2656 21.3193 51.4937C18.2946 46.2822 19.0439 40.7282 19.6455 35.104C20.4421 27.6405 24.7112 22.7506 30.8486 19.4048C32.9023 18.2873 35.5575 18.113 37.9707 17.8882ZM57.8887 24.3882C49.4657 20.7907 40.7769 19.5757 31.833 22.9282C29.6526 23.7462 28.0774 25.0036 26.6816 26.6978L26.4014 27.0425C23.2823 31.0191 22.5666 35.6338 22.7783 40.8286C21.9851 44.2146 23.3395 47.3358 25.3359 50.3091C26.5594 52.1313 27.676 54.0275 28.8457 55.8833C31.8841 60.6989 36.1223 64.183 41.127 66.6294C44.3871 68.2233 47.8861 68.5822 51.2471 66.6157C53.6133 65.2264 55.9798 63.8333 58.3057 62.3735C60.6649 60.897 62.3623 58.8098 63.001 56.0649C64.1505 51.1083 65.4746 46.1579 66.1367 41.1274C67.2996 32.2914 64.9604 27.4085 57.8887 24.3882ZM38.7637 24.7915C45.8758 23.919 52.4066 25.241 58.4297 29.3218C61.0075 31.0702 62.513 33.1676 62.2744 36.3354C61.9484 40.6511 61.6828 44.9736 61.1719 49.269C60.3447 56.2327 54.1429 60.7587 48.2588 62.3345L47.6875 62.478C43.3248 63.5116 39.9204 61.4874 37.1475 58.5142C32.4688 53.4938 29.5074 47.6413 29.6855 40.396C30.22 37.2616 30.5762 34.0831 31.3291 31.0024C32.2098 27.3918 35.154 25.2345 38.7637 24.7915ZM56.9678 32.7847C52.4404 29.553 47.4623 27.6943 41.7451 28.4058C39.789 28.6507 37.7757 28.4458 35.1475 28.4458C34.4479 29.2995 33.8809 31.5402 33.4473 35.1685L33.3623 35.9058C32.475 44.0236 34.8074 50.9101 41.0186 56.2896C42.9108 57.9305 45.1227 59.0677 47.6064 58.2456C52.6984 56.561 56.6213 53.5042 57.4785 47.7524C58.0533 43.8999 58.4602 40.0032 58.6182 36.1138C58.6652 35.003 57.8718 33.4324 56.9678 32.7847ZM37.1006 33.8989C37.1544 31.8015 38.1999 30.6135 40.4922 30.9692C41.2483 31.0867 42.0347 30.9898 42.8076 30.9897C46.841 30.6877 50.572 31.456 52.5752 35.3589C53.5129 37.181 53.8761 39.5299 53.7988 41.6138C53.6946 44.3387 52.545 47.0338 52.4912 49.7554C52.4196 53.455 48.2317 55.1708 45.0518 53.4165L44.751 53.2358C40.2874 50.3934 38.3279 45.9932 37.3633 41.0835C36.9062 38.7613 37.0367 36.2949 37.1006 33.8989ZM38.9561 33.2446C39.1779 35.238 39.2522 36.983 39.585 38.6743C40.3983 42.7852 42.6669 46.0637 45.7188 48.7954C46.2834 49.3021 47.2072 49.3969 47.9668 49.6821C48.1953 49.0446 48.5148 48.4233 48.6426 47.769C49.0929 45.4871 49.3385 43.1547 49.9199 40.9097C50.6863 37.9598 49.1971 34.9161 46.2158 34.2583C44.2573 33.8267 42.2474 33.6539 39.9541 33.3726L38.9561 33.2446Z" fill="white"/>
  </symbol>
</svg>

<div class="page">

  <div class="hdr">
    <div class="hdr-logo">
      <svg class="hdr-iso" viewBox="0 0 87 87" fill="none"><use href="#iso"/></svg>
      <span class="hdr-name">MAKAI</span>
    </div>
    <div class="hdr-right">
      <div class="hdr-doc">KYC · Verificación de Identidad</div>
      <div class="hdr-ref">{{ $proyecto }} · Cap Cana, R.D. · Exp. #{{ $referencia }}</div>
    </div>
  </div>

  <div class="cover-band">
    <div class="cover-eyebrow">Conozca a su Cliente</div>
    <div class="cover-name">{{ $comprador_nombre }}</div>
    <div class="cover-sub">Titular de la compra · {{ $proyecto }} · {{ $unidad }}</div>

    <div class="cover-meta">
      <div class="meta-cell">
        <div class="meta-lbl">Expediente</div>
        <div class="meta-val">{{ $referencia }}</div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">Fecha de llenado</div>
        <div class="meta-val">{{ $fecha_llenado }}</div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">Estado KYC</div>
        <div class="meta-val">
          <span class="status-tag {{ $estado_clase }}">{{ $estado }}</span>
        </div>
      </div>
      <div class="meta-cell">
        <div class="meta-lbl">Asesor</div>
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
          <div class="dc-lbl">Apellidos</div>
          <div class="dc-val">{{ $apellidos }}</div>
        </div>
      </div>

      <div class="dg c3">
        <div class="dc">
          <div class="dc-lbl">Fecha de nacimiento</div>
          <div class="dc-val">{{ $fecha_nacimiento }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">Nacionalidad</div>
          <div class="dc-val">{{ $nacionalidad }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">País de residencia</div>
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
          <div class="dc-lbl">Tipo de documento</div>
          <div class="dc-val">{{ $id_tipo }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">Número de documento</div>
          <div class="dc-val">{{ $id_numero }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">Fecha de expedición</div>
          <div class="dc-val">{{ $id_expedicion }}</div>
        </div>
      </div>

      <div class="id-img-row">
        <div class="id-img-box">
          @if($id_imagen_url)
            <img src="{{ $id_imagen_url }}" alt="Documento de identidad">
          @else
            <svg class="id-img-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="16" rx="2"/>
              <circle cx="9" cy="10" r="2"/>
              <path d="M15 8h2M15 12h2M7 16h10"/>
            </svg>
            <div class="id-img-lbl">Frente del documento</div>
            <div class="id-img-sub">Adjunto en el expediente</div>
          @endif
        </div>
        <div class="id-img-box">
          <svg class="id-img-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M7 9h10M7 13h6M7 17h4"/>
          </svg>
          <div class="id-img-lbl">Reverso del documento</div>
          <div class="id-img-sub">Adjunto en el expediente</div>
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
          <div class="dc-lbl">Teléfono móvil</div>
          <div class="dc-val">{{ $telefono }}</div>
        </div>
        <div class="dc">
          <div class="dc-lbl">Correo electrónico</div>
          <div class="dc-val">{{ $email }}</div>
        </div>
      </div>

      <div class="dg" style="grid-template-columns: 1fr;">
        <div class="dc">
          <div class="dc-lbl">Dirección de residencia</div>
          <div class="dc-val">{{ $direccion }}</div>
        </div>
      </div>
    </div>

    <div class="declaration">
      <div class="declaration-title">Declaración del titular</div>
      <div class="declaration-text">
        Yo, <strong>{{ $comprador_nombre }}</strong>, declaro bajo juramento que los datos suministrados en el presente formulario son verídicos, completos y exactos al momento de su llenado. Me comprometo a notificar a Duna Development Group cualquier cambio que se produzca en la información aquí registrada. Asimismo, autorizo el tratamiento de mis datos personales conforme a la política de privacidad de la empresa.
      </div>
    </div>

    <div class="sig-row">
      <div class="sig-block">
        <div class="sig-role">Titular de la compra</div>
        <div class="sig-name">{{ $comprador_nombre }}</div>
        <div class="sig-entity">Comprador · {{ $proyecto }}</div>
        <div class="sig-line"></div>
        <div class="sig-label">Firma · Fecha: _____ / _____ / _________</div>
      </div>
      <div class="sig-block">
        <div class="sig-role">Verificado por</div>
        <div class="sig-name">{{ $asesor }}</div>
        <div class="sig-entity">Asesor Comercial · Duna Development Group</div>
        <div class="sig-line"></div>
        <div class="sig-label">Firma · Sello · Fecha: _____ / _____ / _________</div>
      </div>
    </div>

  </div>

  <div class="footer">
    <div class="footer-l">Duna Development Group · Confidencial</div>
    <div class="footer-c">Este documento contiene información personal protegida. Uso interno exclusivo.</div>
    <div class="footer-r">KYC · Pág. 1 de 1</div>
  </div>

</div>

</body>
</html>
