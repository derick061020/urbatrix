@extends('layouts.admin_crm')
@section('title', 'Dashboard — CRM Duna Makai')
@section('page_title', 'Dashboard')
@section('page_breadcrumb', 'Vista global · todos los proyectos')
@php $activeRoute = 'crm.dashboard'; @endphp

@push('styles')
<style>
    .kpi-card { background:#fff; }
    .kpi-card .kpi-card-top {
        background-image: radial-gradient(circle at top right, color-mix(in srgb, var(--kpi-color) 14%, transparent), transparent 55%);
    }
    @supports not (background: color-mix(in srgb, red, blue)) {
        .kpi-card .kpi-card-top { background-image: radial-gradient(circle at top right, var(--kpi-color), transparent 55%); opacity: 1; }
    }
</style>
@endpush

@section('content')
@php
    $expedientesActivos     = $stats['expedientes_activos']     ?? 0;
    $expedientesIncompletos = $stats['expedientes_incompletos'] ?? 0;
    $docsPendientes         = $stats['docs_pendientes']         ?? 0;
    $docsRechazados         = $stats['docs_rechazados']         ?? 0;
    $aprobCola              = $stats['aprobaciones_cola']       ?? 0;
    $aprobAlta              = $stats['aprobaciones_alta']       ?? 0;
    $tareasVencidas         = $stats['tareas_vencidas']         ?? 0;
    $tareasHoyCount         = $stats['tareas_hoy']              ?? 0;
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-5">

    {{-- Alerta superior --}}
    <div class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-err-soft border border-err/20 text-[12px] text-ink-700">
        <i class="pi pi-exclamation-triangle text-err text-[14px]"></i>
        <span class="leading-tight">
            <span class="font-bold text-ink-950">{{ $aprobCola + $docsPendientes + $tareasVencidas }} alertas activas</span> &mdash;
            {{ $aprobCola }} verificaciones pendientes · {{ $tareasVencidas }} tareas vencidas · {{ $docsPendientes }} documentos sin gestionar. Requieren atención inmediata.
        </span>
        <a href="{{ route('admin.crm.aprobaciones') }}" class="ml-auto text-err font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">Ver todas <i class="pi pi-arrow-right text-[10px]"></i></a>
        <button type="button" class="text-ink-400 hover:text-ink-700 shrink-0" onclick="this.parentElement.style.display='none'"><i class="pi pi-times text-[11px]"></i></button>
    </div>

    {{-- KPI cards con punto en esquina superior derecha --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $kpis = [
                ['n' => $expedientesActivos,'label' => 'Expedientes activos',  'sub' => $expedientesIncompletos.' INCOMPLETOS · 3 SIN ASESOR', 'dot' => '#1fc16b', 'href' => route('admin.crm.expedientes')],
                ['n' => $docsPendientes,    'label' => 'Documentos pendientes','sub' => '3 SIN REVISAR · 2 POR FIRMAR',                       'dot' => '#fa7319', 'href' => route('admin.crm.documentos')],
                ['n' => $aprobCola,         'label' => 'Aprobaciones en cola', 'sub' => '3 KYC · 2 CONTRATOS · 2 BROKERS',                    'dot' => '#fb3748', 'href' => route('admin.crm.aprobaciones')],
                ['n' => $tareasVencidas,    'label' => 'Tareas vencidas',      'sub' => '4 ESCALADAS HOY · SIN RESOLVER',                     'dot' => '#335cff', 'href' => route('admin.crm.tareas')],
            ];
        @endphp
        @foreach($kpis as $k)
            <a href="{{ $k['href'] }}" class="crm-card kpi-card block hover:shadow-card transition-shadow relative overflow-hidden"
               style="--kpi-color: {{ $k['dot'] }};">
                <span class="absolute top-4 right-4 w-2.5 h-2.5 rounded-full z-10" style="background:{{ $k['dot'] }}"></span>
                <div class="kpi-card-top px-4 pt-4 pb-3">
                    <div class="font-display text-[26px] font-bold text-ink-950 leading-none">{{ $k['n'] }}</div>
                    <div class="text-[13px] font-medium text-ink-700 mt-2">{{ $k['label'] }}</div>
                </div>
                <div class="px-4 py-1.5 bg-ink-50 border-t border-ink-100 text-[10px] text-ink-500 tracking-wider font-semibold uppercase truncate">{{ $k['sub'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Proyectos activos + Expedientes recientes --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="crm-card lg:col-span-2 overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="font-display text-[14px] font-semibold text-ink-950">Proyectos activos</h3>
                <a href="{{ route('admin.crm.proyectos') }}" class="text-[11px] text-brand font-semibold hover:underline">Ver todos &rarr;</a>
            </div>
            <table class="w-full">
                <thead class="bg-ink-50/60">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Proyecto</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Estado</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Vendidas</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Avance</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Valor total</th>
                        <th class="px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse(($proyectos ?? collect()) as $p)
                        @php
                            $totalUnits = $p->units()->count();
                            $sold       = $p->units()->where('status', 'SOLD')->count();
                            $reserved   = $p->units()->where('status', 'RESERVED')->count();
                            $pctVentas  = $totalUnits > 0 ? round((($sold + $reserved) / $totalUnits) * 100) : 0;
                            $valor      = $p->units()->sum('price');
                            $isActive   = ($sold + $reserved) > 0 || ($p->progress ?? 0) > 0;
                        @endphp
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-3.5">
                                <div class="text-[13px] font-semibold text-ink-950">{{ $p->name }}</div>
                                <div class="text-[11px] text-ink-500">{{ $totalUnits }} unidades</div>
                            </td>
                            <td class="px-3 py-3.5">
                                @if($isActive)
                                    <span class="crm-pill bg-ok-soft text-ok-dark"><i class="pi pi-check-circle text-[10px]"></i> Activo</span>
                                @else
                                    <span class="crm-pill bg-ink-100 text-ink-500"><i class="pi pi-pause-circle text-[10px]"></i> Inactivo</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-[13px] text-ink-700">{{ $isActive ? ($sold + $reserved) : '—' }}</td>
                            <td class="px-3 py-3.5">
                                @if($isActive)
                                    <div class="flex items-center gap-2 min-w-[140px]">
                                        <div class="crm-progress flex-1"><span class="bg-brand" style="width:{{ $pctVentas }}%"></span></div>
                                        <span class="text-[11px] font-semibold text-ink-700 w-8 text-right">{{ $pctVentas }}%</span>
                                    </div>
                                @else
                                    <span class="text-[13px] text-ink-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-[13px] font-bold text-ok-dark">
                                @if($isActive)
                                    ${{ $valor >= 1_000_000 ? number_format($valor/1_000_000, 2).'M' : number_format($valor, 0) }}
                                @else
                                    <span class="text-ink-400 font-normal">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-right">
                                <a href="{{ route('admin.crm.proyecto.detalle', $p->id) }}" class="text-[12px] text-brand font-semibold hover:underline whitespace-nowrap">Ver &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-[12px] text-ink-500">Sin proyectos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Expedientes recientes con color de estado en texto --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="font-display text-[14px] font-semibold text-ink-950">Expedientes recientes</h3>
                <a href="{{ route('admin.crm.expedientes') }}" class="text-[11px] text-brand font-semibold hover:underline">Ver todos &rarr;</a>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse(($expedientesRecientes ?? collect()) as $r)
                    @php
                        $init = strtoupper(substr($r->first_name ?? 'C', 0, 1) . substr($r->last_name ?? 'M', 0, 1));
                        $totalDocs    = $r->documents->count();
                        $approvedDocs = $r->documents->where('status', 'approved')->count();
                        $rejected     = $r->documents->where('status', 'rejected')->isNotEmpty();
                        if ($rejected)                                        { $estado = 'Pago vencido'; $color = 'text-err'; }
                        elseif ($approvedDocs === $totalDocs && $totalDocs)   { $estado = 'Al día';        $color = 'text-ok-dark'; }
                        elseif ($totalDocs === 0)                             { $estado = 'KYC Pendiente'; $color = 'text-warn'; }
                        else                                                  { $estado = 'En revisión';   $color = 'text-info'; }
                        $bgPalette = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#5c7c68','#d56a6a'];
                        $bg = $bgPalette[$r->id % count($bgPalette)];
                    @endphp
                    <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="px-5 py-3 flex items-center gap-3 hover:bg-ink-50">
                        <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $r->first_name }} {{ $r->last_name }}</div>
                            <div class="text-[11px] text-ink-500 truncate">{{ $r->unit->name ?? $r->unit->custom_id ?? 'Sin unidad' }} · Makai</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[12px] font-semibold {{ $color }}">{{ $estado }}</div>
                            <div class="text-[10px] text-ink-400">{{ $r->created_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-6 text-center text-[12px] text-ink-500">Sin expedientes recientes.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Verificaciones, Tareas, Actividad reciente --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Verificaciones pendientes con botones --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="font-display text-[14px] font-semibold text-ink-950">Verificaciones pendientes</h3>
                <a href="{{ route('admin.crm.aprobaciones') }}" class="text-[11px] text-brand font-semibold hover:underline flex items-center gap-1">
                    <span class="crm-pill bg-err-soft text-err">{{ $aprobCola }}</span>
                    <span>Ver todos &rarr;</span>
                </a>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse(($aprobacionesUrgentes ?? collect()) as $a)
                    @php
                        $colors = ['descuento' => 'info','comision' => 'ok','contrato' => 'warn','pagos' => 'err','kyc' => 'warn', 'promesa' => 'info'];
                        $typeLabel = strtoupper($a->type ?? 'GENERAL');
                        $color = $colors[strtolower($a->type)] ?? 'warn';
                    @endphp
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-[13px] font-semibold text-ink-950">{{ $a->requested_by ?? 'Solicitud' }}</div>
                            <span class="crm-pill bg-{{ $color }}-soft text-{{ $color }}">{{ $typeLabel }}</span>
                        </div>
                        <div class="text-[12px] text-ink-500 mb-2.5">{{ $a->amount_or_condition ?? $a->notes ?? 'Sin descripción' }}</div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] text-ink-400">{{ $a->created_at?->diffForHumans() }} · Sistema</span>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.crm.aprobaciones.decide', $a->id) }}" class="inline m-0">
                                    @csrf
                                    <button type="submit" name="decision" value="aprobada" class="text-[11px] font-semibold text-ok-dark hover:bg-ok-soft px-2 py-1 rounded inline-flex items-center gap-1"><i class="pi pi-check text-[10px]"></i> Verificar</button>
                                </form>
                                <form method="POST" action="{{ route('admin.crm.aprobaciones.decide', $a->id) }}" class="inline m-0">
                                    @csrf
                                    <button type="submit" name="decision" value="rechazada" class="text-[11px] font-semibold text-err hover:bg-err-soft px-2 py-1 rounded inline-flex items-center gap-1"><i class="pi pi-times text-[10px]"></i> Rechazar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-[12px] text-ink-500">Sin aprobaciones urgentes.</div>
                @endforelse
            </div>
        </div>

        {{-- Tareas del día con bullets de color --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="font-display text-[14px] font-semibold text-ink-950">Tareas del día</h3>
                <a href="{{ route('admin.crm.tareas') }}" class="text-[11px] text-brand font-semibold hover:underline flex items-center gap-1">
                    <span class="crm-pill bg-err-soft text-err">{{ $tareasVencidas }}</span>
                    <span>Ver todos &rarr;</span>
                </a>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse(($tareasHoy ?? collect()) as $t)
                    @php
                        $prioDot = ['alta' => 'bg-err','media' => 'bg-warn','baja' => 'bg-info'][$t->priority ?? 'media'] ?? 'bg-ink-400';
                        $isDone = $t->status === 'completada';
                    @endphp
                    <form method="POST" action="{{ route('admin.crm.tareas.complete', $t->id) }}" class="px-5 py-2.5 flex items-center gap-3 m-0">
                        @csrf
                        <input type="checkbox" class="w-4 h-4 accent-brand" {{ $isDone ? 'checked' : '' }} onclick="this.form.submit()">
                        <span class="dot {{ $prioDot }}"></span>
                        <div class="flex-1 text-[12px] {{ $isDone ? 'line-through text-ink-400' : 'text-ink-700' }}">{{ $t->title }}</div>
                        <div class="text-[10px] text-ink-400 whitespace-nowrap">{{ $isDone ? 'Completado' : ($t->due_label ?? optional($t->due_date)->format('d/m')) }}</div>
                    </form>
                @empty
                    <div class="px-5 py-6 text-center text-[12px] text-ink-500">No hay tareas para hoy.</div>
                @endforelse
            </div>
        </div>

        {{-- Actividad reciente --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="font-display text-[14px] font-semibold text-ink-950">Actividad reciente</h3>
            </div>
            <div class="divide-y divide-ink-100">
                @php
                    $recent = \App\Models\Document::with('reservation')->orderBy('updated_at', 'desc')->take(7)->get();
                @endphp
                @forelse($recent as $a)
                    @php
                        $statusMap = ['approved' => 'bg-ok','generated' => 'bg-info','signed' => 'bg-ok','pending' => 'bg-warn','rejected' => 'bg-err'];
                        $dot = $statusMap[$a->status] ?? 'bg-ink-400';
                        $who = $a->reservation ? ($a->reservation->first_name.' '.$a->reservation->last_name) : 'Sistema';
                        $verb = $a->status === 'approved' ? 'aprobó' : ($a->status === 'rejected' ? 'rechazó' : 'subió');
                    @endphp
                    <div class="px-5 py-2.5 flex items-start gap-3">
                        <span class="dot {{ $dot }} mt-1.5 shrink-0"></span>
                        <div class="flex-1 min-w-0">
                            <div class="text-[12px] text-ink-700"><span class="font-semibold text-ink-950">{{ $who }}</span> {{ $verb }} {{ $a->title }}</div>
                            <div class="text-[10px] text-ink-400 mt-0.5">{{ $a->updated_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-[12px] text-ink-500">Sin actividad reciente.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
