@extends('layouts.admin_crm')
@section('title', 'Avance de Obra — CRM Duna Makai')
@section('page_title', 'Avance de Obra')
@section('page_breadcrumb', 'Proyectos · Avance de obra')
@php $activeRoute = 'crm.avance-obra'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    <div class="flex items-center justify-between">
        <div class="text-[14px] font-semibold text-ink-700">Entrega estimada Q4 2026</div>
        <div class="flex items-center gap-2">
            <button class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo reporte</button>
        </div>
    </div>

    {{-- Project tabs --}}
    <div class="crm-card p-4 flex items-center gap-3">
        @php
            $tabs = [
                ['Makai residences', 'Activo · En construcción', true],
                ['Naviva Residences', 'En preparación', false],
                ['LIV at Cap Cana', 'En preparación', false],
            ];
        @endphp
        @foreach($tabs as $t)
            <button class="px-4 py-2 rounded-lg border {{ $t[2] ? 'border-brand bg-brand-tint text-brand' : 'border-ink-200 text-ink-500' }} text-left">
                <div class="text-[13px] font-semibold">{{ $t[0] }}</div>
                <div class="text-[10px] opacity-70">{{ $t[1] }}</div>
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Progress card --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100">
                <h3 class="text-[14px] font-semibold text-ink-900">Makai Residences — Progreso general</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-5 mb-5">
                    <div class="relative w-24 h-24">
                        <svg viewBox="0 0 36 36" class="w-24 h-24 -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eaecf0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1fc16b" stroke-width="3"
                                stroke-dasharray="52, 100" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[20px] font-bold text-ink-900">52%</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[14px] font-semibold text-ink-900">Avance global de obra</div>
                        <div class="text-[12px] text-ink-500">102 unidades · Cap Cana · Punta Cana</div>
                        <div class="text-[11px] text-ink-400 mt-1">Entrega estimada Q4 2026</div>
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $stages = [
                            ['Cimentación',   '#1fc16b','Jun 2025',100],
                            ['Estructura',    '#1fc16b','Oct 2025',100],
                            ['Mampostería',   '#fa7319','En curso',75],
                            ['Instalaciones', '#fa7319','En curso',40],
                            ['Acabados',      '#cacfd8','—',         0],
                            ['Entrega',       '#cacfd8','Q4 2026',  0],
                        ];
                    @endphp
                    @foreach($stages as $s)
                        <div>
                            <div class="flex items-center justify-between text-[12px] mb-1">
                                <div class="flex items-center gap-2 text-ink-700">
                                    <span class="dot" style="background:{{ $s[1] }}"></span>
                                    <span class="font-semibold">{{ $s[0] }}</span>
                                </div>
                                <div class="text-ink-500">{{ $s[2] }} · <span class="font-bold text-ink-700">{{ $s[3] }}%</span></div>
                            </div>
                            <div class="crm-progress"><span style="background:{{ $s[1] }};width:{{ $s[3] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reportes publicados --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="text-[14px] font-semibold text-ink-900">Reportes publicados</h3>
                <span class="crm-pill bg-ink-100 text-ink-600">3</span>
            </div>
            <div class="divide-y divide-ink-100">
                @php
                    $reports = [
                        ['Mayo 2026 — Reporte mensual',    'Mampostería piso 3 al 80%. Instalaciones eléctricas en progreso en pisos 1–2.', '2026-05-01'],
                        ['Abril 2026 — Reporte mensual',   'Estructura completada, inicio de mampostería en pisos 1 y 2.',                  '2026-04-01'],
                        ['Q4 2025 — Reporte trimestral',   'Estructura pisos 3 y 4 completada al 100%. Inspección aprobada.',              '2026-01-01'],
                    ];
                @endphp
                @foreach($reports as $r)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="text-[13px] font-semibold text-ink-900">{{ $r[0] }}</div>
                                <div class="text-[11px] text-ink-500 mt-0.5">{{ $r[1] }}</div>
                                <a href="#" class="text-[11px] text-brand font-semibold mt-2 inline-flex items-center gap-1 hover:underline">Ver reporte &rarr;</a>
                                <a href="#" class="text-[11px] text-ink-500 ml-3 hover:underline">Notificar compradores</a>
                            </div>
                            <div class="text-[10px] text-ink-400 whitespace-nowrap">{{ $r[2] }}</div>
                        </div>
                    </div>
                @endforeach
                <div class="px-5 py-3 text-center">
                    <button class="text-[12px] text-brand font-semibold hover:underline"><i class="pi pi-plus text-[10px]"></i> Agregar reporte de avance</button>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 rounded-lg bg-info-soft border border-info/20 text-[11px] text-ink-600 flex items-center gap-2">
        <i class="pi pi-info-circle text-info"></i>
        Los reportes publicados generan notificaciones automáticas a todos los compradores activos del proyecto. Las actualizaciones se envían vía Email y WhatsApp según la configuración de plantillas.
    </div>
</div>
@endsection
