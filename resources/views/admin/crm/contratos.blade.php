@extends('layouts.admin_crm')
@section('title', 'Reservas y Contratos — CRM Duna Makai')
@section('page_title', 'Reservas y Contratos')
@section('page_breadcrumb', 'Gestión · Reservas y contratos')
@php $activeRoute = 'crm.contratos'; @endphp

@section('content')
@php
    $units = \App\Models\Unit::orderBy('custom_id')->get(['id','custom_id','name','price']);

    $countContratos = \App\Models\Document::where('document_type', 'contract')->whereIn('status', ['signed','approved'])->count();
    $porFirmar      = \App\Models\Document::whereIn('document_type', ['contract','promise'])->where('status', 'generated')->count();
    $reservasCount  = $reservations->count();
    $firmados       = $reservations->filter(fn($r) => $r->documents->where('status', 'signed')->isNotEmpty() || $r->documents->where('status', 'approved')->isNotEmpty())->count();
    $pagoVencido    = \App\Models\Payment::where('status', 'overdue')->count();
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $reservasCount }} contratos activos</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-contratos').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nueva reserva</button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php $kpi = [
            ['n' => $reservasCount,  'label' => 'Reservas',    'c' => '#5c7c68'],
            ['n' => $countContratos, 'label' => 'Contratos',   'c' => '#335cff'],
            ['n' => $porFirmar,      'label' => 'Por firmar',  'c' => '#fa7319'],
            ['n' => $pagoVencido,    'label' => 'Pago vencido','c' => '#fb3748'],
            ['n' => $firmados,       'label' => 'Firmados',    'c' => '#1fc16b'],
        ]; @endphp
        @foreach($kpi as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color: {{ $k['c'] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k['label'] }}</div>
                <div class="text-[28px] font-bold text-ink-900 leading-tight mt-1">{{ $k['n'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['Todos','Reservas','Contratos','Por firmar','Pago vencido'] as $i => $tab)
                    <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="Buscar documento…" class="crm-input pr-3">
                </div>
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
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Pagado</th>
                        <th>Total</th>
                        <th>Fecha firma</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $r)
                        @php
                            $hasContract = $r->documents->whereIn('document_type', ['contract','promise'])->isNotEmpty();
                            $signed      = $r->documents->whereIn('status', ['signed','approved'])->count() > 0;
                            $tipo        = $hasContract ? 'CONTRATO' : 'RESERVA';
                            if ($signed)              { $estado = ['Firmado','ok']; }
                            elseif ($hasContract)     { $estado = ['Por firmar','warn']; }
                            elseif ($r->payments->where('status', 'overdue')->count() > 0) { $estado = ['Pago vencido','err']; }
                            else                      { $estado = ['Firmado','ok']; }
                            $tipoColor = ['RESERVA' => 'bg-info-soft text-info', 'CONTRATO' => 'bg-warn-soft text-warn-dark'];
                            $total = (float)($r->unit?->price ?? 0);
                            $paid  = $r->payments->where('status', 'paid')->sum('amount');
                            $firmaDoc = $r->documents->whereIn('status', ['signed','approved'])->sortByDesc('signed_at')->first();
                            $fechaFirma = optional($firmaDoc?->signed_at ?? $r->created_at)->format('Y-m-d');
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td class="text-[13px] font-semibold text-ink-900">{{ $r->first_name }} {{ $r->last_name }}</td>
                            <td>
                                <div class="text-[13px] text-ink-900">{{ $r->unit->name ?? $r->unit->custom_id ?? '—' }}</div>
                                <div class="text-[11px] text-ink-500">Makai Residences</div>
                            </td>
                            <td><span class="crm-pill {{ $tipoColor[$tipo] }}">{{ $tipo }}</span></td>
                            <td><span class="crm-pill bg-{{ $estado[1] }}-soft text-{{ $estado[1] }}">{{ $estado[0] }}</span></td>
                            <td class="text-[13px] font-bold text-ok-dark">${{ number_format($paid) }}</td>
                            <td class="text-[13px] text-ink-700">${{ number_format($total) }}</td>
                            <td class="text-[12px] text-ink-700">{{ $fechaFirma }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if($estado[0] === 'Por firmar')
                                    <a href="{{ route('admin.crm.contract.generate', $r->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1">Firmar</a>
                                @endif
                                <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="text-[12px] text-brand font-semibold hover:underline">Ver &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-[12px] text-ink-500 py-8">Sin contratos activos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.crm._partials.modal_nueva_reserva', ['units' => $units])
@include('admin.crm._partials.modal_exportar', ['name' => 'Contratos', 'id' => 'modal-exportar-contratos'])
@endsection
