@extends('layouts.client')
@section('title', 'Guardados — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Guardados')
@php $activeRoute = 'guardados'; @endphp

@section('content')
@php
    $units = $units ?? collect();
    $project = optional($units->first())->project_id ? \App\Models\Project::find(optional($units->first())->project_id) : null;
    $projectName = $project->name ?? 'Makai Residences, Cap Cana';
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    {{-- Header summary --}}
    <div class="px-5 py-4 rounded-2xl bg-ink-100/70 border border-ink-200 flex items-center justify-between flex-wrap gap-2">
        <div>
            <div class="text-[15px] font-bold text-ink-950">Guardadas</div>
            <div class="text-[12px] text-ink-500">{{ $units->count() }} {{ $units->count() === 1 ? 'propiedad' : 'propiedades' }} · {{ $projectName }}</div>
        </div>
        <a href="/" class="cli-btn cli-btn-ghost text-[12px]"><i class="pi pi-search text-[11px]"></i> Explorar más</a>
    </div>

    @if($units->isEmpty())
        <div class="cli-card p-10 text-center">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto"><i class="pi pi-heart text-[22px]"></i></div>
            <div class="mt-3 text-[15px] font-bold text-ink-950">Aún no tenés propiedades guardadas</div>
            <p class="text-[12px] text-ink-500 mt-1 max-w-md mx-auto">Tocá el corazón en cualquier unidad del listado para agregarla a tu lista de guardados y revisarla después.</p>
            <a href="/" class="cli-btn cli-btn-primary inline-flex mt-4"><i class="pi pi-arrow-right text-[11px]"></i> Ver unidades</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($units as $u)
                @php
                    $img = $u->images?->first()?->path ?? null;
                    $unitId   = $u->custom_id ?? $u->name ?? ('Unit-'.$u->id);
                    $floorTxt = $u->floor ? ucfirst($u->floor).' Floor' : 'Ground Floor';
                    $direction = $u->direction ? strtoupper($u->direction) : null;
                    $outlook   = $u->outlook ?: null;
                    $price = (float) ($u->price ?? 0);
                    $sqft  = $u->internal_area && $u->internal_area > 0 ? round($price / $u->internal_area) : null;
                @endphp
                <div class="cli-card overflow-hidden flex flex-col">
                    <div class="relative">
                        <div class="aspect-[16/10] bg-ink-100 overflow-hidden">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $unitId }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-ink-400 text-[12px]"><i class="pi pi-image text-[28px]"></i></div>
                            @endif
                        </div>
                        <button type="button"
                                class="absolute top-3 right-3 w-9 h-9 rounded-full bg-err/10 text-err flex items-center justify-center hover:bg-err/20 transition-colors"
                                data-wishlist-remove data-unit-id="{{ $u->id }}"
                                title="Quitar de guardados">
                            <i class="pi pi-heart-fill text-[14px]"></i>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 text-center py-1.5 text-[11px] font-bold tracking-wider uppercase text-warn-dark"
                             style="background:linear-gradient(180deg,rgba(252,239,222,0) 0%, rgba(252,239,222,0.95) 80%);">
                            RESERVE FROM $5000
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-baseline justify-between gap-2">
                            <div class="text-[18px] font-bold text-ink-950 font-display">{{ $unitId }}</div>
                            @if($u->roi_percent)<div class="text-[12px] font-semibold text-ok-dark">{{ rtrim(rtrim(number_format($u->roi_percent, 1), '0'), '.') }}% ROI</div>@endif
                        </div>
                        <div class="text-[11px] text-ink-500 mt-0.5">
                            {{ $floorTxt }}@if($direction) · {{ $direction }}@endif @if($outlook) · {{ $outlook }}@endif
                        </div>

                        <div class="mt-3 pb-3 border-b border-ink-100">
                            <div class="font-display text-[22px] font-bold text-ink-950">${{ number_format($price, 0, ' ', ' ') }}</div>
                            @if($sqft)<div class="text-[11px] text-ink-500">${{ number_format($sqft) }}/sqft</div>@endif
                        </div>

                        <div class="grid grid-cols-6 gap-1.5 my-3 text-[11px] text-ink-700">
                            <div class="flex flex-col items-center"><i class="pi pi-th-large text-ink-400"></i><span class="mt-1">{{ $u->bedrooms ?? 0 }}</span></div>
                            <div class="flex flex-col items-center"><i class="pi pi-cloud text-ink-400"></i><span class="mt-1">{{ $u->bathrooms ?? 0 }}</span></div>
                            <div class="flex flex-col items-center"><i class="pi pi-car text-ink-400"></i><span class="mt-1">{{ $u->parking_bays ?? 0 }}</span></div>
                            <div class="flex flex-col items-center"><i class="pi pi-stop text-ink-400"></i><span class="mt-1">{{ number_format($u->internal_area ?? 0) }}m²</span></div>
                            <div class="flex flex-col items-center"><i class="pi pi-window-maximize text-ink-400"></i><span class="mt-1">{{ number_format($u->external_area ?? 0) }}m²</span></div>
                            <div class="flex flex-col items-center"><i class="pi pi-clone text-ink-400"></i><span class="mt-1">{{ number_format($u->total_area ?? 0) }}m²</span></div>
                        </div>

                        <div class="mt-auto pt-2 flex items-center gap-2">
                            <a href="/?unit={{ $u->custom_id ?? $u->id }}" class="cli-btn cli-btn-ghost flex-1 text-[12px] py-2">More Info</a>
                            <button type="button" class="cli-btn cli-btn-primary flex-1 text-[12px] py-2"><i class="pi pi-video text-[11px]"></i> Book Video Call</button>
                        </div>
                        <div class="text-center text-[10px] text-ok-dark mt-2 flex items-center justify-center gap-1"><span class="dot bg-ok"></span> An advisor is available right now.</div>
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
        const card = btn.closest('.cli-card');
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
                setTimeout(() => { card.remove(); if (!document.querySelector('.cli-card')) window.location.reload(); }, 220);
            }
          })
          .catch(() => { btn.disabled = false; });
    });
</script>
@endpush
@endsection
