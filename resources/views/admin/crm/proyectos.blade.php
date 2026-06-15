@extends('layouts.admin_crm')
@section('title', 'Proyectos — CRM Duna Makai')
@section('page_title', 'Proyectos')
@section('page_breadcrumb', 'Proyectos · Gestión de proyectos')
@php $activeRoute = 'crm.proyectos'; @endphp

@push('styles')
<style>
    .pr-bg { background:#f5f5f5; }
    .pr-card {
        background:#ffffff;
        border:1px solid #eaecf0;
        border-radius:16px;
        box-shadow:0 1px 2px rgba(10,13,20,.04);
        overflow:hidden;
    }
    .pr-card-row {
        background:#ffffff;
        border:1px solid #eaecf0;
        border-radius:14px;
        padding:16px 20px;
        display:flex; align-items:center; gap:14px;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .pr-card-row:hover {
        border-color:#d0d5dd;
        box-shadow:0 2px 6px rgba(10,13,20,.04);
    }
    /* Project icon — backgrounds come from the Figma asset itself, so when an
       <img> is present it covers the whole 44×44 square. The wrapper's
       background-color is the fallback when no icon is set. */
    .pr-icon {
        width:44px; height:44px; border-radius:12px;
        display:inline-flex; align-items:center; justify-content:center;
        color:#fff; flex-shrink:0;
        overflow:hidden;
    }
    .pr-icon img {
        width:100%; height:100%;
        object-fit:cover;
        display:block;
        image-rendering: -webkit-optimize-contrast;
    }
    .pr-stat-label {
        font-size:10px; font-weight:700; letter-spacing:.06em;
        color:#99a0ae; text-transform:uppercase;
    }
    .pr-stat-value {
        font-family:'Inter Tight', Inter, sans-serif;
        font-size:22px; font-weight:700; line-height:1.1; margin-top:6px;
    }
    .pr-divider { height:1px; background:#eaecf0; }
    .pr-prog-track {
        height:6px; border-radius:999px; background:#f2f5f8; overflow:hidden;
    }
    .pr-prog-track > span { display:block; height:100%; border-radius:999px; }
    .pr-pill {
        display:inline-flex; align-items:center; gap:4px;
        padding:4px 10px; border-radius:999px;
        font-size:10px; font-weight:700; line-height:1; letter-spacing:.06em;
        text-transform:uppercase; white-space:nowrap;
    }
    .pr-pill-ok    { background:rgba(31,193,107,.12); color:#1daf61; }
    .pr-pill-prep  { background:#eaecf0; color:#717784; }
    .pr-pill-warn  { background:rgba(250,115,25,.12); color:#e16614; }
    .pr-pill-err   { background:rgba(251,55,72,.12); color:#e93544; }
    .pr-pill-info  { background:rgba(51,92,255,.12); color:#3559e9; }
    .pr-pill-amber { background:rgba(246,181,30,.16); color:#b67a06; }
    .pr-btn {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; border-radius:8px; font-size:12px; font-weight:600;
        line-height:1; cursor:pointer; transition: background-color .15s, border-color .15s;
    }
    .pr-btn-ghost { background:#fff; color:#525866; border:1px solid #eaecf0; }
    .pr-btn-ghost:hover { background:#f5f7fa; }
    .pr-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; }
    .pr-btn-primary:hover { background:#4a6354; }
    .pr-avatar-sm {
        width:30px; height:30px; border-radius:999px;
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:700; font-size:11px; color:#fff; flex-shrink:0;
    }
    .pr-client-row {
        display:flex; align-items:center; gap:10px;
        padding:6px 4px; border-radius:8px;
        text-decoration:none;
    }
    .pr-client-row:hover { background:#f8f9fb; }
</style>
@endpush

@section('content')
@php
    // A project is "active" when it has real units in the system. Projects
    // seeded without units (Naviva, LIV) stay collapsed as "En preparación"
    // — matches Figma 644:40555.
    $activos = $proyectos->filter(fn($p) => ($p->units_count ?? 0) > 0)->count();

    // Fallback assets when no icon_path is stored in DB (matched by slug prefix)
    $iconFallback = [
        'makai'  => '/images/projects/makai.png',
        'naviva' => '/images/projects/naviva.png',
        'liv'    => '/images/projects/liv.png',
    ];

    $resolveIcon = function($p) use ($iconFallback) {
        $img = $p->icon_path ?: null;
        if (!$img) {
            $key = strtolower(\Illuminate\Support\Str::slug($p->name));
            foreach ($iconFallback as $k => $v) {
                if (str_starts_with($key, $k)) { $img = $v; break; }
            }
        }
        return [
            'bg'  => $p->color ?: '#5c7c68',
            'img' => $img,
        ];
    };
@endphp

<div class="pr-bg min-h-full p-5 sm:p-7 space-y-5">

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>
    @endif

    {{-- Action bar --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="text-[14px] font-semibold text-ink-700">
            {{ $proyectos->count() }} proyectos · {{ $activos }} {{ $activos === 1 ? 'activo' : 'activos' }}
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="pr-btn pr-btn-ghost">
                <i class="pi pi-upload text-[11px]"></i> Exportar
            </button>
            <button type="button" onclick="document.getElementById('modal-nuevo-proyecto').showModal()" class="pr-btn pr-btn-primary">
                <i class="pi pi-plus text-[11px]"></i> Nuevo proyecto
            </button>
        </div>
    </div>

    @forelse($proyectos as $p)
        @php
            $total      = $p->units_count;
            $sold       = $p->sold_count ?? 0;
            $reserved   = $p->reserved_count ?? 0;
            $available  = $p->available_count ?? 0;
            $valorTotal = $p->units()->sum('price');
            $valorM     = $valorTotal >= 1_000_000 ? '$'.number_format($valorTotal / 1_000_000, 2).'M'
                          : '$'.number_format($valorTotal, 0);
            $isActive   = ($total ?? 0) > 0;
            $pctVentas  = $total > 0 ? round((($sold + $reserved) / $total) * 100) : 0;
            $pctObra    = (int) ($p->progress ?? 0);
            $icon       = $resolveIcon($p);
            $locationTxt = $p->location ?: 'Sin ubicación';
        @endphp

        @if(!$isActive)
            {{-- COLLAPSED ROW (EN PREPARACIÓN) --}}
            <div class="pr-card-row">
                <div class="pr-icon" style="background:{{ $icon['bg'] }}">
                    @if($icon['img'])
                        <img src="{{ $icon['img'] }}" alt="{{ $p->name }}">
                    @else
                        <i class="pi pi-building"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-display text-[16px] font-bold text-ink-950 truncate">{{ $p->name }}</div>
                    <div class="text-[11px] font-semibold text-err uppercase tracking-wide flex items-center gap-1 mt-0.5">
                        <i class="pi pi-map-marker text-[10px]"></i> {{ $locationTxt }}
                    </div>
                </div>
                <span class="pr-pill pr-pill-prep">{{ __('En preparación') }}</span>
            </div>
        @else
            {{-- EXPANDED CARD (ACTIVO) --}}
            <div class="pr-card">
                {{-- Header --}}
                <div class="px-5 sm:px-6 py-5 flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="pr-icon" style="background:{{ $icon['bg'] }}">
                            @if($icon['img'])
                                <img src="{{ $icon['img'] }}" alt="{{ $p->name }}">
                            @else
                                <i class="pi pi-building"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-display text-[16px] font-bold text-ink-950 uppercase tracking-wide truncate">{{ $p->name }}</div>
                            <div class="text-[11px] font-semibold text-err uppercase tracking-wide flex items-center gap-1 mt-0.5">
                                <i class="pi pi-map-marker text-[10px]"></i> {{ $locationTxt }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="pr-pill pr-pill-ok">{{ __('Activo') }}</span>
                        <a href="{{ route('admin.units') }}?project={{ $p->id }}" class="pr-btn pr-btn-ghost">{{ __('Ver unidades') }}</a>
                        <a href="{{ route('admin.crm.proyecto.detalle', $p->id) }}" class="pr-btn pr-btn-primary">{{ __('Ficha completa') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="px-5 sm:px-6 py-5 border-t border-ink-100">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-5">
                        <div>
                            <div class="pr-stat-label">{{ __('Unidades totales') }}</div>
                            <div class="pr-stat-value text-ink-950">{{ $total }}</div>
                        </div>
                        <div>
                            <div class="pr-stat-label">{{ __('Vendidas') }}</div>
                            <div class="pr-stat-value" style="color:#1daf61;">{{ $sold }}</div>
                        </div>
                        <div>
                            <div class="pr-stat-label">{{ __('Reservadas') }}</div>
                            <div class="pr-stat-value" style="color:#e16614;">{{ $reserved }}</div>
                        </div>
                        <div>
                            <div class="pr-stat-label">{{ __('Disponibles') }}</div>
                            <div class="pr-stat-value" style="color:#3559e9;">{{ $available }}</div>
                        </div>
                        <div>
                            <div class="pr-stat-label">{{ __('Valor total') }}</div>
                            <div class="pr-stat-value text-ink-950">{{ $valorM }}</div>
                        </div>
                    </div>
                </div>

                {{-- Progress + Clients --}}
                @php
                    $clientes = \App\Models\Reservation::whereHas('unit', fn($q) => $q->where('project_id', $p->id))
                        ->with(['unit','documents'])
                        ->orderByDesc('created_at')
                        ->get();
                    $totalClientes = $clientes->count();
                    $top = $clientes->take(4);
                    $extra = max(0, $totalClientes - $top->count());
                    $avatarPalette = ['#a4b2db', '#f3c4c4', '#d8c3e8', '#f8d4a8'];
                @endphp
                <div class="px-5 sm:px-6 py-5 border-t border-ink-100 grid grid-cols-1 lg:grid-cols-2 gap-7">
                    {{-- Left: progress bars --}}
                    <div class="space-y-4">
                        <div>
                            <div class="pr-stat-label mb-2">{{ __('Progreso de ventas') }}</div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 pr-prog-track">
                                    <span style="background:#1fc16b;width:{{ $pctVentas }}%"></span>
                                </div>
                                <div class="text-[12px] font-bold text-ink-700 w-10 text-right">{{ $pctVentas }}%</div>
                            </div>
                        </div>
                        <div>
                            <div class="pr-stat-label mb-2">{{ __('Avance de obra') }}</div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 pr-prog-track">
                                    <span style="background:#335cff;width:{{ $pctObra }}%"></span>
                                </div>
                                <div class="text-[12px] font-bold text-ink-700 w-10 text-right">{{ $pctObra }}%</div>
                            </div>
                        </div>
                        <div class="text-[11px] text-ink-500 flex items-center gap-1.5">
                            <i class="pi pi-info-circle text-[11px]"></i>
                            <span>{{ $p->stage ?? 'En desarrollo' }}@if(!empty($p->description)) · {{ $p->description }}@endif</span>
                        </div>
                    </div>

                    {{-- Right: clientes activos --}}
                    <div>
                        <div class="pr-stat-label mb-3">Clientes activos ({{ $totalClientes }})</div>
                        <div class="space-y-1">
                            @forelse($top as $i => $cli)
                                @php
                                    $name  = trim(($cli->first_name ?? '') . ' ' . ($cli->last_name ?? ''));
                                    $name  = $name !== '' ? $name : ($cli->name ?? 'Cliente');
                                    $init  = strtoupper(substr($cli->first_name ?? $name, 0, 1) . substr($cli->last_name ?? '', 0, 1));
                                    if (strlen($init) < 2) $init = strtoupper(substr($name, 0, 2));
                                    $unitName = $cli->unit?->custom_id ?? $cli->unit?->name ?? '—';

                                    $docs = $cli->documents;
                                    $hasKyc      = $docs->where('document_type','kyc')->count() > 0;
                                    $kycApproved = $docs->where('document_type','kyc')->where('status','approved')->count() > 0;
                                    $needsSign   = $docs->whereIn('document_type', ['purchase_promise','contract'])
                                                        ->whereIn('status', ['pending','generated'])->count() > 0;
                                    $allApproved = $docs->count() > 0 && $docs->where('status','approved')->count() === $docs->count();

                                    if ($allApproved)         $estado = ['AL DÍA',          'pr-pill-ok'];
                                    elseif ($needsSign)       $estado = ['FIRMA REQUERIDA', 'pr-pill-err'];
                                    elseif (!$hasKyc)         $estado = ['KYC PENDIENTE',   'pr-pill-warn'];
                                    elseif (!$kycApproved)    $estado = ['EN REVISIÓN',     'pr-pill-amber'];
                                    else                      $estado = ['EN REVISIÓN',     'pr-pill-amber'];

                                    $avatarBg = $avatarPalette[$i % count($avatarPalette)];
                                @endphp
                                <a href="{{ route('admin.crm.expediente.detalle', $cli->id) }}" class="pr-client-row">
                                    <div class="pr-avatar-sm" style="background:{{ $avatarBg }}; color:#5b3a8a;">{{ $init }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-ink-950 truncate">{{ $name }}</div>
                                        <div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">UNIDAD {{ $unitName }}</div>
                                    </div>
                                    <span class="pr-pill {{ $estado[1] }}">{{ $estado[0] }}</span>
                                </a>
                            @empty
                                <div class="text-[12px] text-ink-500 py-1">{{ __('Sin clientes activos.') }}</div>
                            @endforelse
                        </div>

                        @if($extra > 0 || $totalClientes > 0)
                            <div class="text-[11px] text-ink-500 mt-2 flex items-center gap-2">
                                @if($extra > 0)<span>+{{ $extra }} más</span><span class="text-ink-300">·</span>@endif
                                <a href="{{ route('admin.crm.proyecto.detalle', $p->id) }}" class="text-ink-700 font-semibold hover:text-ink-950">{{ __('Ver todos') }} <i class="pi pi-arrow-right text-[10px]"></i></a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="pr-card p-6 text-center text-[12px] text-ink-500">{{ __('No hay proyectos creados.') }}</div>
    @endforelse
</div>

<dialog id="modal-nuevo-proyecto" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.proyectos.store') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">{{ __('Nuevo proyecto') }}</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre') }}</label><input type="text" name="name" required class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Tipo') }}</label><input type="text" name="type" placeholder="{{ __('Residencial') }}" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Etapa') }}</label><input type="text" name="stage" placeholder="{{ __('En desarrollo') }}" class="crm-input pl-3 mt-1"></div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Ubicación') }}</label><input type="text" name="location" placeholder="{{ __('Cap Cana · Punta Cana') }}" class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">Avance %</label><input type="number" name="progress" value="0" min="0" max="100" class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Color') }}</label><input type="color" name="color" value="#5c7c68" class="h-9 w-full rounded-md border border-ink-200 mt-1"></div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Ícono (URL o ruta)') }}</label><input type="text" name="icon_path" placeholder="/images/projects/proyecto.png" class="crm-input pl-3 mt-1"></div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Descripción') }}</label><textarea name="description" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Crear proyecto') }}</button>
        </div>
    </form>
</dialog>
@endsection
