@extends('layouts.admin_crm')
@section('title', 'Expedientes — CRM Duna Makai')
@section('page_title', 'Expedientes')
@section('page_breadcrumb', 'Gestión · Expedientes de clientes')
@php $activeRoute = 'crm.expedientes'; @endphp

@section('content')
@php
    $advisors = \App\Models\Agent::pluck('name', 'id');
    $units    = \App\Models\Unit::orderBy('custom_id')->get(['id','custom_id','name','price']);
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>
    @endif

    {{-- Header counter + actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $reservations->total() }} clientes activos</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-expedientes').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo Expediente</button>
        </div>
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @php $currentTab = request('tab', 'todos'); @endphp
                @foreach (['todos' => 'Todos','kyc' => 'KYC Pendiente','firma' => 'Firma requerida','vencido' => 'Pago Vencido','al-dia' => 'Al día'] as $slug => $label)
                    <a href="?tab={{ $slug }}" class="crm-tab {{ $currentTab === $slug ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <form method="GET" class="relative w-full sm:w-64 m-0">
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar expediente…" class="crm-input pr-3">
                </form>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> Filtros</button>
                <button class="crm-btn crm-btn-ghost">Acciones en lote <i class="pi pi-angle-down text-[10px]"></i></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>Cliente</th>
                        <th>Unidad</th>
                        <th>Paso</th>
                        <th>Estado</th>
                        <th>Asesor</th>
                        <th>Pagado</th>
                        <th>Últ. actividad</th>
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
                                <div class="text-[11px] text-ink-500">Makai Residences</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="dot" style="background: {{ $s <= $step ? '#5c7c68' : '#eaecf0' }}"></span>
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
                                <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="text-[12px] text-brand font-semibold hover:underline">Ver &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-[12px] text-ink-500 py-8">No hay expedientes. Crea uno con <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="text-brand font-semibold hover:underline">Nueva reserva</button>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-ink-100">
            {{ $reservations->withQueryString()->links() }}
        </div>
    </div>
</div>

@include('admin.crm._partials.modal_nueva_reserva', ['units' => $units])
@include('admin.crm._partials.modal_exportar', ['name' => 'Expedientes', 'id' => 'modal-exportar-expedientes'])
@endsection
