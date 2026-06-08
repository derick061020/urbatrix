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
                                    <button type="button" onclick="openReportModal({{ $r->id }})" class="inline-flex items-center gap-1 text-[11px] text-brand font-semibold hover:underline"><i class="pi pi-eye text-[10px]"></i> Ver reporte</button>
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

{{-- ===== Modal: Publicar reporte de avance ===== --}}
@php
    // Fases fijas del proyecto (estructura del reporte). El % de cada una se
    // controla por slider y el avance global se calcula como su promedio.
    $phaseDefs = [
        'Cimentación'   => 100,
        'Estructura'    => 100,
        'Mampostería'   => 75,
        'Instalaciones' => 40,
        'Acabados'      => 0,
        'Entrega'       => 0,
    ];
    // Si ya hay un reporte previo, precargamos sus porcentajes por nombre de fase.
    $prevPhases = collect($phases)->mapWithKeys(fn ($p) => [($p['name'] ?? '') => (int) ($p['pct'] ?? 0)]);
    $aorPhases  = collect($phaseDefs)->map(fn ($pct, $name) => [
        'name' => $name,
        'pct'  => $prevPhases->has($name) ? $prevPhases[$name] : $pct,
    ])->values();
    $aorGlobal = $aorPhases->count() ? (int) round($aorPhases->avg('pct')) : 0;
