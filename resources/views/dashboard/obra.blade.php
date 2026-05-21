@extends('layouts.client')
@section('title', 'Avance de Obra — MAKAI')
@section('page_title', 'Avance de Obra')
@section('page_breadcrumb', 'Mi Propiedad · Avance de Obra')
@php $activeRoute = 'progress'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <div class="px-4 py-3 rounded-xl bg-ink-100/60 border border-ink-200">
        <div class="text-[14px] font-bold text-ok-dark">Entrega estimada Q4 2026</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Progreso general --}}
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 text-[14px] font-bold text-ink-950">Makai Residences — Progreso general</div>
            <div class="p-5">
                <div class="flex items-center gap-5 mb-5">
                    <div class="relative w-28 h-28 shrink-0">
                        <svg viewBox="0 0 36 36" class="w-28 h-28 -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eaecf0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1fc16b" stroke-width="3" stroke-dasharray="52, 100" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[26px] font-bold text-ink-950">52%</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[14px] font-semibold text-ink-950">Avance global de obra</div>
                        <div class="text-[12px] text-ink-500">102 unidades · Cap Cana · Punta Cana</div>
                        <div class="text-[11px] text-ink-400 mt-1">Entrega estimada Q4 2026</div>
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $stages = [
                            ['Cimentación',   100, '#1fc16b', 'Jun 2025',  true ],
                            ['Estructura',    100, '#1fc16b', 'Dic 2025',  true ],
                            ['Mampostería',    75, '#fa7319', 'En curso',  false, 'curso'],
                            ['Instalaciones',  40, '#fa7319', 'En curso',  false, 'curso'],
                            ['Acabados',        0, '#cacfd8', 'Q3 2026',   false],
                            ['Entrega',         0, '#cacfd8', 'Q4 2026',   false],
                        ];
                    @endphp
                    @foreach($stages as $s)
                        <div>
                            <div class="flex items-center justify-between text-[13px] mb-1.5">
                                <div class="flex items-center gap-2.5">
                                    @if($s[4])
                                        <span class="w-5 h-5 rounded-full bg-ok flex items-center justify-center"><i class="pi pi-check text-white text-[9px]"></i></span>
                                    @elseif(isset($s[5]))
                                        <span class="w-5 h-5 rounded-full bg-warn flex items-center justify-center"><i class="pi pi-spin pi-spinner text-white text-[9px]"></i></span>
                                    @else
                                        <span class="w-5 h-5 rounded-full border border-ink-300 bg-white"></span>
                                    @endif
                                    <span class="font-semibold {{ $s[1] > 0 ? 'text-ink-950' : 'text-ink-400' }}">{{ $s[0] }}</span>
                                </div>
                                <div class="text-[11px] {{ $s[1] > 0 ? 'text-ink-500' : 'text-ink-400' }}">
                                    {{ $s[3] }} <span class="ml-2 font-bold" style="color:{{ $s[2] }}">{{ $s[1] }}%</span>
                                </div>
                            </div>
                            <div class="cli-progress"><span style="background:{{ $s[2] }};width:{{ $s[1] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reportes publicados --}}
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between bg-ink-50/60 border-b border-ink-100">
                <div class="text-[14px] font-bold text-ink-950">Reportes publicados</div>
                <span class="cli-pill bg-err-soft text-err">{{ count($reports ?? []) ?: 3 }}</span>
            </div>
            <div class="divide-y divide-ink-100">
                @php
                    $reports = $reports ?? [
                        ['Mayo 2026 — Reporte mensual',   'Mampostería piso 3 al 80%. Instalaciones eléctricas en progreso en pisos 1–2.', '2026-05-01'],
                        ['Abril 2026 — Reporte mensual',  'Estructura completada. Inicio de mampostería en pisos 1 y 2.',                  '2026-05-01'],
                        ['Q4 2025 — Reporte trimestral',  'Estructura de pisos 3 y 4 completada al 100%. Inspección aprobada.',           '2026-05-01'],
                    ];
                @endphp
                @foreach($reports as $r)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="text-[14px] font-bold text-ink-950">{{ $r[0] }}</div>
                                <div class="text-[12px] text-ink-500 mt-1">{{ $r[1] }}</div>
                                <div class="flex items-center gap-3 mt-2">
                                    <a href="#" class="text-[12px] text-brand font-semibold hover:underline">Ver reporte</a>
                                    <a href="#" class="text-[12px] text-ink-500 hover:text-ink-950">Notificar compradores</a>
                                </div>
                            </div>
                            <div class="text-[11px] text-ink-400 whitespace-nowrap">{{ $r[2] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
