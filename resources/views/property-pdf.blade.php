@php
  use Carbon\Carbon;

  $unitNum    = $unit->custom_id ?: ($unit->name ?: ('Unit ' . $unit->id));
  $price      = (float) ($unit->price ?? 0);
  $priceFmt   = '$' . number_format($price, 0, '.', ',');
  $intArea    = $unit->internal_area ?: 0;
  $extArea    = $unit->external_area ?: 0;
  $pricePerM2 = $intArea > 0 ? '$' . number_format($price / $intArea, 0, '.', ',') : '—';

  $projectName = optional($unit->project)->name ?: 'Makai Residences';
  $devName     = optional($unit->project)->developer_name ?: 'Duna Development Group';

  $roi         = $unit->roi_percent ? number_format((float) $unit->roi_percent, 1) . '%' : '—';
  $projVal     = $unit->projected_value ? '+' . number_format((((float)$unit->projected_value / max($price,1)) - 1) * 100, 0) . '% año 1' : '—';
  $projYear    = $unit->projected_value_year ?: 'Q4 2026';

  $floor       = $unit->floor ?: '1er Piso';
  $direction   = $unit->direction ?: 'SE';
  $outlook     = $unit->outlook ?: 'Vista al lago';
  $bedrooms    = $unit->bedrooms ?: '—';
  $bathrooms   = $unit->bathrooms ?: '—';
  $parking     = $unit->parking_bays ?: '—';

  $reserveAmt  = '$5,000';
  $initialPct  = 29;
  $constructPct = 35;
  $finalPct    = 35;
  $reservePct  = max(0, 100 - $initialPct - $constructPct - $finalPct);
  $initialAmt  = '$' . number_format($price * $initialPct / 100, 0, '.', ',');
  $constructAmt = '$' . number_format($price * $constructPct / 100, 0, '.', ',');
  $finalAmt    = '$' . number_format($price * $finalPct / 100, 0, '.', ',');

  $now         = now();
  $dateStr     = $now->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') . ' · ' . $now->format('h:i A');
  $ref         = 'REF ' . strtoupper(substr(md5($unit->id . $now->format('Ymd')), 0, 4)) . '-' . str_replace(' ', '', strtoupper($unitNum)) . '-' . $now->format('dm');

  $recipientName = request('to', 'Cliente');
  $advisorName   = optional($unit->agent)->name ?? 'Carlos Ramírez Méndez';
  $advisorEmail  = optional($unit->agent)->email ?? 'carlos.ramirez@dunadevelopment.com';
  $advisorPhone  = '+1 (809) 710-9044';
  $advisorWA     = '18097109044';
  $advisorInitials = collect(explode(' ', $advisorName))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');

  $mainImage = optional($unit->images->first())->image_path;
  if ($mainImage && !preg_match('/^https?:\/\//', $mainImage)) {
    $mainImage = asset($mainImage);
  }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $projectName }} · {{ $unitNum }} · Ficha de Propiedad</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
@page { margin: 0; size: A4 portrait; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', sans-serif;
  background: #fff;
  color: #111827;
  -webkit-font-smoothing: antialiased;
  width: 210mm;
  margin: 0 auto;
}

/* Each .sheet is exactly one A4 page. Fixed height + hidden overflow guarantees
   the browser paginates into exactly two pages with no stray page breaks. */
.sheet {
  position: relative;
  width: 210mm;
  height: 297mm;
  overflow: hidden;
  page-break-after: always;
  break-after: page;
}
.sheet:last-of-type {
  page-break-after: auto;
  break-after: auto;
}

