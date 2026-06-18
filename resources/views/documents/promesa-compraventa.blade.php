<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Makai · Promesa de Venta de Inmueble · {{ $referencia }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
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

/* ── TOOLBAR ── */
.toolbar {
  position: sticky; top: 0; z-index: 50;
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px; padding: 12px 20px;
  background: var(--green-bg); color: #F1EDE3;
}
.toolbar-title { font-size: 12px; letter-spacing: .14em; text-transform: uppercase; opacity: .7; }
.toolbar-btn {
  appearance: none; border: none; cursor: pointer;
  background: #F1EDE3; color: var(--green-bg);
  font-size: 13px; font-weight: 600; letter-spacing: .03em;
  padding: 9px 18px; border-radius: 6px;
}
.toolbar-btn:hover { background: #fff; }

.page {
  width: 210mm; min-height: 297mm; background: #fff;
  display: flex; flex-direction: column;
  page-break-after: always; break-after: page;
  margin: 16px auto;
}
.page:last-child { page-break-after: auto; break-after: auto; }

.hdr { background: var(--green-bg); padding: 16px 44px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,.06); flex-shrink: 0; }
.hdr-logo { display: flex; align-items: center; gap: 12px; }
.hdr-iso { width: 28px; height: 28px; flex-shrink: 0; }
.hdr-name { font-size: 14px; font-weight: 600; color: #F1EDE3; letter-spacing: .2em; }
.hdr-right { text-align: right; }
.hdr-doc { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.35); letter-spacing: .18em; text-transform: uppercase; }
.hdr-ref { font-size: 8.5px; color: rgba(241,237,227,.5); margin-top: 4px; }

.ph { display: flex; background: var(--green-bg); padding: 10px 44px; align-items: center; justify-content: space-between; flex-shrink: 0; }
.ph-left { display: flex; align-items: center; gap: 10px; }
.ph-iso { width: 18px; height: 18px; flex-shrink: 0; }
.ph-name { font-size: 10px; font-weight: 600; color: rgba(241,237,227,.7); letter-spacing: .18em; }
.ph-right { text-align: right; }
.ph-doc { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.3); letter-spacing: .14em; text-transform: uppercase; }
.ph-pg { font-size: 8px; color: rgba(241,237,227,.4); margin-top: 3px; }

.footer { background: var(--green-bg); padding: 7px 44px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; margin-top: auto; }
.footer-l { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.4); letter-spacing: .14em; text-transform: uppercase; }
.footer-c { font-size: 6.5px; color: rgba(241,237,227,.22); }
.footer-r { font-size: 7px; color: rgba(241,237,227,.3); }

.cover { padding: 44px 52px 20px; flex: 1; }
.cover-eyebrow { font-size: 7.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .22em; text-transform: uppercase; margin-bottom: 10px; }
.cover-title { font-size: 26px; font-weight: 300; color: var(--ink); letter-spacing: -.02em; line-height: 1.1; margin-bottom: 4px; }
.cover-title strong { font-weight: 700; }
.cover-project { font-size: 9px; color: var(--ink-mute); letter-spacing: .1em; margin-bottom: 36px; }
.cover-rule { border: none; border-top: 1.5px solid var(--ink); margin-bottom: 32px; }
.cover-parties { display: grid; grid-template-columns: 1fr 1px 1fr; gap: 0 28px; margin-bottom: 32px; }
.cover-party-divider { background: var(--rule); }
.cover-party-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--rule-lt); }
.cover-party-name { font-size: 12px; font-weight: 700; color: var(--ink); line-height: 1.3; margin-bottom: 3px; }
.cover-party-sub { font-size: 8px; color: var(--green); font-weight: 500; margin-bottom: 12px; }
.cover-party-detail { font-size: 7.5px; color: var(--ink-mid); line-height: 1.8; }
.cover-party-detail b { color: var(--ink); font-weight: 500; }
.cover-property { margin-bottom: 28px; }
.cover-prop-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 14px; }
.cover-prop-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-top: 1px solid var(--rule); border-left: 1px solid var(--rule); }
.cover-prop-cell { border-right: 1px solid var(--rule); border-bottom: 1px solid var(--rule); padding: 9px 12px; }
.cover-prop-cell-label { font-size: 6.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 4px; }
.cover-prop-cell-val { font-size: 10.5px; font-weight: 600; color: var(--ink); }
.cover-price { display: flex; align-items: baseline; gap: 16px; padding: 16px 0; border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); margin-bottom: 24px; }
.cover-price-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .18em; text-transform: uppercase; min-width: 80px; }
.cover-price-amount { font-size: 22px; font-weight: 300; color: var(--ink); letter-spacing: -.02em; }
.cover-price-amount strong { font-weight: 700; }
.cover-price-sep { width: 1px; height: 28px; background: var(--rule); flex-shrink: 0; }
.cover-price-lit { font-size: 8px; color: var(--ink-mid); line-height: 1.5; font-style: italic; }
.cover-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.cover-meta-item-label { font-size: 6.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .16em; text-transform: uppercase; margin-bottom: 3px; }
.cover-meta-item-val { font-size: 8.5px; font-weight: 600; color: var(--ink); }

