<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $company['brand'] }} · Comprobante de Pago · {{ $d['numero_comprobante'] }}</title>
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

.page { width: 210mm; min-height: 297mm; background: #fff; display: flex; flex-direction: column; }

.hdr { background: var(--green-bg); padding: 14px 44px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.hdr-logo { display: flex; align-items: center; gap: 12px; }
.hdr-iso  { width: 26px; height: 26px; flex-shrink: 0; }
.hdr-name { font-size: 12px; font-weight: 600; color: #F1EDE3; letter-spacing: .2em; }
.hdr-sub  { font-size: 7px; font-weight: 400; color: rgba(241,237,227,.3); letter-spacing: .14em; text-transform: uppercase; margin-top: 2px; }
.hdr-right { text-align: right; }
.hdr-doc  { font-size: 7px; font-weight: 500; color: rgba(241,237,227,.3); letter-spacing: .18em; text-transform: uppercase; }
.hdr-ref  { font-size: 11px; font-weight: 600; color: rgba(241,237,227,.75); margin-top: 3px; letter-spacing: .04em; }

.gold-bar { height: 2px; background: var(--gold); flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

.body { padding: 28px 44px 24px; flex: 1; }

.cover-eyebrow { font-size: 7.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .22em; text-transform: uppercase; margin-bottom: 8px; }
.cover-title   { font-size: 26px; font-weight: 300; color: var(--ink); letter-spacing: -.02em; line-height: 1.1; margin-bottom: 3px; }
.cover-title strong { font-weight: 700; }
.cover-rule    { border: none; border-top: 1.5px solid var(--ink); margin: 16px 0 22px; }

.parties { display: grid; grid-template-columns: 1fr 1px 1fr; gap: 0 28px; margin-bottom: 22px; }
.party-divider { background: var(--rule); }
.party-label  { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--rule-lt); }
.party-name   { font-size: 12px; font-weight: 700; color: var(--ink); line-height: 1.3; margin-bottom: 2px; }
.party-sub    { font-size: 8px; color: var(--green); font-weight: 500; margin-bottom: 10px; }
.party-detail { font-size: 7.5px; color: var(--ink-mid); line-height: 1.85; }
.party-detail b { color: var(--ink); font-weight: 500; }

.prop-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 12px; }
.prop-grid  { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-top: 1px solid var(--rule); border-left: 1px solid var(--rule); margin-bottom: 22px; }
.prop-cell  { border-right: 1px solid var(--rule); border-bottom: 1px solid var(--rule); padding: 9px 12px; }
.prop-cell-label { font-size: 6.5px; font-weight: 500; color: var(--ink-mute); letter-spacing: .14em; text-transform: uppercase; margin-bottom: 4px; }
.prop-cell-val   { font-size: 10.5px; font-weight: 600; color: var(--ink); }

.amount-row { display: flex; align-items: baseline; gap: 16px; padding: 16px 0; border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); margin-bottom: 22px; }
.amount-label  { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .18em; text-transform: uppercase; min-width: 88px; flex-shrink: 0; }
.amount-figure { font-size: 28px; font-weight: 300; color: var(--ink); letter-spacing: -.02em; line-height: 1; }
.amount-figure strong { font-weight: 700; }
.amount-sep    { width: 1px; height: 32px; background: var(--rule); flex-shrink: 0; }
.amount-words  { font-size: 8px; color: var(--ink-mid); line-height: 1.55; font-style: italic; flex: 1; }
.amount-concept { text-align: right; flex-shrink: 0; }
.amount-concept-label { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .16em; text-transform: uppercase; margin-bottom: 3px; }
.amount-concept-val   { font-size: 11px; font-weight: 600; color: var(--ink); }

.sec-title { font-size: 7px; font-weight: 600; color: var(--ink-mute); letter-spacing: .24em; text-transform: uppercase; padding-bottom: 6px; border-bottom: 1.5px solid var(--ink); margin-bottom: 12px; }

.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.data-table td { font-size: 8.5px; color: var(--ink-mid); line-height: 1.7; padding: 6px 10px; border-bottom: 1px solid var(--rule-lt); }
.data-table td:first-child { color: var(--ink-mute); font-weight: 500; text-transform: uppercase; letter-spacing: .1em; font-size: 7.5px; width: 36%; padding-left: 0; }
.data-table td:last-child  { color: var(--ink); font-weight: 600; text-align: right; padding-right: 0; }
.data-table tr:first-child td { border-top: 1px solid var(--rule-lt); }
.data-table .row-total td   { font-size: 10px; font-weight: 700; color: var(--ink); border-bottom: 1.5px solid var(--ink); }
.data-table .row-next td    { color: var(--ink-mute); font-size: 7.5px; }
.data-table .row-next td:last-child { color: var(--ink-mid); font-weight: 500; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 32px; }

.notes { margin: 18px 0 22px; }
.note  { display: flex; gap: 8px; font-size: 8px; color: var(--ink-mute); line-height: 1.65; margin-bottom: 5px; }
.note .mk { color: var(--green); font-weight: 700; flex-shrink: 0; }
.note b   { color: var(--ink); font-weight: 600; }
.note em  { font-style: italic; }

.sig-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding-top: 18px; border-top: 1px solid var(--rule); }
.sig-role   { font-size: 6.5px; font-weight: 600; color: var(--ink-mute); letter-spacing: .2em; text-transform: uppercase; margin-bottom: 3px; }
.sig-name   { font-size: 11px; font-weight: 700; color: var(--ink); margin-bottom: 1px; }
.sig-entity { font-size: 8px; color: var(--ink-mute); margin-bottom: 22px; }
.sig-box    { border-top: 1px solid var(--ink); border-bottom: 1px solid var(--rule-lt); height: 48px; margin-bottom: 5px; }
.sig-hint   { font-size: 6.5px; color: var(--ink-mute); letter-spacing: .1em; }

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
    <div class="hdr-right">
      <div class="hdr-doc">Comprobante de Pago · Payment Receipt</div>
      <div class="hdr-ref">{{ $d['numero_comprobante'] }}</div>
    </div>
  </div>
  <div class="gold-bar"></div>

  <div class="body">

    <div class="cover-eyebrow">{{ $company['project'] }} · {{ $company['location'] }}</div>
    <div class="cover-title"><strong>Comprobante</strong> de Pago</div>
    <hr class="cover-rule">

    <div class="parties">
      <div>
        <div class="party-label">Emitido por · Issued by</div>
        <div class="party-name">{{ $company['legal_name'] }}</div>
        <div class="party-sub">{{ $company['project'] }}</div>
        <div class="party-detail">
          @if($company['rnc'])<b>RNC</b> {{ $company['rnc'] }}<br>@endif
          {{ $company['address'] }}<br>
          {{ $company['support_email'] }}<br>
          <b>Emitido</b> {{ $d['fecha_emision'] }} &nbsp;·&nbsp; <b>Pago recibido</b> {{ $d['fecha_pago'] }}
        </div>
      </div>
      <div class="party-divider"></div>
      <div>
        <div class="party-label">Cliente · Client</div>
        <div class="party-name">{{ $d['nombre_cliente'] }}</div>
        <div class="party-sub">{{ $d['proyecto'] }} — Unidad {{ $d['unidad'] }}</div>
        <div class="party-detail">
          <b>Documento</b> {{ $d['documento_cliente'] }}<br>
          <b>Correo</b> {{ $d['email_cliente'] }}<br>
          <b>Teléfono</b> {{ $d['telefono_cliente'] }}<br>
          <b>Contrato</b> {{ $d['numero_contrato'] }} &nbsp;·&nbsp; <b>Asesor</b> {{ $d['nombre_asesor'] }}
        </div>
      </div>
    </div>

    <div class="amount-row">
      <div class="amount-label">Monto recibido /<br>Amount received</div>
      <div class="amount-figure">{{ $d['moneda'] }} <strong>{{ $d['monto'] }}</strong></div>
      <div class="amount-sep"></div>
      <div class="amount-words">{{ $d['monto_en_letras'] }}</div>
      <div class="amount-concept">
        <div class="amount-concept-label">Concepto</div>
        <div class="amount-concept-val">{{ $d['concepto_pago'] }}</div>
      </div>
    </div>

    <div class="prop-label">Detalle del pago · Payment details</div>
    <div class="prop-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:22px">
      <div class="prop-cell">
        <div class="prop-cell-label">Método / Method</div>
        <div class="prop-cell-val">{{ $d['metodo_pago'] }}</div>
      </div>
      <div class="prop-cell">
        <div class="prop-cell-label">Referencia / Reference</div>
        <div class="prop-cell-val">{{ $d['referencia'] }}</div>
      </div>
      <div class="prop-cell">
        <div class="prop-cell-label">Cuenta receptora</div>
        <div class="prop-cell-val">{{ $d['cuenta_receptora'] }}</div>
      </div>
    </div>

    <div class="two-col">
      <div>
        <div class="sec-title">Estado de cuenta · Account summary</div>
        <table class="data-table">
          <tr><td>Precio total de la unidad</td><td>{{ $d['moneda'] }} {{ $d['precio_total'] }}</td></tr>
          <tr><td>Total pagado a la fecha</td><td>{{ $d['moneda'] }} {{ $d['total_pagado'] }}</td></tr>
          <tr class="row-total"><td>Saldo pendiente</td><td>{{ $d['moneda'] }} {{ $d['saldo_pendiente'] }}</td></tr>
          <tr class="row-next"><td>Próximo pago — {{ $d['fecha_proxima_cuota'] }}</td><td>{{ $d['moneda'] }} {{ $d['monto_proxima_cuota'] }}</td></tr>
        </table>
      </div>
      <div>
        <div class="sec-title">Notas</div>
        <div class="notes" style="margin-top:0">
          <div class="note"><span class="mk">›</span><span>Este documento acredita la recepción del pago indicado conforme al contrato de compraventa/reserva suscrito entre las partes.</span></div>
          <div class="note"><span class="mk">›</span><span>El presente comprobante <b>no sustituye el e-CF/NCF</b> que se emite bajo la normativa DGII.</span></div>
          <div class="note"><span class="mk">›</span><span>Consultas: <b>{{ $company['support_email'] }}</b></span></div>
        </div>
      </div>
    </div>

    <div class="sig-grid">
      <div>
        <div class="sig-role">Autorizado por · Authorized by</div>
        <div class="sig-name">{{ $company['signer_name'] }}</div>
        <div class="sig-entity">{{ $company['signer_title'] }} · {{ $company['group'] }}</div>
        <div class="sig-box"></div>
        <div class="sig-hint">Firma / Signature</div>
      </div>
      <div>
        <div class="sig-role">Recibido por · Received by</div>
        <div class="sig-name">{{ $d['nombre_cliente'] }}</div>
        <div class="sig-entity">Cliente · {{ $d['documento_cliente'] }}</div>
        <div class="sig-box"></div>
        <div class="sig-hint">Firma / Signature</div>
      </div>
    </div>

  </div>

  <div class="footer">
    <div class="footer-l">{{ $company['brand'] }} · {{ $company['group'] }}</div>
    <div class="footer-c">Documento generado electrónicamente · válido sin firma autógrafa</div>
    <div class="footer-r">{{ $d['numero_comprobante'] }}</div>
  </div>

</div>
</body>
</html>
