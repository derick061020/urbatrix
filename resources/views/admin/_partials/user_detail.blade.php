@php
    $fullName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? '—');
    $parts = preg_split('/\s+/', trim($fullName !== '—' ? $fullName : 'U'));
    $init  = strtoupper(substr($parts[0] ?? 'U', 0, 1).substr($parts[1] ?? '', 0, 1));
    $avBg  = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3','#5c7c68'];
    $bg    = $avBg[$user->id % count($avBg)];
    $agente = $reservation?->budget_configured_by ?: '—';
    $unitName = $unit ? ($unit->custom_id ?? $unit->name ?? '—') : '—';
    $project  = $unit?->project?->name ?? ($reservation ? 'Makai Residences' : '—');
    $editData = \Illuminate\Support\Js::from([
        'id' => $user->id, 'first' => $user->first_name, 'last' => $user->last_name,
        'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
        'country' => $user->country, 'role' => $user->role,
    ]);
@endphp

{{-- ===== HEADER ===== --}}
<div class="px-6 py-4 border-b border-ink-100 flex items-start gap-4">
    <div class="crm-avatar" style="width:52px;height:52px;font-size:18px;background:{{ $bg }}">{{ $init }}</div>
    <div class="flex-1 min-w-0">
        <div class="text-[17px] font-bold text-ink-950">{{ $fullName }}</div>
        <div class="text-[12px] text-ink-500 mt-0.5">
            @if($user->country)<span class="text-ink-700 font-medium">{{ $user->country }}</span> · @endif{{ $user->email }}
        </div>
        @if($user->phone)<div class="text-[12px] text-ink-500">{{ $user->phone }}</div>@endif
    </div>
    <div class="text-right shrink-0">
        <span class="crm-pill bg-{{ $estado[1] }}-soft text-{{ $estado[1] }} uppercase">{{ $estado[0] }}</span>
        <div class="text-[11px] text-ink-400 mt-2">Agente: {{ $agente }}</div>
    </div>
    <button type="button" onclick="closeUserDetail()" class="text-ink-400 hover:text-ink-700 p-1 -mt-1"><i class="pi pi-times text-[12px]"></i></button>
</div>

{{-- ===== STRIP ===== --}}
<div class="px-6 py-4 border-b border-ink-100 grid grid-cols-2 sm:grid-cols-5 gap-4">
    @php $strip = [
        ['Unidad', $unitName],
        ['Proyecto', $project],
        ['Precio total', '$'.number_format($price, 0)],
        ['Pagado', '$'.number_format($paid, 0)],
    ]; @endphp
    @foreach($strip as [$l, $v])
        <div>
            <div class="text-[10px] uppercase tracking-wider text-ink-400">{{ $l }}</div>
            <div class="text-[14px] font-semibold text-ink-950 mt-0.5 truncate">{{ $v }}</div>
        </div>
    @endforeach
    <div>
        <div class="text-[10px] uppercase tracking-wider text-ink-400">Progreso</div>
        <div class="text-[14px] font-semibold text-ink-950 mt-0.5">{{ $pct }}%</div>
        <div class="crm-progress mt-1.5"><span class="bg-brand" style="width:{{ $pct }}%"></span></div>
    </div>
</div>

{{-- ===== TABS ===== --}}
<div class="px-6 border-b border-ink-100 flex items-center gap-1">
    @foreach(['info'=>'Información','propiedad'=>'Propiedad','documentos'=>'Documentos','actividad'=>'Actividad'] as $key=>$label)
        <button type="button" class="udt-tab px-3 py-3 text-[13px] font-semibold border-b-2 -mb-px transition-colors {{ $key==='info' ? 'text-brand border-brand' : 'text-ink-500 border-transparent' }}"
                data-tab="{{ $key }}" onclick="switchUserTab('{{ $key }}')">{{ $label }}</button>
    @endforeach
</div>

