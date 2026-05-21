@extends('layouts.admin_crm')
@section('title', $proyecto->name . ' — CRM Duna Makai')
@section('page_title', 'Ficha de Proyecto')
@section('page_breadcrumb', 'Proyectos · '.$proyecto->name)
@php $activeRoute = 'crm.proyectos'; @endphp

@section('content')
@php
    $color      = $proyecto->color ?? '#5c7c68';
    $totalUnits = $proyecto->units_count;
    $sold       = $proyecto->sold_count;
    $reserved   = $proyecto->reserved_count;
    $available  = $proyecto->available_count;
    $valorTotal = $proyecto->units()->sum('price');
    $vendidoUSD = $proyecto->units()->whereIn('status', ['SOLD','RESERVED'])->sum('price');
    $avgPrice   = $totalUnits > 0 ? round($valorTotal / $totalUnits) : 0;
    $reservasActivas = \App\Models\Reservation::whereHas('unit', fn($q) => $q->where('project_id', $proyecto->id))->count();
    $pctVentas  = $totalUnits > 0 ? round((($sold + $reserved) / $totalUnits) * 100) : 0;
    $pctObra    = $proyecto->progress ?? 0;

    $tipos = $units->groupBy(fn($u) => $u->layout ?: $u->type ?: 'Sin tipo')->map(function ($group) {
        return [
            'disp'  => $group->whereIn('status', ['AVAILABLE','available'])->count(),
            'resv'  => $group->whereIn('status', ['RESERVED','reserved'])->count(),
            'sold'  => $group->whereIn('status', ['SOLD','sold'])->count(),
            'min'   => $group->min('price'),
            'max'   => $group->max('price'),
        ];
    });
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    <div class="crm-card overflow-hidden">
        <div class="p-6 text-white relative" style="background:linear-gradient(135deg,{{ $color }} 0%, color-mix(in srgb, {{ $color }} 70%, black) 100%)">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.crm.proyectos') }}" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur flex items-center justify-center hover:bg-white/25"><i class="pi pi-arrow-left text-[12px]"></i></a>
                <div class="flex-1">
                    <div class="text-[24px] font-bold leading-tight">{{ $proyecto->name }}</div>
                    <div class="text-[12px] opacity-80 mt-1"><i class="pi pi-map-marker"></i> Cap Cana · Punta Cana &nbsp;·&nbsp; {{ $totalUnits }} unidades &nbsp;·&nbsp; {{ $proyecto->stage ?? 'En desarrollo' }}</div>
                </div>
                <a href="{{ route('admin.units') }}" class="crm-btn crm-btn-primary bg-white !text-brand"><i class="pi pi-plus"></i> Nueva unidad</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div>
                    <div class="text-[10px] uppercase opacity-70 font-semibold">Unidades vendidas</div>
                    <div class="text-[24px] font-bold mt-1">{{ $sold }}</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase opacity-70 font-semibold">Reservadas</div>
                    <div class="text-[24px] font-bold mt-1">{{ $reserved }}</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase opacity-70 font-semibold">Disponibles</div>
                    <div class="text-[24px] font-bold mt-1">{{ $available }}</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase opacity-70 font-semibold">Valor proyecto</div>
                    <div class="text-[24px] font-bold mt-1">${{ number_format($valorTotal / 1_000_000, 2) }}M</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpis = [
            ['Avance ventas', '$'.number_format($vendidoUSD / 1_000_000, 2).'M', '#5c7c68'],
            ['Avance obra',   $pctObra.'%',                                       '#fa7319'],
            ['Avg precio',    '$'.number_format($avgPrice),                       '#335cff'],
            ['Reservas activas', $reservasActivas,                                '#fb3748'],
        ]; @endphp
        @foreach($kpis as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color:{{ $k[2] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $k[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 flex items-center justify-between">
                <h3 class="text-[14px] font-semibold text-ink-900">Inventario por tipología</h3>
                <a href="{{ route('admin.units') }}" class="text-[11px] text-brand font-semibold hover:underline">Ver detalle &rarr;</a>
            </div>
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr><th>Tipología</th><th>Disponibles</th><th>Reservadas</th><th>Vendidas</th><th>Precio mín.</th><th>Precio máx.</th></tr>
                </thead>
                <tbody>
                    @forelse($tipos as $tipo => $d)
                        <tr>
                            <td class="text-[13px] font-semibold text-ink-900">{{ $tipo }}</td>
                            <td class="text-[13px] text-ink-700">{{ $d['disp'] }}</td>
                            <td class="text-[13px] text-warn">{{ $d['resv'] }}</td>
                            <td class="text-[13px] text-err">{{ $d['sold'] }}</td>
                            <td class="text-[13px] font-bold text-ok-dark">${{ number_format($d['min'] ?? 0) }}</td>
                            <td class="text-[13px] font-bold text-ok-dark">${{ number_format($d['max'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">Sin tipologías registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-4">
            <div class="crm-card p-5">
                <div class="text-[14px] font-semibold text-ink-900 mb-3">Progreso de ventas</div>
                <div class="flex items-center gap-3 mb-1 text-[12px]"><span class="text-ink-500 flex-1">{{ $pctVentas }}% completado</span><span class="font-bold text-ink-900">{{ $pctVentas }}%</span></div>
                <div class="crm-progress"><span style="background:{{ $color }};width:{{ $pctVentas }}%"></span></div>

                <div class="text-[14px] font-semibold text-ink-900 mt-5 mb-3">Avance de obra</div>
                <div class="flex items-center gap-3 mb-1 text-[12px]"><span class="text-ink-500 flex-1">{{ $pctObra }}% completado</span><span class="font-bold text-ink-900">{{ $pctObra }}%</span></div>
                <div class="crm-progress"><span class="bg-info" style="width:{{ $pctObra }}%"></span></div>
            </div>

            <div class="crm-card p-5">
                <div class="text-[14px] font-semibold text-ink-900 mb-3">{{ $proyecto->name }}</div>
                <div class="text-[12px] text-ink-600">{{ $proyecto->description ?? 'Resort privado de talla mundial.' }}</div>
                <div class="text-[10px] uppercase font-semibold text-ink-400 mt-4">Amenidades</div>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach (['Beach Club','Spa','Marina','Golf','Gimnasio','Restaurantes','Helipuerto'] as $a)
                        <span class="crm-pill bg-ink-100 text-ink-600">{{ $a }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @php $strip = [
            ['file-pdf','Plano por unidad','warn'],
            ['box','Brochure','info'],
            ['video','Render','err'],
            ['shield','Especificaciones','ok'],
        ]; @endphp
        @foreach($strip as $s)
            <div class="crm-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $s[2] }}-soft flex items-center justify-center text-{{ $s[2] }}">
                    <i class="pi pi-{{ $s[0] }}"></i>
                </div>
                <div class="flex-1">
                    <div class="text-[13px] font-semibold text-ink-900">{{ $s[1] }}</div>
                    <a href="#" class="text-[11px] text-brand font-semibold hover:underline">Descargar &rarr;</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
