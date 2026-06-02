@extends('layouts.client')
@section('title', 'Guardados — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Guardados')
@php $activeRoute = 'guardados'; @endphp

@push('styles')
<style>
    /* === Saved-units page — same card as home (fg-card) === */
    .sv-scope {
        --brand: #5c7c68;
        --brand-soft: rgba(92, 124, 104, 0.10);
    }

    /* Header summary block */
    .sv-header {
        display:flex; align-items:flex-end; justify-content:space-between;
        flex-wrap:wrap; gap:8px;
        padding:18px 20px;
        background:#f6f6f6;
        border:1px solid #eaecf0;
        border-radius:14px;
    }
    .sv-header-title {
        font-family:'Inter Tight', Inter, sans-serif;
        font-size:16px; font-weight:700; color:#171717; line-height:1.2;
    }
    .sv-header-sub { font-size:11px; color:#717784; margin-top:4px; }
    .sv-cta-explore {
        display:inline-flex; align-items:center; justify-content:center; gap:6px;
        height:34px; padding:0 14px;
        background:#fff; color:#525866; border:1px solid #eaecf0; border-radius:10px;
        font-family:'Poppins', sans-serif; font-weight:600; font-size:12px;
        cursor:pointer; text-decoration:none;
        transition: background .15s;
    }
    .sv-cta-explore:hover { background:#f5f7fa; }

    /* Empty state */
    .sv-empty {
        background:#fff; border:1px dashed #eaecf0; border-radius:18px;
        padding:48px 20px; text-align:center;
    }
    .sv-empty-cta {
        display:inline-flex; align-items:center; gap:6px;
        margin-top:16px; padding:0 18px; height:38px;
        background:#5c7c68; color:#fff; border:1px solid #5c7c68; border-radius:10px;
        font-family:'Poppins', sans-serif; font-weight:600; font-size:12px;
        text-decoration:none; cursor:pointer;
    }

    /* ===========================================================
       UNIT CARD — copied 1:1 from home (public/css/style.css)
       Scoped under .sv-scope so dashboard layout styles don't leak
       =========================================================== */
    .sv-scope .fg-units-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        align-items: start;
    }
    @media (max-width: 1280px) {
        .sv-scope .fg-units-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 1024px) {
        .sv-scope .fg-units-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 700px) {
        .sv-scope .fg-units-grid { grid-template-columns: 1fr; gap: 12px; }
    }

    .sv-scope .fg-card {
        --status-color: var(--brand);
        --status-soft: rgba(92, 124, 104, 0.10);
        --status-bg-faint: rgba(92, 124, 104, 0.04);
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 316px;
        margin-inline: auto;
        background: #ffffff;
        border: 1px solid #eaecf0;
        border-radius: 28px;
        overflow: hidden;
        isolation: isolate;
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .sv-scope .fg-card-inner {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 6px 6px 0 6px;
        background: #ffffff;
        border-radius: 28px;
        z-index: 2;
    }
    .sv-scope .fg-card-img {
        position: relative;
        height: 300px;
        border-radius: 24px;
        overflow: hidden;
        background: white;
        flex-shrink: 0;
    }
    .sv-scope .fg-card-img > img {
        position: absolute;
        left: 50%;
        top: 5.33%;
        bottom: 5.33%;
        transform: translateX(-50%);
        height: 89.34%;
        aspect-ratio: 768 / 561;
        width: auto;
        object-fit: cover;
        display: block;
    }
    .sv-scope .fg-card-img-noimage {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        color: #848484; font-family: 'Poppins', sans-serif; font-size: 12px;
    }
    .sv-scope .fg-chip-row {
        position: absolute;
        top: 0; left: 0; right: 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 8px;
        z-index: 5;
    }
    .sv-scope .fg-add-to-list {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px 8px 8px;
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.44);
        border-radius: 100px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        cursor: pointer;
        flex-shrink: 0;
        font-family: 'Poppins', sans-serif;
        transition: background 0.2s ease;
    }
    .sv-scope .fg-add-to-list:hover { background: rgba(255, 255, 255, 0.85); }
    .sv-scope .fg-add-to-list .heart {
        width: 20px; height: 20px;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .sv-scope .fg-add-to-list .heart svg { color: #5c5c5c; transition: color 0.2s ease; }
    .sv-scope .fg-add-to-list .text {
        display: flex; flex-direction: column; gap: 2px;
        align-items: flex-start; line-height: 1;
    }
    .sv-scope .fg-add-to-list .text .label {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 12px;
        line-height: 1;
        color: #5c5c5c;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .sv-scope .fg-add-to-list .text .meta {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 10px;
        line-height: 1;
        color: #a3a3a3;
    }
    .sv-scope .fg-add-to-list.is-fav .text { display: none; }
    .sv-scope .fg-add-to-list.is-fav {
        padding: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        justify-content: center;
        align-items: center;
        background: #e74c3c;
        border-color: #e74c3c;
    }
    .sv-scope .fg-add-to-list.is-fav .heart { width: 14px; height: 14px; }
    .sv-scope .fg-add-to-list.is-fav .heart svg { width: 14px; height: 14px; color: #fff; animation: sv-heart-pop 0.4s ease; }

    @keyframes sv-heart-pop {
        0%   { transform: scale(1); }
        25%  { transform: scale(1.35); }
        50%  { transform: scale(0.9); }
        75%  { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    .sv-scope .fg-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px 4px 4px;
        border-radius: 100px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 10px;
        line-height: 20px;
        letter-spacing: 0.8px;
        color: #ffffff;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .sv-scope .fg-status-badge svg { width: 16px; height: 16px; flex-shrink: 0; }
    .sv-scope .fg-status-badge.high-demand { background: #fa7319; }
    .sv-scope .fg-status-badge.pending     { background: #f59e0b; }
    .sv-scope .fg-status-badge.second      { background: #5b8def; }
    .sv-scope .fg-status-badge.sold        { background: #e2534e; }

    .sv-scope .fg-reserve-banner {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        padding: 10px;
        background: rgba(205, 150, 0, 0.12);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 12px;
        line-height: 24px;
        letter-spacing: 0.96px;
        color: #cd9600;
        text-transform: uppercase;
    }

    .sv-scope .fg-card-status-strip {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 34px 12px 6px 12px;
        margin-top: -28px;
        border-bottom-left-radius: 28px;
        border-bottom-right-radius: 28px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 11px;
        line-height: 20px;
    }
    .sv-scope .fg-card-status-strip svg { width: 16px; height: 16px; flex-shrink: 0; }
    .sv-scope .fg-card-status-strip .fg-card-status-dot {
        width: 8px; height: 8px;
        border-radius: 999px;
        background: currentColor;
        flex-shrink: 0;
        display: inline-block;
    }
    .sv-scope .fg-card-status-strip .fg-countdown {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }

    .sv-scope .fg-card-body {
        display: flex; flex-direction: column;
        gap: 16px;
        padding: 8px;
        width: 100%;
    }
    .sv-scope .fg-card-head {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }
    .sv-scope .fg-card-title-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 8px;
    }
    .sv-scope .fg-card-title-row .name {
        font-family: 'Poppins', sans-serif;
        font-weight: 600; font-size: 18px;
        color: #5c5c5c; line-height: 24px;
        white-space: nowrap;
    }
    .sv-scope .fg-card-title-row .roi {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 12px;
        color: #1fc16b; line-height: 24px;
        white-space: nowrap;
    }
    .sv-scope .fg-card-subtitle {
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 12px;
        color: #a3a3a3; line-height: 24px;
    }
    .sv-scope .fg-card-divider {
        width: 100%; height: 1px;
        background: #ebebeb;
        margin: 4px 0;
    }
    .sv-scope .fg-card-price {
        display: flex; align-items: flex-end; gap: 4px;
    }
    .sv-scope .fg-card-price .price {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 24px;
        color: var(--status-color, var(--brand));
        line-height: 24px;
        white-space: nowrap;
    }
    .sv-scope .fg-card-price .sqft {
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 12px;
        color: #a3a3a3; line-height: 24px;
    }
    .sv-scope .fg-discount {
        display: inline-flex;
        align-items: center; justify-content: center;
        padding: 8px;
        background: rgba(205, 150, 0, 0.08);
        border: 1px solid rgba(205, 150, 0, 0.12);
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 10px;
        color: #cd9600;
        cursor: pointer;
        line-height: 1.2;
        text-align: center;
    }

    .sv-scope .fg-stats {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 12px;
        border: 1px solid #f2f5f8;
        border-radius: 12px;
    }
    .sv-scope .fg-stat {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 2px;
        flex: 1 0 0;
        min-width: 0;
        padding: 8px 0;
    }
    .sv-scope .fg-stat svg { width: 22px; height: 22px; color: #848484; }
    .sv-scope .fg-stat .v {
        font-family: 'Poppins', sans-serif;
        font-weight: 600; font-size: 10px;
        color: #848484; line-height: 24px; letter-spacing: 0.2px;
        white-space: nowrap;
    }
    .sv-scope .fg-stat .v sup {
        font-size: 6.45px; vertical-align: super; line-height: 1;
    }
    .sv-scope .fg-stat-divider {
        width: 1px; height: 48px;
        background: #ebebeb;
        flex-shrink: 0;
    }

    .sv-scope .fg-card-actions {
        display: flex; flex-direction: column; gap: 12px;
    }
    .sv-scope .fg-card-buttons {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }
    .sv-scope .fg-btn-info {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center; justify-content: center;
        padding: 10px;
        background: #f2f5f8;
        border: none;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 14px;
        color: #717784;
        letter-spacing: -0.084px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s ease;
    }
    .sv-scope .fg-btn-info:hover { background: #e7eaef; }
    .sv-scope .fg-btn-cta {
        flex: 1 1 0;
        min-width: 0;
        display: inline-flex;
        align-items: center; justify-content: center;
        gap: 4px;
        padding: 10px;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.16) 0%, rgba(255,255,255,0) 100%),
            var(--status-color, var(--brand));
        box-shadow:
            0 1px 2px rgba(14,18,27,0.24),
            0 0 0 1px var(--status-color, var(--brand));
        transition: filter 0.2s ease;
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 14px;
        color: #ffffff;
        letter-spacing: -0.084px;
        cursor: pointer;
    }
    .sv-scope .fg-btn-cta:hover { filter: brightness(1.05); }
    .sv-scope .fg-btn-cta svg { width: 20px; height: 20px; flex-shrink: 0; color: #ffffff; }

    .sv-scope .fg-card-availability {
        display: flex; align-items: center; justify-content: center;
        gap: 8px;
        padding-bottom: 4px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 10px;
        color: #1fc16b;
        letter-spacing: 0.2px;
        line-height: 20px;
    }
    .sv-scope .fg-card-availability .dot {
        display: inline-block; width: 6px; height: 6px; border-radius: 50%;
        background: #1fc16b; box-shadow: 0 0 6px rgba(31,193,107,0.6);
        animation: sv-advisor-dot 1.6s ease-in-out infinite;
    }
    @keyframes sv-advisor-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
    }

    /* Status variants */
    .sv-scope .fg-card.is-high-demand,
    .sv-scope .fg-card.is-pending,
    .sv-scope .fg-card.is-second-chance,
    .sv-scope .fg-card.is-reserved {
        border-color: transparent;
    }
    .sv-scope .fg-card.is-high-demand {
        --status-color: #fa7319;
        box-shadow: 0 0 0 3px #ffe6d5, 0 1px 2px rgba(10,13,20,0.03);
    }
    .sv-scope .fg-card.is-high-demand .fg-card-status-strip { background: #ffe6d5; color: #b75310; }
    .sv-scope .fg-card.is-pending {
        --status-color: #f59e0b;
        box-shadow: 0 0 0 3px #fef3c7, 0 1px 2px rgba(10,13,20,0.03);
    }
    .sv-scope .fg-card.is-pending .fg-card-status-strip { background: #fef3c7; color: #92400e; }
    .sv-scope .fg-card.is-second-chance {
        --status-color: #5b8def;
        box-shadow: 0 0 0 3px #dbeafe, 0 1px 2px rgba(10,13,20,0.03);
    }
    .sv-scope .fg-card.is-second-chance .fg-card-status-strip { background: #dbeafe; color: #1e40af; }
    .sv-scope .fg-card.is-reserved {
        --status-color: #fb3748;
        box-shadow: 0 0 0 3px #ffd5d8, 0 1px 2px rgba(10,13,20,0.03);
    }
    .sv-scope .fg-card.is-reserved .fg-card-status-strip { background: #ffd5d8; color: #ad1f2b; }
    .sv-scope .fg-card.is-reserved .fg-card-img > img { filter: grayscale(20%); opacity: 0.85; }

    /* SOLD */
    .sv-scope .fg-card.is-sold {
        --status-color: var(--brand);
        box-shadow: 0 1px 2px rgba(10,13,20,0.03);
    }
    .sv-scope .fg-card.is-sold .fg-card-img::after {
        content: "";
        position: absolute; inset: 0;
        background: rgba(0, 0, 0, 0.32);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 4;
        pointer-events: none;
        border-radius: 24px;
    }
    .sv-scope .fg-card.is-sold .fg-sold-badge {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%) rotate(-8deg);
        padding: 16px;
        background: #262626;
        border: 2px solid #ffffff;
        border-radius: 32px;
        z-index: 5;
        pointer-events: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .sv-scope .fg-card.is-sold .fg-sold-badge span {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 32px;
        color: #ffffff;
        letter-spacing: 6.4px;
        line-height: 20px;
        white-space: nowrap;
    }
    .sv-scope .fg-card.is-sold .fg-card-price .price { text-decoration: line-through; }
    .sv-scope .fg-card.is-sold .fg-card-title-row .roi { opacity: 0; pointer-events: none; }
    .sv-scope .fg-card.is-sold .fg-card-buttons { width: 100%; }
    .sv-scope .fg-card.is-sold .fg-card-buttons .fg-btn-cta { display: none; }
    .sv-scope .fg-card.is-sold .fg-card-buttons .fg-btn-info {
        flex: 1 1 0; width: 100%;
        background: #f2f5f8; border: 1px solid #eaecf0;
        color: #717784; cursor: not-allowed;
    }
    .sv-scope .fg-card.is-sold .fg-btn-info-similar {
        flex: 1 1 0; width: 100%;
        background: #f2f5f8; border: 1px solid #eaecf0;
        color: #717784; padding: 10px; border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500; font-size: 14px; letter-spacing: -0.084px;
        cursor: pointer; text-align: center;
        transition: background 0.2s ease;
        text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .sv-scope .fg-card.is-sold .fg-btn-info-similar:hover { background: #e7eaef; }
    .sv-scope .fg-card.is-sold .fg-card-availability { color: #e2534e; }
    .sv-scope .fg-card.is-sold .fg-card-availability .dot {
        background: #e2534e;
        box-shadow: 0 0 6px rgba(226, 83, 78, 0.6);
        animation: none;
    }

    @media (max-width: 900px) {
        .sv-scope .fg-stat svg { width: 18px; height: 18px; }
        .sv-scope .fg-card-title-row .name { font-size: 16px; }
        .sv-scope .fg-card-price .price { font-size: 20px; }
    }
</style>
@endpush

@section('content')
@php
    $units = $units ?? collect();
    $project = optional($units->first())->project_id ? \App\Models\Project::find(optional($units->first())->project_id) : null;
    $projectName = $project->name ?? 'Makai Residences, Cap Cana';
@endphp

<div class="sv-scope p-4 sm:p-6 lg:p-7 space-y-5">

    {{-- Header summary --}}
    <div class="sv-header">
        <div>
            <div class="sv-header-title">Guardadas</div>
            <div class="sv-header-sub">{{ $units->count() }} {{ $units->count() === 1 ? 'propiedad' : 'propiedades' }} · {{ $projectName }}</div>
        </div>
        <a href="/" class="sv-cta-explore">
            <i class="pi pi-search text-[11px]"></i> Explorar más
        </a>
    </div>

    @if($units->isEmpty())
        <div class="sv-empty">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto" style="background:#f3f4f6;color:#9ca3af;">
                <i class="pi pi-heart text-[22px]"></i>
            </div>
            <div class="mt-3 text-[15px] font-bold" style="color:#171717;">Aún no tenés propiedades guardadas</div>
            <p class="text-[12px] mt-1 max-w-md mx-auto" style="color:#717784;">
                Tocá el corazón en cualquier unidad del listado para agregarla a tu lista de guardados y revisarla después.
            </p>
            <a href="/" class="sv-empty-cta">
                <i class="pi pi-arrow-right text-[11px]"></i> Ver unidades
            </a>
        </div>
    @else
        <div class="fg-units-grid">
            @foreach($units as $unit)
                @php
                    $st = strtolower($unit->status ?? '');
                    $isSold      = $st === 'sold';
                    $isReserved  = $st === 'reserved';
                    $isPending   = $st === 'pending';
                    $isHighDem   = !empty($unit->is_high_demand) || ($unit->demand_level ?? '') === 'high';
                    $isSecond    = !empty($unit->is_second_chance);
                    $hasDiscount = !empty($unit->discount) && $unit->discount > 0;
                    $cardCls = 'fg-card';
                    if ($isSold)         $cardCls .= ' is-sold';
                    elseif ($isReserved) $cardCls .= ' is-reserved';
                    elseif ($isPending)  $cardCls .= ' is-pending';
                    elseif ($isSecond)   $cardCls .= ' is-second-chance';
                    elseif ($isHighDem)  $cardCls .= ' is-high-demand';
                    $unitId = $unit->custom_id ?? $unit->id;
                    $shortlistedCount = (int) ($unit->shortlisted_count ?? 0);
                @endphp

                <div class="{{ $cardCls }}" data-unit-card data-unit-id="{{ $unit->id }}">
                    <div class="fg-card-inner">

                        <div class="fg-card-img">
                            @if($unit->images && $unit->images->isNotEmpty())
                                <img src="{{ $unit->images->first()->path }}" alt="{{ $unitId }}" onerror="this.style.display='none'">
                            @else
                                <div class="fg-card-img-noimage">No Image Available</div>
                            @endif

                            <div class="fg-chip-row">
                                @if($isPending)
                                    <span class="fg-status-badge pending">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        PENDING
                                    </span>
                                @elseif(!$isReserved && !$isSold && $isHighDem)
                                    <span class="fg-status-badge high-demand">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7 7 0 0 1-7-7c0-2 1-3 1-3 0 1 1 2 2 2 0-3 2-5 2-8 0-2-1-3-1-3 4 0 8 4 8 9 1-1 2-2 2-4 2 1 3 4 3 7a7 7 0 0 1-7 7z"/></svg>
                                        HIGH DEMAND
                                    </span>
                                @elseif(!$isReserved && !$isSold && $isSecond)
                                    <span class="fg-status-badge second">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        2ND CHANCE
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                <button type="button"
                                        class="fg-add-to-list is-fav"
                                        aria-label="Quitar de guardados"
                                        aria-pressed="true"
                                        data-wishlist-toggle data-unit-id="{{ $unit->id }}"
                                        title="Quitar de guardados">
                                    <span class="heart">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                    </span>
                                    <span class="text">
                                        <span class="label">Saved</span>
                                        <span class="meta">Shortlisted by <span data-unit-count="{{ $unit->id }}">{{ $shortlistedCount }}</span> other</span>
                                    </span>
                                </button>
                            </div>

                            <div class="fg-reserve-banner">Reserve from $5000</div>

                            @if($isSold)
                                <div class="fg-sold-badge"><span>SOLD</span></div>
                            @endif
                        </div>

                        <div class="fg-card-body">
                            <div class="fg-card-head">
                                <div class="fg-card-title-row">
                                    <span class="name">{{ $unitId }}</span>
                                    @if(!empty($unit->roi_percent) && (float) $unit->roi_percent > 0)
                                        <span class="roi">{{ rtrim(rtrim(number_format((float) $unit->roi_percent, 1, '.', ''), '0'), '.') }}% ROI</span>
                                    @endif
                                </div>
                                <div class="fg-card-subtitle">
                                    {{ $unit->floor ? ucfirst($unit->floor) . ' Floor' : 'Ground Floor' }}
                                    @if($unit->direction) · {{ strtoupper($unit->direction) }} @endif
                                    @if($unit->outlook) · {{ $unit->outlook }} {{ str_contains(strtolower($unit->outlook), 'view') ? '' : 'View' }} @endif
                                </div>
                                <div class="fg-card-divider"></div>
                                <div class="fg-card-price">
                                    <span class="price">${{ number_format($unit->price, 0, ' ', ' ') }}</span>
                                    @if($unit->internal_area && $unit->internal_area > 0)
                                        <span class="sqft">${{ number_format($unit->price / $unit->internal_area, 0) }}/sqft</span>
                                    @endif
                                </div>
                                @if($hasDiscount)
                                    <button type="button" class="fg-discount" title="Limited time offer">Unlock ${{ number_format($unit->discount, 0, ',', ',') }} Discount</button>
                                @endif
                            </div>

                            <div class="fg-stats">
                                <div class="fg-stat" title="Bedrooms">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg>
                                    <span class="v">{{ $unit->bedrooms ?? 0 }}</span>
                                </div>
                                <span class="fg-stat-divider"></span>
                                <div class="fg-stat" title="Bathrooms">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg>
                                    <span class="v">{{ $unit->bathrooms ?? 0 }}</span>
                                </div>
                                <span class="fg-stat-divider"></span>
                                <div class="fg-stat" title="Parking">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                                    <span class="v">{{ $unit->parking_bays ?? 0 }}</span>
                                </div>
                                <span class="fg-stat-divider"></span>
                                <div class="fg-stat" title="Internal area">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg>
                                    <span class="v">{{ number_format(($unit->internal_area ?? 0)) }}m<sup>2</sup></span>
                                </div>
                                <span class="fg-stat-divider"></span>
                                <div class="fg-stat" title="External area">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg>
                                    <span class="v">{{ number_format(($unit->external_area ?? 0)) }}m<sup>2</sup></span>
                                </div>
                                <span class="fg-stat-divider"></span>
                                <div class="fg-stat" title="Total area">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg>
                                    <span class="v">{{ number_format(($unit->total_area ?? 0)) }}m<sup>2</sup></span>
                                </div>
                            </div>

                            <div class="fg-card-actions">
                                @if($isSold)
                                    <div class="fg-card-buttons">
                                        <a class="fg-btn-info-similar" href="/?unit={{ $unitId }}">View Similar Units</a>
                                    </div>
                                    <div class="fg-card-availability">
                                        <span class="dot"></span>
                                        <span>This unit has been sold.</span>
                                    </div>
                                @elseif($isReserved)
                                    <div class="fg-card-buttons">
                                        <a class="fg-btn-info" href="/?unit={{ $unitId }}">More Info</a>
                                        <button class="fg-btn-cta" type="button" disabled style="cursor:not-allowed;opacity:.5;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l-1 12H6z"/><path d="M9 8V5a3 3 0 1 1 6 0v3"/></svg>
                                            Reserved
                                        </button>
                                    </div>
                                    <div class="fg-card-availability">
                                        <span class="dot"></span>
                                        <span>Currently on hold by another buyer.</span>
                                    </div>
                                @else
                                    <div class="fg-card-buttons">
                                        <a class="fg-btn-info" href="/?unit={{ $unitId }}">More Info</a>
                                        <a class="fg-btn-cta" href="/?unit={{ $unitId }}&action=videocall">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
                                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                            </svg>
                                            Book Video Call
                                        </a>
                                    </div>
                                    <div class="fg-card-availability">
                                        <span class="dot"></span>
                                        <span>An advisor is available right now.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isHighDem)
                        <div class="fg-card-status-strip">
                            <span class="fg-card-status-dot"></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>{{ (int)($unit->views_today ?? 0) ?: $shortlistedCount }} people viewed this unit today</span>
                        </div>
                    @elseif($isPending)
                        <div class="fg-card-status-strip">
                            <span class="fg-card-status-dot"></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Pending review · Hold expires soon</span>
                        </div>
                    @elseif($isSecond)
                        <div class="fg-card-status-strip">
                            <span class="fg-card-status-dot"></span>
                            @php
                                $releasedDays = $unit->released_at ? \Carbon\Carbon::parse($unit->released_at)->diffInDays(now()) : null;
                            @endphp
                            <span>This unit was released {{ $releasedDays !== null ? ($releasedDays === 0 ? 'today' : $releasedDays.' '.\Illuminate\Support\Str::plural('day', $releasedDays).' ago') : 'recently' }}</span>
                        </div>
                    @elseif($isReserved)
                        @php
                            $reservedFuture = !empty($unit->reserved_until) && \Carbon\Carbon::parse($unit->reserved_until)->isFuture();
                        @endphp
                        <div class="fg-card-status-strip is-reserved-strip" @if($reservedFuture) data-reserved-until="{{ \Carbon\Carbon::parse($unit->reserved_until)->toIso8601String() }}" @endif>
                            <span class="fg-card-status-dot"></span>
                            @if($reservedFuture)
                                <span>Reserved for <span class="fg-countdown" data-countdown>00:00:00</span> remaining</span>
                            @else
                                <span>Reserved · Awaiting deposit</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Wishlist toggle — on this page, unsaving removes the card from view
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const unitId = btn.dataset.unitId;
        if (!unitId) return;
        const card = btn.closest('[data-unit-card]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        btn.disabled = true;
        fetch(`/api/wishlist/toggle/${unitId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).then(r => r.ok ? r.json() : Promise.reject(r))
          .then(() => {
            if (card) {
                card.style.transition = 'opacity .2s, transform .2s';
                card.style.opacity = '0';
                card.style.transform = 'scale(.95)';
                setTimeout(() => {
                    card.remove();
                    if (!document.querySelector('[data-unit-card]')) window.location.reload();
                }, 220);
            }
          })
          .catch(() => { btn.disabled = false; });
    });

    // Reserved card countdown (HH:MM:SS)
    (function initReservedCountdowns(){
        function pad(n){ return n.toString().padStart(2, '0'); }
        function tick(){
            const now = Date.now();
            document.querySelectorAll('[data-reserved-until]').forEach(el => {
                const until = Date.parse(el.getAttribute('data-reserved-until'));
                const target = el.querySelector('[data-countdown]');
                if (!target || isNaN(until)) return;
                let diff = Math.max(0, Math.floor((until - now) / 1000));
                const h = Math.floor(diff / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                target.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
                if (diff === 0) el.removeAttribute('data-reserved-until');
            });
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
@endsection