.body { padding: 20px 52px; flex: 1; }
.sec-title { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .24em; text-transform: uppercase; padding-bottom: 6px; border-bottom: 1.5px solid var(--ink); margin-bottom: 14px; }
.p { font-size: 8.5px; color: var(--ink-mid); line-height: 1.7; text-align: justify; margin-bottom: 8px; }
.p strong { color: var(--ink); font-weight: 600; }
.f { font-style: normal; font-weight: 600; color: var(--ink); }
.pc { margin-bottom: 11px; }
.pc-tag { font-size: 7px; font-weight: 600; color: var(--green); letter-spacing: .16em; text-transform: uppercase; margin-bottom: 3px; }
.art { margin-bottom: 16px; }
.art-hdr { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.art-hdr::after { content: ''; flex: 1; height: 1px; background: var(--rule); }
.art-num { font-size: 7px; font-weight: 700; color: var(--green); letter-spacing: .2em; text-transform: uppercase; white-space: nowrap; }
.art-name { font-size: 9px; font-weight: 700; color: var(--ink); letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; }
.sub { padding-left: 16px; margin-bottom: 8px; }
.sub-id { font-size: 7.5px; font-weight: 700; color: var(--ink); margin-bottom: 3px; }
.note { border-left: 2px solid var(--rule); padding: 6px 12px; margin: 8px 0 8px 16px; }
.note-id { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 4px; }
.qblock { border-left: 3px solid var(--ink); padding: 8px 14px; margin: 8px 0; background: var(--field-bg); }
.axtlist { padding-left: 18px; margin-top: 6px; }
.axtlist li { font-size: 8.5px; color: var(--ink-mid); line-height: 1.9; }
.sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin: 20px 0; }
.sig-role { font-size: 6.5px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 4px; }
.sig-name { font-size: 11px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.sig-entity { font-size: 8px; color: var(--ink-mute); margin-bottom: 18px; }
.sig-box { border-top: 1px solid var(--ink); border-bottom: 1px solid var(--rule-lt); height: 52px; margin-bottom: 6px; }
.sig-label { font-size: 6.5px; color: var(--ink-mute); letter-spacing: .1em; }

@media print {
  body { background: #fff; }
  .toolbar { display: none !important; }
  .page { margin: 0; }
}
</style>
</head>
<body>

<svg style="display:none">
  <symbol id="iso" viewBox="0 0 87 87" fill="none">
    <path d="M43.2607 0.00439453C48.6922 0.111782 54.1102 0.474765 59.1484 3.27686C64.4825 6.24346 70.3854 8.20656 74.7549 12.7739C78.4586 16.6464 81.5372 20.8815 82.7842 26.2808C83.4698 29.2474 84.3912 32.1577 85.2314 35.0874C86.8379 40.6882 86.9721 46.3025 85.2109 51.8765C82.7876 59.5514 79.7994 66.9715 74.8115 73.3745C73.82 74.6463 72.7314 75.9118 71.4609 76.8716C63.0732 83.2078 53.7484 87.1215 43.2578 86.228L42.2422 86.1235C27.9273 84.4858 16.5973 77.3406 8.55762 65.189C2.30603 55.7422 0.201666 49.0606 0 38.0063C0.0874549 30.1369 3.18353 22.1503 10.4199 15.8647C15.0044 11.8847 19.3436 7.63278 24.9297 5.04541C30.7712 2.34396 36.7436 -0.11974 43.2607 0.00439453ZM44.8242 4.26611C39.423 3.97751 34.5219 4.66552 29.9609 7.64209C25.5445 10.5281 21.0539 13.2969 16.6475 16.1997C8.93708 21.281 4.71592 28.2933 4.6582 37.6294V38.5405C4.83967 40.5875 4.81015 42.6814 5.24707 44.6714C6.92091 52.2825 10.1804 59.1625 15.3027 65.0151C24.734 75.7909 35.957 82.8947 50.8467 80.8745C62.0895 79.3509 71.1376 74.0657 76.2432 63.2397C77.3288 60.9343 78.4818 58.6521 79.4531 56.2964C81.8731 50.4303 83.3318 44.4231 81.5605 38.0503C80.6497 34.7749 79.6177 31.5297 78.8379 28.2241C77.4363 22.2944 74.1935 17.6059 69.5049 13.9614C62.2349 8.31019 54.2015 4.76287 44.8242 4.26611ZM42.9453 9.02881C54.4033 7.93143 62.7993 13.5556 69.5518 21.7104C74.5934 27.798 76.1565 35.399 75.2188 43.3491C74.7045 47.7118 74.0623 52.0677 73.2422 56.3833C72.8657 58.3666 72.2774 60.4776 71.1816 62.1187C65.0241 71.3371 56.0432 76.1362 45.4121 77.6665C39.369 78.5377 33.9851 76.1995 29.3857 72.3335L28.9395 71.9546C25.1651 68.666 21.4613 65.2498 18.0566 61.5786C16.282 59.6658 14.9708 57.1852 13.8818 54.7622C9.85194 45.7886 9.86523 37.1001 15.1455 28.3647C18.9099 22.1397 24.1062 17.4751 29.373 12.8306C33.2215 9.43776 38.1658 8.98854 42.9453 9.02881ZM50.3965 12.8374C46.7867 11.8375 42.7331 12.1224 38.915 12.3774C36.4144 12.5453 33.6012 13.1429 31.5947 14.5288C26.0188 18.3847 21.004 22.9049 18.0664 29.3647C16.0329 33.8449 13.4415 38.3657 14.46 43.3257C15.428 48.0407 16.8094 53.1116 19.542 56.897C23.2022 61.9709 28.0723 66.2764 32.9189 70.3101C36.6463 73.4108 41.4393 75.25 46.333 73.9146C51.499 72.5017 56.6758 70.6627 61.3848 68.1323C66.5977 65.3301 70.365 60.8163 71.2725 54.561C71.9547 49.8629 72.4227 45.1279 72.9873 40.4097C73.2562 34.9733 71.4846 29.9527 68.7051 25.6841C64.4062 19.083 57.8582 14.9046 50.3965 12.8374ZM37.9707 17.8882C44.192 17.311 50.2788 18.1127 56.2178 20.1597C63.6458 22.7169 69.3697 27.4391 68.7109 39.1411C68.8857 45.8997 66.8455 53.1682 63.8945 60.2222C63.5719 60.994 63.1688 61.8569 62.5537 62.3569C59.3405 64.9644 56.1408 67.6226 52.7461 69.9751C48.7094 72.7704 44.3332 71.7127 40.2227 70.1724C33.6552 67.7146 28.3275 63.6004 24.7588 57.4146L24.415 56.8101C23.4302 55.0113 22.3478 53.2656 21.3193 51.4937C18.2946 46.2822 19.0439 40.7282 19.6455 35.104C20.4421 27.6405 24.7112 22.7506 30.8486 19.4048C32.9023 18.2873 35.5575 18.113 37.9707 17.8882ZM57.8887 24.3882C49.4657 20.7907 40.7769 19.5757 31.833 22.9282C29.6526 23.7462 28.0774 25.0036 26.6816 26.6978L26.4014 27.0425C23.2823 31.0191 22.5666 35.6338 22.7783 40.8286C21.9851 44.2146 23.3395 47.3358 25.3359 50.3091C26.5594 52.1313 27.676 54.0275 28.8457 55.8833C31.8841 60.6989 36.1223 64.183 41.127 66.6294C44.3871 68.2233 47.8861 68.5822 51.2471 66.6157C53.6133 65.2264 55.9798 63.8333 58.3057 62.3735C60.6649 60.897 62.3623 58.8098 63.001 56.0649C64.1505 51.1083 65.4746 46.1579 66.1367 41.1274C67.2996 32.2914 64.9604 27.4085 57.8887 24.3882ZM38.7637 24.7915C45.8758 23.919 52.4066 25.241 58.4297 29.3218C61.0075 31.0702 62.513 33.1676 62.2744 36.3354C61.9484 40.6511 61.6828 44.9736 61.1719 49.269C60.3447 56.2327 54.1429 60.7587 48.2588 62.3345L47.6875 62.478C43.3248 63.5116 39.9204 61.4874 37.1475 58.5142C32.4688 53.4938 29.5074 47.6413 29.6855 40.396C30.22 37.2616 30.5762 34.0831 31.3291 31.0024C32.2098 27.3918 35.154 25.2345 38.7637 24.7915ZM56.9678 32.7847C52.4404 29.553 47.4623 27.6943 41.7451 28.4058C39.789 28.6507 37.7757 28.4458 35.1475 28.4458C34.4479 29.2995 33.8809 31.5402 33.4473 35.1685L33.3623 35.9058C32.475 44.0236 34.8074 50.9101 41.0186 56.2896C42.9108 57.9305 45.1227 59.0677 47.6064 58.2456C52.6984 56.561 56.6213 53.5042 57.4785 47.7524C58.0533 43.8999 58.4602 40.0032 58.6182 36.1138C58.6652 35.003 57.8718 33.4324 56.9678 32.7847ZM37.1006 33.8989C37.1544 31.8015 38.1999 30.6135 40.4922 30.9692C41.2483 31.0867 42.0347 30.9898 42.8076 30.9897C46.841 30.6877 50.572 31.456 52.5752 35.3589C53.5129 37.181 53.8761 39.5299 53.7988 41.6138C53.6946 44.3387 52.545 47.0338 52.4912 49.7554C52.4196 53.455 48.2317 55.1708 45.0518 53.4165L44.751 53.2358C40.2874 50.3934 38.3279 45.9932 37.3633 41.0835C36.9062 38.7613 37.0367 36.2949 37.1006 33.8989ZM38.9561 33.2446C39.1779 35.238 39.2522 36.983 39.585 38.6743C40.3983 42.7852 42.6669 46.0637 45.7188 48.7954C46.2834 49.3021 47.2072 49.3969 47.9668 49.6821C48.1953 49.0446 48.5148 48.4233 48.6426 47.769C49.0929 45.4871 49.3385 43.1547 49.9199 40.9097C50.6863 37.9598 49.1971 34.9161 46.2158 34.2583C44.2573 33.8267 42.2474 33.6539 39.9541 33.3726L38.9561 33.2446Z" fill="white"/>
  </symbol>
</svg>

<div class="toolbar">
  <span class="toolbar-title">Promesa de Compraventa · {{ $referencia }}</span>
  <button class="toolbar-btn" onclick="window.print()">Descargar PDF</button>
</div>

@php
  $phHeader = '<div class="ph"><div class="ph-left"><svg class="ph-iso" viewBox="0 0 87 87" fill="none"><use href="#iso"/></svg><div class="ph-name">MAKAI</div></div><div class="ph-right"><div class="ph-doc">Promesa de Venta de Inmueble</div><div class="ph-pg">Makai Residences · Cap Cana, R.D.</div></div></div>';
  $ftr = '<div class="footer"><div class="footer-l">MAKAI · Duna Development Group</div><div class="footer-c">Documento Confidencial · Uso Exclusivo de las Partes</div><div class="footer-r">Makai Residences · Cap Cana, R.D.</div></div>';
@endphp

<!-- PÁGINA 1 — PORTADA -->
<div class="page">
  <div class="hdr">
    <div class="hdr-logo">
      <svg class="hdr-iso" viewBox="0 0 87 87" fill="none"><use href="#iso"/></svg>
      <div class="hdr-name">MAKAI</div>
    </div>
    <div class="hdr-right">
      <div class="hdr-doc">Documento Legal · Confidencial</div>
      <div class="hdr-ref">Ref: <span class="f">{{ $referencia }}</span></div>
    </div>
  </div>

  <div class="cover">
    <div class="cover-eyebrow">Documento Legal Oficial</div>
    <div class="cover-title"><strong>Promesa</strong> de Venta de Inmueble</div>
    <div class="cover-project">Proyecto Makai Residences — Dolce by Wyndham · Cap Cana, República Dominicana</div>
    <hr class="cover-rule">

    <div class="cover-parties">
      <div>
        <div class="cover-party-label">El Desarrollador — Promitente Vendedor</div>
        <div class="cover-party-name">IGUANAS LAKE CONDO &amp;<br>RESIDENCES, S.R.L.</div>
        <div class="cover-party-sub">Representada por JOSE ANTONIO GONZALEZ DIAZ</div>
        <div class="cover-party-detail">
          <b>RNC:</b> 1-33-17485-5 &nbsp;·&nbsp; <b>Reg. Mercantil:</b> #203755SD<br>
          <b>Domicilio:</b> Calle Abraham Lincoln, No. 51,<br>
          &nbsp;&nbsp;&nbsp;Ensanche Serralles, Santo Domingo<br>
          <b>Representante:</b> Nacionalidad española<br>
          &nbsp;&nbsp;&nbsp;Pasaporte No. XDE584016<br>
          <b>Correo:</b> jgonzalez@dunadevelopment.com
        </div>
      </div>
      <div class="cover-party-divider"></div>
      <div>
        <div class="cover-party-label">El Promitente Comprador</div>
        <div class="cover-party-name"><span class="f">{{ $comprador_nombre }}</span></div>
        <div class="cover-party-sub"><span class="f">{{ $comprador_tipo }}</span></div>
        <div class="cover-party-detail">
          <b>Pasaporte/ID:</b> <span class="f">{{ $comprador_pasaporte }}</span><br>
          <b>ID Empresa:</b> <span class="f">{{ $comprador_empresa_id }}</span><br>
          <b>Nacionalidad:</b> <span class="f">{{ $comprador_nacionalidad }}</span><br>
          <b>Estado Civil:</b> <span class="f">{{ $comprador_estado_civil }}</span><br>
          <b>Dirección:</b> <span class="f">{{ $comprador_direccion }}</span><br>
          <b>Correo:</b> <span class="f">{{ $comprador_email }}</span><br>
          <b>Ocupación:</b> <span class="f">{{ $comprador_ocupacion }}</span>
        </div>
      </div>
    </div>

    <div class="cover-property">
      <div class="cover-prop-label">Unidad Objeto de la Promesa</div>
      <div class="cover-prop-grid">
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Proyecto</div><div class="cover-prop-cell-val">MAKAI Residences</div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Unidad</div><div class="cover-prop-cell-val"><span class="f">{{ $unidad }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Nivel</div><div class="cover-prop-cell-val"><span class="f">{{ $nivel }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Área (m²)</div><div class="cover-prop-cell-val"><span class="f">{{ $area }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Dormitorios</div><div class="cover-prop-cell-val"><span class="f">{{ $dormitorios }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Baños</div><div class="cover-prop-cell-val"><span class="f">{{ $banos }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Estacionamientos</div><div class="cover-prop-cell-val"><span class="f">{{ $estacionamientos }}</span></div></div>
        <div class="cover-prop-cell"><div class="cover-prop-cell-label">Ubicación</div><div class="cover-prop-cell-val">Cap Cana, La Altagracia</div></div>
      </div>
    </div>

    <div class="cover-price">
      <div class="cover-price-label">Precio de Venta</div>
      <div class="cover-price-amount">US$ <strong><span class="f">{{ $precio_usd }}</span></strong></div>
      <div class="cover-price-sep"></div>
      <div class="cover-price-lit"><span class="f">{{ $precio_letras }}</span><br>DÓLARES ESTADOUNIDENSES CON 00/100</div>
    </div>

    <div class="cover-meta">
      <div>
        <div class="cover-meta-item-label">Fecha de Suscripción</div>
        <div class="cover-meta-item-val"><span class="f">{{ $fecha }}</span></div>
      </div>
      <div>
        <div class="cover-meta-item-label">Plazo de Entrega</div>
        <div class="cover-meta-item-val">24 meses desde permisos</div>
      </div>
      <div>
        <div class="cover-meta-item-label">Operador Exclusivo</div>
        <div class="cover-meta-item-val">Ophdom Condos, S.R.L. (15 años)</div>
      </div>
      <div>
        <div class="cover-meta-item-label">Marca Hotelera</div>
        <div class="cover-meta-item-val">Dolce by Wyndham</div>
      </div>
      <div>
        <div class="cover-meta-item-label">Ley Aplicable</div>
        <div class="cover-meta-item-val">Derecho Dominicano</div>
      </div>
      <div>
        <div class="cover-meta-item-label">Referencia</div>
        <div class="cover-meta-item-val"><span class="f">{{ $referencia }}</span></div>
      </div>
    </div>
  </div>

  {!! $ftr !!}
</div>

<!-- PÁGINA 2 — PARTES Y PREÁMBULO -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">
    <div class="sec-title">Promesa de Venta de Inmueble — Entre:</div>

    <p class="p">De una parte, <strong>IGUANAS LAKE CONDO &amp; RESIDENCES, S.R.L.</strong>, sociedad constituida de conformidad con las leyes de la República Dominicana, provista del Registro Nacional de Contribuyentes (RNC) número: 1-33-17485-5, Registro Mercantil # 203755SD, con domicilio social en Calle Abraham Lincoln, No. 51, ensanche Serralles, Santo Domingo, República Dominicana, y debidamente representada por el señor <strong>JOSE ANTONIO GONZALEZ DIAZ</strong>, de nacionalidad española, mayor de edad, soltero, portador del pasaporte No. XDE584016, domiciliado y residente en la Calle A, No. 9, Urb. Fernández, Santo Domingo, Distrito Nacional, República Dominicana y accidentalmente en Punta Cana, Distrito Municipal de Verón, Municipio de Higüey, Provincia La Altagracia, República Dominicana, con el correo electrónico jgonzalez@dunadevelopment.com, quien en lo adelante y para los fines del presente Contrato se denominará <strong>EL DESARROLLADOR</strong>.</p>

    <p class="p">Nombre(s) de "El Comprador": <span class="f">{{ $comprador_nombre }}</span><br>
    Empresa o Individuo: <span class="f">{{ $comprador_tipo }}</span><br>
    Número de Pasaporte (Individuo o representante): <span class="f">{{ $comprador_pasaporte }}</span><br>
    Número de Identificación de la Empresa: <span class="f">{{ $comprador_empresa_id }}</span><br>
    Nacionalidad(es): <span class="f">{{ $comprador_nacionalidad }}</span> &nbsp;·&nbsp; Estado Civil: <span class="f">{{ $comprador_estado_civil }}</span><br>
    Dirección: <span class="f">{{ $comprador_direccion }}</span><br>
    Correo(s) Electrónico(s): <span class="f">{{ $comprador_email }}</span> &nbsp;·&nbsp; Ocupación(es): <span class="f">{{ $comprador_ocupacion }}</span></p>

    <p class="p">Cuando sean designadas conjuntamente EL DESARROLLADOR y EL PROMITENTE COMPRADOR a los fines del presente Contrato, se denominarán indistintamente como <strong>LAS PARTES</strong>.</p>

    <div class="sec-title" style="margin-top:16px;">Preámbulo</div>

    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">LA PRIMERA PARTE es propietaria del inmueble que se describe más adelante en el Artículo Primero de este acuerdo, el cual se encuentra localizado dentro de la ciudad destino Cap Cana (en lo adelante "Cap Cana"), ubicada en la localidad de Juanillo, Provincia La Altagracia.</p>
    </div>
    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">CAP CANA, está permitiendo la construcción y el desarrollo por y a riesgo de terceros desarrolladores de una serie de edificaciones dentro del proyecto residencial y comercial, el cual estará dotado de áreas destinadas a la construcción de viviendas, edificios residenciales, clústeres de viviendas individuales y de apartamentos, edificios comerciales, escuela, iglesia, y demás amenidades.</p>
    </div>
    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">EL DESARROLLADOR, dentro de las instalaciones de CAP CANA está llevando a cabo un desarrollo inmobiliario residencial denominado Proyecto MAKAI RESIDENCES, el cual será operado bajo los estándares y usos de la marca DOLCE BY WYNDHAM (en lo adelante EL CONDOMINIO).</p>
    </div>
    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">LA PRIMERA PARTE ha manifestado su interés en vender dicho inmueble a favor de LA SEGUNDA PARTE, quien ha manifestado su interés en adquirirlo, de conformidad con las condiciones pactadas en lo adelante del presente Contrato.</p>
    </div>
    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">LA SEGUNDA PARTE reconoce, acepta, se compromete a cumplir y se somete a las regulaciones de Cap Cana, entre las cuales se pueden mencionar (i) la Declaración Maestra de Regulaciones de Cap Cana (en lo adelante, la "Declaración Maestra de Cap Cana"), la cual es parte integral del presente Contrato y se anexa al mismo como Anexo F.</p>
      <p class="p"><strong>Estos documentos</strong> (denominados conjuntamente, los "Documentos de Cap Cana"), establecen los derechos y obligaciones de todos los adquirientes de inmuebles en Cap Cana sin importar la fecha de adquisición, con la finalidad de garantizar que el desarrollo de Cap Cana y de las construcciones que en el mismo se edifiquen, y su uso, se realicen de la manera más uniforme posible y de acuerdo con la naturaleza misma de Cap Cana.</p>
      <p class="p"><strong>En consecuencia,</strong> LA SEGUNDA PARTE acepta que es una condición indispensable, sin la cual no se hubiese suscrito este Contrato, su adhesión a los Documentos de Cap Cana y las modificaciones que sean introducidas a los mismos de tiempo en tiempo, y procede a firmar en señal de aceptación tanto las Condiciones y Obligaciones en Cap Cana, como la Declaración Maestra de Cap Cana.</p>
    </div>
    <div class="pc">
      <div class="pc-tag">Por Cuanto</div>
      <p class="p">En el entendido de que el anterior preámbulo se considera parte integrante del presente Contrato, de común acuerdo y buena fe entre LAS PARTES;</p>
    </div>

    <p class="p" style="font-weight:700; color:var(--ink); text-align:center; margin-top:14px; letter-spacing:.05em;">HAN CONVENIDO Y PACTADO LO SIGUIENTE:</p>
  </div>
  {!! $ftr !!}
</div>

<!-- PÁGINA 3 — ART. 1 · ART. 2 -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 1</span><span class="art-name">Objeto del Contrato</span></div>
      <p class="p"><strong>Venta de El Inmueble:</strong> EL DESARROLLADOR por medio del presente Contrato otorga a EL PROMITENTE COMPRADOR con todas las garantías ordinarias de derecho y con sujeción a las demás estipulaciones contenidas en el presente Contrato, formal promesa de venta, cesión y traspaso, libre de cargas y gravámenes, ocupantes y afectaciones de cualquier género respecto de EL INMUEBLE que a continuación se describe, la cual se convertirá en una venta perfecta para LAS PARTES, una vez se cumplan con las condiciones que se establecen en el presente Contrato:</p>
      <div class="qblock">
        <p class="p" style="margin:0; font-style:italic;">"Unidad <span class="f">{{ $unidad }}</span>, ubicada en el nivel <span class="f">{{ $nivel }}</span>, del proyecto MAKAI, con un área de <span class="f">{{ $area }}</span> m2. La unidad tendrá la siguiente distribución: recibidor, salón-cocina, terraza, <span class="f">{{ $dormitorios }}</span> dormitorio, <span class="f">{{ $banos }}</span> baño, <span class="f">{{ $estacionamientos }}</span> estacionamiento. De acuerdo al anexo A."</p>
      </div>
      <p class="p">El condominio contará con: 4 ascensores, escaleras de emergencias, Spa, gimnasio, cine, área de coworking, cancha de paddle, cancha de pickleball, 3 piscinas, área de mascotas y área de juegos.</p>
      <p class="p">EL PROMITENTE COMPRADOR declara, reconoce y acepta, que no podrá requerir de EL DESARROLLADOR el cambio o sustitución de El Inmueble tras la firma del presente Contrato, a menos que EL DESARROLLADOR lo acepte.</p>
      <div class="note">
        <div class="note-id">Cargas y Gravámenes</div>
        <p class="p" style="margin-bottom:5px;">EL DESARROLLADOR DECLARA y EL PROMITENTE COMPRADOR reconoce y acepta saber, que EL PROYECTO puede constituir la garantía de una facilidad crediticia con una entidad de intermediación financiera, para su desarrollo, entendiéndose que dicha garantía permanecerá vigente, respecto de la unidad inmobiliaria objeto de la presente promesa, hasta que EL DESARROLLADOR, la libere con el abono del saldo de su precio de venta y respecto del resto del PROYECTO, hasta que efectúe el saldo de la facilidad crediticia con los flujos que proyectan generarse con el saldo del precio de venta de la mayor parte del resto de las unidades inmobiliarias que lo conforman.</p>
        <p class="p" style="margin:0;">EL DESARROLLADOR DECLARA que se compromete a entregar el inmueble, libre de cualquier garantía crediticia, dígase libre de cargas y gravámenes.</p>
      </div>
      <div class="note">
        <div class="note-id">Párrafo</div>
        <p class="p" style="margin-bottom:4px;">Queda expresamente convenido entre las partes que, para el caso de que, al momento de la entrega de EL INMUEBLE, debidamente terminado, este resulte con un área mayor o menor a la cantidad de metros cuadrados estimados en el presente contrato, dicha variación solo podrá producirse dentro de un margen máximo del cinco por ciento (5%), ya sea en exceso o en defecto, respecto de la superficie originalmente pactada. En tal supuesto, el precio de venta será ajustado única y exclusivamente de manera proporcional a la variación real de la superficie, sin que dicha circunstancia otorgue a EL PROMITENTE COMPRADOR derecho alguno a rescindir unilateralmente el contrato, ni a reclamar la eliminación, reducción o inaplicabilidad de las penalidades contractuales pactadas, las cuales se mantendrán plenamente vigentes y exigibles.</p>
        <p class="p" style="margin:0;">Asimismo, LAS PARTES acuerdan que el margen antes indicado constituye un límite máximo absoluto y no acumulativo, por lo que en ningún caso la variación de la superficie podrá exceder el cinco por ciento (5%) de los metros cuadrados originalmente estimados, ni aun por causas técnicas, constructivas, arquitectónicas o de ajustes de diseño. Cualquier diferencia que sobrepase dicho porcentaje requerirá necesariamente el consentimiento previo y por escrito de EL PROMITENTE COMPRADOR, o en su defecto facultará a este último a exigir la regularización de la superficie o la revisión expresa de las condiciones económicas correspondientes, sin que pueda imponerse unilateralmente una variación superior al referido límite.</p>
      </div>
      <p class="p"><strong>Justificación de Derecho de propiedad.</strong> EL DESARROLLADOR justifica su derecho de propiedad sobre El Inmueble precedentemente descrito mediante Certificado de Titulo Matricula No. 3000050704 y 3000050703.</p>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 2</span><span class="art-name">Entrega del Certificado de Título</span></div>
      <p class="p">Por encontrarse el residencial denominado Proyecto MAKAI RESIDENCES sobre los inmuebles identificados como 506422751836 y Número 506422658172, matriculas 3000050704, 3000050703, y debido a que se someterá a un proceso de Régimen de Condominio, LAS PARTES convienen lo siguiente:</p>
      <div class="sub">
        <div class="sub-id">2.1.1.</div>
        <p class="p">EL DESARROLLADOR entrega a la firma del presente Contrato copia de los planos particulares provisionales correspondientes a El Inmueble descrito en el Ordinal 1.1 del presente Contrato.</p>
      </div>
      <div class="sub">
        <div class="sub-id">2.1.2.</div>
        <p class="p">EL PROMITENTE COMPRADOR no depositará documento alguno ni realizará actuación alguna por ante el Registrador de Títulos correspondiente o cualquier otro organismo competente que pueda comprometer la celeridad del referido proceso, obligación ésta que se mantendrá vigente hasta tanto EL DESARROLLADOR le haya entregado debidamente el Certificado de Título correspondiente contra acuse de recibo, descargo y finiquito firmado por EL PROMITENTE COMPRADOR;</p>
      </div>
      <div class="sub">
        <div class="sub-id">2.1.3.</div>
        <p class="p">EL DESARROLLADOR, hace constar que realizará sus mejores gestiones para obtener la clasificación definitiva del proyecto MAKAI RESIDENCES, bajo los beneficios de la Ley 158-01 de incentivo turístico y sus modificaciones. La aprobación del proyecto bajo dicha clasificación es a total discreción del consejo de fomento turístico CONFOTUR, por lo que a la fecha EL DESARROLLADOR no puede otorgar garantía alguna respecto a la posibilidad de obtener dicha clasificación.</p>
      </div>
      <div class="sub">
        <div class="sub-id">2.1.4.</div>
        <p class="p">Una vez haya concluido el proceso de Régimen de Condominio mencionado anteriormente, EL DESARROLLADOR entregará a EL PROMITENTE COMPRADOR el Certificado de Título (Duplicado del Dueño) correspondiente a El Inmueble a los fines de que éste deposite por ante el Registrador de Títulos correspondiente la solicitud de registro de la transferencia; previo a esto el comprador se compromete a realizar la gestión pertinente de los beneficios de la resolución definitiva que gozará el proyecto una vez sea clasificado de forma definitiva bajo la ley de fomento turístico (CONFOTUR), en caso de requerirlo sin que esto afecte el plan de pago acordado.</p>
      </div>
      <div class="sub">
        <div class="sub-id">2.1.5.</div>
        <p class="p">EL PROMITENTE COMPRADOR acepta que el traspaso de la propiedad de El Inmueble y la entrega del mismo, así como el pago y cancelación definitiva de la hipoteca en caso de existir, estarán sujetos al pago total del precio de compra, así como a la conclusión del Régimen de Condominio, en el entendido de que dicho pago constituye una condición suspensiva de la obligación puesta a cargo de EL DESARROLLADOR. A tales fines EL DESARROLLADOR retendrá el correspondiente duplicado del dueño del Certificado de Título correspondiente de El Inmueble hasta tanto EL PROMITENTE COMPRADOR haya cumplido con su obligación de pago total del precio de compra y demás gastos relacionados.</p>
      </div>
      <div class="note">
        <div class="note-id">Párrafo I</div>
        <p class="p" style="margin:0;">El PROMITENTE COMPRADOR tendrá el derecho de retener el diez por ciento (10%) del precio total del inmueble hasta tanto EL DESARROLLADOR haga entrega formal del Título de Propiedad debidamente registrado a favor del PROMITENTE COMPRADOR. Dicha retención no se considerará incumplimiento ni mora por parte del PROMITENTE COMPRADOR, siempre que la misma se mantenga exclusivamente con el propósito de garantizar la efectiva entrega del referido título. Una vez EL DESARROLLADOR entregue el Título de Propiedad en las condiciones legales correspondientes, el PROMITENTE COMPRADOR procederá a realizar el pago del monto retenido dentro de un plazo no mayor de cinco (5) días hábiles contados a partir de dicha entrega.</p>
      </div>
    </div>

  </div>
  {!! $ftr !!}
</div>

<!-- PÁGINA 4 — ART. 3 · ART. 4 -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 3</span><span class="art-name">Gastos y Honorarios</span></div>
      <p class="p">Los gastos y honorarios legales generados por el traspaso definitivo de El Inmueble a favor de EL PROMITENTE COMPRADOR así como cualquier gasto generado por las diligencias, registro e inscripción de la transferencia, estará a cargo de EL PROMITENTE COMPRADOR.</p>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 4</span><span class="art-name">Precio de El Inmueble</span></div>
      <p class="p">El precio de EL Inmueble objeto de este Contrato asciende a la suma de <strong><span class="f">{{ $precio_letras }}</span> DOLARES ESTADOUNIDENSES CON 00/100 (US$ <span class="f">{{ $precio_usd }}</span>)</strong>, los cuales EL PROMITENTE COMPRADOR se obligan a pagar de la siguiente manera: Plan de pagos según el calendario adjunto.</p>
      <div class="sub">
        <div class="sub-id">4.1</div>
        <p class="p">El precio de venta del inmueble podrá ser objeto de ajuste únicamente en los siguientes casos, pudiendo dicho ajuste representar un aumento que oscile entre un cinco por ciento (5%) y un ocho por ciento (8%) del precio originalmente pactado:</p>
        <p class="p"><strong>A)</strong> En caso de que se produzcan aumentos significativos en el costo de los materiales de construcción, derivados de situaciones de fuerza mayor o circunstancias extraordinarias ajenas a la voluntad de EL DESARROLLADOR, tales como pandemias, conflictos armados, desastres naturales u otros eventos que afecten de manera directa el mercado de insumos de la construcción. En tales casos, EL DESARROLLADOR podrá realizar los ajustes correspondientes al precio de venta, previa notificación escrita y debidamente justificada a EL PROMITENTE COMPRADOR.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.1.2</div>
        <p class="p">Para la determinación objetiva del porcentaje de variación aplicable, se tomará como referencia el aumento efectivo en los costos de los materiales e insumos de construcción, así como el Índice de Costos Directos de la Construcción de Viviendas (ICDV) elaborado por la Oficina Nacional de Estadística (ONE) de la República Dominicana, disponible en https://www.one.gob.do/; y, de ser pertinente, otros indicadores oficiales o elaborados por la Asociación Dominicana de Constructores y Promotores de Viviendas (ACOPROVI), cuyas publicaciones se encuentran en https://acoprovi.org/, u otra entidad oficial reconocida que refleje de forma confiable las variaciones del sector construcción en la República Dominicana.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.1.3</div>
        <p class="p">El Desarrollador no podrá, en ningún caso, aumentar unilateralmente el precio del inmueble; cualquier ajuste deberá ser fehacientemente probado, notificado al Comprador con la debida anticipación y estrictamente vinculado a los índices de indexación oficiales establecidos por las autoridades gubernamentales competentes indicadas anteriormente.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.2</div>
        <p class="p">Se conviene que con el cumplimiento total de la planificación de pago establecida en el artículo 4, se materializará la presente promesa condicional de compraventa y se suscribirá un contrato de venta definitivo suscrito exclusivamente entre EL DESARROLLADOR y EL PROMITENTE COMPRADOR, por medio del cual EL DESARROLLADOR traspasará a EL PROMITENTE COMPRADOR EL Inmueble.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.3.1</div>
        <p class="p">El pago del saldo que deberá efectuar EL PROMITENTE COMPRADOR podrá ser realizado por pago directo a EL DESARROLLADOR o por financiamiento bancario solicitado, gestionado y obtenido, bajo la responsabilidad única de EL PROMITENTE COMPRADOR, antes de la entrega de El Inmueble, una vez recibe los documentos necesarios para el mismo por parte de EL DESARROLLADOR.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.3.2 — Pagos en Moneda Extranjera</div>
        <p class="p">Queda expresamente convenido entre LAS PARTES que es de la esencia misma de este Contrato que los pagos estipulados en el mismo sean efectuados en dólares estadounidenses (US$), no pudiendo ser obligado EL DESARROLLADOR a aceptar como pago, una suma equivalente en pesos dominicanos (RD$) o cualquier otra moneda de curso legal o extranjera.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.3.5</div>
        <p class="p">En caso de que, por cualquier causa, EL PROMITENTE COMPRADOR decidiera de manera unilateral rescindir el presente Contrato o incurriera en incumplimiento de cualquiera de las obligaciones asumidas en virtud del mismo, EL DESARROLLADOR tendrá derecho, a título de indemnización por daños, perjuicios y gastos operativos, a retener el veinte por ciento (20%) del precio de venta del inmueble, así como a deducir previamente los honorarios por comisiones, gastos administrativos, costos de intermediación, mercadeo y cualesquiera otras erogaciones razonables en que haya incurrido con motivo de la presente operación.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.3.7.</div>
        <p class="p">Todo pago realizado fuera de los quince (15) días siguientes a la fecha en la cual debió haber sido realizado, estará sujeto al pago de una penalidad por mora calculada sobre la base de una tasa de interés mensual equivalente al tres por ciento (3%) del monto adeudado vencido según planificación de pago. Esta penalidad será calculada desde el momento del incumplimiento hasta el momento del pago de la suma vencida.</p>
      </div>
      <div class="sub">
        <div class="sub-id">4.4.1</div>
        <p class="p">EL PROMITENTE COMPRADOR acepta y reconoce que EL DESARROLLADOR podrá solicitarle y será una obligación de EL PROMITENTE COMPRADOR suministrarle toda la información que resulte necesaria para confirmar el origen lícito de los fondos utilizados para la adquisición de la unidad habitacional en cumplimiento de las disposiciones de la Ley 155-17 sobre Lavado de Activos y Financiamiento al terrorismo.</p>
      </div>
    </div>

  </div>
  {!! $ftr !!}
</div>

<!-- PÁGINA 5 — ART. 5 · ART. 6 · ART. 7 -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 5</span><span class="art-name">Destino de El Inmueble</span></div>
      <div class="sub">
        <div class="sub-id">5.1 — Uso de El Inmueble</div>
        <p class="p">Queda convenido entre LAS PARTES que El Inmueble descrito en el Ordinal 1.1 será utilizado exclusivamente para fines de vivienda. En consecuencia, EL PROMITENTE COMPRADOR se obliga a no instalar o establecer en el mismo ningún tipo de explotación comercial, incluyendo oficinas, bares, restaurantes, salas de baile, sin que esta enunciación resulte limitativa.</p>
      </div>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 6</span><span class="art-name">Régimen de Condominio</span></div>
      <p class="p">Dado el proceso de Régimen de Condominio referido en el artículo 2 de este Contrato, y la necesidad de proceder con la individualización de cada apartamento en el Condominio en cuestión de conformidad con las provisiones de la Ley Número 5038, del 21 de Noviembre de 1958, que instruye un sistema especial para la propiedad por pisos o departamentos modificada por la Ley 108-05 del 25 de Marzo de 2005 que instituye el Registro Inmobiliario y sus Reglamentos, LAS PARTES reconocen que en adición a las condiciones establecidas en el Artículo 3 antes indicado, la entrega del Certificado de Propiedad de El Inmueble objeto de este Contrato estará condicionada a la vez a la constitución del régimen de condominio.</p>
      <div class="sub">
        <div class="sub-id">6.1 — Constitución Régimen de Condominio</div>
        <p class="p">EL PROMITENTE COMPRADOR autoriza a EL DESARROLLADOR a realizar todas las diligencias que sean necesarias para la constitución del régimen de condominio y otorga poder a los mismos para someter ante las autoridades correspondientes El Reglamento, a su vez otorga poder a EL DESARROLLADOR para que en su nombre y representación ejerza los derechos de voto que le corresponderá en las asambleas de condóminos que sean realizadas una vez sea aprobado El Reglamento.</p>
      </div>
      <div class="sub">
        <div class="sub-id">6.1.1.</div>
        <p class="p">En caso de que el comprador decida arrendar o alquilar la propiedad, reconoce, consiente y se obliga a ceder la unidad funcional que adquiere, para que el administrador sea quien disponga el programa de renta que será utilizado para dicho alquiler.</p>
      </div>
      <div class="sub">
        <div class="sub-id">6.1.2.</div>
        <p class="p">EL PROMITENTE COMPRADOR se obliga a ceder a EL DESARROLLADOR la facultad para que sea este último quien designe el administrador del condominio, esto como garantía de mantener el valor de la inversión y la buena administración del mismo, así como la seguridad de los demás propietarios.</p>
      </div>
      <div class="sub">
        <div class="sub-id">6.1.6.</div>
        <p class="p">LAS PARTES reconocen y aceptan expresamente que el proyecto MAKAI RESIDENCES ha sido concebido, estructurado y será operado bajo un modelo de condo-hotel, lo cual implica la integración de las unidades privadas dentro de un esquema de explotación hotelera organizado y centralizado. En ese sentido, EL PROMITENTE COMPRADOR declara, reconoce y otorga su consentimiento expreso, pleno e irrevocable respecto de dicho modelo de operación condo-hotel. Asimismo, LAS PARTES acuerdan que la sociedad OPHDOM CONDOS, S.R.L., o cualquier entidad que válidamente la sustituya o designe el desarrollador, será la operadora exclusiva del CONDOMINIO, por un período de quince (15) años contados a partir de su constitución, teniendo a su cargo la gestión, administración y explotación del programa de rentas de corta estadía (por períodos menores de ciento ochenta (180) días) de todas las unidades del proyecto.</p>
      </div>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 7</span><span class="art-name">Fecha de Entrega</span></div>
      <p class="p">EL DESARROLLADOR se obliga y compromete a entregar EL INMUEBLE totalmente terminado y apto para ser utilizado conforme a su destino antes indicado, sustancialmente con las instalaciones urbanísticas y los acabados; terminaciones e instalaciones básicas, una vez se efectúe el pago total del precio de venta y en un plazo de veinticuatro (24) meses luego de obtener los permisos correspondientes.</p>
      <div class="sub">
        <div class="sub-id">7.2 — Plazo de entrega y período de gracia</div>
        <p class="p">EL DESARROLLADOR se compromete a cumplir con sus obligaciones de entrega dentro del plazo convenido. No obstante, LAS PARTES acuerdan un período de gracia de hasta ciento ochenta (180) días calendarios contados a partir del vencimiento de LA FECHA PROGRAMADA, el cual se otorga en beneficio de EL DESARROLLADOR, sin que durante dicho plazo pueda reputarse incumplimiento de sus obligaciones. Este plazo podrá extenderse cuando el retraso obedezca a causas ajenas a la voluntad de EL DESARROLLADOR.</p>
      </div>
      <div class="sub">
        <div class="sub-id">7.4 — Imposibilidad de entrega y derecho de rescisión</div>
        <p class="p">Si por causas distintas a las previstas EL DESARROLLADOR no pudiese entregar EL INMUEBLE dentro de la FECHA PROGRAMADA, incluyendo el período de gracia y las extensiones derivadas de fuerza mayor o caso fortuito, EL PROMITENTE COMPRADOR podrá optar por la rescisión del presente Contrato y exigir el reembolso de la totalidad de las sumas pagadas, más un interés anual equivalente al tres por ciento (3%) del monto entregado, a título de penalidad e indemnización.</p>
      </div>
    </div>

  </div>
  {!! $ftr !!}
</div>

<!-- PÁGINA 6 — ART. 8 a 11 -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 8</span><span class="art-name">Aseguramiento de El Inmueble</span></div>
      <p class="p">EL PROMITENTE COMPRADOR se obliga a mantener asegurado El Inmueble descrito en el Ordinal 1.1 después de recibirlo con una compañía de seguros de primer orden, de conformidad con el ranking de la Superintendencia de Seguros, dicho Inmueble deberá estar asegurado por un valor total y/o mayor al valor de la propiedad, en el entendido de que en ningún caso el monto asegurado podrá ser inferior al valor de El Inmueble.</p>
      <div class="sub">
        <div class="sub-id">8.1.2 — Siniestro</div>
        <p class="p">En caso de siniestro, EL PROMITENTE COMPRADOR tendrá la obligación de aplicar las sumas recibidas por concepto de las pólizas de seguro antes indicadas a la reparación de El Inmueble objeto del presente Contrato y a los daños ocasionados a terceros.</p>
      </div>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 9</span><span class="art-name">Pago de Impuestos</span></div>
      <p class="p"><strong>Obligación de EL PROMITENTE COMPRADOR:</strong> EL PROMITENTE COMPRADOR deberá satisfacer a sus expensas todos los impuestos, tributos, tasas, contribuciones, honorarios y en general, todos los gastos de cualquier naturaleza que tengan su origen en el otorgamiento, formalización, inscripción, cancelación de las cargas, gravámenes o privilegios que se le otorguen, resolución o ejecución de este, etc.</p>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 10</span><span class="art-name">Arbitrios</span></div>
      <div class="sub">
        <div class="sub-id">10.1.</div>
        <p class="p">Queda convenido que EL PROMITENTE COMPRADOR, de igual manera una vez reciba la referida unidad se compromete al pago de los arbitrios municipales y cualquier otro que se derive de impuestos relativos a la propiedad del referido inmueble y/o necesarios para el levantamiento de construcciones en el mismo, por lo que por este mismo Contrato se exime de toda responsabilidad de pago de los mismos a EL DESARROLLADOR.</p>
      </div>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 11</span><span class="art-name">Régimen de Invitados, Arrendatarios, Usuarios u Ocupantes</span></div>
      <p class="p"><strong>Cumplimiento Reglamentación:</strong> EL PROMITENTE COMPRADOR siempre será responsable de que los arrendatarios, invitados, usuarios u ocupantes a cualquier título cumplan con la reglamentación aplicable a los usuarios de los inmuebles que conforman el Proyecto. En tal sentido, EL PROMITENTE COMPRADOR se obliga a hacer del conocimiento de las personas antes citadas dichas obligaciones sin que estas últimas puedan alegar ignorancia de estas.</p>
    </div>

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 12</span><span class="art-name">Derecho de Primera Opción</span></div>
      <div class="sub">
        <div class="sub-id">12.1 — Derecho de Opción Preferencial</div>
        <p class="p">Queda expresamente convenido entre LAS PARTES que EL DESARROLLADOR tendrá el derecho de opción preferencial para adquirir de EL PROMITENTE COMPRADOR El Inmueble (Derecho de Opción Preferencial). En consecuencia, si EL PROMITENTE COMPRADOR desea vender o de otra forma transferir la propiedad de El Inmueble deberá notificarlo por escrito a EL DESARROLLADOR (Notificación de Opción Preferencial) en el momento en que el mismo se haga disponible para venta.</p>
      </div>
      <div class="sub">
        <div class="sub-id">12.1.1.</div>
        <p class="p">Si EL DESARROLLADOR desea ejercer su Derecho de Opción Preferencial, deberá notificarlo a EL PROMITENTE COMPRADOR dentro de los Treinta (30) días de haber recibido la Notificación de Opción Preferencial. Si la Notificación de Opción Preferencial no fuere respondida por EL DESARROLLADOR dentro de ese plazo, el silencio será interpretado como rechazo a la misma.</p>
      </div>
    </div>

  </div>
  {!! $ftr !!}
</div>

<!-- PÁGINA 7 — DISPOSICIONES GENERALES · FIRMAS -->
<div class="page">
  {!! $phHeader !!}
  <div class="body">

    <div class="art">
      <div class="art-hdr"><span class="art-num">Art. 12</span><span class="art-name">Disposiciones Generales y Declaraciones</span></div>
      <div class="sub">
        <div class="sub-id">12.2 — Declaraciones de LAS PARTES</div>
        <p class="p">Queda comprendido que no hay acuerdos o manifestaciones orales o escritos entre EL DESARROLLADOR y EL PROMITENTE COMPRADOR que afecten el presente Contrato y el mismo sustituye y cancela cualesquiera y todas las negociaciones, arreglos, manifestaciones y entendidos previos que pudiere haber entre LAS PARTES. El presente Contrato sólo podrá ser modificado o alterado por acuerdo escrito entre LAS PARTES.</p>
      </div>
      <div class="sub">
        <div class="sub-id">12.2.2 — Ley Aplicable</div>
        <p class="p">LAS PARTES convienen que el presente Contrato estará regido y será interpretado de conformidad con las leyes de la República Dominicana y que en el caso de surgir dificultades en su interpretación o en su ejecución las mismas serán de la competencia de los tribunales dominicanos.</p>
      </div>
      <div class="sub">
        <div class="sub-id">12.2.5 — Validez De Las Firmas Remotas y Electrónicas</div>
        <p class="p">Las partes acuerdan y reconocen que las firmas colocadas de manera remota o mediante medios electrónicos o digitales ("firma electrónica") tendrán plena validez y efecto legal para los fines de este contrato en virtud de lo establecido en el Reglamento de Aplicación de la Ley No. 126-02, sobre Comercio Electrónico, Documentos y Firmas Digitales, y sus modificaciones.</p>
      </div>
      <div class="sub">
        <div class="sub-id">12.2.6 — Anexos</div>
        <p class="p">Los documentos anexados a este contrato se consideran parte integral de este, los siguientes serán:</p>
        <ul class="axtlist">
          <li><strong>a)</strong> Plano de La Unidad.</li>
          <li><strong>b)</strong> Memoria de calidades.</li>
          <li><strong>c)</strong> Instructivo de Pago por Transferencia.</li>
          <li><strong>e)</strong> Precio y Forma de Pago de La Unidad.</li>
          <li><strong>f)</strong> Declaración Maestra de Cap Cana.</li>
        </ul>
      </div>
    </div>

    <div style="margin-top:24px; border-top:1.5px solid var(--ink); padding-top:18px;">
      <p class="p" style="margin-bottom:20px;">En fe de lo cual las partes suscriben el presente instrumento en los términos antes establecidos.</p>
      <div class="sig-grid">
        <div class="sig-block">
          <div class="sig-role">Por El Desarrollador</div>
          <div class="sig-name">{{ $vendedor_nombre ?? 'JOSE ANTONIO GONZALEZ DIAZ' }}</div>
          <div class="sig-entity">{{ $vendedor_entidad ?? 'En Rep. De IGUANAS LAKE CONDO & RESIDENCES, S.R.L.' }}</div>
          @if(!empty($vendedor_firma_img))
            <div class="sig-box" style="display:flex; align-items:flex-end; justify-content:center; padding-bottom:2px;">
              <img src="{{ $vendedor_firma_img }}" alt="Firma del Desarrollador" style="max-height:46px; max-width:200px; object-fit:contain; display:block;">
            </div>
          @else
            <div class="sig-box"></div>
          @endif
          <div class="sig-label">Firma &nbsp;·&nbsp; Fecha: ____________________</div>
        </div>
        <div class="sig-block">
          <div class="sig-role">Por El Promitente Comprador</div>
          <div class="sig-name"><span class="f">{{ $comprador_nombre }}</span></div>
          <div class="sig-entity"><span class="f">{{ $comprador_tipo }}</span></div>
          <div class="sig-box"></div>
          <div class="sig-label">Firma &nbsp;·&nbsp; Fecha: ____________________</div>
        </div>
      </div>
    </div>

  </div>
  {!! $ftr !!}
</div>

</body>
</html>
