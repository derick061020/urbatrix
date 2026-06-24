@extends('layouts.admin_crm')
@section('title', 'Expedientes — CRM Duna Makai')
@section('page_title', 'Expedientes')
@section('page_breadcrumb', 'Gestión · Expedientes de clientes')
@php $activeRoute = 'crm.expedientes'; @endphp

@push('styles')
<style>
    .dot-tip { position: relative; display: inline-flex; align-items: center; justify-content: center; padding: 3px; cursor: default; outline: none; }
    /* Floating tooltip is appended to <body> so it never gets clipped by the
       table's / cell's overflow-x-auto (which also clips overflow-y). */
    #dot-tip-float {
        position: fixed; z-index: 1000; transform: translate(-50%, -100%);
        background: #222530; color: #fff; font-size: 11px; font-weight: 500; line-height: 1.2;
        padding: 5px 8px; border-radius: 6px; white-space: nowrap; pointer-events: none;
        opacity: 0; visibility: hidden; transition: opacity .12s;
        box-shadow: 0 4px 12px -2px rgba(0,0,0,.25);
    }
    #dot-tip-float.show { opacity: 1; visibility: visible; }
    #dot-tip-float::after {
        content: ""; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
        border: 4px solid transparent; border-top-color: #222530;
    }
    .dot-tip__done { color: #4ade80; font-weight: 700; margin-left: 2px; }
    .dot-tip__pending { color: #99a0ae; font-weight: 500; margin-left: 2px; }
</style>
@endpush

@section('content')
@php
    $currentTab = $tab ?? request('tab', 'todos');
    $hasFilters = filled($search ?? null) || filled($unitId ?? null) || filled($dateFrom ?? null) || filled($dateTo ?? null);
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>
    @endif

    {{-- Header counter + actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $reservations->total() }} clientes activos</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-expedientes').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nuevo Expediente') }}</button>
        </div>
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['todos' => 'Todos','kyc' => 'KYC Pendiente','firma' => 'Firma requerida','vencido' => 'Pago Vencido','al-dia' => 'Al día'] as $slug => $label)
                    <a href="{{ route('admin.crm.expedientes', array_merge(request()->except(['page', 'tab']), ['tab' => $slug])) }}" class="crm-tab {{ $currentTab === $slug ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.crm.expedientes') }}" class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto m-0">
                <div class="relative w-full sm:w-64">
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('Buscar expediente…') }}" class="crm-input pr-3">
                </div>
                <select name="unit_id" class="crm-input pl-3 w-full sm:w-44">
                    <option value="">{{ __('Todas las unidades') }}</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected((string)($unitId ?? '') === (string)$u->id)>{{ $u->custom_id ?? $u->name }} {{ $u->name && $u->custom_id ? '· '.$u->name : '' }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="crm-input pl-3 w-full sm:w-36" title="{{ __('Desde') }}">
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="crm-input pl-3 w-full sm:w-36" title="Hasta">
                <button type="submit" class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> {{ __('Filtros') }}</button>
                @if($hasFilters)
                    <a href="{{ route('admin.crm.expedientes', ['tab' => $currentTab]) }}" class="crm-btn crm-btn-ghost"><i class="pi pi-times"></i> {{ __('Limpiar') }}</a>
                @endif
                <button type="button" class="crm-btn crm-btn-ghost">{{ __('Acciones en lote') }} <i class="pi pi-angle-down text-[10px]"></i></button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>{{ __('Cliente') }}</th>
                        <th>{{ __('Unidad') }}</th>
                        <th>{{ __('Paso') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Asesor') }}</th>
                        <th>{{ __('Pagado') }}</th>
                        <th>{{ __('Últ. actividad') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $avBg = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3','#5c7c68']; @endphp
                    @forelse($reservations as $r)
                        @php
                            $init = strtoupper(substr($r->first_name ?? 'C', 0, 1) . substr($r->last_name ?? '', 0, 1));
                            [$estado, $color, $step] = $r->pipelineStage();
                            $stepName = $estado;
                            $total  = (float)($r->unit?->price ?? 0);
                            $paidSum= $r->payments?->where('status', 'paid')->sum('amount') ?? 0;
                            $pct    = $total > 0 ? round(($paidSum / $total) * 100) : 0;
                            $bg = $avBg[$r->id % count($avBg)];
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-semibold text-ink-900">{{ $r->first_name }} {{ $r->last_name }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $r->country ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-[13px] font-semibold text-ink-900">{{ $r->unit->name ?? $r->unit->custom_id ?? '—' }}</div>
                                <div class="text-[11px] text-ink-500">{{ __('Makai Residences') }}</div>
                            </td>
                            <td>
                                @php $phaseNames = [1 => 'Reserva', 2 => 'KYC', 3 => 'Presupuesto', 4 => 'Plan de pagos / Documentos', 5 => 'Contrato firmado']; @endphp
                                <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="dot-tip" tabindex="0" data-tip-label="{{ $phaseNames[$s] }}" data-tip-state="{{ $s <= $step ? 'done' : 'pending' }}">
                                            <span class="dot" style="background: {{ $s <= $step ? '#5c7c68' : '#eaecf0' }}"></span>
                                        </span>
                                    @endfor
                                    <span class="text-[11px] text-ink-500 ml-2">{{ $stepName }}</span>
                                </div>
                            </td>
                            <td><span class="crm-pill bg-{{ $color }}-soft text-{{ $color }}">● {{ $estado }}</span></td>
                            <td><span class="text-[13px] text-ink-700">{{ $advisors[$r->user_id ?? 0] ?? 'Sin asignar' }}</span></td>
                            <td>
                                <div class="text-[13px] font-bold text-ok-dark">${{ number_format($paidSum) }}</div>
                                <div class="text-[11px] text-ink-500">{{ $pct }}% de ${{ number_format($total) }}</div>
                            </td>
                            <td><span class="text-[12px] text-ink-500">{{ $r->updated_at?->diffForHumans() }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="text-[12px] text-brand font-semibold hover:underline">{{ __('Ver &rarr;') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-[12px] text-ink-500 py-8">{{ __('No hay expedientes. Crea uno con') }} <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="text-brand font-semibold hover:underline">{{ __('Nueva reserva') }}</button>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-ink-100">
            {{ $reservations->withQueryString()->links('pagination::crm') }}
        </div>
    </div>
</div>

@include('admin.crm._partials.modal_nueva_reserva', ['units' => $units, 'clients' => $clients])
@include('admin.crm._partials.modal_exportar', ['name' => 'Expedientes', 'id' => 'modal-exportar-expedientes'])
@endsection

@push('scripts')
<script>
(function () {
    var tip = document.createElement('div');
    tip.id = 'dot-tip-float';
    document.body.appendChild(tip);

    function show(el) {
        var label = el.dataset.tipLabel || '';
        var badge = el.dataset.tipState === 'done'
            ? '<span class="dot-tip__done">✓</span>'
            : '<span class="dot-tip__pending">pendiente</span>';
        tip.innerHTML = label + ' ' + badge;
        var r = el.getBoundingClientRect();
        tip.style.left = (r.left + r.width / 2) + 'px';
        tip.style.top  = (r.top - 6) + 'px';
        tip.classList.add('show');
    }
    function hide() { tip.classList.remove('show'); }

    document.querySelectorAll('.dot-tip').forEach(function (el) {
        el.addEventListener('mouseenter', function () { show(el); });
        el.addEventListener('mouseleave', hide);
        el.addEventListener('focus', function () { show(el); });
        el.addEventListener('blur', hide);
    });
    // Hide while scrolling so the fixed tooltip doesn't float over stale spots.
    window.addEventListener('scroll', hide, true);
})();
</script>
@endpush
