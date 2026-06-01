@extends('layouts.admin_crm')
@section('title', 'Avance de Obra — CRM Duna Makai')
@section('page_title', 'Avance de Obra')
@section('page_breadcrumb', 'Proyectos · Avance de obra')
@php $activeRoute = 'crm.avance-obra'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))
        <div class="p-3 rounded-lg bg-ok-soft border border-ok/20 text-[12px] text-ok-dark">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="p-3 rounded-lg bg-err-soft border border-err/20 text-[12px] text-err">{{ $errors->first() }}</div>
    @endif

    @php
        $deliveryGlobal = optional($latest)->estimated_delivery ?: 'Q4 2026';
        $overall = optional($latest)->overall_progress ?? (int) optional($activeProject)->progress ?? 0;
        $phases  = optional($latest)->phases ?: [];
        $unitsCount = optional($activeProject)->total_units ?? 0;
        $location   = optional($activeProject)->location ?? 'Cap Cana · Punta Cana';
        $statusMap = ['done' => '#1fc16b', 'active' => '#fa7319', 'pending' => '#cacfd8'];
    @endphp

    <div class="flex items-center justify-between">
        <div class="text-[14px] font-semibold text-ink-700">Entrega estimada {{ $deliveryGlobal }}</div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.crm.export', ['resource' => 'avance-obra']) }}" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</a>
            <button type="button" class="crm-btn crm-btn-primary" onclick="document.getElementById('reportModal').showModal()"><i class="pi pi-plus"></i> Nuevo reporte</button>
        </div>
    </div>

    {{-- Project tabs --}}
    @if($projects->count())
    <div class="crm-card p-4 flex items-center gap-3 flex-wrap">
        @foreach($projects as $p)
            @php $isActive = optional($activeProject)->id === $p->id; @endphp
            <div class="px-4 py-2 rounded-lg border text-left {{ $isActive ? 'border-brand bg-brand-tint text-brand' : 'border-ink-200 text-ink-400 opacity-50 cursor-not-allowed' }}" @unless($isActive) title="Solo el proyecto activo registra avance de obra" @endunless>
                <div class="text-[13px] font-semibold">{{ $p->name }}</div>
                <div class="text-[10px] opacity-70">{{ $isActive ? 'Activo · '.$p->progress.'%' : ($p->stage ?: 'En preparación') }}</div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Progress card --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100">
                <h3 class="text-[14px] font-semibold text-ink-900">{{ optional($activeProject)->name ?? config('company.project') }} — Progreso general</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-5 mb-5">
                    <div class="relative w-24 h-24">
                        <svg viewBox="0 0 36 36" class="w-24 h-24 -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eaecf0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1fc16b" stroke-width="3"
                                stroke-dasharray="{{ $overall }}, 100" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[20px] font-bold text-ink-900">{{ $overall }}%</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[14px] font-semibold text-ink-900">Avance global de obra</div>
                        <div class="text-[12px] text-ink-500">{{ $unitsCount ? $unitsCount.' unidades · ' : '' }}{{ $location }}</div>
                        <div class="text-[11px] text-ink-400 mt-1">Entrega estimada {{ $deliveryGlobal }}</div>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($phases as $s)
                        @php $color = $statusMap[$s['status'] ?? 'pending'] ?? '#cacfd8'; $pct = (int)($s['pct'] ?? 0); @endphp
                        <div>
                            <div class="flex items-center justify-between text-[12px] mb-1">
                                <div class="flex items-center gap-2 text-ink-700">
                                    <span class="dot" style="background:{{ $color }}"></span>
                                    <span class="font-semibold">{{ $s['name'] ?? '—' }}</span>
                                </div>
                                <div class="text-ink-500">{{ $s['date'] ?? '—' }} · <span class="font-bold text-ink-700">{{ $pct }}%</span></div>
                            </div>
                            <div class="crm-progress"><span style="background:{{ $color }};width:{{ $pct }}%"></span></div>
                        </div>
                    @empty
                        <div class="text-[12px] text-ink-400 text-center py-4">Aún no se han registrado fases. Publica un reporte para definir el avance por etapa.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Reportes publicados --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between border-b border-ink-100">
                <h3 class="text-[14px] font-semibold text-ink-900">Reportes publicados</h3>
                <span class="crm-pill bg-ink-100 text-ink-600">{{ $reports->count() }}</span>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse($reports as $r)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="text-[13px] font-semibold text-ink-900">{{ $r->period }} — {{ $r->title }}</div>
                                <div class="text-[11px] text-ink-500 mt-0.5">{{ $r->description }}</div>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[11px] text-ink-400">{{ $r->overall_progress }}% · {{ $r->photos ? count($r->photos).' fotos' : 'sin fotos' }}</span>
                                    <form method="POST" action="{{ route('admin.crm.avance-obra.notify', $r) }}">
                                        @csrf
                                        <button class="text-[11px] text-brand font-semibold hover:underline">Notificar avance mensual</button>
                                    </form>
                                </div>
                                @if($r->notified_at)
                                    <div class="text-[10px] text-ink-400 mt-1">Última notificación: {{ $r->notified_count }} compradores · {{ $r->notified_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </div>
                            <div class="text-[10px] text-ink-400 whitespace-nowrap">{{ optional($r->published_at)->format('Y-m-d') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[12px] text-ink-400">Todavía no hay reportes publicados.</div>
                @endforelse
                <div class="px-5 py-3 text-center">
                    <button type="button" class="text-[12px] text-brand font-semibold hover:underline" onclick="document.getElementById('reportModal').showModal()"><i class="pi pi-plus text-[10px]"></i> Agregar reporte de avance</button>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 rounded-lg bg-info-soft border border-info/20 text-[11px] text-ink-600 flex items-center gap-2">
        <i class="pi pi-info-circle text-info"></i>
        Al publicar un reporte se envía automáticamente la notificación "nuevo reporte" por correo a todos los compradores activos del proyecto.
    </div>
</div>

{{-- ===== Modal: Publicar reporte ===== --}}
<dialog id="reportModal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[560px] max-w-[94vw]">
    <form method="POST" action="{{ route('admin.crm.avance-obra.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-building"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">Publicar reporte de avance</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Proyecto</label>
                    <select name="project_id" class="crm-input mt-1 w-full">
                        <option value="">— General —</option>
                        @foreach($projects as $p)
                            @php $isActive = optional($activeProject)->id === $p->id; @endphp
                            <option value="{{ $p->id }}" @selected($isActive) @disabled(!$isActive)>{{ $p->name }}@unless($isActive) (no disponible)@endunless</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Período *</label>
                    <input type="text" name="period" required placeholder="Mayo 2026" class="crm-input mt-1 w-full">
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Título *</label>
                <input type="text" name="title" required placeholder="Mampostería y estructura" class="crm-input mt-1 w-full">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Descripción</label>
                <textarea name="description" rows="3" placeholder="Resumen del avance del período…" class="crm-input mt-1 w-full"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Avance global (%) *</label>
                    <input type="number" name="overall_progress" min="0" max="100" required value="{{ $overall }}" class="crm-input mt-1 w-full">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Entrega estimada</label>
                    <input type="text" name="estimated_delivery" value="{{ $deliveryGlobal }}" class="crm-input mt-1 w-full">
                </div>
            </div>

            <div>
                <label class="text-[12px] font-semibold text-ink-700">Fases (opcional)</label>
                <div class="space-y-2 mt-1">
                    @for($i = 0; $i < 6; $i++)
                        <div class="grid grid-cols-[1fr_110px_90px_70px] gap-2">
                            <input type="text" name="phases[{{ $i }}][name]" placeholder="Etapa" class="crm-input">
                            <select name="phases[{{ $i }}][status]" class="crm-input">
                                <option value="pending">Pendiente</option>
                                <option value="active">En curso</option>
                                <option value="done">Completada</option>
                            </select>
                            <input type="text" name="phases[{{ $i }}][date]" placeholder="Fecha" class="crm-input">
                            <input type="number" name="phases[{{ $i }}][pct]" min="0" max="100" placeholder="%" class="crm-input">
                        </div>
                    @endfor
                </div>
            </div>

            <div>
                <label class="text-[12px] font-semibold text-ink-700">Fotos del avance</label>
                <input type="file" name="photos[]" accept="image/*" multiple class="crm-input mt-1 w-full">
            </div>

            <label class="flex items-center gap-2 text-[12px] text-ink-700">
                <input type="checkbox" name="notify" value="1" checked class="accent-brand"> Notificar a los compradores por correo al publicar
            </label>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Publicar y notificar</button>
        </div>
    </form>
</dialog>
@endsection
