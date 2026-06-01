@extends('layouts.broker')
@section('title', 'Material de ventas — Portal Broker')
@section('page_title', 'Material de ventas')
@section('page_breadcrumb', 'Portal Broker · Recursos de venta')

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500 max-w-xl leading-relaxed">
        Solo puedes usar material aprobado por Duna. No está permitido modificar renders, planos ni material publicitario (Art. 1 del Acuerdo de Colaboración).
    </p>

    {{-- Aviso legal --}}
    <div class="flex items-start gap-3 p-3 rounded-lg bg-warn-soft border border-warn/20">
        <i class="pi pi-exclamation-triangle text-warn-dark mt-0.5"></i>
        <p class="text-[11px] text-ink-600 leading-relaxed m-0">
            Todo el material está registrado y marcado digitalmente. Su uso fuera de los términos del Acuerdo puede dar lugar a la terminación anticipada del contrato.
        </p>
    </div>

    {{-- Grid de recursos --}}
    @if($materials->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($materials as $m)
                <div class="brk-card p-5 flex flex-col gap-3.5 hover:border-brand transition-colors">
                    <div class="flex items-start justify-between">
                        <span class="w-11 h-11 rounded-lg bg-ink-50 border border-ink-200 flex items-center justify-center text-ink-700"><i class="pi {{ $m->icon }} text-[18px]"></i></span>
                        <span class="brk-pill {{ $m->badge_color }}">{{ strtoupper($m->format) }}</span>
                    </div>
                    <div>
                        <div class="text-[13px] font-bold text-ink-950 leading-tight mb-1">{{ $m->title }}</div>
                        <div class="text-[11px] text-ink-500 leading-relaxed">{{ \Illuminate\Support\Str::limit($m->description, 90) }}</div>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-ink-100 mt-auto">
                        <span class="text-[10.5px] text-ink-400">{{ $m->file_size ?: '—' }}@if($m->category) · {{ $m->category }}@endif</span>
                        @if($m->downloadUrl())
                            <a href="{{ $m->downloadUrl() }}" target="_blank" class="text-brand hover:text-brand-dark text-[12px] font-semibold inline-flex items-center gap-1">
                                <i class="pi pi-download text-[12px]"></i> Descargar
                            </a>
                        @else
                            <span class="text-[11px] text-ink-300">No disponible</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="brk-card p-12 text-center text-[13px] text-ink-400">
            <i class="pi pi-folder-open text-[28px] text-ink-300 block mb-3"></i>
            Aún no hay material de ventas publicado. Duna lo cargará próximamente.
        </div>
    @endif
</div>
@endsection
