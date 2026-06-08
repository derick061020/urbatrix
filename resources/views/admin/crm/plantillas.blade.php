@extends('layouts.admin_crm')
@section('title', 'Plantillas y Automatizaciones — CRM Duna Makai')
@section('page_title', 'Plantillas y Automatizaciones')
@section('page_breadcrumb', 'Comunicación · Plantillas y flujo de automatización')
@php $activeRoute = 'crm.plantillas'; @endphp

@push('styles')
<style>
    .crm-pa-card { background:#fff; border:1px solid #eaecf0; border-radius:12px; }
    .crm-pa-row { transition: background-color .12s ease; }
    .crm-pa-row:hover { background:#f9fafb; }
    .crm-pa-icon { width:36px; height:36px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; }
    .crm-pa-modal { position:fixed; inset:0; background:rgba(15,23,42,.45); display:none; z-index:80; align-items:center; justify-content:center; padding:16px; }
    .crm-pa-modal.open { display:flex; }
    .crm-pa-dialog { background:#fff; border-radius:14px; box-shadow:0 24px 48px -12px rgba(0,0,0,.25); width:100%; max-width:720px; max-height:92vh; overflow:hidden; display:flex; flex-direction:column; animation: paModalIn .2s ease; }
    .crm-pa-dialog.wide { max-width:900px; }
    .crm-pa-dialog.narrow { max-width:520px; }
    @keyframes paModalIn { from { transform: translateY(8px); opacity:0; } to { transform:none; opacity:1; } }
    .crm-pa-dialog header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #eaecf0; }
    .crm-pa-dialog header h3 { font-size:15px; font-weight:700; color:#222530; }
    .crm-pa-dialog .pa-body { padding:18px 20px; overflow-y:auto; }
    .crm-pa-dialog footer { padding:14px 20px; border-top:1px solid #eaecf0; display:flex; align-items:center; justify-content:flex-end; gap:8px; background:#fafbfc; }
    .pa-field label { display:block; font-size:11px; font-weight:600; color:#525866; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
    .pa-input, .pa-textarea, .pa-select { width:100%; border:1px solid #eaecf0; border-radius:8px; padding:9px 12px; font-size:13px; color:#222530; background:#fff; outline:none; transition:border-color .15s, box-shadow .15s; }
    .pa-textarea { min-height:140px; font-family:'Inter', system-ui; line-height:1.5; resize:vertical; }
    .pa-input:focus, .pa-textarea:focus, .pa-select:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
    .pa-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; border:1px solid #eaecf0; font-size:12px; font-weight:500; color:#525866; cursor:pointer; user-select:none; background:#fff; }
    .pa-chip.active { background:#5c7c68; color:#fff; border-color:#5c7c68; }
    .pa-chip input { display:none; }
    .pa-var { display:inline-block; padding:3px 8px; border-radius:6px; background:#eef2ef; color:#4a6354; font-size:11px; font-weight:600; cursor:pointer; margin:2px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .pa-var:hover { background:#dde6e0; }
    .pa-tag { display:inline-block; padding:2px 7px; border-radius:6px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
    .pa-flash { position:fixed; bottom:20px; right:20px; z-index:90; background:#1fc16b; color:#fff; padding:12px 18px; border-radius:10px; font-size:13px; font-weight:600; box-shadow:0 12px 28px -8px rgba(31,193,107,.45); animation: paFlashIn .22s ease; }
    .pa-flash.err { background:#fb3748; box-shadow:0 12px 28px -8px rgba(251,55,72,.45); }
    @keyframes paFlashIn { from { transform: translateY(6px); opacity:0; } to { transform:none; opacity:1; } }
    .pa-toggle { width:42px; height:24px; border-radius:999px; background:#cacfd8; position:relative; cursor:pointer; transition:background .15s; flex-shrink:0; }
    .pa-toggle::after { content:""; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:999px; background:#fff; transition:left .15s; box-shadow:0 1px 2px rgba(0,0,0,.18); }
    .pa-toggle.on { background:#1fc16b; }
    .pa-toggle.on::after { left:21px; }
    .pa-icon-btn { width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:#525866; background:#fff; border:1px solid #eaecf0; cursor:pointer; transition: background-color .15s, border-color .15s, color .15s; }
    .pa-icon-btn:hover { background:#f5f7fa; color:#222530; }
    .pa-icon-btn.danger:hover { background:#fff1f2; color:#fb3748; border-color:#fecdd3; }
    .pa-empty { padding:60px 20px; text-align:center; color:#717784; }
    .pa-empty .pi { font-size:36px; color:#cacfd8; margin-bottom:10px; }
    .pa-preview-block { background:#f9fafb; border:1px solid #eaecf0; border-radius:10px; padding:14px 16px; font-size:13px; color:#2b303b; white-space:pre-wrap; word-break:break-word; line-height:1.55; max-height:50vh; overflow-y:auto; }
    /* Cadena de fases */
    .pa-step { position:relative; border:1px solid #eaecf0; border-radius:10px; padding:14px 14px 14px 18px; background:#fcfcfd; }
    .pa-step::before { content:""; position:absolute; left:0; top:14px; bottom:14px; width:3px; border-radius:3px; background:#5c7c68; }
    .pa-step-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
    .pa-step-badge { display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:#4a6354; text-transform:uppercase; letter-spacing:.04em; }
    .pa-step-badge .num { width:20px; height:20px; border-radius:999px; background:#5c7c68; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:11px; }
    .pa-step-delay { display:flex; align-items:center; gap:8px; font-size:12px; color:#717784; margin-bottom:10px; }
    .pa-step-delay input { width:90px; }
    .pa-step-grid { display:grid; grid-template-columns:1fr; gap:10px; }
    .pa-step-remove { color:#fb3748; background:none; border:none; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; gap:4px; }
    .pa-step-remove:hover { text-decoration:underline; }
    .pa-step .pa-conn { font-size:11px; color:#9aa1ad; }
</style>
@endpush

@section('content')
@php
    $categoryColors = [
        'bienvenida'  => ['bg' => '#eef2ef', 'text' => '#4a6354'],
        'seguimiento' => ['bg' => '#ebf1ff', 'text' => '#335cff'],
        'pagos'       => ['bg' => '#fff3eb', 'text' => '#e16614'],
        'legal'       => ['bg' => '#ffebec', 'text' => '#e93544'],
        'proyectos'   => ['bg' => '#e3f7ec', 'text' => '#1daf61'],
        'profesional' => ['bg' => '#fdf3e7', 'text' => '#b8962e'],
        'interno'     => ['bg' => '#eef0f3', 'text' => '#3a4250'],
        'otro'        => ['bg' => '#f2f5f8', 'text' => '#525866'],
    ];
    $triggerEvents = \App\Models\CrmAutomation::$TRIGGER_EVENTS;
    $allCategories = \App\Models\CrmTemplate::$CATEGORIES;
    $iconChoices   = \App\Models\CrmTemplate::$ICONS;
    $channelDefs   = \App\Models\CrmChannelSetting::$CHANNELS;
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4" x-data>
    @if(session('success'))
        <div class="pa-flash" id="pa-flash">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="pa-flash err" id="pa-flash">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">
            {{ $counts['templates'] }} {{ $counts['templates'] === 1 ? 'plantilla' : 'plantillas' }} · {{ $counts['automations'] }} {{ $counts['automations'] === 1 ? 'flujo activo' : 'flujos activos' }}
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="crm-btn crm-btn-ghost" onclick="paOpenModal('pa-modal-channels')">
                <i class="pi pi-sliders-h"></i> Config. canales
            </button>
            <button type="button" class="crm-btn crm-btn-primary" id="pa-new-btn"
                    onclick="paOpenNew()">
                <i class="pi pi-plus"></i> <span id="pa-new-label">Nueva plantilla</span>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="crm-card">
        <div class="px-4 border-b border-ink-100 flex items-center gap-6">
            <button type="button" class="crm-tab-line flex items-center gap-2 {{ $tab === 'plantillas' ? 'active' : '' }}" data-pa-tab="plantillas" onclick="paSwitchTab('plantillas')">
                Plantillas <span class="crm-pill bg-err-soft text-err">{{ $counts['templates'] }}</span>
            </button>
            <button type="button" class="crm-tab-line flex items-center gap-2 {{ $tab === 'automatizaciones' ? 'active' : '' }}" data-pa-tab="automatizaciones" onclick="paSwitchTab('automatizaciones')">
                Automatizaciones <span class="crm-pill bg-err-soft text-err">{{ $counts['automations'] }}</span>
            </button>
        </div>

        {{-- ============ TAB 1: PLANTILLAS ============ --}}
        <div data-pa-panel="plantillas" class="{{ $tab === 'plantillas' ? '' : 'hidden' }}">
            <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
                <button type="button" class="crm-tab pa-filter {{ $filter === 'todas' ? 'active' : '' }}" data-cat="todas">Todas</button>
                @foreach($allCategories as $key => $label)
                    @if($key === 'otro' && empty($counts['by_category'][$key]))
                        @continue
                    @endif
                    <button type="button" class="crm-tab pa-filter {{ $filter === $key ? 'active' : '' }}" data-cat="{{ $key }}">
                        {{ $label }}
                        @if(!empty($counts['by_category'][$key]))
                            <span class="ml-1 text-[10px] opacity-70">({{ $counts['by_category'][$key] }})</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="divide-y divide-ink-100" id="pa-templates-list">
                @forelse($templates as $tpl)
                    @php
                        $catColor = $categoryColors[$tpl->category] ?? $categoryColors['otro'];
                    @endphp
                    <div class="crm-pa-row px-5 py-3 flex items-center gap-4" data-cat="{{ $tpl->category }}" data-tpl-id="{{ $tpl->id }}">
                        <div class="crm-pa-icon" style="background: {{ $catColor['bg'] }}; color: {{ $catColor['text'] }};">
                            <i class="pi pi-{{ $tpl->icon ?: 'file' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $tpl->name }}</div>
                            <div class="text-[11px] text-ink-500">
                                <span class="font-semibold" style="color: {{ $catColor['text'] }};">{{ $tpl->categoryLabel() }}</span>
                                · {{ $tpl->channelsLabel() ?: '—' }}
                                · {{ $tpl->lastUsedLabel() }}
                                @if($tpl->usage_count)
                                    · {{ $tpl->usage_count }} {{ $tpl->usage_count === 1 ? 'envío' : 'envíos' }}
                                @endif
                            </div>
                        </div>
                        <button type="button" class="pa-icon-btn" title="Vista previa" onclick="paOpenPreview({{ $tpl->id }})">
                            <i class="pi pi-eye"></i>
                        </button>
                        <button type="button" class="pa-icon-btn" title="Duplicar" onclick="paDuplicate({{ $tpl->id }})">
                            <i class="pi pi-copy"></i>
                        </button>
                        <button type="button" class="pa-icon-btn danger" title="Eliminar" onclick="paDeleteTemplate({{ $tpl->id }}, @js($tpl->name))">
                            <i class="pi pi-trash"></i>
                        </button>
                        <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" onclick="paEditTemplate({{ $tpl->id }})">
                            Editar
                        </button>
                        <a href="#" class="text-[12px] text-brand font-semibold hover:underline" onclick="event.preventDefault(); paOpenPreview({{ $tpl->id }});">Ver &rarr;</a>
                    </div>
                @empty
                    <div class="pa-empty">
                        <i class="pi pi-inbox"></i>
                        <div class="text-[13px] font-semibold">No hay plantillas en esta categoría</div>
                        <div class="text-[12px] mt-1">Crea una nueva plantilla para empezar.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ TAB 2: AUTOMATIZACIONES ============ --}}
        <div data-pa-panel="automatizaciones" class="{{ $tab === 'automatizaciones' ? '' : 'hidden' }}">
            <div class="px-5 py-4 flex items-center justify-between border-b border-ink-100">
                <div class="text-[12px] text-ink-500">
                    Flujos automáticos disparados por eventos del CRM. Cada flujo envía una plantilla por los canales configurados.
                </div>
            </div>
            <div class="divide-y divide-ink-100" id="pa-automations-list">
                @forelse($automations as $auto)
                    <div class="crm-pa-row px-5 py-3 flex items-center gap-4" data-auto-id="{{ $auto->id }}">
                        <div class="crm-pa-icon" style="background:{{ $auto->is_active ? '#e3f7ec' : '#f2f5f8' }}; color:{{ $auto->is_active ? '#1daf61' : '#717784' }}">
                            <i class="pi pi-{{ $auto->is_active ? 'bolt' : 'pause' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $auto->name }}</div>
                            @php $stepCount = $auto->stepsCount(); @endphp
                            <div class="text-[11px] text-ink-500">
                                <span class="font-semibold text-brand">{{ $auto->triggerLabel() }}</span>
                                @if($stepCount > 1)
                                    · <span class="font-semibold text-ink-700">{{ $stepCount }} fases en cadena</span>
                                @elseif($auto->template)
                                    · usa <span class="font-semibold text-ink-700">{{ $auto->template->name }}</span>
                                @endif
                                · {{ $auto->channelsLabel() ?: '—' }}
                                · {{ $auto->delayLabel() }}
                                @if($auto->run_count)
                                    · {{ $auto->run_count }} {{ $auto->run_count === 1 ? 'ejecución' : 'ejecuciones' }}
                                @endif
                            </div>
                            @if($stepCount > 1)
                                <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                    @foreach($auto->resolvedSteps() as $i => $step)
                                        @if($i > 0)<i class="pi pi-arrow-right text-[9px] text-ink-300"></i>@endif
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-ink-600 bg-ink-50 border border-ink-100 rounded-full px-2 py-0.5">
                                            @if($i > 0 && $step->delay_minutes > 0)
                                                <span class="text-ink-400">+{{ $step->delayLabel() }}</span>
                                            @endif
                                            {{ $step->template?->name ?? 'Sin plantilla' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.crm.automatizaciones.toggle', $auto) }}" class="inline-flex items-center" onclick="event.stopPropagation();">
                            @csrf
                            <button type="submit" class="pa-toggle {{ $auto->is_active ? 'on' : '' }}" title="{{ $auto->is_active ? 'Pausar' : 'Activar' }}"></button>
                        </form>
                        <button type="button" class="pa-icon-btn" title="Ejecutar ahora" onclick="paRunAutomation({{ $auto->id }})">
                            <i class="pi pi-play"></i>
                        </button>
                        <button type="button" class="pa-icon-btn danger" title="Eliminar" onclick="paDeleteAutomation({{ $auto->id }}, @js($auto->name))">
                            <i class="pi pi-trash"></i>
                        </button>
                        <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" onclick="paEditAutomation({{ $auto->id }})">Editar</button>
                    </div>
                @empty
                    <div class="pa-empty">
                        <i class="pi pi-bolt"></i>
                        <div class="text-[13px] font-semibold">No hay automatizaciones</div>
                        <div class="text-[12px] mt-1">Crea un flujo para enviar plantillas automáticamente.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODALES ===================== --}}

{{-- Modal: Editor de Plantilla (crear / editar) --}}
<div class="crm-pa-modal" id="pa-modal-template" role="dialog" aria-modal="true">
    <div class="crm-pa-dialog wide">
        <header>
            <h3 id="pa-tpl-title">Nueva plantilla</h3>
            <button type="button" class="pa-icon-btn" onclick="paCloseModal('pa-modal-template')"><i class="pi pi-times"></i></button>
        </header>
        <form id="pa-tpl-form" method="POST" action="{{ route('admin.crm.plantillas.store') }}" class="contents">
            @csrf
            <input type="hidden" name="_method" id="pa-tpl-method" value="POST">
            <div class="pa-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="pa-field md:col-span-2">
                    <label>Nombre de la plantilla *</label>
                    <input class="pa-input" name="name" id="pa-tpl-name" required maxlength="160" placeholder="Ej.: Bienvenida — Reserva confirmada">
                </div>
                <div class="pa-field">
                    <label>Categoría *</label>
                    <select class="pa-select" name="category" id="pa-tpl-category" required>
                        @foreach($allCategories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pa-field">
                    <label>Ícono</label>
                    <select class="pa-select" name="icon" id="pa-tpl-icon">
                        @foreach($iconChoices as $ic)
                            <option value="{{ $ic }}">{{ $ic }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pa-field md:col-span-2">
                    <label>Canales de envío *</label>
                    <div class="flex flex-wrap gap-2" id="pa-tpl-channels">
                        @foreach($channelDefs as $key => $def)
                            <label class="pa-chip">
                                <input type="checkbox" name="channels[]" value="{{ $key }}">
                                <i class="pi pi-{{ $def['icon'] }}"></i> {{ $def['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="pa-field md:col-span-2">
                    <label>Asunto (solo email)</label>
                    <input class="pa-input" name="subject" id="pa-tpl-subject" maxlength="255" placeholder="Ej.: ¡Bienvenido a Makai, @{{nombre_cliente}}!">
                </div>
                <div class="pa-field md:col-span-2">
                    <label>Cuerpo del mensaje *</label>
                    <textarea class="pa-textarea" name="body" id="pa-tpl-body" required placeholder="Contenido del mensaje (HTML para email). Usa variables como @{{nombre_cliente}} para personalizar."></textarea>
                    <div class="text-[11px] text-ink-500 mt-2">
                        <span class="font-semibold">Variables disponibles (clic para insertar):</span><br>
                        @php
                            $paVars = [
                                'nombre_cliente'         => 'Nombre del cliente',
                                'cliente_email'          => 'Email del cliente',
                                'nombre_asesor'          => 'Nombre del asesor',
                                'tel_asesor'             => 'Teléfono del asesor',
                                'nombre_profesional'     => 'Nombre del broker',
                                'proyecto'               => 'Proyecto',
                                'unidad'                 => 'Unidad',
                                'precio_venta'           => 'Precio de venta',
                                'monto_reserva'          => 'Monto de reserva',
                                'monto_downpayment'      => 'Monto inicial',
                                'monto_comision'         => 'Monto de comisión',
                                'monto'                  => 'Monto del pago',
                                'moneda'                 => 'Moneda',
                                'monto_en_letras'        => 'Monto en letras',
                                'concepto_pago'          => 'Concepto del pago',
                                'metodo_pago'            => 'Método de pago',
                                'referencia_transaccion' => 'Referencia',
                                'fecha_pago'             => 'Fecha de pago',
                                'fecha_vencimiento'      => 'Fecha de vencimiento',
                                'fecha_entrega'          => 'Fecha de entrega',
                                'total_pagado'           => 'Total pagado',
                                'saldo_pendiente'        => 'Saldo pendiente',
                                'pct_obra'               => '% de obra',
                                'mes_reporte'            => 'Mes del reporte',
                                'num_fotos'              => 'N.º de fotos',
                                'hitos_actualizados'     => 'Hitos actualizados',
                                'link_portal'            => 'Enlace al portal',
                                'link_comprobante'       => 'Enlace al comprobante',
                            ];
                        @endphp
                        @foreach($paVars as $v => $label)
                            @php $token = '{{' . $v . '}}'; @endphp
                            <span class="pa-var" onclick="paInsertVar('{{ $token }}')" title="Inserta {{ $token }}">{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" name="variables[]" value="">
            </div>
            <footer>
                <button type="button" class="crm-btn crm-btn-ghost" onclick="paCloseModal('pa-modal-template')">Cancelar</button>
                <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar plantilla</button>
            </footer>
        </form>
    </div>
</div>

{{-- Modal: Vista previa / Enviar prueba --}}
<div class="crm-pa-modal" id="pa-modal-preview" role="dialog" aria-modal="true">
    <div class="crm-pa-dialog">
        <header>
            <h3 id="pa-prev-title">Vista previa</h3>
            <button type="button" class="pa-icon-btn" onclick="paCloseModal('pa-modal-preview')"><i class="pi pi-times"></i></button>
        </header>
        <div class="pa-body space-y-3">
            <div>
                <div class="text-[11px] uppercase font-semibold text-ink-500 mb-1">Categoría · Canales</div>
                <div class="text-[12px] text-ink-700" id="pa-prev-meta">—</div>
            </div>
            <div>
                <div class="text-[11px] uppercase font-semibold text-ink-500 mb-1">Asunto</div>
                <div class="pa-preview-block" id="pa-prev-subject">—</div>
            </div>
            <div>
                <div class="text-[11px] uppercase font-semibold text-ink-500 mb-1">Vista previa del correo</div>
                <iframe id="pa-prev-frame" title="Vista previa del correo"
                        style="width:100%;height:46vh;border:1px solid #eaecf0;border-radius:10px;background:#EFEDE8;"></iframe>
            </div>
            <form id="pa-test-form" method="POST" class="border-t border-ink-100 pt-3">
                @csrf
                <label class="block text-[11px] uppercase font-semibold text-ink-500 mb-1">Enviar prueba a</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="to" class="pa-input" required placeholder="correo@ejemplo.com o +1 809 555 0100">
                    <button type="submit" class="crm-btn crm-btn-primary whitespace-nowrap"><i class="pi pi-send"></i> Enviar</button>
                </div>
            </form>
        </div>
        <footer>
            <button type="button" class="crm-btn crm-btn-ghost" onclick="paCloseModal('pa-modal-preview')">Cerrar</button>
            <button type="button" class="crm-btn crm-btn-primary" id="pa-prev-edit-btn">
                <i class="pi pi-pencil"></i> Editar plantilla
            </button>
        </footer>
    </div>
</div>

{{-- Modal: Automatización --}}
<div class="crm-pa-modal" id="pa-modal-automation" role="dialog" aria-modal="true">
    <div class="crm-pa-dialog">
        <header>
            <h3 id="pa-auto-title">Nueva automatización</h3>
            <button type="button" class="pa-icon-btn" onclick="paCloseModal('pa-modal-automation')"><i class="pi pi-times"></i></button>
        </header>
        <form id="pa-auto-form" method="POST" action="{{ route('admin.crm.automatizaciones.store') }}" class="contents">
            @csrf
            <input type="hidden" name="_method" id="pa-auto-method" value="POST">
            <div class="pa-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="pa-field md:col-span-2">
                    <label>Nombre del flujo *</label>
                    <input class="pa-input" name="name" id="pa-auto-name" required maxlength="160" placeholder="Ej.: Recordatorio cuota 3 días antes">
                </div>
                <div class="pa-field md:col-span-2">
                    <label>Descripción</label>
                    <textarea class="pa-textarea" name="description" id="pa-auto-description" maxlength="1000" style="min-height:70px" placeholder="¿Cuándo y por qué se envía este mensaje?"></textarea>
                </div>
                <div class="pa-field">
                    <label>Evento disparador *</label>
                    <select class="pa-select" name="trigger_event" id="pa-auto-trigger" required>
                        @foreach($triggerEvents as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pa-field">
                    <label>Estado</label>
                    <label class="pa-chip">
                        <input type="checkbox" name="is_active" id="pa-auto-active" value="1" checked>
                        <i class="pi pi-power-off"></i> Activo
                    </label>
                </div>

                {{-- ====== Cadena de fases ====== --}}
                <div class="pa-field md:col-span-2">
                    <label>Cadena de fases *</label>
                    <p class="text-[11px] text-ink-500 -mt-1 mb-2">
                        El disparador inicia la cadena. Cada fase envía una plantilla; el retraso se cuenta
                        respecto de la fase anterior (la primera, desde el disparo).
                    </p>
                    <div id="pa-steps-list" class="space-y-3"></div>
                    <button type="button" class="crm-btn crm-btn-ghost text-[12px] mt-3" onclick="paAddStep()">
                        <i class="pi pi-plus"></i> Añadir fase
                    </button>
                </div>
            </div>
            <footer>
                <button type="button" class="crm-btn crm-btn-ghost" onclick="paCloseModal('pa-modal-automation')">Cancelar</button>
                <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar flujo</button>
            </footer>
        </form>
    </div>
</div>

{{-- Modal: Config canales --}}
<div class="crm-pa-modal" id="pa-modal-channels" role="dialog" aria-modal="true">
    <div class="crm-pa-dialog">
        <header>
            <h3>Configuración de canales</h3>
            <button type="button" class="pa-icon-btn" onclick="paCloseModal('pa-modal-channels')"><i class="pi pi-times"></i></button>
        </header>
        <form method="POST" action="{{ route('admin.crm.canales.update') }}" class="contents">
            @csrf
            <div class="pa-body space-y-5">
                @foreach($channelDefs as $key => $def)
                    @php
                        $setting = $channels[$key] ?? null;
                        $enabled = $setting?->enabled ?? false;
                        $cfg = $setting?->config ?? [];
                    @endphp
                    <div class="crm-pa-card p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="crm-pa-icon" style="background:#eef2ef; color:#4a6354"><i class="pi pi-{{ $def['icon'] }}"></i></div>
                                <div>
                                    <div class="text-[13px] font-semibold text-ink-900">{{ $def['label'] }}</div>
                                    <div class="text-[11px] text-ink-500">Canal {{ $key }}</div>
                                </div>
                            </div>
                            <label class="pa-chip">
                                <input type="checkbox" name="channels[{{ $key }}][enabled]" value="1" {{ $enabled ? 'checked' : '' }}>
                                <i class="pi pi-power-off"></i> Habilitado
                            </label>
                        </div>
                        @if($key === 'email')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="pa-field">
                                    <label>Remitente (nombre)</label>
                                    <input class="pa-input" name="channels[email][config][from_name]" value="{{ $cfg['from_name'] ?? '' }}" placeholder="Makai CRM">
                                </div>
                                <div class="pa-field">
                                    <label>Email remitente</label>
                                    <input class="pa-input" type="email" name="channels[email][config][from_email]" value="{{ $cfg['from_email'] ?? '' }}" placeholder="no-reply@makai.do">
                                </div>
                                <div class="pa-field md:col-span-2">
                                    <label>Reply-to</label>
                                    <input class="pa-input" type="email" name="channels[email][config][reply_to]" value="{{ $cfg['reply_to'] ?? '' }}" placeholder="hola@makai.do">
                                </div>
                            </div>
                        @elseif($key === 'whatsapp')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="pa-field">
                                    <label>Número de negocio</label>
                                    <input class="pa-input" name="channels[whatsapp][config][business_number]" value="{{ $cfg['business_number'] ?? '' }}" placeholder="+1 809 555 0100">
                                </div>
                                <div class="pa-field">
                                    <label>Proveedor API</label>
                                    <select class="pa-select" name="channels[whatsapp][config][api_provider]">
                                        @foreach(['twilio' => 'Twilio', 'meta' => 'Meta Cloud API', 'gupshup' => 'Gupshup'] as $k => $v)
                                            <option value="{{ $k }}" {{ ($cfg['api_provider'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @elseif($key === 'sms')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="pa-field">
                                    <label>Proveedor</label>
                                    <input class="pa-input" name="channels[sms][config][provider]" value="{{ $cfg['provider'] ?? '' }}" placeholder="Twilio / Nexmo">
                                </div>
                                <div class="pa-field">
                                    <label>Sender ID</label>
                                    <input class="pa-input" name="channels[sms][config][sender_id]" value="{{ $cfg['sender_id'] ?? '' }}" placeholder="MAKAI">
                                </div>
                            </div>
                        @elseif($key === 'push')
                            <div class="pa-field">
                                <label>App key</label>
                                <input class="pa-input" name="channels[push][config][app_key]" value="{{ $cfg['app_key'] ?? '' }}" placeholder="Firebase / OneSignal key">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <footer>
                <button type="button" class="crm-btn crm-btn-ghost" onclick="paCloseModal('pa-modal-channels')">Cancelar</button>
                <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-save"></i> Guardar configuración</button>
            </footer>
        </form>
    </div>
</div>

{{-- Hidden forms for destructive actions --}}
<form id="pa-delete-template-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="pa-duplicate-template-form" method="POST" class="hidden">
    @csrf
</form>
<form id="pa-delete-automation-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="pa-run-automation-form" method="POST" class="hidden">
    @csrf
</form>

<script>
(() => {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const TPL_BASE  = '{{ url('/admin/crm/plantillas') }}';
    const AUTO_BASE = '{{ url('/admin/crm/automatizaciones') }}';

    // Flash auto-dismiss
    const flash = document.getElementById('pa-flash');
    if (flash) setTimeout(() => flash.remove(), 3500);

    // ---------- Tabs ----------
    window.paSwitchTab = (tab) => {
        document.querySelectorAll('[data-pa-tab]').forEach(b => {
            b.classList.toggle('active', b.dataset.paTab === tab);
        });
        document.querySelectorAll('[data-pa-panel]').forEach(p => {
            p.classList.toggle('hidden', p.dataset.paPanel !== tab);
        });
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
        const newLabel = document.getElementById('pa-new-label');
        if (newLabel) newLabel.textContent = tab === 'automatizaciones' ? 'Nueva automatización' : 'Nueva plantilla';
    };

    // ---------- Category filters ----------
    document.querySelectorAll('.pa-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            const cat = btn.dataset.cat;
            document.querySelectorAll('.pa-filter').forEach(b => b.classList.toggle('active', b === btn));
            document.querySelectorAll('#pa-templates-list [data-cat]').forEach(row => {
                row.style.display = (cat === 'todas' || row.dataset.cat === cat) ? '' : 'none';
            });
            const url = new URL(window.location.href);
            url.searchParams.set('cat', cat);
            window.history.replaceState({}, '', url);
        });
    });

    // ---------- Modal helpers ----------
    window.paOpenModal = (id) => {
        document.getElementById(id)?.classList.add('open');
        document.body.style.overflow = 'hidden';
    };
    window.paCloseModal = (id) => {
        document.getElementById(id)?.classList.remove('open');
        document.body.style.overflow = '';
    };
    document.querySelectorAll('.crm-pa-modal').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) paCloseModal(m.id); });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.crm-pa-modal.open').forEach(m => paCloseModal(m.id));
    });

    // ---------- Channel chip toggle ----------
    document.querySelectorAll('.pa-chip').forEach(chip => {
        const cb = chip.querySelector('input[type=checkbox]');
        if (!cb) return;
        const sync = () => chip.classList.toggle('active', cb.checked);
        sync();
        cb.addEventListener('change', sync);
        chip.addEventListener('click', e => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'I') return;
            e.preventDefault();
            cb.checked = !cb.checked;
            sync();
        });
    });

    // ---------- New / Edit dispatcher ----------
    window.paOpenNew = () => {
        const activeTab = document.querySelector('[data-pa-tab].active')?.dataset.paTab || 'plantillas';
        if (activeTab === 'automatizaciones') paOpenNewAutomation();
        else paOpenNewTemplate();
    };

    // ---------- Template editor ----------
    const tplForm = document.getElementById('pa-tpl-form');

    function resetTplForm() {
        tplForm.reset();
        document.getElementById('pa-tpl-method').value = 'POST';
        tplForm.action = '{{ route('admin.crm.plantillas.store') }}';
        document.getElementById('pa-tpl-title').textContent = 'Nueva plantilla';
        tplForm.querySelectorAll('input[name="channels[]"]').forEach(cb => { cb.checked = false; cb.dispatchEvent(new Event('change')); });
    }

    window.paOpenNewTemplate = () => {
        resetTplForm();
        paOpenModal('pa-modal-template');
    };

    window.paEditTemplate = async (id) => {
        try {
            const res = await fetch(`${TPL_BASE}/${id}/data`, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) throw new Error('No se pudo cargar la plantilla');
            const t = await res.json();
            resetTplForm();
            document.getElementById('pa-tpl-method').value = 'PUT';
            tplForm.action = `${TPL_BASE}/${id}`;
            document.getElementById('pa-tpl-title').textContent = 'Editar plantilla';
            document.getElementById('pa-tpl-name').value = t.name || '';
            document.getElementById('pa-tpl-category').value = t.category || 'otro';
            document.getElementById('pa-tpl-icon').value = t.icon || 'file';
            document.getElementById('pa-tpl-subject').value = t.subject || '';
            document.getElementById('pa-tpl-body').value = t.body || '';
            const ch = Array.isArray(t.channels) ? t.channels : [];
            tplForm.querySelectorAll('input[name="channels[]"]').forEach(cb => {
                cb.checked = ch.includes(cb.value);
                cb.dispatchEvent(new Event('change'));
            });
            paOpenModal('pa-modal-template');
        } catch (e) {
            alert(e.message);
        }
    };

    window.paInsertVar = (token) => {
        const ta = document.getElementById('pa-tpl-body');
        const start = ta.selectionStart, end = ta.selectionEnd;
        ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = start + token.length;
    };

    window.paDuplicate = (id) => {
        const f = document.getElementById('pa-duplicate-template-form');
        f.action = `${TPL_BASE}/${id}/duplicate`;
        f.submit();
    };

    window.paDeleteTemplate = (id, name) => {
        if (!confirm(`¿Eliminar la plantilla "${name}"?`)) return;
        const f = document.getElementById('pa-delete-template-form');
        f.action = `${TPL_BASE}/${id}`;
        f.submit();
    };

    // ---------- Preview ----------
    window.paOpenPreview = async (id) => {
        try {
            const res = await fetch(`${TPL_BASE}/${id}/data`, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) throw new Error('No se pudo cargar la plantilla');
            const t = await res.json();
            document.getElementById('pa-prev-title').textContent = t.name;
            const channels = Array.isArray(t.channels) ? t.channels.join(' · ') : '—';
            document.getElementById('pa-prev-meta').textContent = `${t.category} · ${channels}`;
            document.getElementById('pa-prev-subject').textContent = t.subject || '(sin asunto)';
            document.getElementById('pa-prev-frame').src = `${TPL_BASE}/${id}/preview`;
            document.getElementById('pa-test-form').action = `${TPL_BASE}/${id}/test`;
            document.getElementById('pa-prev-edit-btn').onclick = () => {
                paCloseModal('pa-modal-preview');
                paEditTemplate(id);
            };
            paOpenModal('pa-modal-preview');
        } catch (e) {
            alert(e.message);
        }
    };

    // ---------- Automation editor (cadena de fases) ----------
    const autoForm = document.getElementById('pa-auto-form');
    const stepsList = document.getElementById('pa-steps-list');
    const CHANNEL_DEFS = @json($channelDefs);
    const TEMPLATE_OPTS = @json($templatesAll->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values());
    let stepSeq = 0;

    function channelChipsHtml(idx, selected) {
        return Object.entries(CHANNEL_DEFS).map(([key, def]) => {
            const active = selected.includes(key) ? ' active' : '';
            const checked = selected.includes(key) ? ' checked' : '';
            return `<label class="pa-chip${active}">
                <input type="checkbox" name="steps[${idx}][channels][]" value="${key}"${checked}>
                <i class="pi pi-${def.icon}"></i> ${def.label}
            </label>`;
        }).join('');
    }

    function templateOptionsHtml(selectedId) {
        let html = '<option value="">— Sin plantilla —</option>';
        TEMPLATE_OPTS.forEach(t => {
            const sel = String(t.id) === String(selectedId) ? ' selected' : '';
            html += `<option value="${t.id}"${sel}>${t.name.replace(/</g,'&lt;')}</option>`;
        });
        return html;
    }

    // Crea una fase. data = { template_id, delay_minutes, channels[] }
    window.paAddStep = (data = {}) => {
        const idx = stepSeq++;
        const channels = Array.isArray(data.channels) && data.channels.length ? data.channels : ['email'];
        const delay = data.delay_minutes ?? 0;

        const card = document.createElement('div');
        card.className = 'pa-step';
        card.dataset.stepIdx = idx;
        card.innerHTML = `
            <div class="pa-step-head">
                <span class="pa-step-badge"><span class="num"></span> <span class="pa-step-name">Fase</span></span>
                <button type="button" class="pa-step-remove" title="Eliminar fase"><i class="pi pi-trash"></i> Quitar</button>
            </div>
            <div class="pa-step-delay">
                <i class="pi pi-clock"></i>
                <span class="pa-delay-prefix">Esperar</span>
                <input type="number" min="0" max="43200" class="pa-input" name="steps[${idx}][delay_minutes]" value="${delay}">
                <span>minutos <span class="pa-conn pa-delay-suffix"></span></span>
            </div>
            <div class="pa-step-grid">
                <div class="pa-field">
                    <label>Plantilla a enviar</label>
                    <select class="pa-select" name="steps[${idx}][template_id]">${templateOptionsHtml(data.template_id)}</select>
                </div>
                <div class="pa-field">
                    <label>Canales de envío *</label>
                    <div class="flex flex-wrap gap-2">${channelChipsHtml(idx, channels)}</div>
                </div>
            </div>`;

        card.querySelector('.pa-step-remove').addEventListener('click', () => {
            card.remove();
            renumberSteps();
        });
        // Wire chip toggles dentro de la fase
        card.querySelectorAll('.pa-chip').forEach(chip => {
            const cb = chip.querySelector('input[type=checkbox]');
            const sync = () => chip.classList.toggle('active', cb.checked);
            cb.addEventListener('change', sync);
            chip.addEventListener('click', e => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'I') return;
                e.preventDefault();
                cb.checked = !cb.checked;
                sync();
            });
        });

        stepsList.appendChild(card);
        renumberSteps();
    };

    function renumberSteps() {
        const cards = [...stepsList.querySelectorAll('.pa-step')];
        cards.forEach((card, i) => {
            card.querySelector('.num').textContent = i + 1;
            card.querySelector('.pa-step-name').textContent = i === 0 ? 'Fase inicial' : `Fase ${i + 1}`;
            const prefix = card.querySelector('.pa-delay-prefix');
            const suffix = card.querySelector('.pa-delay-suffix');
            if (i === 0) {
                prefix.textContent = 'Esperar';
                suffix.textContent = 'tras el disparo del evento';
            } else {
                prefix.textContent = 'Esperar';
                suffix.textContent = 'tras la fase anterior';
            }
            // No permitir eliminar si solo queda una fase
            card.querySelector('.pa-step-remove').style.display = cards.length <= 1 ? 'none' : '';
        });
    }

    function setSteps(steps) {
        stepsList.innerHTML = '';
        stepSeq = 0;
        const list = Array.isArray(steps) && steps.length ? steps : [{ delay_minutes: 0, channels: ['email'] }];
        list.forEach(s => paAddStep(s));
    }

    function resetAutoForm() {
        autoForm.reset();
        document.getElementById('pa-auto-method').value = 'POST';
        autoForm.action = '{{ route('admin.crm.automatizaciones.store') }}';
        document.getElementById('pa-auto-title').textContent = 'Nueva automatización';
        document.getElementById('pa-auto-active').checked = true;
        document.getElementById('pa-auto-active').dispatchEvent(new Event('change'));
        setSteps([{ delay_minutes: 0, channels: ['email'] }]);
    }

    window.paOpenNewAutomation = () => {
        resetAutoForm();
        paOpenModal('pa-modal-automation');
    };

    window.paEditAutomation = async (id) => {
        try {
            const res = await fetch(`${AUTO_BASE}/${id}/data`, { headers: { 'Accept': 'application/json' }});
            if (!res.ok) throw new Error('No se pudo cargar la automatización');
            const a = await res.json();
            resetAutoForm();
            document.getElementById('pa-auto-method').value = 'PUT';
            autoForm.action = `${AUTO_BASE}/${id}`;
            document.getElementById('pa-auto-title').textContent = 'Editar automatización';
            document.getElementById('pa-auto-name').value = a.name || '';
            document.getElementById('pa-auto-description').value = a.description || '';
            document.getElementById('pa-auto-trigger').value = a.trigger_event || '';
            document.getElementById('pa-auto-active').checked = !!a.is_active;
            document.getElementById('pa-auto-active').dispatchEvent(new Event('change'));
            setSteps(a.steps);
            paOpenModal('pa-modal-automation');
        } catch (e) {
            alert(e.message);
        }
    };

    window.paRunAutomation = (id) => {
        if (!confirm('¿Ejecutar este flujo ahora?')) return;
        const f = document.getElementById('pa-run-automation-form');
        f.action = `${AUTO_BASE}/${id}/run`;
        f.submit();
    };

    window.paDeleteAutomation = (id, name) => {
        if (!confirm(`¿Eliminar la automatización "${name}"?`)) return;
        const f = document.getElementById('pa-delete-automation-form');
        f.action = `${AUTO_BASE}/${id}`;
        f.submit();
    };

    // Initialize new-btn label
    paSwitchTab(document.querySelector('[data-pa-tab].active')?.dataset.paTab || 'plantillas');
})();
</script>
@endsection