.hdr { background: #0b1c0a; padding: 14px 36px; display: flex; align-items: center; justify-content: space-between; }
.hdr-logo { display: flex; align-items: center; gap: 14px; }
.hdr-isotipo { width: 36px; height: 36px; flex-shrink: 0; }
.hdr-wordmark { display: flex; flex-direction: column; gap: 2px; }
.hdr-name { font-size: 18px; font-weight: 700; color: #F1EDE3; letter-spacing: .18em; line-height: 1; }
.hdr-dev { font-size: 8.5px; color: rgba(241,237,227,.42); letter-spacing: .16em; text-transform: uppercase; }
.hdr-right { text-align: right; }
.hdr-doctype { font-size: 8.5px; font-weight: 600; color: rgba(241,237,227,.4); letter-spacing: .14em; text-transform: uppercase; }
.hdr-date { font-size: 10.5px; color: rgba(241,237,227,.68); margin-top: 5px; }
.hdr-ref { font-size: 8px; font-weight: 700; color: #82b870; letter-spacing: .1em; margin-top: 3px; }

.hdr-for-strip { background: #0f2b0d; padding: 6px 36px; border-top: 1px solid rgba(130,184,112,.15); border-bottom: 1px solid rgba(130,184,112,.15); font-size: 8px; font-weight: 600; color: #82b870; letter-spacing: .1em; text-transform: uppercase; }

.hero { background: linear-gradient(140deg, #132012 0%, #0b1c0a 55%, #163020 100%); padding: 24px 36px; display: flex; gap: 26px; align-items: center; position: relative; }
.hero-img { width: 220px; height: 176px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); border-radius: 10px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: rgba(255,255,255,.12); overflow: hidden; }
.hero-img img { width: 100%; height: 100%; object-fit: cover; }
.hero-img-label { font-size: 28px; font-weight: 700; letter-spacing: .06em; }
.hero-img-sub { font-size: 7px; letter-spacing: .12em; text-transform: uppercase; }
.hero-info { flex: 1; }
.hero-name { font-size: 26px; font-weight: 600; color: #F1EDE3; line-height: 1.05; margin-bottom: 3px; letter-spacing: -.02em; }
.hero-sub { font-size: 11px; color: rgba(241,237,227,.45); margin-bottom: 14px; letter-spacing: .02em; }
.hero-price-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 4px; }
.hero-price { font-size: 26px; font-weight: 700; color: #F1EDE3; letter-spacing: -.01em; }
.hero-price-cur { font-size: 14px; font-weight: 500; color: rgba(241,237,227,.6); margin-left: 3px; }
.hero-price-sub { font-size: 10px; color: rgba(241,237,227,.38); margin-bottom: 12px; }
.hero-roi { display: inline-flex; align-items: center; gap: 5px; background: rgba(130,184,112,.12); border: 1px solid rgba(130,184,112,.2); color: #82b870; font-size: 10px; font-weight: 600; padding: 4px 12px; border-radius: 100px; }
.hero-roi svg { width: 11px; height: 11px; }
.hero-reserve { position: absolute; top: 24px; right: 36px; text-align: right; }
.reserve-lbl { font-size: 7.5px; color: rgba(201,124,64,.48); font-weight: 600; text-transform: uppercase; letter-spacing: .14em; margin-bottom: 1px; }
.reserve-amt { font-size: 19px; font-weight: 700; color: #c97c40; line-height: 1; letter-spacing: -.02em; }
.reserve-cur { font-size: 10px; font-weight: 500; color: rgba(201,124,64,.5); margin-left: 1px; }

.validity { background: #FFFBEB; border-top: 1px solid #FDE68A; border-bottom: 1px solid #FDE68A; padding: 7px 36px; display: flex; align-items: center; gap: 8px; font-size: 9.5px; color: #92400E; line-height: 1.4; }
.validity svg { flex-shrink: 0; width: 13px; height: 13px; }

.section { padding: 18px 36px 0; }
.section-last { padding-bottom: 80px; }
.sec-title { font-size: 8px; font-weight: 600; color: #9CA3AF; letter-spacing: .12em; text-transform: uppercase; padding-bottom: 7px; border-bottom: 1px solid #F3F4F6; margin-bottom: 12px; }

.specs-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1px; background: #F3F4F6; border: 1px solid #F3F4F6; border-radius: 8px; overflow: hidden; margin-bottom: 18px; }
.spec-item { background: #fff; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; gap: 3px; }
.spec-ico { width: 19px; height: 19px; color: #6B7280; margin-bottom: 2px; }
.spec-val { font-size: 15px; font-weight: 700; color: #111827; line-height: 1; }
.spec-lbl { font-size: 7.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: .06em; font-weight: 500; }

.payment-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 7px; margin-bottom: 18px; }
.payment-card { border: 1px solid #F3F4F6; border-radius: 7px; padding: 12px; background: #FAFAFA; }
.payment-pct { font-size: 20px; font-weight: 700; color: #c97c40; line-height: 1; margin-bottom: 3px; }
.payment-label { font-size: 11px; font-weight: 600; color: #111827; margin-bottom: 2px; }
.payment-amount { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 4px; }
.payment-timing { font-size: 7.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: .07em; font-weight: 600; }

.metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px; margin-bottom: 18px; }
.metric-card { border: 1px solid #F3F4F6; border-radius: 7px; padding: 12px 14px; }
.metric-card-lbl { font-size: 7.5px; font-weight: 600; color: #9CA3AF; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 5px; }
.metric-card-val { font-size: 24px; font-weight: 700; color: #c97c40; line-height: 1; margin-bottom: 3px; }
.metric-card-val.green { color: #059669; }
.metric-card-val.blue { color: #2563EB; }
.metric-card-sub { font-size: 9px; color: #6B7280; }

.advisor-row { display: flex; align-items: center; border: 1px solid #F3F4F6; border-radius: 8px; padding: 14px 16px; gap: 14px; margin-bottom: 0; }
.advisor-avatar { width: 44px; height: 44px; background: #c97c40; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #fff; flex-shrink: 0; }
.advisor-info { flex: 1; }
.advisor-lbl { font-size: 7px; color: #9CA3AF; font-weight: 600; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 2px; }
.advisor-name { font-size: 15px; font-weight: 600; color: #111827; line-height: 1.2; margin-bottom: 1px; }
.advisor-title { font-size: 8px; color: #6B7280; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
.advisor-contact { font-size: 9px; color: #6B7280; line-height: 1.6; }
.advisor-cta { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
.advisor-wa { background: #25D366; color: #fff; border-radius: 5px; padding: 7px 14px; font-size: 8.5px; font-weight: 700; letter-spacing: .04em; white-space: nowrap; display: flex; align-items: center; gap: 5px; text-decoration: none; }
.advisor-wa svg { width: 12px; height: 12px; }
.advisor-phone { font-size: 8.5px; color: #6B7280; text-align: right; }

.page-break { page-break-before: always; }

.p2-hdr { padding: 28px 36px 20px; border-bottom: 3px solid #0b1c0a; display: flex; align-items: flex-end; justify-content: space-between; }
.p2-hdr-sub { font-size: 9px; color: #9CA3AF; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 6px; }
.p2-hdr-title { font-size: 26px; font-weight: 700; color: #111827; letter-spacing: -.02em; line-height: 1; }
.p2-hdr-right { text-align: right; }
.p2-hdr-unit { font-size: 9px; color: #6B7280; letter-spacing: .06em; margin-bottom: 3px; }
.p2-hdr-ref { font-size: 8px; color: #82b870; font-weight: 700; letter-spacing: .1em; margin-top: 2px; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.confotur-card { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 14px; margin-bottom: 10px; }
.confotur-header { display: flex; align-items: center; gap: 9px; margin-bottom: 7px; }
.confotur-icon { width: 28px; height: 28px; background: #059669; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.confotur-icon svg { width: 16px; height: 16px; color: #fff; }
.confotur-title { font-size: 12px; font-weight: 700; color: #065F46; }
.confotur-text { font-size: 9.5px; color: #047857; line-height: 1.55; margin-bottom: 10px; }
.confotur-badges { display: flex; gap: 6px; }
.confotur-badge { flex: 1; background: #fff; border: 1px solid #BBF7D0; border-radius: 5px; padding: 7px 6px; text-align: center; }
.confotur-badge-title { font-size: 9px; font-weight: 700; color: #065F46; margin-bottom: 2px; }
.confotur-badge-sub { font-size: 8px; color: #047857; }

.airbnb-block { border: 1px solid #F3F4F6; border-radius: 8px; overflow: hidden; margin-bottom: 10px; }
.airbnb-header { background: #F9FAFB; padding: 8px 14px; display: flex; align-items: center; gap: 7px; }
.airbnb-header-ico { width: 14px; height: 14px; color: #374151; }
.airbnb-header-lbl { font-size: 7.5px; font-weight: 700; color: #374151; letter-spacing: .1em; text-transform: uppercase; }
.airbnb-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; border-top: 1px solid #F3F4F6; font-size: 10.5px; }
.airbnb-dk { color: #6B7280; }
.airbnb-dv { font-weight: 600; color: #111827; }
.airbnb-dv.green { color: #059669; }
.airbnb-dv.red { color: #DC2626; }
.airbnb-total { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #111827; font-size: 11px; font-weight: 700; }
.airbnb-total-lbl { color: rgba(255,255,255,.7); }
.airbnb-total-val { color: #82b870; font-size: 14px; }

.location-placeholder { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border: 1px solid #A5D6A7; border-radius: 7px; height: 72px; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #2E7D32; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 8px; }
.distances-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
.dist-row { display: flex; justify-content: space-between; align-items: center; background: #F9FAFB; border: 1px solid #F3F4F6; border-radius: 5px; padding: 6px 10px; font-size: 9.5px; }
.dist-label { color: #374151; }
.dist-time { font-weight: 700; color: #111827; font-size: 10px; }

.costs-block { border: 1px solid #F3F4F6; border-radius: 8px; overflow: hidden; }
.costs-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; border-bottom: 1px solid #F9FAFB; font-size: 10.5px; }
.costs-dk { color: #6B7280; }
.costs-dv { font-weight: 500; color: #111827; }
.costs-total { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #F9FAFB; font-size: 12px; font-weight: 700; color: #111827; }

.amenities-list { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
.amenity-item { display: flex; align-items: center; gap: 9px; background: #F9FAFB; border: 1px solid #F3F4F6; border-radius: 6px; padding: 8px 12px; font-size: 10px; color: #374151; }
.amenity-item svg { width: 16px; height: 16px; color: #6B7280; flex-shrink: 0; }

.details-grid { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #F3F4F6; border-radius: 8px; overflow: hidden; margin-bottom: 18px; }
.details-col:first-child { border-right: 1px solid #F3F4F6; }
.detail-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 14px; border-bottom: 1px solid #F9FAFB; font-size: 10.5px; }
.detail-row:last-child { border-bottom: none; }
.dk { color: #6B7280; }
.dv { color: #111827; font-weight: 500; }

.footer { position: absolute; bottom: 0; left: 0; right: 0; background: #F9FAFB; border-top: 1px solid #E5E7EB; padding: 10px 36px; display: flex; align-items: center; justify-content: space-between; }
.footer-brand { font-size: 9px; font-weight: 700; color: #374151; letter-spacing: .06em; }
.footer-contact { font-size: 9px; color: #6B7280; text-align: center; line-height: 1.55; }
.footer-disc { font-size: 7.5px; color: #9CA3AF; text-align: right; max-width: 190px; line-height: 1.45; }

.cta-duo { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
.cta-box { padding: 16px 18px; display: flex; flex-direction: column; }
.cta-reserve { background: #0b1c0a; border-top: 2px solid #c97c40; }
.cta-visit { background: #F9FAFB; border: 1px solid #E9EAEC; border-top: 2px solid #0b1c0a; }
.cta-label { font-size: 7px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; margin-bottom: 8px; }
.cta-reserve .cta-label { color: rgba(241,237,227,.28); }
.cta-visit .cta-label { color: #9CA3AF; }
.cta-amount { font-size: 26px; font-weight: 700; color: #c97c40; line-height: 1; margin-bottom: 12px; letter-spacing: -.02em; }
.cta-amount-cur { font-size: 12px; font-weight: 400; color: rgba(201,124,64,.45); margin-left: 2px; }
.cta-modes { font-size: 15px; font-weight: 600; color: #111827; line-height: 1.2; margin-bottom: 12px; letter-spacing: -.01em; }
.cta-rule { border: none; border-top: 1px solid; margin: 0 0 10px; }
.cta-reserve .cta-rule { border-color: rgba(255,255,255,.07); }
.cta-visit .cta-rule { border-color: #E9EAEC; }
.cta-desc { font-size: 8.5px; line-height: 1.6; flex: 1; margin-bottom: 14px; }
.cta-reserve .cta-desc { color: rgba(241,237,227,.42); }
.cta-visit .cta-desc { color: #6B7280; }
.cta-link { font-size: 8px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; text-decoration: none; display: inline-block; }
.cta-reserve .cta-link { color: #c97c40; }
.cta-visit .cta-link { color: #111827; }

.qr-section { display: flex; align-items: center; justify-content: center; gap: 64px; padding: 22px 0 14px; }
.qr-divider { width: 1px; height: 120px; background: #E5E7EB; }
.qr-item { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.qr-canvas { display: inline-flex; padding: 14px; border-radius: 10px; background: #fff; border: 1px solid #E5E7EB; line-height: 0; }
.qr-canvas canvas { display: block !important; }
.qr-canvas img { display: none !important; }
.qr-text { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.qr-label { font-size: 13px; font-weight: 700; color: #111827; }
.qr-sub { font-size: 10px; color: #9CA3AF; }

@media print { body { width: 210mm; } .sheet { margin: 0; } @page { margin: 0; } }
</style>
</head>
<body>

<!-- ============ PAGE 1 ============ -->
<div class="sheet">

<!-- HEADER -->
<div class="hdr">
  <div class="hdr-logo">
    <svg class="hdr-isotipo" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="13" cy="13" r="11" stroke="#F1EDE3" stroke-width="1.5"/>
      <path d="M8 17V9l5 5 5-5v8" stroke="#F1EDE3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </svg>
    <div class="hdr-wordmark">
      <div class="hdr-name">{{ strtoupper($projectName) }}</div>
      <div class="hdr-dev">{{ $devName }}</div>
    </div>
  </div>
  <div class="hdr-right">
    <div class="hdr-doctype">Ficha de Propiedad · {{ $unitNum }}</div>
    <div class="hdr-date">{{ $dateStr }}</div>
    <div class="hdr-ref">{{ $ref }}</div>
  </div>
</div>

<div class="hdr-for-strip">Preparado para · {{ $recipientName }}</div>

<!-- HERO -->
<div class="hero">
  <div class="hero-img">
    @if($mainImage)
      <img src="{{ $mainImage }}" alt="{{ $unitNum }}">
    @else
      <div class="hero-img-label">{{ $unitNum }}</div>
      <div class="hero-img-sub">Render referencial · {{ $unitNum }}</div>
    @endif
  </div>
  <div class="hero-info">
    <div class="hero-name">{{ $unitNum }}</div>
    <div class="hero-sub">{{ $floor }} · Orientación {{ $direction }} · {{ $outlook }}</div>
    <div class="hero-price-row">
      <span class="hero-price">{{ $priceFmt }}</span>
      <span class="hero-price-cur">USD</span>
    </div>
    <div class="hero-price-sub">{{ $pricePerM2 }} / m² interior · Entrega {{ $projYear }}</div>
    @if($unit->roi_percent)
    <div class="hero-roi">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
      </svg>
      {{ $roi }} ROI proyectado anual
    </div>
    @endif
  </div>
  <div class="hero-reserve">
    <div class="reserve-lbl">Reserva desde</div>
    <div class="reserve-amt">{{ $reserveAmt }} <span class="reserve-cur">USD</span></div>
  </div>
</div>

<!-- VALIDITY -->
<div class="validity">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
  </svg>
  Precios vigentes al <strong style="margin:0 4px">{{ $dateStr }}</strong>. Los valores están sujetos a cambio sin previo aviso.
</div>

<!-- SPECS -->
<div class="section">
  <div class="sec-title">Especificaciones de la unidad</div>
  <div class="specs-grid">
    <div class="spec-item">
      <svg class="spec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="13" rx="2"/><path d="M2 12h20"/>
        <rect x="5" y="8.5" width="5" height="3" rx="1"/><rect x="14" y="8.5" width="5" height="3" rx="1"/>
      </svg>
      <div class="spec-val">{{ $bedrooms }}</div><div class="spec-lbl">Dormitorio{{ $bedrooms == 1 ? '' : 's' }}</div>
    </div>
    <div class="spec-item">
      <svg class="spec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 12V6a2 2 0 0 1 4 0v6"/><path d="M2 12h20v3a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5v-3z"/>
      </svg>
      <div class="spec-val">{{ $bathrooms }}</div><div class="spec-lbl">Baños</div>
    </div>
    <div class="spec-item">
      <svg class="spec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h5a3 3 0 0 1 0 6H9"/>
      </svg>
      <div class="spec-val">{{ $parking }}</div><div class="spec-lbl">Parking</div>
    </div>
    <div class="spec-item">
      <svg class="spec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M13 3h8v8"/><path d="M21 3l-8 8"/><path d="M11 21H3v-8"/><path d="M3 21l8-8"/>
      </svg>
      <div class="spec-val">{{ $intArea ? $intArea . ' m²' : '—' }}</div><div class="spec-lbl">Interior</div>
    </div>
    <div class="spec-item">
      <svg class="spec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 15V10a7 7 0 0 1 14 0v5"/><path d="M3 15h18"/><path d="M3 21h18"/>
      </svg>
      <div class="spec-val">{{ $extArea ? $extArea . ' m²' : '—' }}</div><div class="spec-lbl">Terraza</div>
    </div>
  </div>
</div>

<!-- PAYMENT PLAN -->
<div class="section">
  <div class="sec-title">Plan de pagos · Total {{ $priceFmt }} USD</div>
  <div class="payment-grid">
    <div class="payment-card">
      <div class="payment-pct">{{ number_format(5000 / max($price,1) * 100, 1) }}%</div>
      <div class="payment-label">Reserva</div>
      <div class="payment-amount">{{ $reserveAmt }}</div>
      <div class="payment-timing">A la firma</div>
    </div>
    <div class="payment-card">
      <div class="payment-pct">{{ $initialPct }}%</div>
      <div class="payment-label">Inicial</div>
      <div class="payment-amount">{{ $initialAmt }}</div>
      <div class="payment-timing">60 días · Promesa</div>
    </div>
    <div class="payment-card">
      <div class="payment-pct">{{ $constructPct }}%</div>
      <div class="payment-label">Construcción</div>
      <div class="payment-amount">{{ $constructAmt }}</div>
      <div class="payment-timing">18 meses</div>
    </div>
    <div class="payment-card">
      <div class="payment-pct">{{ $finalPct }}%</div>
      <div class="payment-label">Contraentrega</div>
      <div class="payment-amount">{{ $finalAmt }}</div>
      <div class="payment-timing">{{ $projYear }}</div>
    </div>
  </div>
</div>

<!-- METRICS -->
<div class="section">
  <div class="metrics-row">
    <div class="metric-card">
      <div class="metric-card-lbl">ROI Anual Estimado</div>
      <div class="metric-card-val">{{ $roi }}</div>
      <div class="metric-card-sub">Mercado actual</div>
    </div>
    <div class="metric-card">
      <div class="metric-card-lbl">Plusvalía Proyectada</div>
      <div class="metric-card-val green">{{ $projVal }}</div>
      <div class="metric-card-sub">A la entrega vs precio actual</div>
    </div>
    <div class="metric-card">
      <div class="metric-card-lbl">Disponibilidad</div>
      <div class="metric-card-val blue">{{ $unit->status === 'AVAILABLE' || $unit->status === 'available' ? 'Disponible' : ucfirst(strtolower($unit->status)) }}</div>
      <div class="metric-card-sub">Estado actual de la unidad</div>
    </div>
  </div>
</div>

<!-- CTAs -->
<div class="section">
  <div class="cta-duo">
    <div class="cta-box cta-reserve">
      <div class="cta-label">Asegura tu unidad</div>
      <div class="cta-amount">{{ $reserveAmt }}<span class="cta-amount-cur"> USD</span></div>
      <hr class="cta-rule">
      <div class="cta-desc">Bloquea el precio actual. Reserva totalmente reembolsable en los primeros 30 días.</div>
      <a class="cta-link" href="https://wa.me/{{ $advisorWA }}?text=Hola%2C+quiero+reservar+la+unidad+{{ urlencode($unitNum) }}">Iniciar reserva →</a>
    </div>
    <div class="cta-box cta-visit">
      <div class="cta-label">Agenda tu visita</div>
      <div class="cta-modes">Presencial · Tour 360°</div>
      <hr class="cta-rule">
      <div class="cta-desc">Recorre {{ $projectName }} desde donde estés o visítanos en Cap Cana.</div>
      <a class="cta-link" href="#">Agendar →</a>
    </div>
  </div>
</div>

<!-- ADVISOR -->
<div class="section section-last">
  <div class="sec-title">Tu asesor personal</div>
  <div class="advisor-row">
    <div class="advisor-avatar">{{ strtoupper($advisorInitials ?: 'CR') }}</div>
    <div class="advisor-info">
      <div class="advisor-lbl">Senior Sales Advisor · {{ $projectName }}</div>
      <div class="advisor-name">{{ $advisorName }}</div>
      <div class="advisor-contact">
        {{ $advisorEmail }}<br>
        dunadevelopment.com · Cap Cana, R.D.
      </div>
    </div>
    <div class="advisor-cta">
      <a class="advisor-wa" href="https://wa.me/{{ $advisorWA }}">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp directo
      </a>
      <div class="advisor-phone">{{ $advisorPhone }}</div>
    </div>
  </div>
</div>

<div class="footer">
  <div class="footer-brand">{{ strtoupper($projectName) }} · {{ strtoupper($devName) }}</div>
  <div class="footer-contact">{{ $advisorPhone }} · {{ $advisorEmail }}<br>Cap Cana, Punta Cana · República Dominicana</div>
  <div class="footer-disc">Documento referencial preparado para {{ $recipientName }}. Validez 30 días naturales. Ref: {{ $ref }}</div>
</div>

</div><!-- /sheet page 1 -->

<!-- ============ PAGE 2 ============ -->
<div class="sheet">

<div class="p2-hdr">
  <div>
    <div class="p2-hdr-sub">{{ $projectName }} · Cap Cana</div>
    <div class="p2-hdr-title">Detalles del Proyecto</div>
  </div>
  <div class="p2-hdr-right">
    <div class="p2-hdr-unit">{{ $unitNum }} · Página 2 / 2</div>
    <div class="p2-hdr-ref">{{ $ref }}</div>
  </div>
</div>

<div class="section" style="padding-top:20px">
  <div class="two-col">
    <div>
      <div class="sec-title">Beneficios fiscales y legales</div>
      <div class="confotur-card">
        <div class="confotur-header">
          <div class="confotur-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
            </svg>
          </div>
          <div class="confotur-title">Proyecto acogido a CONFOTUR</div>
        </div>
        <div class="confotur-text">Exención del impuesto de transferencia (3%) al momento de la compra y exoneración total del Impuesto al Patrimonio Inmobiliario (IPI 1% anual) durante 15 años. Aplica para inversores nacionales y extranjeros.</div>
        <div class="confotur-badges">
          <div class="confotur-badge"><div class="confotur-badge-title">CONFOTUR</div><div class="confotur-badge-sub">Exención fiscal</div></div>
          <div class="confotur-badge"><div class="confotur-badge-title">Ley 189-11</div><div class="confotur-badge-sub">Fideicomiso bancario</div></div>
          <div class="confotur-badge"><div class="confotur-badge-title">RNI Registrado</div><div class="confotur-badge-sub">Catastro nacional</div></div>
        </div>
      </div>

      <div class="sec-title" style="margin-top:14px">Costos mensuales estimados</div>
      <div class="costs-block">
        <div class="costs-row"><span class="costs-dk">Cuota de mantenimiento (HOA)</span><span class="costs-dv">${{ $unit->levies ? number_format((float)$unit->levies, 0) : '310' }}</span></div>
        <div class="costs-row"><span class="costs-dk">Servicios públicos promedio</span><span class="costs-dv">$180</span></div>
        <div class="costs-row"><span class="costs-dk">Seguro de la propiedad</span><span class="costs-dv">$95</span></div>
        <div class="costs-total"><span>Total mensual estimado</span><span>${{ number_format(((float)($unit->levies ?: 310)) + 180 + 95, 0) }} USD</span></div>
      </div>
    </div>

    <div>
      <div class="sec-title">Rentabilidad Airbnb estimada</div>
      <div class="airbnb-block">
        <div class="airbnb-header">
          <svg class="airbnb-header-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
          </svg>
          <span class="airbnb-header-lbl">Proyección Renta Vacacional</span>
        </div>
        <div class="airbnb-row"><span class="airbnb-dk">Tarifa diaria promedio</span><span class="airbnb-dv green">${{ $unit->est_rental ? number_format((float)$unit->est_rental / 30, 0) : '240' }}</span></div>
        <div class="airbnb-row"><span class="airbnb-dk">Ocupación anual estimada</span><span class="airbnb-dv">72%</span></div>
        <div class="airbnb-row"><span class="airbnb-dk">Gastos operativos (35%)</span><span class="airbnb-dv red">-${{ number_format(((float)($unit->est_rental ?: 7200)) * 12 * 0.35 / 1000, 0) }}K</span></div>
        <div class="airbnb-total">
          <span class="airbnb-total-lbl">Ingreso neto anual</span>
          <span class="airbnb-total-val">${{ number_format(((float)($unit->est_rental ?: 7200)) * 12 * 0.65 / 1000, 0) }}K USD</span>
        </div>
      </div>

      <div class="sec-title" style="margin-top:14px">Ubicación y entorno</div>
      <div class="location-placeholder">Cap Cana · Punta Cana · República Dominicana</div>
      <div class="distances-grid">
        <div class="dist-row"><span class="dist-label">Playa Juanillo</span><span class="dist-time">3 min</span></div>
        <div class="dist-row"><span class="dist-label">Aeropuerto PUJ</span><span class="dist-time">15 min</span></div>
        <div class="dist-row"><span class="dist-label">Punta Espada Golf</span><span class="dist-time">5 min</span></div>
        <div class="dist-row"><span class="dist-label">Supermercado</span><span class="dist-time">7 min</span></div>
      </div>
    </div>
  </div>
</div>

<!-- AMENITIES -->
<div class="section" style="margin-top:14px">
  <div class="sec-title">Amenidades incluidas</div>
  <div class="amenities-list">
    @php
      $amenities = [
        ['label' => 'Piscina infinita', 'path' => '<path d="M2 6c2.5 0 2.5 2 5 2s2.5-2 5-2 2.5 2 5 2 2.5-2 5-2"/><path d="M2 12c2.5 0 2.5 2 5 2s2.5-2 5-2 2.5 2 5 2 2.5-2 5-2"/><path d="M2 18c2.5 0 2.5 2 5 2s2.5-2 5-2 2.5 2 5 2 2.5-2 5-2"/>'],
        ['label' => 'Gimnasio premium', 'path' => '<line x1="8" y1="12" x2="16" y2="12"/><line x1="5" y1="8" x2="5" y2="16"/><line x1="19" y1="8" x2="19" y2="16"/>'],
        ['label' => 'Restaurante', 'path' => '<line x1="8" y1="2" x2="8" y2="22"/><path d="M6 2v5a2 2 0 0 0 4 0V2"/><path d="M18 2v4a4 4 0 0 1-2 3.46V22"/>'],
        ['label' => 'Beach Club', 'path' => '<circle cx="12" cy="7" r="3"/><path d="M2 16c2.5 0 2.5 2 5 2s2.5-2 5-2 2.5 2 5 2 2.5-2 5-2"/>'],
        ['label' => 'Seguridad 24/7', 'path' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>'],
        ['label' => 'Conserjería', 'path' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>'],
        ['label' => 'Parking cubierto', 'path' => '<path d="M5 17H3a1 1 0 0 1-1-1V9l3-6h14l3 6v7a1 1 0 0 1-1 1h-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>'],
        ['label' => 'Jardines tropicales', 'path' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/>'],
      ];
    @endphp
    @foreach($amenities as $a)
      <div class="amenity-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">{!! $a['path'] !!}</svg>
        {{ $a['label'] }}
      </div>
    @endforeach
  </div>
</div>

<!-- DETAILS -->
<div class="section" style="margin-top:14px">
  <div class="sec-title">Ficha completa de la unidad</div>
  <div class="details-grid">
    <div class="details-col">
      <div class="detail-row"><span class="dk">Proyecto</span><span class="dv">{{ $projectName }}</span></div>
      <div class="detail-row"><span class="dk">Unidad</span><span class="dv">{{ $unitNum }}</span></div>
      <div class="detail-row"><span class="dk">Piso</span><span class="dv">{{ $floor }}</span></div>
      <div class="detail-row"><span class="dk">Orientación</span><span class="dv">{{ $direction }}</span></div>
      <div class="detail-row"><span class="dk">Vista</span><span class="dv">{{ $outlook }}</span></div>
    </div>
    <div class="details-col">
      <div class="detail-row"><span class="dk">Precio USD</span><span class="dv">{{ $priceFmt }}</span></div>
      <div class="detail-row"><span class="dk">Precio / m²</span><span class="dv">{{ $pricePerM2 }}</span></div>
      <div class="detail-row"><span class="dk">ROI estimado</span><span class="dv">{{ $roi }} anual</span></div>
      <div class="detail-row"><span class="dk">Entrega estimada</span><span class="dv">{{ $projYear }}</span></div>
      <div class="detail-row"><span class="dk">Reserva mínima</span><span class="dv">{{ $reserveAmt }} USD</span></div>
    </div>
  </div>
</div>

<!-- QR -->
<div class="section section-last" style="margin-top:14px">
  <div class="sec-title">Canales directos de contacto</div>
  <div class="qr-section">
    <div class="qr-item">
      <div id="qr-whatsapp" class="qr-canvas"></div>
      <div class="qr-text"><div class="qr-label">WhatsApp Directo</div><div class="qr-sub">Habla con tu asesor ahora</div></div>
    </div>
    <div class="qr-divider"></div>
    <div class="qr-item">
      <div id="qr-project" class="qr-canvas"></div>
      <div class="qr-text"><div class="qr-label">Visitar Proyecto</div><div class="qr-sub">{{ parse_url(url('/'), PHP_URL_HOST) }}</div></div>
    </div>
  </div>
</div>

<div class="footer">
  <div class="footer-brand">{{ strtoupper($projectName) }} · {{ strtoupper($devName) }}</div>
  <div class="footer-contact">{{ $advisorPhone }} · {{ $advisorEmail }}<br>Cap Cana, Punta Cana · República Dominicana</div>
  <div class="footer-disc">Documento referencial preparado para {{ $recipientName }}. Validez 30 días naturales. Ref: {{ $ref }}</div>
</div>

</div><!-- /sheet page 2 -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
var WA_URL   = 'https://wa.me/{{ $advisorWA }}?text=' + encodeURIComponent('Hola, vi la unidad {{ $unitNum }} y quiero más información');
var PROJ_URL = '{{ url('/') }}';
new QRCode(document.getElementById('qr-whatsapp'), { text: WA_URL, width: 130, height: 130, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
new QRCode(document.getElementById('qr-project'), { text: PROJ_URL, width: 130, height: 130, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });

// Auto-open the browser print dialog so the user can "Save as PDF"
window.addEventListener('load', function () {
  setTimeout(function () { window.print(); }, 500);
});
</script>
</body>
</html>