@endphp
<style>
    #reportModal .aor-range { -webkit-appearance: none; appearance: none; width: 100%; height: 5px; border-radius: 999px; outline: none; cursor: pointer;
        background: linear-gradient(#1fc16b, #1fc16b) 0/var(--val,0%) 100% no-repeat, #e6e8ec; }
    #reportModal .aor-range::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #1fc16b; box-shadow: 0 1px 3px rgba(16,24,40,.18); }
    #reportModal .aor-range::-moz-range-thumb { width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #1fc16b; box-shadow: 0 1px 3px rgba(16,24,40,.18); }
    #reportModal .aor-phase.is-dim .aor-name { color: #98a2b3; }
    #reportModal .aor-phase.is-dim .aor-range { background: #eef0f3; }
    #reportModal .aor-phase.is-dim .aor-range::-webkit-slider-thumb { border-color: #cacfd8; }
    #reportModal .aor-phase.is-dim .aor-range::-moz-range-thumb { border-color: #cacfd8; }
    #reportModal .aor-check { display: none; }
    #reportModal .aor-phase.is-done .aor-check { display: inline-flex; }
    #reportModal .aor-tile { position: relative; aspect-ratio: 1/1; border: 1.5px dashed #d0d5dd; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; color: #98a2b3; transition: border-color .15s, color .15s; overflow: hidden; }
    #reportModal .aor-tile:hover { border-color: #1fc16b; color: #1fc16b; }
    #reportModal .aor-tile.has-img { border-style: solid; border-color: #1fc16b; }
    #reportModal .aor-tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    #reportModal .aor-tile .aor-tile-x { position: absolute; top: 4px; right: 4px; width: 18px; height: 18px; border-radius: 999px; background: rgba(16,24,40,.65); color: #fff; display: none; align-items: center; justify-content: center; font-size: 9px; z-index: 2; }
    #reportModal .aor-tile.has-img .aor-tile-x { display: inline-flex; }
    #reportModal .aor-tile.has-img .aor-tile-lbl { display: none; }
</style>
<dialog id="reportModal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[600px] max-w-[94vw]">
    <form method="POST" action="{{ route('admin.crm.avance-obra.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="project_id" value="{{ optional($activeProject)->id }}">
        <input type="hidden" name="estimated_delivery" value="{{ $deliveryGlobal }}">
        <input type="hidden" name="overall_progress" id="aorGlobalInput" value="{{ $aorGlobal }}">

        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-brand-tint text-brand flex items-center justify-center"><i class="pi pi-chart-bar"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">Publicar reporte de avance</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>

        <div class="p-6 space-y-5 max-h-[72vh] overflow-y-auto">
            {{-- Proyecto (fijo) --}}
            <div class="px-4 py-2.5 rounded-lg bg-ink-50 border border-ink-100 text-[12px] text-ink-600">
                Proyecto: <span class="font-semibold text-ink-900">{{ optional($activeProject)->name ?? config('company.project') }}</span>
                <span class="text-ink-400"> · {{ $location }}</span>
            </div>

            {{-- Período + Tipo --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Período *</label>
                    <input type="text" name="period" required placeholder="ej. Junio 2026" class="crm-input mt-1 w-full">
                </div>
                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Tipo de reporte</label>
                    <select name="report_type" class="crm-input mt-1 w-full">
                        <option value="Mensual" selected>Mensual</option>
                        <option value="Quincenal">Quincenal</option>
                        <option value="Semanal">Semanal</option>
                        <option value="Hito">Hito</option>
                    </select>
                </div>
            </div>

            {{-- Título --}}
            <div>
                <label class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Título</label>
                <input type="text" name="title" required value="Reporte mensual" class="crm-input mt-1 w-full">
            </div>

            {{-- Avance por fase --}}
            <div class="rounded-xl border border-ink-100 bg-ink-50/40 p-4">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-ink-500 mb-3">Avance por fase</div>
                <div class="space-y-3.5">
                    @foreach($aorPhases as $i => $ph)
                        @php $pct = (int) $ph['pct']; @endphp
                        <div class="aor-phase {{ $pct >= 100 ? 'is-done' : '' }} {{ $pct === 0 ? 'is-dim' : '' }}" data-idx="{{ $i }}">
                            <input type="hidden" name="phases[{{ $i }}][name]"   value="{{ $ph['name'] }}">
                            <input type="hidden" name="phases[{{ $i }}][pct]"    value="{{ $pct }}" id="aorPct-{{ $i }}">
                            <input type="hidden" name="phases[{{ $i }}][status]" value="{{ $pct >= 100 ? 'done' : ($pct > 0 ? 'active' : 'pending') }}" id="aorStatus-{{ $i }}">
                            <input type="hidden" name="phases[{{ $i }}][date]"   value="{{ $pct >= 100 ? 'Completada' : ($pct > 0 ? 'En curso' : 'Pendiente') }}" id="aorDate-{{ $i }}">
                            <div class="grid grid-cols-[140px_1fr_44px] items-center gap-3">
                                <div class="aor-name flex items-center gap-1.5 text-[13px] font-semibold text-ink-800">
                                    <i class="pi pi-check-circle aor-check text-ok text-[12px]"></i>
                                    <span>{{ $ph['name'] }}</span>
                                </div>
                                <input type="range" min="0" max="100" value="{{ $pct }}" class="aor-range" style="--val: {{ $pct }}%" data-idx="{{ $i }}" oninput="aorPhase(this)">
                                <div class="text-[12px] font-bold text-ink-700 text-right" id="aorLbl-{{ $i }}">{{ $pct }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-ink-100 flex items-center justify-between">
                    <span class="text-[12px] text-ink-500">Avance global calculado</span>
                    <span class="text-[20px] font-bold text-ok" id="aorGlobalLbl">{{ $aorGlobal }}%</span>
                </div>
            </div>

            {{-- Descripción --}}
            <div>
                <label class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Descripción / Resumen del período *</label>
                <textarea name="description" rows="3" required placeholder="Describe los avances más importantes de este período…" class="crm-input mt-1 w-full"></textarea>
            </div>

            {{-- Fotos --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Fotos del avance</label>
                    <span class="text-[11px] text-ink-400"><span id="aorPhotoCount">0</span> / 8 fotos</span>
                </div>
                <div class="grid grid-cols-4 gap-2.5">
                    @for($i = 0; $i < 8; $i++)
                        <label class="aor-tile">
                            <span class="aor-tile-x" onclick="aorPhotoClear(event, this)"><i class="pi pi-times"></i></span>
                            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="aorPhotoPick(this)">
                            <span class="aor-tile-lbl flex flex-col items-center gap-1">
                                <i class="pi pi-upload text-[14px]"></i>
                                <span class="text-[10px]">Subir</span>
                            </span>
                        </label>
                    @endfor
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-[10px] text-ink-400">JPG, PNG, WebP · Máx. 10 MB por foto</span>
                </div>
            </div>

            {{-- Notificar --}}
            <label class="flex items-start gap-3 p-3 rounded-lg bg-ok-soft/60 border border-ok/20 cursor-pointer">
                <input type="checkbox" name="notify" value="1" checked class="accent-ok mt-0.5">
                <span>
                    <span class="block text-[12px] font-semibold text-ink-800">Notificar a compradores activos</span>
                    <span class="block text-[11px] text-ink-500">Se enviará por Email y WhatsApp según configuración de plantillas</span>
                </span>
            </label>
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-chart-bar"></i> Publicar reporte</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    if (window.__aorInit) return;
    window.__aorInit = true;

    // Slider de fase -> actualiza etiqueta, estado/fecha ocultos y recalcula global.
    window.aorPhase = function (slider) {
        const i = slider.dataset.idx;
        const pct = parseInt(slider.value, 10) || 0;
        slider.style.setProperty('--val', pct + '%');
        document.getElementById('aorLbl-' + i).textContent = pct + '%';
        document.getElementById('aorPct-' + i).value = pct;
        const status = pct >= 100 ? 'done' : (pct > 0 ? 'active' : 'pending');
        document.getElementById('aorStatus-' + i).value = status;
        document.getElementById('aorDate-' + i).value = status === 'done' ? 'Completada' : (status === 'active' ? 'En curso' : 'Pendiente');
        const row = slider.closest('.aor-phase');
        row.classList.toggle('is-done', pct >= 100);
        row.classList.toggle('is-dim', pct === 0);
        aorRecalc();
    };

    function aorRecalc() {
        const sliders = document.querySelectorAll('#reportModal .aor-range');
        let sum = 0;
        sliders.forEach(s => sum += parseInt(s.value, 10) || 0);
        const global = sliders.length ? Math.round(sum / sliders.length) : 0;
        document.getElementById('aorGlobalLbl').textContent = global + '%';
        document.getElementById('aorGlobalInput').value = global;
    }

    // Galería de fotos (8 tiles independientes).
    window.aorPhotoPick = function (input) {
        const tile = input.closest('.aor-tile');
        const file = input.files && input.files[0];
        if (!file) { aorPhotoReset(tile, input); return; }
        const reader = new FileReader();
        reader.onload = e => {
            let img = tile.querySelector('img');
            if (!img) { img = document.createElement('img'); tile.appendChild(img); }
            img.src = e.target.result;
            tile.classList.add('has-img');
            aorPhotoCount();
        };
        reader.readAsDataURL(file);
    };
    window.aorPhotoClear = function (ev, x) {
        ev.preventDefault();
        const tile = x.closest('.aor-tile');
        const input = tile.querySelector('input[type=file]');
        input.value = '';
        aorPhotoReset(tile, input);
        aorPhotoCount();
    };
    function aorPhotoReset(tile) {
        const img = tile.querySelector('img');
        if (img) img.remove();
        tile.classList.remove('has-img');
    }
    function aorPhotoCount() {
        const n = document.querySelectorAll('#reportModal .aor-tile.has-img').length;
        document.getElementById('aorPhotoCount').textContent = n;
    }
})();
</script>

@include('_partials.construction_report_modal', ['reports' => $reports])
@endsection
