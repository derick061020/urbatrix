@extends('layouts.admin_crm')
@section('title', 'Proyectos — CRM Duna Makai')
@section('page_title', 'Proyectos')
@section('page_breadcrumb', 'Proyectos · Gestión de proyectos')
@php $activeRoute = 'crm.proyectos'; @endphp

@section('content')
@php
    $activos = $proyectos->filter(fn($p) => ($p->progress ?? 0) > 0 || $p->sold_count > 0 || $p->reserved_count > 0)->count();
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex items-center justify-between">
        <div class="text-[14px] font-semibold text-ink-700">{{ $proyectos->count() }} proyectos · {{ $activos }} activos</div>
        <div class="flex items-center gap-2">
            <button class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button type="button" onclick="document.getElementById('modal-nuevo-proyecto').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo proyecto</button>
        </div>
    </div>

    @forelse($proyectos as $p)
        @php
            $color = $p->color ?? '#5c7c68';
            $total = $p->units_count;
            $valorTotal = $p->units()->sum('price');
            $isActive = ($p->sold_count + $p->reserved_count) > 0;
            $pctVentas = $total > 0 ? round((($p->sold_count + $p->reserved_count) / $total) * 100) : 0;
            $pctObra = $p->progress ?? 0;
        @endphp
        <div class="crm-card p-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white shrink-0" style="background:{{ $color }}">
                    <i class="pi pi-building text-base"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-[16px] font-bold text-ink-900">{{ $p->name }}</div>
                            <div class="text-[11px] text-err"><i class="pi pi-map-marker"></i> CAP CANA · PUNTA CANA</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="crm-pill {{ $isActive ? 'bg-ok-soft text-ok-dark' : 'bg-ink-100 text-ink-600' }}">{{ $isActive ? 'ACTIVO' : 'EN PREPARACIÓN' }}</span>
                            <a href="{{ route('admin.units') }}?project={{ $p->id }}" class="crm-btn crm-btn-ghost">Ver unidades</a>
                            <a href="{{ route('admin.crm.proyecto.detalle', $p->id) }}" class="crm-btn crm-btn-primary">Ficha completa &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-5 pt-5 border-t border-ink-100">
                @php $stats = [
                    ['Unidades totales', $total,                  'text-ink-900'],
                    ['Vendidas',         $p->sold_count ?? 0,     'text-ok-dark'],
                    ['Reservadas',       $p->reserved_count ?? 0, 'text-warn'],
                    ['Disponibles',      $p->available_count ?? 0,'text-info'],
                    ['Valor total',      '$'.number_format($valorTotal, 0), 'text-ink-900'],
                ]; @endphp
                @foreach($stats as $s)
                    <div>
                        <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $s[0] }}</div>
                        <div class="text-[22px] font-bold {{ $s[2] }} mt-1">{{ $s[1] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5 pt-5 border-t border-ink-100">
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-[12px] text-ink-700 mb-1">
                            <span class="font-semibold">Progreso de ventas</span><span class="font-bold">{{ $pctVentas }}%</span>
                        </div>
                        <div class="crm-progress"><span style="background:{{ $color }};width:{{ $pctVentas }}%"></span></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-[12px] text-ink-700 mb-1">
                            <span class="font-semibold">Avance de obra</span><span class="font-bold">{{ $pctObra }}%</span>
                        </div>
                        <div class="crm-progress"><span class="bg-info" style="width:{{ $pctObra }}%"></span></div>
                    </div>
                    <div class="text-[11px] text-ink-500"><i class="pi pi-info-circle"></i> {{ $p->stage ?? 'En desarrollo' }} · {{ $p->description }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-400 mb-2">Clientes activos</div>
                    <div class="space-y-2">
                        @php
                            $recientes = \App\Models\Reservation::whereHas('unit', fn($q) => $q->where('project_id', $p->id))
                                ->with(['unit','documents'])->orderBy('created_at', 'desc')->take(3)->get();
                        @endphp
                        @forelse($recientes as $cli)
                            @php
                                $init = strtoupper(substr($cli->first_name ?? 'C',0,1) . substr($cli->last_name ?? '',0,1));
                                $hasAll = $cli->documents->where('status', 'approved')->count() === $cli->documents->count() && $cli->documents->count() > 0;
                                $estado = $hasAll ? ['AL DÍA','ok'] : ($cli->documents->count() === 0 ? ['KYC PENDIENTE','warn'] : ['EN REVISIÓN','info']);
                            @endphp
                            <a href="{{ route('admin.crm.expediente.detalle', $cli->id) }}" class="flex items-center gap-3 hover:bg-ink-50 rounded px-2 py-1">
                                <div class="crm-avatar crm-avatar-sm" style="background:#7cb8e7">{{ $init }}</div>
                                <div class="flex-1">
                                    <div class="text-[13px] font-semibold text-ink-900">{{ $cli->first_name }} {{ $cli->last_name }}</div>
                                    <div class="text-[11px] text-ink-500">{{ $cli->unit->name ?? '—' }}</div>
                                </div>
                                <span class="crm-pill bg-{{ $estado[1] }}-soft text-{{ $estado[1] }}">{{ $estado[0] }}</span>
                            </a>
                        @empty
                            <div class="text-[12px] text-ink-500">Sin clientes activos.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="crm-card p-6 text-center text-[12px] text-ink-500">No hay proyectos creados.</div>
    @endforelse
</div>

<dialog id="modal-nuevo-proyecto" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.proyectos.store') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">Nuevo proyecto</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">Nombre</label><input type="text" name="name" required class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">Tipo</label><input type="text" name="type" placeholder="Residencial" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">Etapa</label><input type="text" name="stage" placeholder="En desarrollo" class="crm-input pl-3 mt-1"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">Avance %</label><input type="number" name="progress" value="0" min="0" max="100" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">Color</label><input type="color" name="color" value="#5c7c68" class="h-9 w-full rounded-md border border-ink-200 mt-1"></div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">Descripción</label><textarea name="description" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary">Crear proyecto</button>
        </div>
    </form>
</dialog>
@endsection
