@extends('layouts.admin_crm')
@section('title', __('Dashboard — CRM Duna Makai'))
@section('page_title', __('Escritorio'))
@section('page_breadcrumb', __('Vista global · todos los proyectos'))
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

    /* ── Bandeja de trabajo ── */
    .esc-ty { font-size:9.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; padding:3px 8px; border-radius:6px; white-space:nowrap; flex:none; width:74px; text-align:center; }
    .esc-age { font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:999px; white-space:nowrap; flex:none; }
    .esc-age.hot  { color:#fb3748; background:#ffebec; }
    .esc-age.warn { color:#fa7319; background:#fff3eb; }
    .esc-age.ok   { color:#717784; background:#f2f5f8; }
    .esc-item:hover { background:#fafbfc; }
    .esc-tab { font-size:11.5px; font-weight:600; color:#525866; background:none; border:0; border-radius:8px; padding:6px 10px; cursor:pointer; }
    .esc-tab.on { background:#eef2ef; color:#4a6354; }
    .esc-tab .n { color:#99a0ae; }
    .esc-tab.on .n { color:#4a6354; }

    .esc-batch { display:none; }
    .esc-batch.show { display:flex; }

    /* ── Drawer ── */
    .esc-overlay { position:fixed; inset:0; background:rgba(16,22,35,.35); opacity:0; pointer-events:none; transition:opacity .2s; z-index:60; }
    .esc-overlay.show { opacity:1; pointer-events:auto; }
    .esc-drawer { position:fixed; top:0; right:0; height:100vh; width:400px; max-width:92vw; background:#fff; border-left:1px solid #eaecf0; box-shadow:-20px 0 50px -30px rgba(16,32,61,.5); transform:translateX(100%); transition:transform .25s; z-index:61; display:flex; flex-direction:column; }
    .esc-drawer.show { transform:translateX(0); }
    .esc-doc-prev { border:1px solid #eaecf0; border-radius:11px; height:150px; background:repeating-linear-gradient(45deg,#fafbfa,#fafbfa 12px,#f3f5f2 12px,#f3f5f2 24px); display:grid; place-items:center; color:#99a0ae; font-size:12px; }
</style>
@endpush

@section('content')
@php
    $expedientesActivos     = $stats['expedientes_activos']     ?? 0;
    $expedientesIncompletos = $stats['expedientes_incompletos'] ?? 0;
    $docsPendientes         = $stats['docs_pendientes']         ?? 0;
    $docsRevisar            = $stats['docs_revisar']            ?? 0;
    $docsFirmar             = $stats['docs_firmar']             ?? 0;
    $aprobCola              = $stats['aprobaciones_cola']       ?? 0;
    $aprobKyc               = $stats['aprob_kyc']               ?? 0;
    $aprobContrato          = $stats['aprob_contrato']          ?? 0;
    $aprobBroker            = $stats['aprob_broker']            ?? 0;
    $tareasVencidas         = $stats['tareas_vencidas']         ?? 0;
    $tareasHoyCount         = $stats['tareas_hoy']              ?? 0;
    $sinAsesorCount         = $stats['sin_asesor']              ?? 0;
    $perfilesPendientes     = $stats['perfiles_pendientes']     ?? 0;

    // Color por tipo de ítem de la bandeja
    $pillStyle = [
        'kyc'       => 'background:#eef0fb;color:#5b61c9;',
        'documento' => 'background:#eef3fb;color:#3b6fb5;',
        'contrato'  => 'background:#e3f7ec;color:#1daf61;',
        'broker'    => 'background:#fcf1e6;color:#d98a3b;',
        'tarea'     => 'background:#eef2ef;color:#4a6354;',
        'noadv'     => 'background:#ffebec;color:#fb3748;',
    ];
    $pillShort = ['kyc' => 'KYC', 'documento' => __('Doc.'), 'contrato' => __('Contrato'), 'broker' => 'Broker', 'tarea' => __('Tarea'), 'noadv' => 'S/Asesor'];

    $bandeja      = $bandeja      ?? collect();
    $bandejaTotal = $bandejaTotal ?? $bandeja->count();
    $urgentes     = $bandeja->filter(fn ($i) => $i->date && $i->date->diffInHours(now()) >= 48)->count();

    $cTodos    = $bandeja->count();
    $cDoc      = $bandeja->where('cat', 'documento')->count();
    $cKyc      = $bandeja->where('cat', 'kyc')->count();
    $cContrato = $bandeja->where('cat', 'contrato')->count();
    $cNoadv    = $bandeja->where('cat', 'noadv')->count();
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-5">

    {{-- Banner honesto: solo lo realmente urgente --}}
    @if($urgentes > 0)
    <div class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-warn-soft border border-warn/20 text-[12px] text-ink-700">
        <i class="pi pi-exclamation-triangle text-warn text-[14px]"></i>
        <span class="leading-tight">
            <span class="font-bold text-ink-950">{{ trans_choice('{1}:n pendiente urgente|[2,*]:n pendientes urgentes', $urgentes, ['n' => $urgentes]) }}</span>
            {{ __('llevan más de 48 h esperando en la bandeja.') }}
        </span>
        <a href="#esc-bandeja" class="ml-auto text-warn font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">{{ __('Ver en la bandeja') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
        <button type="button" class="text-ink-400 hover:text-ink-700 shrink-0" onclick="this.parentElement.style.display='none'"><i class="pi pi-times text-[11px]"></i></button>
    </div>
    @endif

    @if($perfilesPendientes > 0)
    <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-info-soft border border-info/20 text-[13px] text-ink-700">
        <i class="pi pi-user-check text-info text-[16px]"></i>
        <span class="leading-tight">
            <span class="font-bold text-ink-950">{{ $perfilesPendientes }} {{ $perfilesPendientes === 1 ? __('perfil pendiente de verificación') : __('perfiles pendientes de verificación') }}</span> &mdash;
            {{ __('Revisa los documentos KYC y aprueba o rechaza el perfil para que pueda continuar.') }}
        </span>
        <a href="{{ route('admin.crm.aprobaciones') }}" class="ml-auto text-info font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">{{ __('Ir a verificaciones') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
        <button type="button" class="text-ink-400 hover:text-ink-700 shrink-0" onclick="this.parentElement.style.display='none'"><i class="pi pi-times text-[11px]"></i></button>
    </div>
    @endif

    {{-- KPI cards (cada número = suma de su desglose) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $kpis = [
                ['n' => $expedientesActivos, 'label' => __('Expedientes activos'),   'sub' => __(':a incompletos · :b sin asesor', ['a' => $expedientesIncompletos, 'b' => $sinAsesorCount]), 'dot' => '#1fc16b', 'href' => route('admin.crm.expedientes')],
                ['n' => $docsPendientes,     'label' => __('Documentos pendientes'),  'sub' => __(':a por revisar · :b por firmar', ['a' => $docsRevisar, 'b' => $docsFirmar]),                  'dot' => '#fa7319', 'href' => route('admin.crm.documentos')],
                ['n' => $aprobCola,          'label' => __('Aprobaciones en cola'),   'sub' => __(':a KYC · :b contratos · :c brokers', ['a' => $aprobKyc, 'b' => $aprobContrato, 'c' => $aprobBroker]), 'dot' => '#fb3748', 'href' => route('admin.crm.aprobaciones')],
                ['n' => $tareasVencidas,     'label' => __('Tareas vencidas'),        'sub' => $tareasVencidas === 0 ? __('✓ Todo al día') : __(':a por resolver', ['a' => $tareasVencidas]),         'dot' => '#335cff', 'href' => route('admin.crm.tareas'), 'ok' => $tareasVencidas === 0],
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
                <div class="px-4 py-1.5 bg-ink-50 border-t border-ink-100 text-[10px] tracking-wider font-semibold uppercase truncate {{ ($k['ok'] ?? false) ? 'text-ok-dark' : 'text-ink-500' }}">{{ $k['sub'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Layout 2 columnas: bandeja + proyectos (principal) | riesgo + vencimientos + actividad (lateral) --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">

        {{-- ===== COLUMNA PRINCIPAL ===== --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- BANDEJA DE TRABAJO --}}
            <div id="esc-bandeja" class="crm-card overflow-hidden">
                <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                    <h3 class="font-display text-[14px] font-semibold text-ink-950">
                        {{ __('Bandeja de trabajo') }}
                        <span class="text-ink-400 font-medium">· {{ trans_choice('{1}:n requiere tu acción|[2,*]:n requieren tu acción', $bandejaTotal, ['n' => $bandejaTotal]) }}</span>
                    </h3>
                    <a href="{{ route('admin.crm.expedientes') }}" class="text-[11px] text-brand font-semibold hover:underline">{{ __('Ver todo') }} &rarr;</a>
                </div>

                {{-- Tabs --}}
                <div class="flex gap-1.5 px-4 pt-3 flex-wrap" id="esc-tabs">
                    <button class="esc-tab on" data-filter="todos">{{ __('Todos') }} <span class="n">{{ $cTodos }}</span></button>
                    <button class="esc-tab" data-filter="documento">{{ __('Documentos') }} <span class="n">{{ $cDoc }}</span></button>
                    <button class="esc-tab" data-filter="kyc">KYC <span class="n">{{ $cKyc }}</span></button>
                    <button class="esc-tab" data-filter="contrato">{{ __('Contratos') }} <span class="n">{{ $cContrato }}</span></button>
                    <button class="esc-tab" data-filter="noadv">{{ __('Sin asesor') }} <span class="n">{{ $cNoadv }}</span></button>
                </div>

                {{-- Barra de selección en lote --}}
                <div class="esc-batch items-center gap-3 mx-4 mt-3 px-3.5 py-2.5 rounded-lg bg-ink-950 text-white" id="esc-batch">
                    <span class="text-[12.5px] font-bold"><span id="esc-bcount">0</span> {{ __('seleccionados') }}</span>
                    <span class="flex-1"></span>
                    <button class="text-[11.5px] font-bold rounded-lg px-3 py-1.5 bg-white/15" type="button">{{ __('Posponer') }}</button>
                </div>

                <div id="esc-inbox" class="py-1.5 overflow-x-auto">
                    @forelse($bandeja as $i)
                        @php
                            $hrs       = $i->date ? $i->date->diffInHours(now()) : 0;
                            $ageClass  = $hrs >= 48 ? 'hot' : ($hrs >= 12 ? 'warn' : 'ok');
                            $ageLabel  = $i->date ? $i->date->diffForHumans(['short' => true, 'parts' => 1]) : '—';
                        @endphp
                        <div class="esc-item flex items-center gap-3 px-5 py-2.5 border-b border-ink-100 last:border-b-0 cursor-pointer min-w-[560px]"
                             data-cat="{{ $i->cat }}"
                             data-ty="{{ $i->ty }}"
                             data-title="{{ $i->title }}"
                             data-sub="{{ $i->sub }}"
                             data-age="{{ $ageLabel }}"
                             data-url="{{ $i->url }}"
                             data-action="{{ $i->action }}">
                            <input type="checkbox" class="esc-chk w-4 h-4 accent-brand shrink-0" onclick="event.stopPropagation()">
                            <span class="esc-ty" style="{{ $pillStyle[$i->cat] ?? $pillStyle['documento'] }}">{{ $pillShort[$i->cat] ?? $i->ty }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] font-semibold text-ink-950 truncate">{{ $i->title }}</div>
                                <div class="text-[11px] text-ink-500 truncate">{{ $i->sub }}</div>
                            </div>
                            <span class="esc-age {{ $ageClass }}">{{ $ageLabel }}</span>
                            <div class="shrink-0">
                                <a href="{{ $i->url }}" onclick="event.stopPropagation()" class="crm-btn crm-btn-primary !py-1.5 !px-3 !text-[11.5px]">{{ $i->action }}</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-[12px] text-ink-500">
                            <i class="pi pi-check-circle text-ok text-[20px] block mb-2"></i>
                            {{ __('Bandeja vacía. No hay nada pendiente de tu acción.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- PROYECTOS (% Vendido y Avance obra separados) --}}
            <div class="crm-card overflow-hidden">
                <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                    <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('Proyectos activos') }}</h3>
                    <a href="{{ route('admin.crm.proyectos') }}" class="text-[11px] text-brand font-semibold hover:underline">{{ __('Ver todos') }} &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full min-w-[680px]">
                    <thead class="bg-ink-50/60">
                        <tr>
                            <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Proyecto') }}</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Estado') }}</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Vendidas') }}</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('% Vendido') }}</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Avance obra') }}</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Valor total') }}</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse(($proyectos ?? collect()) as $p)
                            @php
                                $totalUnits = $p->units()->count();
                                $sold       = $p->units()->where('status', 'SOLD')->count();
                                $reserved   = $p->units()->where('status', 'RESERVED')->count();
                                $vendidas   = $sold + $reserved;
                                $pctVentas  = $totalUnits > 0 ? round(($vendidas / $totalUnits) * 100) : 0;
                                $obra       = (int) ($p->progress ?? 0);
                                $valor      = $p->units()->sum('price');
                                $isActive   = $vendidas > 0 || $obra > 0;
                            @endphp
                            <tr class="hover:bg-ink-50">
                                <td class="px-5 py-3.5">
                                    <div class="text-[13px] font-semibold text-ink-950">{{ $p->name }}</div>
                                    <div class="text-[11px] text-ink-500">{{ $totalUnits }} {{ __('unidades') }}</div>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($isActive)
                                        <span class="crm-pill bg-ok-soft text-ok-dark"><i class="pi pi-check-circle text-[10px]"></i> {{ __('Activo') }}</span>
                                    @else
                                        <span class="crm-pill bg-ink-100 text-ink-500"><i class="pi pi-pause-circle text-[10px]"></i> {{ __('Inactivo') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[13px] font-semibold text-ink-900">{{ $isActive ? $vendidas.'/'.$totalUnits : '—' }}</td>
                                <td class="px-3 py-3.5">
                                    @if($isActive)
                                        <div class="flex items-center gap-2 min-w-[120px]">
                                            <div class="crm-progress flex-1"><span class="bg-ok" style="width:{{ $pctVentas }}%"></span></div>
                                            <span class="text-[11px] font-semibold text-ink-700 w-8 text-right">{{ $pctVentas }}%</span>
                                        </div>
                                    @else
                                        <span class="text-[13px] text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($obra > 0)
                                        <div class="flex items-center gap-2 min-w-[120px]">
                                            <div class="crm-progress flex-1"><span class="bg-brand" style="width:{{ $obra }}%"></span></div>
                                            <span class="text-[11px] font-semibold text-ink-700 w-8 text-right">{{ $obra }}%</span>
                                        </div>
                                    @else
                                        <span class="text-[13px] text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[13px] font-bold text-ok-dark">
                                    @if($isActive)
                                        ${{ $valor >= 1000000 ? number_format($valor / 1000000, 2).'M' : number_format($valor, 0) }}
                                    @else
                                        <span class="text-ink-400 font-normal">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <a href="{{ route('admin.crm.proyecto.detalle', $p->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-ink-500 hover:text-brand hover:bg-brand-tint transition-colors" title="{{ __('Ver') }}" aria-label="{{ __('Ver') }}"><i class="pi pi-eye text-[14px]"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-6 text-center text-[12px] text-ink-500">{{ __('Sin proyectos.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="px-5 py-2.5 border-t border-ink-100 text-[10.5px] text-ink-400 flex items-center gap-2">
                    <span class="dot bg-warn"></span> {{ __('“Avance obra” se sincroniza desde el módulo Avance de obra.') }}
                </div>
            </div>

            {{-- SIN ASESOR + CARGA POR ASESOR --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="crm-card overflow-hidden">
                    <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                        <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('Sin asesor') }} <span class="text-ink-400 font-medium">· {{ $sinAsesorCount }}</span></h3>
                    </div>
                    <div class="divide-y divide-ink-100">
                        @forelse(($sinAsesor ?? collect()) as $r)
                            @php $init = strtoupper(substr($r->first_name ?? 'L', 0, 1).substr($r->last_name ?? '', 0, 1)); @endphp
                            <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="px-5 py-2.5 flex items-center gap-3 hover:bg-ink-50">
                                <div class="crm-avatar crm-avatar-sm bg-ink-100 !text-ink-500">{{ $init }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[12.5px] font-semibold text-ink-950 truncate">{{ trim(($r->first_name ?? '').' '.($r->last_name ?? '')) ?: __('Lead') }} · {{ optional($r->unit)->name ?? __('Sin unidad') }}</div>
                                    <div class="text-[10.5px] text-ink-400">{{ $r->created_at?->diffForHumans() }} · {{ __('sin asignar') }}</div>
                                </div>
                                <span class="text-[11.5px] font-semibold text-brand bg-brand-tint px-2.5 py-1.5 rounded-lg whitespace-nowrap">{{ __('Asignar') }} &rarr;</span>
                            </a>
                        @empty
                            <div class="px-5 py-6 text-center text-[12px] text-ink-500">{{ __('Todos los expedientes tienen asesor.') }}</div>
                        @endforelse
                    </div>
                </div>

                <div class="crm-card overflow-hidden">
                    <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                        <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('Carga por asesor') }}</h3>
                    </div>
                    <div class="px-5 py-3 space-y-2.5">
                        @forelse(($cargaAsesores ?? collect()) as $c)
                            <div class="flex items-center gap-3">
                                <span class="text-[12.5px] font-semibold text-ink-700 w-24 truncate shrink-0">{{ $c->name }}</span>
                                <div class="crm-progress flex-1">
                                    <span class="{{ ($c->count / $maxCarga) >= 0.85 ? 'bg-warn' : 'bg-brand' }}" style="width:{{ $maxCarga > 0 ? round(($c->count / $maxCarga) * 100) : 0 }}%"></span>
                                </div>
                                <span class="text-[12px] font-bold text-ink-500 w-14 text-right shrink-0">{{ $c->count }} exp.</span>
                            </div>
                        @empty
                            <div class="py-3 text-center text-[12px] text-ink-500">{{ __('Sin asesores activos.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== COLUMNA LATERAL ===== --}}
        <div class="space-y-5">

            {{-- EN RIESGO --}}
            <div class="crm-card overflow-hidden">
                <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                    <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('En riesgo') }}</h3>
                    <a href="{{ route('admin.crm.expedientes') }}" class="text-[11px] text-brand font-semibold hover:underline">{{ __('Ver todo') }} &rarr;</a>
                </div>
                <div class="divide-y divide-ink-100">
                    @forelse(($riesgo ?? collect()) as $r)
                        @php
                            $rb = ['r1' => 'bg-err-soft text-err', 'r2' => 'bg-warn-soft text-warn', 'r3' => 'bg-info-soft text-info'][$r->level] ?? 'bg-ink-100 text-ink-500';
                        @endphp
                        <a href="{{ $r->url }}" class="px-5 py-3 flex gap-3 items-start hover:bg-ink-50">
                            <span class="w-8 h-8 rounded-lg grid place-items-center shrink-0 {{ $rb }}"><i class="pi {{ $r->icon }} text-[14px]"></i></span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[12.5px] font-semibold text-ink-950 leading-snug">{{ $r->title }}</div>
                                <div class="text-[11px] text-ink-500 mt-0.5">{{ $r->sub }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-6 text-center text-[12px] text-ink-500">{{ __('Nada en riesgo ahora mismo.') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- PRÓXIMOS VENCIMIENTOS --}}
            <div class="crm-card overflow-hidden">
                <div class="px-5 py-3 border-b border-ink-100">
                    <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('Próximos vencimientos') }}</h3>
                </div>
                <div class="px-5 py-2">
                    @forelse(($vencimientos ?? collect()) as $p)
                        @php $r = $p->reservation; @endphp
                        <div class="flex items-center gap-3 py-2 border-b border-dashed border-ink-100 last:border-b-0 text-[12px]">
                            <span class="font-bold text-ink-950 w-12 shrink-0">{{ optional($p->due_date)->format('d M') }}</span>
                            <span class="flex-1 text-ink-500 truncate">{{ $p->label ?? __('Cuota') }} · {{ $r ? trim(($r->first_name ?? '').' '.($r->last_name ?? '')) : '—' }}</span>
                            <span class="font-bold text-ink-900 shrink-0">${{ number_format((float) $p->amount, 0) }}</span>
                        </div>
                    @empty
                        <div class="py-5 text-center text-[12px] text-ink-500">{{ __('Sin vencimientos próximos.') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- ACTIVIDAD RECIENTE (con etapa) --}}
            <div class="crm-card overflow-hidden">
                <div class="px-5 py-3 border-b border-ink-100">
                    <h3 class="font-display text-[14px] font-semibold text-ink-950">{{ __('Actividad reciente') }}</h3>
                </div>
                <div class="divide-y divide-ink-100">
                    @php
                        $recent = \App\Models\Document::with('reservation')->orderBy('updated_at', 'desc')->take(7)->get();
                        $stageMap = ['kyc' => __('KYC'), 'payment_plan' => __('Pagos'), 'purchase_promise' => __('Contrato'), 'promise' => __('Contrato'), 'contract' => __('Contrato')];
                    @endphp
                    @forelse($recent as $a)
                        @php
                            $statusMap = ['approved' => 'bg-ok','generated' => 'bg-info','signed' => 'bg-ok','pending' => 'bg-warn','rejected' => 'bg-err'];
                            $dot   = $statusMap[$a->status] ?? 'bg-ink-400';
                            $who   = $a->reservation ? trim(($a->reservation->first_name ?? '').' '.($a->reservation->last_name ?? '')) : __('Sistema');
                            $verb  = $a->status === 'approved' ? __('aprobó') : ($a->status === 'rejected' ? __('rechazó') : __('subió'));
                            $stage = $stageMap[$a->document_type] ?? __('Documento');
                        @endphp
                        <div class="px-5 py-2.5 flex items-start gap-3">
                            <span class="dot {{ $dot }} mt-1.5 shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[12px] text-ink-700"><span class="font-semibold text-ink-950">{{ $who }}</span> {{ $verb }} {{ $a->title }}</div>
                                <div class="text-[10.5px] text-ink-400 mt-1 flex items-center gap-2">
                                    <span class="crm-pill bg-brand-tint text-brand-dark !text-[9px] !py-0.5">{{ $stage }}</span>
                                    {{ $a->updated_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-[12px] text-ink-500">{{ __('Sin actividad reciente.') }}</div>
                    @endforelse
                    <div class="px-5 py-2.5 text-[10px] text-ink-400">{{ __('El KYC lo aprueba un asesor/admin — nunca el propio cliente (queda en auditoría).') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== DRAWER (acción en línea) ===== --}}
<div class="esc-overlay" id="esc-overlay"></div>
<aside class="esc-drawer" id="esc-drawer">
    <div class="flex items-center gap-2.5 px-4 py-4 border-b border-ink-100">
        <span class="esc-ty" id="esc-dr-ty" style="background:#eef0fb;color:#5b61c9;">—</span>
        <span class="text-[12px] text-ink-400">{{ __('Resolver sin salir del Escritorio') }}</span>
        <button type="button" id="esc-dr-close" class="ml-auto topbar-icon-btn !w-8 !h-8"><i class="pi pi-times text-[12px]"></i></button>
    </div>
    <div class="px-5 py-4 overflow-y-auto flex-1">
        <div class="text-[16px] font-semibold text-ink-950" id="esc-dr-title">—</div>
        <div class="text-[12.5px] text-ink-500 mt-1" id="esc-dr-sub">—</div>
        <div class="mt-4 space-y-0">
            <div class="flex justify-between py-2 text-[13px]"><span class="text-ink-500">{{ __('Antigüedad') }}</span><span class="font-semibold text-ink-950" id="esc-dr-age">—</span></div>
        </div>
        <div class="esc-doc-prev mt-3">{{ __('Vista previa del documento / expediente') }}</div>
    </div>
    <div class="px-4 py-3.5 border-t border-ink-100 flex gap-2.5">
        <a href="#" id="esc-dr-open" class="crm-btn crm-btn-primary flex-1 justify-center">{{ __('Abrir') }}</a>
    </div>
</aside>
@endsection

@push('scripts')
<script>
(function () {
    const inbox = document.getElementById('esc-inbox');
    if (!inbox) return;

    // ── Tabs (filtra por categoría) ──
    document.querySelectorAll('#esc-tabs .esc-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('#esc-tabs .esc-tab').forEach(t => t.classList.remove('on'));
            tab.classList.add('on');
            const f = tab.dataset.filter;
            inbox.querySelectorAll('.esc-item').forEach(function (it) {
                it.style.display = (f === 'todos' || it.dataset.cat === f) ? '' : 'none';
            });
        });
    });

    // ── Selección en lote ──
    const batch = document.getElementById('esc-batch');
    const bcount = document.getElementById('esc-bcount');
    function refreshBatch() {
        const n = inbox.querySelectorAll('.esc-chk:checked').length;
        bcount.textContent = n;
        batch.classList.toggle('show', n > 0);
    }
    inbox.addEventListener('change', function (e) {
        if (e.target.classList.contains('esc-chk')) refreshBatch();
    });

    // ── Drawer (acción en línea) ──
    const overlay = document.getElementById('esc-overlay');
    const drawer  = document.getElementById('esc-drawer');
    function openDrawer(it) {
        document.getElementById('esc-dr-ty').textContent    = it.dataset.ty || '';
        document.getElementById('esc-dr-title').textContent = it.dataset.title || '';
        document.getElementById('esc-dr-sub').textContent   = it.dataset.sub || '';
        document.getElementById('esc-dr-age').textContent   = it.dataset.age || '';
        const open = document.getElementById('esc-dr-open');
        open.setAttribute('href', it.dataset.url || '#');
        open.textContent = it.dataset.action || 'Abrir';
        overlay.classList.add('show');
        drawer.classList.add('show');
    }
    function closeDrawer() {
        overlay.classList.remove('show');
        drawer.classList.remove('show');
    }
    inbox.addEventListener('click', function (e) {
        if (e.target.closest('a') || e.target.classList.contains('esc-chk')) return;
        const it = e.target.closest('.esc-item');
        if (it) openDrawer(it);
    });
    document.getElementById('esc-dr-close').addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
})();
</script>
@endpush
