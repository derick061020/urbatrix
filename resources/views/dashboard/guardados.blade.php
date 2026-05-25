@extends('layouts.client')
@section('title', 'Guardados — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Guardados')
@php $activeRoute = 'guardados'; @endphp

@push('styles')
<style>
    /* Saved-units page — match home grid card 1:1 */
    .sv-grid {
        display:grid; gap:20px;
        grid-template-columns: repeat(1, minmax(0,1fr));
    }
    @media (min-width: 768px)  { .sv-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (min-width: 1280px) { .sv-grid { grid-template-columns: repeat(3, minmax(0,1fr)); } }

    .sv-card {
        background:#fff;
        border:1px solid #ebebeb;
        border-radius:18px;
        overflow:hidden;
        display:flex; flex-direction:column;
        transition: box-shadow .2s, transform .2s;
    }
    .sv-card:hover { box-shadow:0 6px 18px -8px rgba(10,13,20,.18); }

    .sv-img-wrap { position:relative; aspect-ratio: 16/12; background:#f2f5f8; overflow:hidden; }
    .sv-img-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
    .sv-img-noimg {
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        color:#a3a3a3; font-size:12px;
    }
    .sv-heart {
        position:absolute; top:12px; right:12px;
        width:36px; height:36px; border-radius:999px;
        background:rgba(255,255,255,.92);
        color:#fb3748;
        border:none; display:inline-flex; align-items:center; justify-content:center;
        cursor:pointer; box-shadow:0 1px 3px rgba(10,13,20,.12);
        transition: transform .15s, background .15s;
    }
    .sv-heart:hover { background:#fff; transform: scale(1.05); }
    .sv-heart .pi { font-size:15px; }

    .sv-reserve-banner {
        position:absolute; left:0; right:0; bottom:0;
        text-align:center; padding:8px 0 10px;
        font-family:'Poppins', sans-serif;
        font-size:11px; font-weight:700; letter-spacing:.12em;
        text-transform:uppercase;
        color:#b67a06;
        background:linear-gradient(180deg, rgba(252,239,222,0) 0%, rgba(252,239,222,.96) 65%);
    }

    .sv-body { padding:16px 16px 14px; display:flex; flex-direction:column; gap:6px; flex:1; }
    .sv-title-row {
        display:flex; align-items:flex-end; justify-content:space-between; gap:8px;
    }
    .sv-name {
        font-family:'Poppins', sans-serif;
        font-weight:600; font-size:18px;
        color:#5c5c5c; line-height:24px;
        white-space:nowrap;
    }
    .sv-roi {
        font-family:'Poppins', sans-serif;
        font-weight:700; font-size:12px;
        color:#1fc16b; line-height:24px;
        white-space:nowrap;
    }
    .sv-subtitle {
        font-family:'Poppins', sans-serif;
        font-weight:500; font-size:12px;
        color:#a3a3a3; line-height:20px;
    }
    .sv-divider { height:1px; background:#eaecf0; margin:10px 0 8px; }
    .sv-price-row { display:flex; align-items:baseline; gap:8px; flex-wrap:wrap; }
    .sv-price {
        font-family:'Inter Tight', Inter, sans-serif;
        font-weight:700; font-size:24px; color:#222530; line-height:1;
    }
    .sv-sqft { font-size:12px; color:#a3a3a3; }

    .sv-stats {
        display:grid; grid-template-columns: repeat(6, minmax(0,1fr));
        gap:4px; margin:14px 0 8px;
        padding:8px 4px; border-radius:10px;
        border:1px solid #f2f5f8; background:#fafbfc;
    }
    .sv-stat {
        display:flex; flex-direction:column; align-items:center; gap:4px;
        font-size:11px; color:#525866;
    }
    .sv-stat svg { width:16px; height:16px; color:#99a0ae; }
    .sv-stat .v { font-weight:600; }

    .sv-actions { display:flex; gap:8px; margin-top:8px; }
    .sv-btn {
        flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px;
        height:38px; border-radius:10px;
        font-family:'Poppins', sans-serif;
        font-weight:600; font-size:12px; cursor:pointer;
        transition: background .15s, border-color .15s;
    }
    .sv-btn-info  { background:#fff; color:#525866; border:1px solid #eaecf0; }
    .sv-btn-info:hover { background:#f5f7fa; }
    .sv-btn-cta   { background:#5c7c68; color:#fff; border:1px solid #5c7c68; }
    .sv-btn-cta:hover { background:#4a6354; }

    .sv-availability {
        display:flex; align-items:center; justify-content:center; gap:6px;
        margin-top:8px;
        font-size:11px; color:#1daf61; font-weight:500;
    }
    .sv-availability .sv-dot {
        width:6px; height:6px; border-radius:999px; background:#1fc16b;
        box-shadow:0 0 0 3px rgba(31,193,107,.18);
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

    /* Empty state */
    .sv-empty {
        background:#fff; border:1px dashed #eaecf0; border-radius:18px;
        padding:48px 20px; text-align:center;
    }
</style>
@endpush

@section('content')
@php
    $units = $units ?? collect();
    $project = optional($units->first())->project_id ? \App\Models\Project::find(optional($units->first())->project_id) : null;
    $projectName = $project->name ?? 'Makai Residences, Cap Cana';
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    {{-- Header summary --}}
    <div class="sv-header">
        <div>
            <div class="sv-header-title">Guardadas</div>
            <div class="sv-header-sub">{{ $units->count() }} {{ $units->count() === 1 ? 'propiedad' : 'propiedades' }} · {{ $projectName }}</div>
        </div>
        <a href="/" class="sv-btn sv-btn-info" style="flex:0 1 auto; padding:0 14px;">
            <i class="pi pi-search text-[11px]"></i> Explorar más
        </a>
    </div>

    @if($units->isEmpty())
        <div class="sv-empty">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto">
                <i class="pi pi-heart text-[22px]"></i>
            </div>
            <div class="mt-3 text-[15px] font-bold text-ink-950">Aún no tenés propiedades guardadas</div>
            <p class="text-[12px] text-ink-500 mt-1 max-w-md mx-auto">
                Tocá el corazón en cualquier unidad del listado para agregarla a tu lista de guardados y revisarla después.
            </p>
            <a href="/" class="sv-btn sv-btn-cta inline-flex mt-4" style="flex:0 1 auto; padding:0 18px;">
                <i class="pi pi-arrow-right text-[11px]"></i> Ver unidades
            </a>
        </div>
    @else
        <div class="sv-grid">
            @foreach($units as $u)
                @php
                    $img       = $u->images?->first()?->path ?? null;
                    $unitId    = $u->custom_id ?? $u->name ?? ('Unit-'.$u->id);
                    $floorTxt  = $u->floor ? ucfirst($u->floor).' Floor' : 'Ground Floor';
                    $direction = $u->direction ? strtoupper($u->direction) : null;
                    $outlook   = $u->outlook ?: null;
                    $price     = (float) ($u->price ?? 0);
                    $sqft      = $u->internal_area && $u->internal_area > 0 ? round($price / $u->internal_area) : null;
                    $roi       = (float) ($u->roi_percent ?? 0);
                @endphp
                <div class="sv-card" data-unit-card data-unit-id="{{ $u->id }}">
                    <div class="sv-img-wrap">
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $unitId }}" onerror="this.style.display='none'">
                        @else
                            <div class="sv-img-noimg"><i class="pi pi-image text-[28px]"></i></div>
                        @endif

                        <button type="button" class="sv-heart" data-wishlist-remove data-unit-id="{{ $u->id }}" title="Quitar de guardados" aria-label="Quitar de guardados">
                            <i class="pi pi-heart-fill"></i>
                        </button>

                        <div class="sv-reserve-banner">Reserve from $5000</div>
                    </div>

                    <div class="sv-body">
                        <div class="sv-title-row">
                            <span class="sv-name">{{ $unitId }}</span>
                            @if($roi > 0)
                                <span class="sv-roi">{{ rtrim(rtrim(number_format($roi, 1, '.', ''), '0'), '.') }}% ROI</span>
                            @endif
                        </div>

                        <div class="sv-subtitle">
                            {{ $floorTxt }}@if($direction) · {{ $direction }}@endif @if($outlook) · {{ $outlook }} @endif
                        </div>

                        <div class="sv-divider"></div>

                        <div class="sv-price-row">
                            <span class="sv-price">${{ number_format($price, 0, ' ', ' ') }}</span>
                            @if($sqft)<span class="sv-sqft">${{ number_format($sqft) }}/sqft</span>@endif
                        </div>

                        <div class="sv-stats">
                            <div class="sv-stat" title="Bedrooms">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg>
                                <span class="v">{{ $u->bedrooms ?? 0 }}</span>
                            </div>
                            <div class="sv-stat" title="Bathrooms">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg>
                                <span class="v">{{ $u->bathrooms ?? 0 }}</span>
                            </div>
                            <div class="sv-stat" title="Parking">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                                <span class="v">{{ $u->parking_bays ?? 0 }}</span>
                            </div>
                            <div class="sv-stat" title="Internal area">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg>
                                <span class="v">{{ number_format($u->internal_area ?? 0) }}m²</span>
                            </div>
                            <div class="sv-stat" title="External area">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg>
                                <span class="v">{{ number_format($u->external_area ?? 0) }}m²</span>
                            </div>
                            <div class="sv-stat" title="Total area">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg>
                                <span class="v">{{ number_format($u->total_area ?? 0) }}m²</span>
                            </div>
                        </div>

                        <div class="sv-actions">
                            <a href="/?unit={{ $u->custom_id ?? $u->id }}" class="sv-btn sv-btn-info">More Info</a>
                            <button type="button" class="sv-btn sv-btn-cta">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                </svg>
                                Book Video Call
                            </button>
                        </div>

                        <div class="sv-availability">
                            <span class="sv-dot"></span>
                            <span>An advisor is available right now.</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-wishlist-remove]');
        if (!btn) return;
        const unitId = btn.dataset.unitId;
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
</script>
@endpush
@endsection
