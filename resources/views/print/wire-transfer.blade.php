<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $company['brand'] }} · Datos para Transferencia en USD</title>
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
  --gold:     #B8962E;
}

body { font-family: 'Inter', sans-serif; background: #f0efec; color: var(--ink); -webkit-font-smoothing: antialiased; width: 210mm; margin: 0 auto; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.page { width: 210mm; min-height: 297mm; background: #fff; display: flex; flex-direction: column; }

.hdr { background: var(--green-bg); padding: 14px 44px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.hdr-logo { display: flex; align-items: center; gap: 12px; }
.hdr-iso  { width: 26px; height: 26px; flex-shrink: 0; }
.hdr-name { font-size: 12px; font-weight: 600; color: #F1EDE3; letter-spacing: .2em; }
.hdr-sub  { font-size: 7px; font-weight: 400; color: rgba(241,237,227,.3); letter-spacing: .14em; text-transform: uppercase; margin-top: 2px; }

.gold-bar { height: 2px; background: var(--gold); flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

.body { padding: 28px 44px 24px; flex: 1; }

.cover-eyebrow { font-size: 7.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .22em; text-transform: uppercase; margin-bottom: 8px; }
.cover-title   { font-size: 26px; font-weight: 300; color: var(--ink); letter-spacing: -.02em; line-height: 1.1; }
.cover-title strong { font-weight: 700; }
.cover-rule    { border: none; border-top: 1.5px solid var(--ink); margin: 16px 0 28px; }

.sec-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .24em; text-transform: uppercase; padding-bottom: 6px; border-bottom: 1.5px solid var(--ink); margin-bottom: 14px; }

.drow { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--rule-lt); }
.drow:last-child { border-bottom: none; }
.drow .k { font-size: 8px; font-weight: 500; color: var(--ink-mute); text-transform: uppercase; letter-spacing: .1em; flex-shrink: 0; }
.drow .v { font-size: 11px; font-weight: 600; color: var(--ink); text-align: right; }
.drow .v.code { font-family: monospace; font-size: 13px; letter-spacing: .06em; }

.account-block { border-bottom: 1.5px solid var(--ink); padding: 18px 0; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
.account-block .label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .22em; text-transform: uppercase; margin-bottom: 6px; }
.account-block .name  { font-size: 12px; font-weight: 700; color: var(--ink); line-height: 1.3; }
.account-block .sep   { width: 1px; height: 40px; background: var(--rule); flex-shrink: 0; }
.account-block .num-wrap { text-align: right; flex-shrink: 0; }
.account-block .num   { font-size: 28px; font-weight: 300; color: var(--ink); letter-spacing: .06em; font-family: monospace; line-height: 1; }
.account-block .num strong { font-weight: 700; }

.ref-block { margin-bottom: 0; }
.ref-line  { border-bottom: 1px solid var(--ink); min-height: 38px; margin-top: 10px; display:flex; align-items:flex-end; padding-bottom:6px; font-size:13px; font-weight:600; color:var(--ink); }
.nota { display: flex; gap: 7px; font-size: 8px; color: var(--ink-mute); line-height: 1.65; margin-top: 10px; }
.nota .mk { font-weight: 700; color: var(--green); flex-shrink: 0; }
.nota b   { color: var(--ink); font-weight: 600; }

.footer { background: var(--green-bg); padding: 7px 44px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; margin-top: auto; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.footer-l { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.35); letter-spacing: .14em; text-transform: uppercase; }
.footer-c { font-size: 6.5px; color: rgba(241,237,227,.18); }
.footer-r { font-size: 7px; color: rgba(241,237,227,.25); }

.print-bar { position: fixed; top: 16px; right: 16px; display: flex; gap: 8px; z-index: 50; }
.print-bar button { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; background: var(--green-bg); color: #F1EDE3; }
@media print { body { background: #fff; } .page { margin-bottom: 0; } .print-bar { display: none; } }
</style>
</head>
<body>

<div class="print-bar"><button onclick="window.print()">Imprimir / Guardar PDF</button></div>

@include('print._makai_iso')

<div class="page">

  <div class="hdr">
    <div class="hdr-logo">
      <svg class="hdr-iso" viewBox="0 0 87 87" fill="none"><use href="#iso"/></svg>
      <div>
        <div class="hdr-name">{{ $company['brand'] }}</div>
        <div class="hdr-sub">{{ $company['group'] }}</div>
      </div>
    </div>
  </div>
  <div class="gold-bar"></div>

  <div class="body">

    <div class="cover-eyebrow">{{ $company['project'] }} · {{ $company['location'] }}</div>
    <div class="cover-title">Recepción de transferencia en <strong>Dólares US$</strong></div>
    <hr class="cover-rule">

    <div class="sec-label">{{ explode(',', $company['bank']['intermediary_name'])[0] }}</div>
    <div class="drow"><span class="k">Banco Beneficiario</span><span class="v">{{ $company['bank']['intermediary_name'] }}</span></div>
    <div class="drow"><span class="k">Cuenta #</span><span class="v code">{{ $company['bank']['intermediary_account'] }}</span></div>
    <div class="drow"><span class="k">Dirección</span><span class="v" style="font-size:9.5px;font-weight:500">{{ $company['bank']['intermediary_address'] }}</span></div>
    <div class="drow"><span class="k">Swift (BIC)</span><span class="v code">{{ $company['bank']['swift'] }}</span></div>
    <div class="drow"><span class="k">ABA</span><span class="v code">{{ $company['bank']['aba'] }}</span></div>
    <div class="drow" style="margin-bottom:24px"><span class="k">Beneficiario Final</span><span class="v" style="font-size:9.5px">{{ $company['bank']['beneficiary_bank'] }}</span></div>

    <div class="sec-label">Número de Cuenta del Beneficiario Final</div>
    <div class="account-block">
      <div>
        <div class="label">Titular de la cuenta</div>
        <div class="name">{{ $company['bank']['account_holder'] }}</div>
      </div>
      <div class="sep"></div>
      <div class="num-wrap">
        <div class="label">Número de cuenta</div>
        <div class="num"><strong>{{ $company['bank']['account_number'] }}</strong></div>
      </div>
    </div>

    <div class="ref-block">
      <div class="sec-label">Referencia / Concepto *</div>
      <div class="nota" style="margin-bottom:12px">
        <span class="mk">*</span>
        <span><b>Es obligatorio</b> colocar estos datos en las instrucciones para evitar que sus fondos sean devueltos.</span>
      </div>
      <div class="ref-line">{{ $reference }}</div>
    </div>

  </div>

  <div class="footer">
    <div class="footer-l">{{ $company['brand'] }} · {{ $company['group'] }}</div>
    <div class="footer-c">{{ $company['support_email'] }} · {{ $company['phone'] }} · {{ $company['website'] }}</div>
    <div class="footer-r">{{ $company['website'] }}</div>
  </div>

</div>
</body>
</html>
