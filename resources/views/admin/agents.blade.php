@extends('layouts.admin_crm')
@section('title', 'Brokers y Externos — CRM Duna Makai')
@section('page_title', 'Brokers y Externos')
@section('page_breadcrumb', 'Equipo · Brokers y agentes externos')
@php $activeRoute = 'agents'; @endphp

@section('content')
@php
    $agents = \App\Models\Agent::orderBy('name')->get();
    $activos     = $agents->where('active', true)->count();
    $pendientes  = $agents->where('active', false)->count();
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $agents->count() }} colaboradores</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-brokers').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button type="button" onclick="document.getElementById('modal-nuevo-broker').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo broker</button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php $kpis = [
            ['Brokers activos',     $activos,    '#1fc16b'],
            ['Pendientes',          $pendientes, '#fa7319'],
            ['Contratos por vencer',0,           '#fb3748'],
            ['Clientes referidos',  $agents->sum(fn($a) => $a->deals?->count() ?? 0), '#335cff'],
            ['Comisión promedio',   $agents->avg('commission_rate') ? '$'.number_format($agents->avg('commission_rate'),2) : '$0','#5c7c68'],
        ]; @endphp
        @foreach($kpis as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color:{{ $k[2] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $k[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['Todos','Activos','Pendientes','Por vencer'] as $i => $tab)
                    <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="Buscar broker…" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> Filtros</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>Cliente</th>
                        <th>Comisión %</th>
                        <th>Estado</th>
                        <th>Licencia</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $avBg = ['#a6c5b3','#f3b04f','#cdd6df','#d6a3c6']; @endphp
                    @forelse($agents as $a)
                        @php
                            $init = strtoupper(substr($a->name ?? 'A',0,2));
                            $bg = $avBg[$a->id % count($avBg)];
                            $st = $a->active ? ['ACTIVO','ok'] : ['PENDIENTE','warn'];
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-900">{{ $a->name }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $a->email }} · {{ $a->phone ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-[13px] font-bold text-ok-dark">{{ number_format($a->commission_rate ?? 0, 2) }}%</td>
                            <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="text-[12px] text-ink-700">{{ $a->license ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.agents.update', $a->id) }}" class="m-0">@csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $a->name }}">
                                    <input type="hidden" name="email" value="{{ $a->email }}">
                                    <input type="hidden" name="phone" value="{{ $a->phone }}">
                                    <input type="hidden" name="license" value="{{ $a->license }}">
                                    <input type="hidden" name="commission_rate" value="{{ $a->commission_rate }}">
                                    <input type="hidden" name="active" value="{{ $a->active ? 0 : 1 }}">
                                    <button type="submit" class="crm-toggle {{ $a->active ? 'on' : '' }} cursor-pointer border-0"></button>
                                </form>
                            </td>
                            <td class="text-right">
                                <a href="#" class="text-[12px] text-brand font-semibold hover:underline">Ver &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-[12px] text-ink-500 py-8">No hay brokers. <button type="button" onclick="document.getElementById('modal-nuevo-broker').showModal()" class="text-brand font-semibold hover:underline">Crear uno</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<dialog id="modal-nuevo-broker" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.agents.store') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">Nuevo broker</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">Nombre</label><input type="text" name="name" required class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">Email</label><input type="email" name="email" required class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">Teléfono</label><input type="text" name="phone" class="crm-input pl-3 mt-1"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">Licencia</label><input type="text" name="license" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">Comisión %</label><input type="number" name="commission_rate" step="0.01" value="3.00" class="crm-input pl-3 mt-1"></div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">Bio</label><textarea name="bio" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="active" value="1" checked class="w-4 h-4 accent-brand"><label class="text-[12px] text-ink-700">Activar inmediatamente</label></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary">Crear broker</button>
        </div>
    </form>
</dialog>

@include('admin.crm._partials.modal_exportar', ['name' => 'Brokers', 'id' => 'modal-exportar-brokers'])
@endsection
