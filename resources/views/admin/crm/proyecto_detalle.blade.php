@extends('layouts.admin_crm')
@section('title', $proyecto->name . ' — CRM Duna Makai')
@section('page_title', 'Ficha de Proyecto')
@section('page_breadcrumb', 'Proyectos · Ficha completa')
@php $activeRoute = 'crm.proyectos'; @endphp

@section('content')
@php
    $color      = $proyecto->color ?? '#5c7c68';
    $totalUnits = (int) $proyecto->units_count;
    $sold       = (int) $proyecto->sold_count;
    $reserved   = (int) $proyecto->reserved_count;
    $available  = max(0, $totalUnits - $sold - $reserved);

    $valorTotal = (float) $proyecto->units()->sum('price');
    $vendidoUSD = (float) $proyecto->units()->whereIn('status', ['SOLD','sold','RESERVED','reserved'])->sum('price');
    $vendidoSoloUSD = (float) $proyecto->units()->whereIn('status', ['SOLD','sold'])->sum('price');
    $disponibleUSD  = (float) $proyecto->units()->whereIn('status', ['AVAILABLE','available'])->sum('price');
    $reservadoUSD   = (float) $proyecto->units()->whereIn('status', ['RESERVED','reserved'])->sum('price');
    $minPrice   = (float) $proyecto->units()->min('price');
    $avgPrice   = $totalUnits > 0 ? round($valorTotal / $totalUnits) : 0;

    $pctVentasNum = $totalUnits > 0 ? (($sold + $reserved) / $totalUnits) * 100 : 0;
    $pctSold      = $totalUnits > 0 ? ($sold / $totalUnits) * 100 : 0;
    $pctReserved  = $totalUnits > 0 ? ($reserved / $totalUnits) * 100 : 0;
    $pctAvailable = max(0, 100 - $pctSold - $pctReserved);
    $pctObra      = (int) ($proyecto->progress ?? 0);

    // Investment estimates (project-level; fall back to project averages if unit-level missing)
    $avgRoi = (float) $proyecto->units()->avg('roi_percent') ?: 15.0;
    $avgRentNet = $vendidoUSD > 0 ? round(($vendidoSoloUSD * ($avgRoi / 100)) / 12) : 0;
    $launchDiscount = (float) ($proyecto->launch_discount ?? 20000);
    $estDeliveryYear = $proyecto->estimated_delivery ?? 'Q4 2026';
    $entregaFmt = is_string($estDeliveryYear) ? $estDeliveryYear : \Carbon\Carbon::parse($estDeliveryYear)->format('M Y');
    $payback = $avgRoi > 0 ? round(100 / $avgRoi, 1) : null;

    // Inventory by typology (used twice in layout)
    $tipos = $units->groupBy(fn($u) => $u->layout ?: $u->type ?: 'Sin tipo')->map(function ($group) use ($totalUnits) {
        $disp  = $group->whereIn('status', ['AVAILABLE','available'])->count();
        $sold  = $group->whereIn('status', ['SOLD','sold'])->count();
        $resv  = $group->whereIn('status', ['RESERVED','reserved'])->count();
        $count = $group->count();
        return [
            'count' => $count,
            'disp'  => $disp,
            'resv'  => $resv,
            'sold'  => $sold,
            'min'   => $group->min('price'),
            'max'   => $group->max('price'),
            'avg'   => $count > 0 ? round($group->avg('price')) : 0,
            'minM2' => $group->min('internal_area'),
            'maxM2' => $group->max('internal_area'),
            'yield' => round($group->avg('roi_percent') ?? 14, 1),
            'sold_pct' => $count > 0 ? round(($sold / $count) * 100) : 0,
        ];
    });

    // Construction phases (use project fields if you have, else generic)
    $phases = [
        ['Progreso de ventas', 'completed', 'Jun '.now()->subYear()->format('Y')],
        ['Estructura',         $pctObra >= 50 ? 'completed' : ($pctObra >= 25 ? 'active' : 'pending'), 'Dic '.now()->subYear()->format('Y')],
        ['Mampostería',        $pctObra >= 35 ? 'active' : 'pending', 'En curso'],
        ['Instalaciones',      $pctObra >= 45 ? 'active' : 'pending', 'En curso'],
        ['Acabados',           $pctObra >= 70 ? 'active' : 'pending', 'Q3 '.now()->addYear()->format('Y')],
        ['Entrega',            'pending', $entregaFmt],
    ];

    $reservasActivas = \App\Models\Reservation::whereHas('unit', fn($q) => $q->where('project_id', $proyecto->id))->count();
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    {{-- ============ HERO ============ --}}
    <div class="crm-card overflow-hidden">
        <div class="p-6 text-white relative" style="background:linear-gradient(135deg,{{ $color }} 0%, color-mix(in srgb, {{ $color }} 70%, black) 100%)">
            {{-- Decorative concentric circles top-right --}}
            <div class="absolute -top-20 -right-32 w-[440px] h-[440px] pointer-events-none opacity-20" style="background:
                radial-gradient(circle at center, transparent 47%, rgba(255,255,255,.5) 47.5%, rgba(255,255,255,.5) 48.5%, transparent 49%),
                radial-gradient(circle at center, transparent 35%, rgba(255,255,255,.45) 35.5%, rgba(255,255,255,.45) 36.5%, transparent 37%),
                radial-gradient(circle at center, transparent 22%, rgba(255,255,255,.4) 22.5%, rgba(255,255,255,.4) 23.5%, transparent 24%),
                radial-gradient(circle at center, transparent 9%, rgba(255,255,255,.35) 9.5%, rgba(255,255,255,.35) 11%, transparent 11.5%);"></div>

            <div class="relative z-10 flex items-start gap-4">
                <a href="{{ route('admin.crm.proyectos') }}" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur flex items-center justify-center hover:bg-white/25"><i class="pi pi-arrow-left text-[12px]"></i></a>
                <div class="flex-1">
                    <div class="text-[10px] uppercase tracking-[0.18em] opacity-80 font-semibold">{{ $proyecto->developer ?? 'Duna Development Group' }}</div>
                    <div class="font-display text-[28px] sm:text-[34px] font-semibold leading-tight mt-1">{{ $proyecto->name }}</div>
                    <div class="text-[12px] opacity-85 mt-1 flex items-center gap-1.5"><i class="pi pi-map-marker text-[11px] text-err"></i> {{ $proyecto->location ?? 'Cap Cana · Punta Cana' }}</div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] opacity-80">Actualizado: <span class="font-semibold">{{ now()->locale('es')->isoFormat('D MMM YYYY') }}</span></div>
                    <span class="crm-pill bg-ok-soft text-ok-dark mt-2 inline-flex"><span class="dot bg-ok"></span> FASE ACTIVA</span>
                </div>
            </div>

            {{-- Chip stats --}}
            <div class="relative z-10 mt-6 inline-flex items-stretch rounded-2xl bg-white/10 backdrop-blur border border-white/15 overflow-hidden flex-wrap">
                @php $chips = [
                    ['Unidad',       '$'.number_format($minPrice ?: $avgPrice, 0).' USD'],
                    ['Entrega est.', $entregaFmt],
                    ['Unidades',     $totalUnits.' totales'],
                    ['Disponibles',  $available.' unidades'],
                    ['Estado legal', 'Fideicomiso activo'],
                ]; @endphp
                @foreach($chips as $i => $c)
                    <div class="px-5 py-3 {{ $i < count($chips)-1 ? 'border-r border-white/15' : '' }}">
                        <div class="text-[10px] uppercase tracking-wider opacity-70">{{ $c[0] }}</div>
                        <div class="font-display text-[15px] font-semibold mt-1 whitespace-nowrap">{{ $c[1] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ INVENTORY OVERVIEW ============ --}}
    <div class="crm-card p-5">
        <div class="flex items-baseline justify-between gap-4 flex-wrap">
            <div>
                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Inventario · {{ $totalUnits }} unidades totales</div>
                <div class="mt-2 flex items-baseline gap-5">
                    <div><span class="font-display text-[28px] font-bold text-err">{{ $sold }}</span> <span class="text-[12px] text-ink-500">vendidas</span></div>
                    <div><span class="font-display text-[28px] font-bold text-ink-900">{{ $available }}</span> <span class="text-[12px] text-ink-500">disponibles</span></div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Valor total del proyecto</div>
                <div class="font-display text-[28px] font-bold text-warn-dark mt-2">${{ number_format($valorTotal / 1_000_000, 2) }}M USD</div>
            </div>
        </div>
        <div class="mt-4 h-2 rounded-full bg-ink-100 overflow-hidden flex">
            <div class="bg-err" style="width:{{ $pctSold }}%"></div>
            <div class="bg-warn" style="width:{{ $pctReserved }}%"></div>
            <div class="bg-ok" style="width:{{ $pctAvailable }}%"></div>
        </div>
        <div class="mt-2 flex items-center gap-4 flex-wrap text-[11px] text-ink-600">
            <span class="flex items-center gap-1.5"><span class="dot bg-err"></span> {{ $sold }} vendidas ({{ round($pctSold) }}%) · ${{ number_format($vendidoSoloUSD / 1_000_000, 2) }}M</span>
            <span class="flex items-center gap-1.5"><span class="dot bg-warn"></span> {{ $reserved }} reservadas ({{ round($pctReserved) }}%) · ${{ number_format($reservadoUSD / 1_000_000, 2) }}M</span>
            <span class="flex items-center gap-1.5"><span class="dot bg-ok"></span> {{ $available }} disponibles ({{ round($pctAvailable) }}%) · ${{ number_format($disponibleUSD / 1_000_000, 2) }}M</span>
            <span class="ml-auto text-ok-dark font-semibold">${{ number_format($vendidoSoloUSD / 1_000_000, 2) }}M en ventas cerradas</span>
        </div>
    </div>

    {{-- ============ INVESTMENT KPIs ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $invKpis = [
            ['Ventas cerradas',  '$'.number_format($vendidoSoloUSD / 1_000_000, 2).'M',  $sold.' unidades · prom. $'.number_format($avgPrice), '#fa7319'],
            ['ROI total anual est.', number_format($avgRoi, 1).'%',                       round($avgRoi - 5, 1).'% renta neta + 5% apreciación', '#5c7c68'],
            ['Renta mensual neta',  '$'.number_format($avgRentNet),                       'Tras 22% gestión · 72% ocup. estimada',                '#335cff'],
            ['Payback period',   '~'.($payback ?: '10.0').'a',                            'Retorno completo de capital invertido',               '#717784'],
        ]; @endphp
        @foreach($invKpis as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color:{{ $k[3] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="font-display text-[28px] font-bold leading-tight mt-1" style="color:{{ $k[3] }}">{{ $k[1] }}</div>
                <div class="text-[11px] text-ink-500 mt-1">{{ $k[2] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ============ TIMELINE + INVESTMENT CASE + DISCOUNT ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Construction timeline --}}
        <div class="crm-card p-5">
            <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-400">Avance de obra</div>
            <ul class="mt-3 space-y-2">
                @foreach($phases as $p)
                    @php
                        $isDone   = $p[1] === 'completed';
                        $isActive = $p[1] === 'active';
                        $iconCls  = $isDone ? 'pi pi-check text-ok' : ($isActive ? 'pi pi-circle-fill text-warn' : 'pi pi-circle text-ink-300');
                        $textCls  = $isDone ? 'text-ink-900 line-through opacity-60' : ($isActive ? 'text-ink-900 font-semibold' : 'text-ink-500');
                        $barCls   = $isDone ? 'bg-ok' : ($isActive ? 'bg-warn' : 'bg-ink-200');
                    @endphp
                    <li class="flex items-center gap-3">
                        <i class="{{ $iconCls }} text-[11px] shrink-0"></i>
                        <span class="text-[13px] {{ $textCls }} flex-1">{{ $p[0] }}</span>
                        @if($isActive)
                            <div class="w-20 h-1.5 rounded-full bg-ink-100 overflow-hidden"><span class="block h-full {{ $barCls }}" style="width:60%;"></span></div>
                        @endif
                        <span class="text-[11px] text-ink-500 w-20 text-right">{{ $p[2] }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 pt-3 border-t border-ink-100 flex items-center justify-between">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Avance general<br><span class="text-[11px] text-ink-500 normal-case font-medium">Entrega estimada {{ $entregaFmt }}</span></div>
                <div class="font-display text-[24px] font-bold text-ok-dark">{{ $pctObra ?: 52 }}%</div>
            </div>
        </div>

        {{-- Investment case --}}
        <div class="crm-card p-5">
            <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-400 mb-3">Caso de inversión</div>
            <dl class="text-[13px] divide-y divide-ink-100">
                @php
                    $rows = [
                        ['Precio desde',    '$'.number_format($minPrice ?: $avgPrice, 0).' USD'],
                        ['Precio promedio', '$'.number_format($avgPrice, 0).' USD'],
                        ['Tarifa noche estimada', '$210 USD'],
                        ['Ocupación estimada',    '72%'],
                        ['Alquiler bruto mensual', '$'.number_format($avgRentNet * 1.28).'/mes'],
                        ['Alquiler neto (tras fees)', '$'.number_format($avgRentNet).'/mes'],
                        ['Retorno neto anual', number_format(max(0, $avgRoi - 5), 1).'%'],
                        ['Apreciación capital/año', '5%'],
                        ['ROI total anual', number_format($avgRoi, 1).'%'],
                    ];
                @endphp
                @foreach($rows as [$k, $v])
                    <div class="flex items-center justify-between py-1.5">
                        <dt class="text-ink-600">{{ $k }}</dt>
                        <dd class="text-ink-950 font-semibold">{{ $v }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="text-[10px] text-ink-400 mt-3 leading-relaxed">* Proyecciones basadas en datos históricos de {{ Str::before($proyecto->location ?? 'Cap Cana', '·') }} {{ $entregaFmt }}. No constituyen garantía de rendimiento.</p>
        </div>

        {{-- Launch discount + signals --}}
        <div class="space-y-4">
            <div class="crm-card p-5 border-2 border-ok/40 bg-ok-soft/40">
                <div class="text-[11px] uppercase tracking-wider font-semibold text-ok-dark">Descuento de lanzamiento</div>
                <div class="font-display text-[36px] font-bold text-ok-dark leading-none mt-2">${{ number_format($launchDiscount, 0) }}</div>
                <div class="text-[11px] text-ink-700 mt-1">Ahorro en precio de compra · Válido hasta {{ now()->addMonths(3)->locale('es')->isoFormat('MMM YYYY') }}</div>
                <a href="{{ route('admin.units') }}" class="mt-3 inline-flex w-full justify-center crm-btn crm-btn-ghost text-[12px] bg-white">Editar descuento</a>
            </div>

            <div class="crm-card p-5">
                <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-400">Señales del proyecto</div>
                <ul class="mt-3 space-y-2 text-[12px]">
                    <li class="flex items-center gap-2"><span class="dot bg-ok"></span> {{ $sold }} unidades cerradas desde lanzamiento</li>
                    <li class="flex items-center gap-2"><span class="dot bg-warn"></span> Descuento activo · plazo limitado</li>
                    <li class="flex items-center gap-2"><span class="dot bg-info"></span> Sin desviaciones de entrega</li>
                    <li class="flex items-center gap-2"><span class="dot bg-ok"></span> Fideicomiso activo · DGII registrado</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ============ INVENTORY BY TYPOLOGY ============ --}}
    <div class="crm-card overflow-hidden">
        <div class="px-5 py-3 border-b border-ink-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="pi pi-home text-ink-500"></i>
                <h3 class="text-[14px] font-bold text-ink-900">Inventario por tipología</h3>
            </div>
            <a href="{{ route('admin.units') }}" class="text-[11px] text-brand font-semibold hover:underline">Ver unidades →</a>
        </div>
        <table class="w-full crm-table">
            <thead class="bg-ink-50">
                <tr>
                    <th>Tipología</th><th>Unidades</th><th>Superficie</th><th>Precio desde</th><th>Precio prom.</th><th>Vendidas</th><th>Disponibles</th><th>Yield est.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tipos as $tipo => $d)
                    @php $availT = $d['disp']; @endphp
                    <tr>
                        <td class="text-[13px] font-semibold text-ink-900">{{ $tipo }}</td>
                        <td class="text-[13px] text-ink-700">{{ $d['count'] }}</td>
                        <td class="text-[12px] text-ink-500">{{ number_format((float) ($d['minM2'] ?? 0)) }}–{{ number_format((float) ($d['maxM2'] ?? 0)) }} sqft</td>
                        <td class="text-[13px] font-semibold text-ink-900">${{ number_format($d['min'] ?? 0) }}</td>
                        <td class="text-[13px] font-bold text-warn-dark">${{ number_format($d['avg'] ?? 0) }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 rounded-full bg-ink-100 overflow-hidden">
                                    <span class="block h-full bg-ok" style="width:{{ $d['sold_pct'] }}%"></span>
                                </div>
                                <span class="text-[13px] font-semibold text-ok-dark">{{ $d['sold'] }}</span>
                            </div>
                        </td>
                        <td class="text-[13px] text-ink-700">{{ $availT }}</td>
                        <td class="text-[13px] font-semibold text-ok-dark">{{ $d['yield'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-[12px] text-ink-500 py-6">Sin tipologías registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ PAYMENT STRUCTURE + LOCATION ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="crm-card p-5">
            <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-400 mb-3">Estructura del plan de pagos</div>
            @php
                $planRows = [
                    ['5%',  'ok',   'Progreso de ventas', 'Al firmar el acuerdo de reserva', '$'.number_format(round($avgPrice * 0.05)).' USD'],
                    ['15%', 'warn', 'Durante construcción', '24 cuotas mensuales hasta entrega', '$'.number_format(round(($avgPrice * 0.15) / 24)).' USD / mes'],
                    ['80%', 'info', 'Entrega de llaves', 'Contra escritura pública · '.$entregaFmt, '$'.number_format(round($avgPrice * 0.80)).' USD'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($planRows as [$pct, $clr, $title, $desc, $amount])
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-{{ $clr }}-soft flex items-center justify-center text-[14px] font-bold text-{{ $clr }}">{{ $pct }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-bold text-ink-900">{{ $title }}</div>
                            <div class="text-[11px] text-ink-500">{{ $desc }}</div>
                            <div class="text-[12px] font-semibold text-{{ $clr }} mt-0.5">{{ $amount }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 px-3 py-2 rounded-lg bg-ink-50 text-[11px] text-ink-600">Financiamiento bancario disponible para el 80% restante a través de bancos locales e internacionales colaboradores.</div>
        </div>

        <div class="crm-card p-5">
            <div class="text-[11px] uppercase tracking-wider font-semibold text-ink-400">Ubicación y amenidades</div>
            <div class="font-display text-[20px] font-bold text-ink-900 mt-2">{{ $proyecto->location ?? 'Cap Cana, Punta Cana' }}</div>
            <div class="text-[12px] text-ink-600 mt-1">{{ $proyecto->description ?? 'República Dominicana · Zona de lujo en la región del Caribe más demandada por inversores internacionales.' }}</div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-[12px]">
                @foreach (['Beach Club privado','Marina Cap Cana','Golf 18 hoyos','Spa & Wellness','Seguridad 24/7','Concierge'] as $a)
                    <div class="flex items-center gap-2 text-ink-700"><span class="dot bg-ok"></span> {{ $a }}</div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-ink-100">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">Pool de renta gestionado</div>
                <div class="mt-2 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-building"></i></div>
                    <div>
                        <div class="text-[13px] font-bold text-ink-900">{{ $proyecto->rental_pool ?? 'Duna Hospitality Group' }}</div>
                        <div class="text-[11px] text-ink-500">Gestión profesional · 72% ocupación estimada</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ STATUS BADGES STRIP ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php $statusStrip = [
            ['Estado legal',  'Fideicomiso activo', 'Registrado ante DGII',                   'ok',   'pi-shield'],
            ['Riesgo entrega','Bajo',               '0 proyectos con retrasos',               'info', 'pi-bolt'],
            ['Financiamiento','Disponible',         'Bancos locales e internac.',             'warn', 'pi-wallet'],
            ['Gestor de renta','Sí',                ($proyecto->rental_pool ?? 'Duna Hospitality Group'), 'err', 'pi-briefcase'],
        ]; @endphp
        @foreach($statusStrip as $s)
            <div class="crm-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $s[3] }}-soft flex items-center justify-center text-{{ $s[3] }}">
                    <i class="pi {{ $s[4] }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] uppercase font-semibold tracking-wide text-ink-400">{{ $s[0] }}</div>
                    <div class="text-[13px] font-bold text-ink-900">{{ $s[1] }}</div>
                    <div class="text-[11px] text-ink-500 truncate">{{ $s[2] }}</div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