<div class="p-6 max-h-[52vh] overflow-y-auto">

    {{-- ===== INFORMACIÓN ===== --}}
    <div class="udt-panel grid grid-cols-1 md:grid-cols-2 gap-8" data-panel="info">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-3">Contacto</div>
            <div class="divide-y divide-ink-100">
                @foreach([
                    ['Nombre', $fullName],
                    ['Email', $user->email],
                    ['Teléfono', $user->phone ?: '—'],
                    ['País', $user->country ?: '—'],
                    ['Registro', optional($user->created_at)->format('Y-m-d') ?? '—'],
                    ['Agente asignado', $agente],
                ] as [$l, $v])
                    <div class="py-2.5 flex items-center justify-between gap-4">
                        <span class="text-[12px] text-ink-500">{{ $l }}</span>
                        <span class="text-[13px] font-semibold text-ink-900 text-right truncate">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-3">Estado del proceso</div>
            <div class="divide-y divide-ink-100">
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-ink-500">Estado actual</span>
                    <span class="crm-pill bg-{{ $estado[1] }}-soft text-{{ $estado[1] }} uppercase">{{ $estado[0] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-ink-500">Etapa</span>
                    <span class="text-[13px] font-semibold text-ink-900">{{ $stage[0] }} — {{ $stage[1] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-ink-500">Última acción</span>
                    <span class="text-[13px] font-semibold text-ink-900">{{ $user->last_seen ? \Carbon\Carbon::parse($user->last_seen)->diffForHumans() : '—' }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between gap-2">
                    <span class="text-[12px] text-ink-500">Alertas</span>
                    <span class="flex items-center gap-1.5">
                        @forelse($alerts as $a)<span class="crm-pill bg-warn-soft text-warn-dark uppercase">{{ $a }}</span>@empty<span class="text-[12px] text-ink-400">—</span>@endforelse
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PROPIEDAD ===== --}}
    <div class="udt-panel" data-panel="propiedad" style="display:none">
        @if($reservation && $unit)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2.5">
                @foreach([
                    ['Unidad', $unitName],
                    ['Proyecto', $project],
                    ['Precio', '$'.number_format($price, 0).' USD'],
                    ['Reserva', $reservation->reservation_code ?? '—'],
                    ['Estado de la unidad', $unit->status ?? '—'],
                    ['Pagado a la fecha', '$'.number_format($paid, 0).' USD'],
                ] as [$l, $v])
                    <div class="py-2 flex items-center justify-between border-b border-ink-100">
                        <span class="text-[12px] text-ink-500">{{ $l }}</span>
                        <span class="text-[13px] font-semibold text-ink-900">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center text-[13px] text-ink-400">Este usuario aún no tiene una unidad asignada.</div>
        @endif
    </div>

    {{-- ===== DOCUMENTOS ===== --}}
    <div class="udt-panel" data-panel="documentos" style="display:none">
        @php $docs = $reservation ? $reservation->documents : collect(); @endphp
        @forelse($docs as $doc)
            <div class="py-3 flex items-center gap-3 border-b border-ink-100">
                <span class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-file"></i></span>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-semibold text-ink-950 truncate">{{ $doc->filename ?? $doc->document_type ?? 'Documento' }}</div>
                    <div class="text-[11px] text-ink-400">{{ str_replace('_', ' ', $doc->document_type ?? '') }}</div>
                </div>
                @php $dc = match($doc->status){'approved','signed','completed'=>'ok','rejected'=>'err','pending','generated'=>'warn',default=>'info'}; @endphp
                <span class="crm-pill bg-{{ $dc }}-soft text-{{ $dc }} uppercase">{{ $doc->status }}</span>
            </div>
        @empty
            <div class="py-10 text-center text-[13px] text-ink-400">Sin documentos cargados.</div>
        @endforelse
    </div>

    {{-- ===== ACTIVIDAD ===== --}}
    <div class="udt-panel grid grid-cols-1 md:grid-cols-2 gap-8" data-panel="actividad" style="display:none">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-3">Resumen de actividad</div>
            <div class="divide-y divide-ink-100">
                @foreach([
                    ['Sesiones este mes', $sessionsThisMonth],
                    ['Última conexión', $user->last_seen ? \Carbon\Carbon::parse($user->last_seen)->diffForHumans() : 'Nunca'],
                    ['Sesión promedio', $avgSession],
                    ['Documentos visitados', $docsViewed],
                    ['Propiedades vistas', $distinctUnits],
                ] as [$l, $v])
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-[12px] text-ink-500">{{ $l }}</span>
                        <span class="text-[14px] font-semibold text-ink-900">{{ $v }}</span>
                    </div>
                @endforeach
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-ink-500">Plataforma</span>
                    <span class="crm-pill bg-{{ $platform[1] }}-soft text-{{ $platform[1] === 'ok' ? 'ok-dark' : ($platform[1] === 'warn' ? 'warn-dark' : 'info') }}">{{ $platform[0] }}</span>
                </div>
            </div>

            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mt-6 mb-3">Últimas acciones en plataforma</div>
            <div class="space-y-2.5">
                @forelse($recentActions as $action)
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-brand/10 flex items-center justify-center text-brand shrink-0"><i class="pi {{ $action->icon }} text-[11px]"></i></span>
                        <span class="text-[12px] text-ink-700 flex-1 min-w-0 truncate">{{ $action->description ?: ucfirst(str_replace('_', ' ', $action->type)) }}</span>
                        <span class="text-[11px] text-ink-400 whitespace-nowrap">{{ $action->created_at->diffForHumans(null, true) }}</span>
                    </div>
                @empty
                    <div class="text-[12px] text-ink-400">Sin actividad registrada todavía.</div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-3">Propiedades más vistas</div>
            @php $maxViews = max(1, optional($topViewed->first())->total ?? 1); @endphp
            <div class="space-y-3">
                @forelse($topViewed as $tv)
                    <div>
                        <div class="flex items-center justify-between text-[12px] mb-1">
                            <span class="text-ink-800 font-medium truncate">{{ optional($tv->unit)->custom_id ?? optional($tv->unit)->name ?? 'Unidad' }}</span>
                            <span class="text-ink-600 font-semibold whitespace-nowrap ml-2">{{ $tv->total }} vis.</span>
                        </div>
                        <div class="crm-progress"><span class="bg-brand" style="width:{{ round($tv->total / $maxViews * 100) }}%"></span></div>
                        <div class="text-[10px] text-ink-400 mt-1">Última visita: {{ \Carbon\Carbon::parse($tv->last_viewed)->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="text-[12px] text-ink-400">Este usuario aún no ha visto propiedades.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===== FOOTER ===== --}}
<div class="px-6 py-4 border-t border-ink-100 flex items-center justify-end gap-2">
    <button type="button" onclick="closeUserDetail()" class="crm-btn crm-btn-ghost">Cerrar</button>
    <button type="button" onclick='closeUserDetail(); openEditUserObj({{ $editData }})' class="crm-btn crm-btn-ghost"><i class="pi pi-pencil"></i> Editar</button>
    @if($reservation)
        <a href="{{ route('admin.crm.expediente.detalle', $reservation->id) }}" class="crm-btn crm-btn-ghost">Ver expediente →</a>
    @endif
    <a href="{{ route('admin.communication') }}" class="crm-btn crm-btn-primary"><i class="pi pi-comment"></i> Mensaje</a>
</div>
