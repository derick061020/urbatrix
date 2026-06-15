@extends('layouts.admin_crm')
@section('title', 'Anuncios — CRM Duna Makai')
@section('page_title', 'Anuncios')
@section('page_breadcrumb', 'Comunicación · Anuncios internos')
@php $activeRoute = 'crm.anuncios'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">
    <div class="flex items-center justify-between">
        <div class="text-[14px] font-semibold text-ink-700">{{ __('3 publicados · 2 fijados') }}</div>
        <div class="flex items-center gap-2">
            <button class="crm-btn crm-btn-ghost">{{ __('Config. canales') }}</button>
            <button class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nuevo reporte') }}</button>
        </div>
    </div>

    <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wider px-2"><i class="pi pi-bookmark-fill text-err"></i> {{ __('Fijados') }}</div>

    @php
        $pinned = [
            [
                'title' => 'Actualización de precios Makai — Q2 2026',
                'body'  => 'A partir del 1 de junio entran en vigor los nuevos precios lista para Makai Residences. Todas las cotizaciones activas tienen 30 días de gracia.',
                'tags'  => [['Compradores activos', 7, 'warn'], ['Compradores en proceso', 3, 'info'], ['Brokers activos', 3, 'away']],
                'meta'  => 'Por Admin Duna · 2026-04-28',
            ],
            [
                'title' => 'Nuevo proceso de aprobación de descuentos',
                'body'  => 'Todo descuento mayor al 3% sobre precio lista requiere aprobación doble: Gerente Comercial + Administración. El flujo está activo en el CRM.',
                'tags'  => [['Equipo interno', 2, 'ok'], ['Brokers activos', 3, 'away']],
                'meta'  => 'Por Admin Duna · 2026-04-15',
            ],
        ];
        $recent = [
            [
                'title' => 'Capacitación CRM — Mayo 2026',
                'body'  => 'Sesión de capacitación el jueves 9 de mayo a las 10 AM (hora RD). Se cubrirán los módulos de expedientes, documentos y aprobaciones. Asistencia obligatoria.',
                'tags'  => [['Equipo interno', 2, 'ok'], ['Compradores en proceso', 3, 'info'], ['Brokers activos', 3, 'away']],
                'meta'  => 'Por Admin Duna · 2026-05-01',
            ],
        ];
    @endphp

    @foreach($pinned as $p)
        <div class="crm-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="text-[14px] font-bold text-ink-900"><span class="text-err">📌</span> {{ $p['title'] }}</div>
                    <div class="text-[12px] text-ink-600 mt-1">{{ $p['body'] }}</div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($p['tags'] as $tag)
                            <span class="crm-pill bg-{{ $tag[2] }}-soft text-{{ $tag[2] }}"><i class="pi pi-user text-[9px]"></i> {{ $tag[0] }} <span class="ml-1 opacity-70">{{ $tag[1] }}</span></span>
                        @endforeach
                    </div>
                    <div class="text-[10px] text-ink-400 mt-3">{{ $p['meta'] }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Archivar') }}</button>
                    <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Editar') }}</button>
                </div>
            </div>
        </div>
    @endforeach

    <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wider px-2">{{ __('Recientes') }}</div>

    @foreach($recent as $p)
        <div class="crm-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="text-[14px] font-bold text-ink-900">{{ $p['title'] }}</div>
                    <div class="text-[12px] text-ink-600 mt-1">{{ $p['body'] }}</div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($p['tags'] as $tag)
                            <span class="crm-pill bg-{{ $tag[2] }}-soft text-{{ $tag[2] }}"><i class="pi pi-user text-[9px]"></i> {{ $tag[0] }} <span class="ml-1 opacity-70">{{ $tag[1] }}</span></span>
                        @endforeach
                    </div>
                    <div class="text-[10px] text-ink-400 mt-3">{{ $p['meta'] }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Archivar') }}</button>
                    <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Editar') }}</button>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
