@extends('layouts.admin_crm')
@section('title', 'Plantillas y Automatizaciones — CRM Duna Makai')
@section('page_title', 'Plantillas y Automatizaciones')
@section('page_breadcrumb', 'Comunicación · Plantillas y flujo de automatización')
@php $activeRoute = 'crm.plantillas'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">7 plantillas · 4 flujos activos</div>
        <div class="flex items-center gap-2">
            <button class="crm-btn crm-btn-ghost">Config. canales</button>
            <button class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo reporte</button>
        </div>
    </div>

    {{-- Tab line --}}
    <div class="crm-card">
        <div class="px-4 border-b border-ink-100 flex items-center gap-6">
            <button class="crm-tab-line active flex items-center gap-2">Plantillas <span class="crm-pill bg-err-soft text-err">7</span></button>
            <button class="crm-tab-line flex items-center gap-2">Automatizaciones <span class="crm-pill bg-err-soft text-err">4</span></button>
        </div>

        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
            @foreach(['Todas','Bienvenida','Seguimiento','Pagos','Legal','Proyectos'] as $i => $t)
                <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $t }}</button>
            @endforeach
        </div>

        <div class="divide-y divide-ink-100">
            @php
                $tpls = [
                    ['file', 'Bienvenida — Reserva confirmada',     'Bienvenida', 'Email + WhatsApp', 'Última vez hace 2 días'],
                    ['user', 'KYC — Documentos pendientes',         'Seguimiento','Email + WhatsApp', 'Última vez hace 2 días'],
                    ['eye',  'Recordatorio de cuota',                'Pagos',      'Email + WhatsApp', 'Última vez hace 2 días'],
                    ['clock','Aviso pago vencido',                   'Pagos',      'Email + WhatsApp', 'Última vez hace 2 días'],
                    ['file-pdf','Promesa de compraventa lista',      'Legal',      'Email + WhatsApp', 'Última vez hace 2 días'],
                    ['chart-line','Actualización avance de obra',    'Proyectos',  'Email + WhatsApp', 'Última vez hace 2 días'],
                    ['check','Felicitación cierre de contrato',      'Seguimiento','Email + WhatsApp', 'Última vez hace 2 días'],
                ];
            @endphp
            @foreach($tpls as $t)
                <div class="px-5 py-3 flex items-center gap-4">
                    <div class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-{{ $t[0] }}"></i></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-ink-900">{{ $t[1] }}</div>
                        <div class="text-[11px] text-ink-500">
                            <span class="text-brand font-semibold">{{ $t[2] }}</span> · {{ $t[3] }} · {{ $t[4] }}
                        </div>
                    </div>
                    <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">Editar</button>
                    <a href="#" class="text-[12px] text-brand font-semibold hover:underline">Ver &rarr;</a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
