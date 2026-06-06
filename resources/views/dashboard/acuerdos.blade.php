@extends('layouts.client')
@section('title', __('Acuerdos').' — MAKAI')
@section('page_title', __('Mi propiedad'))
@section('page_breadcrumb', __('Mi propiedad').' · '.__('Acuerdos'))
@php $activeRoute = 'acuerdos'; @endphp

@section('content')
@php
    $reservation = $reservation ?? null;

    /* ---- Only show the payment plan agreement once the operator has sent the proposal ---- */
    $budgetSent = $reservation && ($reservation->isBudgetSent() || $reservation->budget_status === 'approved' || !empty($reservation->budget_observations));
    $budgetAccepted = $reservation && ($reservation->budget_status === 'approved' || in_array($reservation->status, ['contract_signed','signed']));

    // Usar los documentos del controlador
    $pending   = $pending ?? collect();
    $completed = $completed ?? collect();

    /* Budget pseudo-doc (sourced from reservation, not Document) — agregar como colección separada */
    $budgetCollection = collect();
    if ($budgetSent) {
        $pseudoBudget = (object) [
            'pseudo' => true,
            'id' => 'budget-'.$reservation->id,
            'document_type' => 'budget',
            'title' => 'Presupuesto y plan de pagos',
            'status' => $budgetAccepted ? 'approved' : 'pending',
            'created_at' => $reservation->budget_sent_at ?? $reservation->updated_at,
            'signed_at' => null,
            'file_path' => null,
            'metadata' => [
                'observations' => $reservation->budget_observations ?? [],
                'advisor_message' => $reservation->budget_notes,
            ],
        ];
        $budgetCollection->push($pseudoBudget);
    }

    // Usar los documentos del controlador directamente
    $allPending = $pending ?? collect();
    $allCompleted = $completed ?? collect();

    // Filtrar promesa de compraventa: solo mostrar si el plan de pagos está firmado
    $paymentPlanSigned = $reservation ? $reservation->documents->firstWhere('document_type', 'payment_plan')?->status === 'signed' : false;
    
    $allPending = $allPending->filter(function($d) use ($paymentPlanSigned) {
        $type = is_object($d) ? ($d->document_type ?? '') : ($d['document_type'] ?? '');
        if ($type === 'purchase_promise' && !$paymentPlanSigned) {
            return false;
        }
        return true;
    });
    
    $allCompleted = $allCompleted->filter(function($d) use ($paymentPlanSigned) {
        $type = is_object($d) ? ($d->document_type ?? '') : ($d['document_type'] ?? '');
        if ($type === 'purchase_promise' && !$paymentPlanSigned) {
            return false;
        }
        return true;
    });

    $total = $allPending->count() + $allCompleted->count();

    $typeMeta = [
        'budget'           => [__('Presupuesto y plan de pagos'), 'warn', 'pi-calculator'],
        'payment_plan'     => [__('Plan de pagos'),                'ok', 'pi-calendar'],
        'purchase_promise' => [__('Promesa de compraventa'),       'info', 'pi-file'],
        'contract'         => [__('Contrato'),                     'info', 'pi-file'],
    ];

    // Obtener el nombre del admin/agente que configuró el presupuesto
    $advisorName = null;
    if ($reservation && $reservation->budget_configured_by) {
        $configuredBy = \App\Models\User::find($reservation->budget_configured_by);
        $advisorName = $configuredBy ? $configuredBy->name : null;
    }

    $breakdown = $reservation ? \App\Helpers\PaymentPlanHelper::calculatePaymentBreakdown($reservation) : null;

    /* ---- Contratos y planes firmados/aprobados (movido desde Documentos) ---- */
    $userId = auth()->id();
    $reservationDocs = $reservation ? $reservation->documents : collect();
    $userDocs = \App\Models\Document::whereNull('reservation_id')
        ->where(function($q) use ($userId) {
            $q->where('metadata->user_id', $userId)
              ->orWhere('metadata->source', 'register');
        })
        ->get();
    $signedDocs = $reservationDocs->merge($userDocs)->unique('id')->filter(function($d) {
        return in_array($d->status, ['signed', 'approved', 'completed'])
            && in_array($d->document_type, ['payment_plan', 'purchase_promise', 'contract']);
    })->sortByDesc('signed_at');

    $signedTypeLabel = [
        'payment_plan'     => __('Plan de pagos'),
        'purchase_promise' => __('Promesa de compraventa'),
        'contract'         => __('Contrato'),
    ];
    $signedStatusPill = [
        'signed'    => [__('FIRMADO'),    'ok'],
        'approved'  => [__('APROBADO'),   'ok'],
        'completed' => [__('COMPLETADO'), 'ok'],
    ];
@endphp

@push('styles')
<style>
    .acm-step-pill { display:flex; align-items:center; gap:6px; font-size:11px; color:#a3a3a3; font-weight:600; }
    .acm-step-pill .num { width:20px; height:20px; border-radius:999px; background:#eaecf0; color:#717784; font-size:10px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }
    .acm-step-pill.is-active { color:#e16614; }
    .acm-step-pill.is-active .num { background:#fa7319; color:#fff; }
    .acm-step-pill.is-done { color:#1daf61; }
    .acm-step-pill.is-done .num { background:#1fc16b; color:#fff; }

    .acm-tabs { display:none; gap:6px; padding:4px; background:#f5f7fa; border-radius:10px; }
    .acm-tab {
        flex:1; padding:7px 10px; border-radius:8px;
        background:transparent; border:none; cursor:pointer;
        font-size:12px; font-weight:600; color:#717784;
        transition: background .15s, color .15s;
    }
    .acm-tab:hover { color:#222530; }
    .acm-tab.active { background:#fff; color:#222530; box-shadow:0 1px 2px rgba(10,13,20,.06); }

    .acm-panel { display:none; }
    .acm-panel.active { display:block; }

    #acm-sig-canvas {
        width: 100%; height: 110px;
        background:#fff; border:1px dashed #cacfd8; border-radius:10px;
        cursor: crosshair; touch-action: none;
        display:block;
    }
    .acm-canvas-wrap { position:relative; }
    .acm-canvas-wrap.has-stroke .acm-empty-canvas { display:none; }
    .acm-canvas-wrap.has-stroke #acm-sig-canvas { border-style:solid; border-color:#5c7c68; }
    .acm-empty-canvas {
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        color:#a3a3a3; font-size:11px; pointer-events:none; font-style:italic;
    }

    .acm-toast {
        position:fixed; bottom:24px; left:50%; transform: translateX(-50%);
        background:#171717; color:#fff; padding:10px 18px; border-radius:12px;
        font-size:13px; font-weight:500;
        box-shadow:0 12px 32px -8px rgba(10,13,20,.35);
        z-index:1300; opacity:0; transition: opacity .2s, transform .2s;
        pointer-events:none;
    }
    .acm-toast.show { opacity:1; transform: translateX(-50%) translateY(-4px); }

    .acm-bubble { max-width:90%; padding:9px 12px; border-radius:14px; font-size:12px; line-height:1.45; }
    .acm-bubble .meta { font-size:10px; text-transform:uppercase; letter-spacing:.04em; opacity:.7; margin-bottom:4px; display:flex; align-items:center; gap:4px; }
    .acm-bubble-client { background:#5c7c68; color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
    .acm-bubble-admin  { background:#fff; color:#222530; border:1px solid #eaecf0; align-self:flex-start; border-bottom-left-radius:4px; }
    .acm-bubble-accept { background:#e3f7ec; border:1px solid rgba(31,193,107,.3); color:#1daf61; align-self:flex-end; border-bottom-right-radius:4px; }

    .acm-status-pill {
        display:inline-flex; align-items:center; gap:4px;
        padding:3px 8px; border-radius:999px;
        font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
    }

    .acm-bk-grid { display:grid; gap:8px; grid-template-columns: repeat(3, minmax(0,1fr)); }
    @media (max-width: 760px) { .acm-bk-grid { grid-template-columns: 1fr; } }
    .acm-bk-cell { border:1px solid #eaecf0; border-radius:10px; padding:10px 12px; background:#fff; }
    .acm-bk-cell .lbl { font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:#99a0ae; font-weight:600; }
    .acm-bk-cell .val { font-size:15px; font-weight:700; color:#171717; margin-top:2px; }
    .acm-bk-cell .meta { font-size:10px; color:#717784; margin-top:2px; }
    .acm-bk-cell.total { background:#fff8ec; border-color:#f6dca5; }
    .acm-bk-cell.total .val { color:#b67a06; font-size:17px; }

    /* ---- Document preview: fit-to-width, never horizontal scroll ---- */
    .acm-preview-wrap {
        position:relative;
        width:100%;
        max-width:820px;
        margin:0 auto;
        height:72vh;
        border-radius:12px;
        border:1px solid #e3e6eb;
        background:#fff;
        overflow:hidden;           /* clip the over-wide reserved iframe box */
        box-shadow:0 8px 28px -16px rgba(10,13,20,.25);
    }
    #acm-preview {
        display:block;
        border:0;
        transform-origin: top left;
        background:#fff;
    }
    /* Signature summary card shown on the "Resumen" step */
    .acm-resumen-sig {
        display:flex; align-items:center; gap:12px;
        border:1px solid #e3e6eb; border-radius:12px; background:#fff;
        padding:12px 14px;
    }
    .acm-resumen-sig .sig-thumb {
        width:120px; height:56px; flex:none;
        border:1px solid #eaecf0; border-radius:8px; background:#fafbfc;
        display:flex; align-items:center; justify-content:center; overflow:hidden;
    }
    .acm-resumen-sig .sig-thumb img { max-width:100%; max-height:100%; object-fit:contain; }
</style>
@endpush

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">{{ session('error') }}</div>@endif

    {{-- Header summary --}}
    <div class="px-5 py-4 rounded-2xl bg-ink-100/70 border border-ink-200 flex items-center justify-between flex-wrap gap-2">
        <div>
            <div class="text-[15px] font-bold text-ink-950">{{ trans_choice('{0} :n documentos|{1} :n documento|[2,*] :n documentos', $total, ['n' => $total]) }} · {{ trans_choice('{0} :n pendientes de tu acción|{1} :n pendiente de tu acción|[2,*] :n pendientes de tu acción', $allPending->count(), ['n' => $allPending->count()]) }}</div>
            <div class="text-[12px] text-ink-500 mt-0.5">{{ __('Revisa lo que envió tu asesor, pide cambios o firma. Todo queda registrado.') }}</div>
        </div>
    </div>

    {{-- ============ PENDIENTES ============ --}}
    @if($allPending->isNotEmpty())
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-warn-soft/60 border-b border-warn/15 flex items-center gap-2">
                <i class="pi pi-clock text-warn-dark"></i>
                <div class="text-[14px] font-bold text-warn-dark">{{ __('Requiere tu atención') }}</div>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach($allPending as $doc)
                    @php
                        $docType = is_object($doc) ? ($doc->document_type ?? '') : ($doc['document_type'] ?? '');
                        [$typeLabel, $typeColor, $typeIcon] = $typeMeta[$docType] ?? ['Documento', 'ink-500', 'pi-file'];
                        $createdAt = (is_object($doc) ? ($doc->created_at ?? '') : ($doc['created_at'] ?? '')) ? \Carbon\Carbon::parse(is_object($doc) ? $doc->created_at : $doc['created_at'])->locale('es')->isoFormat('D MMM YYYY') : '';
                        $docTitle = is_object($doc) ? ($doc->title ?? $typeLabel) : ($doc['title'] ?? $typeLabel);
                        $docId = is_object($doc) ? $doc->id : $doc['id'];

                        /* Estado "en espera de cambios": el cliente pidió una revisión y aún
                           no llegó la nueva versión del asesor. */
                        if ($docType === 'budget' || $docType === 'payment_plan') {
                            $docObs = $reservation?->budget_observations ?? [];
                        } else {
                            $docObs = data_get(is_object($doc) ? ($doc->metadata ?? []) : ($doc['metadata'] ?? []), 'observations', []);
                        }
                        $lastDocObs = ! empty($docObs) ? end($docObs) : null;
                        $awaitingChanges = $lastDocObs
                            && (($lastDocObs['from'] ?? '') === 'client')
                            && (($lastDocObs['kind'] ?? null) !== 'accept');
                    @endphp
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-ink-50/40 transition-colors">
                        <span class="w-1 h-12 rounded-full {{ $awaitingChanges ? 'bg-info' : 'bg-warn' }} shrink-0"></span>
                        <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600 shrink-0"><i class="pi {{ $typeIcon }}"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-bold text-ink-950 truncate flex items-center gap-2 flex-wrap">
                                {{ $docTitle }}
                                @if($awaitingChanges)
                                    <span class="acm-status-pill bg-info-soft text-info shrink-0"><i class="pi pi-clock text-[10px]"></i> {{ __('En espera de cambios') }}</span>
                                @endif
                            </div>
                            <div class="text-[12px] text-ink-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1"><span class="dot bg-{{ $typeColor }}"></span> {{ $typeLabel }}</span>
                                @if($advisorName)<span>{{ $advisorName }}</span>@endif
                                @if($advisorName && $createdAt)<span>·</span>@endif
                                <span>{{ $createdAt }}</span>
                            </div>
                            @if($awaitingChanges)
                                <div class="text-[11px] text-info mt-1 flex items-center gap-1">
                                    <i class="pi pi-info-circle text-[10px]"></i> {{ __('Esperando que tu asesor envíe la nueva versión con los cambios solicitados.') }}
                                </div>
                            @endif
                        </div>
                        <button type="button" class="cli-btn {{ $awaitingChanges ? 'cli-btn-ghost' : 'bg-warn text-white border-warn hover:bg-warn-dark' }} px-3 py-2 text-[12px] font-semibold rounded-lg inline-flex items-center gap-2 shrink-0"
                                data-open-acuerdo="{{ $docId }}">
                            @if($awaitingChanges)
                                <i class="pi pi-eye text-[11px]"></i> {{ __('Ver') }}
                            @else
                                <i class="pi pi-pencil text-[11px]"></i> {{ __('Firmar') }}
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ COMPLETADOS ============ --}}
    @if($allCompleted->isNotEmpty())
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-ok-soft/60 border-b border-ok/20 flex items-center gap-2">
                <i class="pi pi-check-circle text-ok-dark"></i>
                <div class="text-[14px] font-bold text-ok-dark">{{ __('Completados') }}</div>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach($allCompleted as $doc)
                    @php
                        $docType = is_object($doc) ? ($doc->document_type ?? '') : ($doc['document_type'] ?? '');
                        [$typeLabel, $typeColor, $typeIcon] = $typeMeta[$docType] ?? ['Documento', 'ink-500', 'pi-file'];
                        $signedAt = (is_object($doc) ? ($doc->signed_at ?? '') : ($doc['signed_at'] ?? '')) ? \Carbon\Carbon::parse(is_object($doc) ? $doc->signed_at : $doc['signed_at'])->locale('es')->isoFormat('D MMM YYYY') : ((is_object($doc) ? ($doc->created_at ?? '') : ($doc['created_at'] ?? '')) ? \Carbon\Carbon::parse(is_object($doc) ? $doc->created_at : $doc['created_at'])->locale('es')->isoFormat('D MMM YYYY') : '');
                        $docTitle = is_object($doc) ? ($doc->title ?? $typeLabel) : ($doc['title'] ?? $typeLabel);
                        $docId = is_object($doc) ? $doc->id : $doc['id'];
                    @endphp
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-ink-50/40 transition-colors">
                        <span class="w-1 h-12 rounded-full bg-ok shrink-0"></span>
                        <div class="w-10 h-10 rounded-lg bg-ok-soft flex items-center justify-center text-ok-dark shrink-0"><i class="pi {{ $typeIcon }}"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-bold text-ink-950 truncate">{{ $docTitle }}</div>
                            <div class="text-[12px] text-ink-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1"><span class="dot bg-ok"></span> {{ $typeLabel }}</span>
                                @if($advisorName)<span>{{ $advisorName }}</span>@endif
                                @if($advisorName && $signedAt)<span>·</span>@endif
                                <span>{{ $signedAt }}</span>
                            </div>
                        </div>
                        <button type="button" class="cli-btn cli-btn-ghost text-[12px] py-2 px-3" data-open-acuerdo="{{ $docId }}">
                            <i class="pi pi-eye text-[11px]"></i> {{ __('Ver') }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($allPending->isEmpty() && $allCompleted->isEmpty())
        <div class="cli-card p-10 text-center">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto"><i class="pi pi-folder-open text-[22px]"></i></div>
            <div class="mt-3 text-[15px] font-bold text-ink-950">{{ __('No tienes acuerdos por revisar') }}</div>
        </div>
    @endif

    {{-- ============ Contratos y planes firmados ============ --}}
    @if($signedDocs->isNotEmpty())
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 flex items-center gap-3 bg-ok-soft/60 border-b border-ok/20">
                <div class="w-8 h-8 rounded-full bg-ok-soft border border-ok/30 flex items-center justify-center text-ok-dark"><i class="pi pi-check-circle"></i></div>
                <div class="flex-1">
                    <div class="text-[14px] font-bold text-ink-950">{{ __('Contratos y planes firmados') }}</div>
                    <div class="text-[11px] text-ink-500">{{ __('Documentos finalizados de tu expediente') }}</div>
                </div>
                <span class="text-[11px] text-ink-500">{{ trans_choice('{0} :count archivos|{1} :count archivo|[2,*] :count archivos', $signedDocs->count(), ['count' => $signedDocs->count()]) }}</span>
            </div>

            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Documento') }}</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Estado') }}</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Firmado') }}</th>
                        <th class="text-right px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach($signedDocs as $d)
                        @php
                            $st = $signedStatusPill[$d->status] ?? [__('COMPLETADO'),'ok'];
                            $when = $d->signed_at ?? $d->updated_at ?? $d->created_at;
                        @endphp
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-10 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-file"></i></div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-950">{{ $d->title ?? ($signedTypeLabel[$d->document_type] ?? __('Documento')) }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $signedTypeLabel[$d->document_type] ?? $d->document_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4"><span class="cli-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="px-3 py-4 text-[12px] text-ink-700">{{ $when ? \Carbon\Carbon::parse($when)->locale(app()->getLocale())->isoFormat(app()->getLocale()==='es' ? 'D MMM YYYY' : 'MMM D, YYYY') : '—' }}</td>
                            <td class="px-3 py-4 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    @if($d->file_path)
                                        <button type="button" data-open-acuerdo="{{ $d->id }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
                                        <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-primary text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> {{ __('Descargar') }}</a>
                                    @else
                                        <span class="text-[11px] text-ink-400">{{ __('Sin archivo') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- =============== MODAL REVIEW + SIGN =============== --}}
<div id="acuerdoModal" class="hidden fixed inset-0 z-[1100] bg-ink-950/55 backdrop-blur-sm" style="display:none;">
    <div class="absolute inset-0 flex items-stretch justify-center p-3 sm:p-6">
        <div class="cli-card bg-white w-full max-w-[1180px] my-auto max-h-[94vh] flex flex-col overflow-hidden">

            {{-- Header --}}
            <div class="px-5 py-3 border-b border-ink-100 flex items-center gap-3">
                <div id="acm-title" class="font-display text-[16px] font-semibold text-ink-950 truncate flex-1">{{ __('Documento') }}</div>
                <a id="acm-download" href="#" target="_blank" class="cli-btn cli-btn-ghost text-[12px] py-1.5 px-3"><i class="pi pi-download text-[11px]"></i> {{ __('Descargar') }}</a>
                <button type="button" onclick="closeAcuerdoModal()" class="w-9 h-9 rounded-full border border-ink-200 text-ink-500 hover:bg-ink-50 flex items-center justify-center"><i class="pi pi-times text-[12px]"></i></button>
            </div>

            <div class="flex-1 grid grid-cols-1 lg:grid-cols-[1fr_420px] overflow-hidden">
                {{-- Preview --}}
                <div class="bg-ink-100/60 p-4 sm:p-6 overflow-y-auto overflow-x-hidden">
                    {{-- Resumen: cómo quedará la firma (solo en el paso Resumen) --}}
                    <div id="acm-resumen-banner" class="max-w-[820px] mx-auto mb-3" style="display:none;">
                        <div class="acm-resumen-sig">
                            <div class="sig-thumb"><img id="acm-resumen-sig-img" src="" alt="{{ __('Tu firma') }}"></div>
                            <div class="min-w-0">
                                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Vista previa con tu firma') }}</div>
                                <div class="text-[13px] font-bold text-ink-950 truncate" id="acm-resumen-name">—</div>
                                <div class="text-[11px] text-ink-500" id="acm-resumen-date">—</div>
                            </div>
                            <span class="acm-status-pill bg-ok-soft text-ok-dark ml-auto shrink-0"><i class="pi pi-check-circle text-[10px]"></i> {{ __('Lista para firmar') }}</span>
                        </div>
                    </div>

                    {{-- Budget breakdown card (only for budget docs) --}}
                    <div id="acm-budget-card" class="cli-card bg-white p-4 max-w-[760px] mx-auto mb-4" style="display:none;">
                        <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-500 mb-3">{{ __('Resumen del plan propuesto') }}</div>
                        <div class="acm-bk-grid">
                            <div class="acm-bk-cell">
                                <div class="lbl">{{ __('Pago inicial') }}</div>
                                <div class="val" id="acm-bk-initial">$—</div>
                                <div class="meta" id="acm-bk-initial-meta">—</div>
                            </div>
                            <div class="acm-bk-cell">
                                <div class="lbl">{{ __('Durante construcción') }}</div>
                                <div class="val" id="acm-bk-construction">$—</div>
                                <div class="meta" id="acm-bk-construction-meta">—</div>
                            </div>
                            <div class="acm-bk-cell">
                                <div class="lbl">{{ __('A la entrega') }}</div>
                                <div class="val" id="acm-bk-delivery">$—</div>
                                <div class="meta" id="acm-bk-delivery-meta">—</div>
                            </div>
                            <div class="acm-bk-cell total" style="grid-column: 1 / -1;">
                                <div class="lbl">{{ __('Precio total del inmueble') }}</div>
                                <div class="val" id="acm-bk-total">$—</div>
                                <div class="meta" id="acm-bk-notes" style="display:none;"></div>
                            </div>
                        </div>
                    </div>

                    <div id="acm-preview-fallback" class="cli-card bg-white p-8 max-w-[760px] mx-auto text-center text-ink-500">
                        <i class="pi pi-file text-[48px] text-ink-300"></i>
                        <div class="mt-3 text-[13px]">{{ __('El documento aún no está disponible para previsualizar. Tu asesor lo enviará en breve.') }}</div>
                    </div>
                    <div id="acm-preview-wrap" class="acm-preview-wrap hidden">
                        <iframe id="acm-preview" scrolling="yes"></iframe>
                    </div>
                </div>

                {{-- Right rail --}}
                <aside class="border-l border-ink-100 flex flex-col bg-white">
                    {{-- Top: type + title + meta + steps --}}
                    <div class="px-5 pt-5 pb-3 border-b border-ink-100">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span id="acm-type" class="text-[10px] uppercase font-bold tracking-wider text-warn-dark">{{ __('PRESUPUESTO') }}</span>
                            <span id="acm-status-pill" class="acm-status-pill bg-warn-soft text-warn-dark">{{ __('Pendiente') }}</span>
                        </div>
                        <div class="font-display text-[18px] font-semibold text-ink-950 mt-1" id="acm-fullTitle">{{ __('Documento') }}</div>
                        @if($advisorName)<div class="text-[11px] text-ink-500 mt-1" id="acm-meta">{{ $advisorName }}</div>@endif

                        <div class="mt-4 flex items-center gap-1">
                            <span class="acm-step-pill is-active" data-step="1"><span class="num">1</span> {{ __('Revisar') }}</span>
                            <span class="flex-1 h-px bg-ink-200 mx-1"></span>
                            <span class="acm-step-pill" data-step="2"><span class="num">2</span> {{ __('Firmar') }}</span>
                            <span class="flex-1 h-px bg-ink-200 mx-1"></span>
                            <span class="acm-step-pill" data-step="3"><span class="num">3</span> {{ __('Resumen') }}</span>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="px-5 pt-4">
                        <div class="acm-tabs">
                            <button type="button" class="acm-tab active" data-acm-tab="review">{{ __('Revisar') }}</button>
                            <button type="button" class="acm-tab" data-acm-tab="observe">{{ __('Observaciones') }}</button>
                            <button type="button" class="acm-tab" data-acm-tab="sign" id="acm-tab-sign">{{ __('Firmar') }}</button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

                        {{-- ============ REVIEW PANEL ============ --}}
                        <div class="acm-panel active overflow-y-auto max-h-full" data-acm-panel="review">
                            @if($advisorName)
                            <div id="acm-advisor-block">
                                <div class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mb-2">{{ __('Mensaje de tu asesor') }}</div>
                                <div class="rounded-xl border border-ink-200 bg-ink-50 p-3">
                                    <div class="text-[12px] font-bold text-ink-950">{{ $advisorName }} · <span id="acm-advisor-date" class="font-medium text-ink-500"></span></div>
                                    <div class="text-[12px] text-ink-700 mt-2 leading-relaxed whitespace-pre-line" id="acm-advisor-msg">—</div>
                                </div>
                            </div>
                            @endif

                            <div class="mt-4">
                                <div class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mb-2 flex items-center justify-between">
                                    <span>{{ __('Conversación con tu asesor') }}</span>
                                    <span class="text-ink-400 normal-case font-normal" id="acm-conv-count">{{ __('0 mensajes') }}</span>
                                </div>
                                <div id="acm-history" class="space-y-3 flex flex-col"></div>
                            </div>
                        </div>

                        {{-- ============ OBSERVATION PANEL ============ --}}
                        <div class="acm-panel" data-acm-panel="observe">
                            <div class="text-[12px] text-ink-700 mb-2">
                                {{ __('Si hay algo que necesita ajustarse, cuéntaselo al asesor con tus palabras. Recibirá tu observación y enviará una nueva versión.') }}
                            </div>
                            <label class="text-[11px] uppercase font-bold tracking-wider text-ink-500">{{ __('Mensaje al asesor') }}</label>
                            <textarea id="acm-obs-text" rows="6" class="w-full mt-1 rounded-xl border border-ink-200 px-3 py-2 text-[13px] text-ink-900 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 resize-none" placeholder="{{ __('Ej. El precio de la unidad es distinto al pactado, podrías revisarlo y enviar la versión corregida.') }}"></textarea>
                            <div class="text-[10px] text-ink-400 mt-1 text-right"><span id="acm-obs-count">0</span>/2000</div>
                        </div>

                        {{-- ============ SIGN PANEL ============ --}}
                        <div class="acm-panel" data-acm-panel="sign">
                            <div id="acm-sign-blocked" class="hidden text-[12px] rounded-xl bg-ink-50 border border-ink-200 p-3 text-ink-700 mb-3"></div>

                            <div class="text-[12px] text-ink-700 mb-3">
                                {{ __('Tu firma equivale a una firma manuscrita. Al confirmar, declaras que leíste y aceptas el documento en su versión actual.') }}
                            </div>

                            <label class="text-[11px] uppercase font-bold tracking-wider text-ink-500">{{ __('Nombre completo') }}</label>
                            <input type="text" id="acm-sig-name" class="w-full mt-1 rounded-xl border border-ink-200 px-3 py-2 text-[13px] outline-none focus:border-brand focus:ring-2 focus:ring-brand/20" placeholder="{{ __('Tal como aparece en tu documento') }}">

                            <label class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mt-3 block">{{ __('Firma') }}</label>
                            <div class="acm-canvas-wrap mt-1" id="acm-canvas-wrap">
                                <canvas id="acm-sig-canvas"></canvas>
                                <div class="acm-empty-canvas">{{ __('Firma aquí con el mouse o el dedo') }}</div>
                            </div>
                            <div class="flex items-center justify-between mt-1.5">
                                <button type="button" onclick="acmClearSig()" class="text-[11px] text-ink-500 hover:text-ink-900 font-semibold inline-flex items-center gap-1"><i class="pi pi-refresh text-[10px]"></i> {{ __('Limpiar') }}</button>
                                <span class="text-[10px] text-ink-400">{{ __('Trazo manuscrito · obligatorio') }}</span>
                            </div>

                            <label class="mt-4 flex items-start gap-2 text-[12px] text-ink-700 cursor-pointer">
                                <input type="checkbox" id="acm-sig-accept" class="mt-0.5 accent-brand">
                                <span>{{ __('Leí y acepto los términos del documento. Entiendo que esta firma electrónica es legalmente vinculante.') }}</span>
                            </label>

                            <button type="button" id="acm-sig-continue" onclick="acmGoResumen()" class="cli-btn cli-btn-primary w-full mt-4 inline-flex items-center justify-center gap-2 py-2.5">
                                <i class="pi pi-eye text-[11px]"></i> {{ __('Ver resumen con mi firma') }}
                            </button>
                            <button type="button" onclick="acmGoTab('review')" class="cli-btn cli-btn-ghost w-full mt-2 py-2.5">{{ __('Volver') }}</button>
                        </div>

                        {{-- ============ RESUMEN PANEL ============ --}}
                        <div class="acm-panel" data-acm-panel="resumen">
                            <div class="text-[12px] text-ink-700 mb-3">
                                {{ __('Así quedará el documento con tu firma. Revisá la vista previa de la izquierda y confirmá para firmar de forma definitiva.') }}
                            </div>

                            <div class="rounded-xl border border-ink-200 bg-ink-50 p-3 space-y-2">
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="text-ink-500">{{ __('Documento') }}</span>
                                    <span class="font-semibold text-ink-900 text-right" id="acm-resumen-doc">—</span>
                                </div>
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="text-ink-500">{{ __('Firmante') }}</span>
                                    <span class="font-semibold text-ink-900 text-right" id="acm-resumen-signer">—</span>
                                </div>
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="text-ink-500">{{ __('Fecha y hora') }}</span>
                                    <span class="font-semibold text-ink-900 text-right" id="acm-resumen-when">—</span>
                                </div>
                                <div class="pt-2 border-t border-ink-200">
                                    <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400 mb-1">{{ __('Tu firma') }}</div>
                                    <div class="rounded-lg border border-ink-200 bg-white h-[64px] flex items-center justify-center overflow-hidden p-1">
                                        <img id="acm-resumen-panel-img" src="" alt="{{ __('Firma') }}" class="max-h-full max-w-full object-contain">
                                    </div>
                                </div>
                            </div>

                            <div class="text-[11px] text-ink-500 mt-3 flex items-start gap-2">
                                <i class="pi pi-shield text-[12px] mt-0.5"></i>
                                <span>{{ __('Al confirmar, tu firma electrónica queda registrada con fecha, hora y dirección IP como evidencia legal.') }}</span>
                            </div>

                            <button type="button" id="acm-sig-confirm" onclick="submitSignAcuerdo()" class="cli-btn cli-btn-primary w-full mt-4 inline-flex items-center justify-center gap-2 py-2.5">
                                <i class="pi pi-check text-[11px]"></i> {{ __('Firmar y confirmar') }}
                            </button>
                            <button type="button" onclick="acmGoTab('sign')" class="cli-btn cli-btn-ghost w-full mt-2 py-2.5">{{ __('Volver a editar mi firma') }}</button>
                        </div>
                    </div>

                    {{-- Footer actions (only visible on Revisar tab) --}}
                    <div id="acm-footer" class="px-5 py-3 border-t border-ink-100 grid grid-cols-2 gap-2 bg-white">
                        <button type="button" id="acm-btn-observe" onclick="acmGoTab('observe')" class="cli-btn border border-ink-200 text-ink-700 bg-white hover:bg-ink-50 py-2.5 inline-flex items-center justify-center gap-2"><i class="pi pi-comment text-[12px]"></i> {{ __('Solicitar cambios') }}</button>
                        <button type="button" id="acm-btn-primary" class="cli-btn cli-btn-primary py-2.5 inline-flex items-center justify-center gap-2"></button>
                    </div>

                    {{-- Footer actions for the Observación tab (pinned, 50% / 50%) --}}
                    <div id="acm-footer-observe" class="px-5 py-3 border-t border-ink-100 grid grid-cols-2 gap-2 bg-white" style="display:none;">
                        <button type="button" onclick="acmGoTab('review')" class="cli-btn cli-btn-ghost py-2.5 inline-flex items-center justify-center gap-2">{{ __('Cancelar') }}</button>
                        <button type="button" id="acm-obs-send" onclick="submitAcuerdoObservation()" class="cli-btn cli-btn-primary py-2.5 inline-flex items-center justify-center gap-2"><i class="pi pi-send text-[11px]"></i> {{ __('Enviar') }}</button>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<div id="acm-toast" class="acm-toast" role="status" aria-live="polite"></div>

@push('scripts')
@php
$acceptBudgetUrl = $reservation ? route('dashboard.budget.accept', $reservation->id) : null;
$obsBudgetUrl    = $reservation ? route('dashboard.budget.observation', $reservation->id) : null;

$__acuerdosData = $allPending->merge($allCompleted)->merge($signedDocs)->unique('id')->map(function($d) use ($typeMeta, $advisorName, $reservation, $breakdown, $acceptBudgetUrl, $obsBudgetUrl) {
    [$typeLabel] = $typeMeta[$d['document_type'] ?? ''] ?? ['Documento'];

    $isBudget   = ($d['document_type'] ?? '') === 'budget';
    $isPayment  = ($d['document_type'] ?? '') === 'payment_plan';
    $isContract = in_array($d['document_type'] ?? '', ['purchase_promise','contract']);
    $isPromise  = ($d['document_type'] ?? '') === 'purchase_promise';

    /* Observations source */
    if ($isBudget) {
        $observations = $reservation?->budget_observations ?? [];
        $advisorMsg = $reservation?->budget_notes;
        $accepted = $reservation && ($reservation->budget_status === 'approved' || in_array($reservation->status, ['contract_signed','signed']));
        $signed = $accepted;   // budget is "accepted", not signed — but in lifecycle terms, this is the final state for it
        $previewUrl = null;
        $downloadUrl = null;
    } elseif ($isPayment) {
        // payment_plan shares the conversation log with the budget — both admin CRM and the
        // simple client partial read/write from reservation.budget_observations
        $observations = $reservation?->budget_observations ?? [];
        $advisorMsg = $reservation?->budget_notes;
        $accepted = !empty(data_get($d['metadata'] ?? [], 'accepted_at'));
        $signed = in_array($d['status'] ?? '', ['signed','approved']);
        $docId = $d['id'] ?? '';
        $previewUrl = ($d['file_path'] ?? '') ? route('documents.preview', $docId) : null;
        $downloadUrl = ($d['file_path'] ?? '') ? route('documents.download', $docId) : null;
    } else {
        $observations = data_get($d['metadata'] ?? [], 'observations', []);
        $advisorMsg = data_get($d['metadata'] ?? [], 'advisor_message');
        $accepted = !empty(data_get($d['metadata'] ?? [], 'accepted_at'));
        $signed = in_array($d['status'] ?? '', ['signed','approved','completed']);
        $docId = $d['id'] ?? '';
        $previewUrl = ($d['file_path'] ?? '') ? route('documents.preview', $docId) : null;
        $downloadUrl = ($d['file_path'] ?? '') ? route('documents.download', $docId) : null;
    }

    /* Endpoints */
    $docId = $d['id'] ?? '';
    $obsUrl    = ($isBudget || $isPayment) ? $obsBudgetUrl : route('dashboard.contract.observation', $docId);
    $acceptUrl = $isBudget ? $acceptBudgetUrl : ($isContract ? route('dashboard.contract.accept', $docId) : null);
    $signUrl   = (!$isBudget) ? route('documents.sign', $docId) : null;

    /* Status label */
    $lastObs = ! empty($observations) ? end($observations) : null;
    $awaitingAdmin = $lastObs
        && (($lastObs['from'] ?? '') === 'client')
        && (($lastObs['kind'] ?? null) !== 'accept');

    if ($signed) $statusLabel = ($isBudget ? 'Aceptado' : 'Firmado');
    elseif ($accepted) $statusLabel = 'Aceptado · pendiente firma';
    elseif ($awaitingAdmin) $statusLabel = 'Pendiente de respuesta del asesor';
    else $statusLabel = 'Pendiente de tu respuesta';

    /* Can-accept / can-sign / can-observe
       La promesa de compraventa se firma directamente: la firma implica la aceptación,
       por lo que no tiene paso previo de "Aceptar". */
    $canAccept  = !$accepted && !$signed && ($isBudget || ($isContract && !$isPromise));
    $canSign    = !$signed && ($isPayment || $isPromise || ($isContract && $accepted));
    $canObserve = !$accepted && !$signed;

    /* Sign block reason (when sign disabled) */
    $signBlocked = null;
    if (!$signed && $isContract) {
        $planDoc = $reservation?->documents->firstWhere('document_type', 'payment_plan');
        if (!$planDoc || !in_array($planDoc->status, ['signed','approved'])) {
            $signBlocked = 'Primero tenés que firmar el plan de pagos. Una vez firmado, podrás continuar con este contrato.';
        } elseif (!$isPromise && !$accepted) {
            $signBlocked = 'Aceptá el contrato antes de firmarlo (botón "Aceptar contrato" en Revisar).';
        }
    }

    /* Payment breakdown (only for budget / payment_plan) */
    $bk = null;
    if (($isBudget || $isPayment) && $reservation && $breakdown) {
        $bk = [
            'initial'           => (float) ($breakdown['pago_inicial'] ?? 0),
            'initial_meta'      => ($reservation->payment_initial_percentage ?? 0).'% + $'.number_format((float)($reservation->legal_costs ?? 0)).' legales',
            'construction'      => (float) ($breakdown['pago_construccion'] ?? 0),
            'construction_meta' => ($reservation->payment_construction_percentage ?? 0).'%'
                . (($reservation->payment_installments ?? 0) > 0
                    ? ' · '.$reservation->payment_installments.' cuotas de $'.number_format((float)($breakdown['cuota'] ?? 0))
                    : ''),
            'delivery'          => (float) ($breakdown['pago_entrega'] ?? 0),
            'delivery_meta'     => ($reservation->payment_delivery_percentage ?? 0).'%',
            'total'             => (float) ($reservation->unit_price ?? 0),
            'notes'             => $reservation->budget_notes,
        ];
    }

    return [
        'id'           => (string) ($d['id'] ?? ''),
        'doc_type'     => $d['document_type'] ?? '',
        'title'        => $d['title'] ?? $typeLabel,
        'type_label'   => strtoupper($typeLabel),
        'preview_url'  => $previewUrl,
        'download_url' => $downloadUrl,
        'status_label' => $statusLabel,
        'signed'       => $signed,
        'accepted'     => $accepted,
        'awaiting_admin' => $awaitingAdmin,
        'created'      => ($d['created_at'] ?? '') ? \Carbon\Carbon::parse($d['created_at'])->locale('es')->isoFormat('D MMM YYYY · h:mm A') : '',
        'advisor'      => $advisorName,
        'advisor_msg'  => $advisorMsg ?: 'Te dejo este documento para que lo revises. Cualquier consulta, me avisás por el chat.',
        'observations' => collect($observations)->map(function($o) {
            return [
                'from'    => $o['from'] ?? 'admin',
                'author'  => $o['author'] ?? null,
                'message' => $o['message'] ?? ($o['text'] ?? ''),
                'kind'    => $o['kind'] ?? null,
                'at'      => isset($o['at']) ? \Carbon\Carbon::parse($o['at'])->locale('es')->isoFormat('D MMM YYYY · h:mm A') : '',
            ];
        })->values(),
        'breakdown'    => $bk,
        'obs_url'      => $obsUrl,
        'accept_url'   => $acceptUrl,
        'sign_url'     => $signUrl,
        'can_accept'   => $canAccept,
        'can_sign'     => $canSign,
        'can_observe'  => $canObserve,
        'sign_blocked' => $signBlocked,
    ];
})->values();
@endphp
<script>
window.__acuerdos = @json($__acuerdosData);
let acmCurrentDoc = null;

function getCsrfToken() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'; }
function fmt$(n) { return '$' + Number(n || 0).toLocaleString('en-US'); }

function acmToast(msg, type) {
    const t = document.getElementById('acm-toast');
    t.textContent = msg;
    t.style.background = type === 'err' ? '#fb3748' : (type === 'ok' ? '#1daf61' : '#171717');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2600);
}

function acmGoTab(tabName) {
    document.querySelectorAll('.acm-tab').forEach(t => t.classList.toggle('active', t.dataset.acmTab === tabName));
    document.querySelectorAll('.acm-panel').forEach(p => p.classList.toggle('active', p.dataset.acmPanel === tabName));
    document.getElementById('acm-footer').style.display = tabName === 'review' ? '' : 'none';
    document.getElementById('acm-footer-observe').style.display = tabName === 'observe' ? '' : 'none';

    // The "con firma" preview only lives on the Resumen step.
    if (tabName === 'resumen') {
        acmBuildResumen();
    } else {
        document.getElementById('acm-resumen-banner').style.display = 'none';
        acmRemoveSigPreview();
    }

    // Steps state — 1 Revisar · 2 Firmar · 3 Resumen
    const steps = document.querySelectorAll('.acm-step-pill');
    const doc = acmCurrentDoc;
    steps.forEach(s => s.classList.remove('is-active','is-done'));
    if (!doc) return;

    if (doc.signed) {
        steps.forEach(s => s.classList.add('is-done'));
    } else if (tabName === 'resumen') {
        steps[0].classList.add('is-done');
        steps[1].classList.add('is-done');
        steps[2].classList.add('is-active');
    } else if (tabName === 'sign') {
        steps[0].classList.add('is-done');
        steps[1].classList.add('is-active');
    } else {
        steps[0].classList.add('is-active');
    }

    if (tabName === 'sign') setTimeout(acmInitCanvas, 50);
    setTimeout(acmFitPreview, 30);
}

/* ---------- Resumen step: preview the document WITH the signature ---------- */
const ACM_DOC_W = 794;  // A4 width @96dpi (210mm) — matches the printable templates
function acmFitPreview() {
    const wrap = document.getElementById('acm-preview-wrap');
    const ifr  = document.getElementById('acm-preview');
    if (!wrap || !ifr || wrap.classList.contains('hidden')) return;
    const avail = wrap.clientWidth;
    if (!avail) return;
    const scale = Math.min(1, avail / ACM_DOC_W);
    ifr.style.width     = ACM_DOC_W + 'px';
    ifr.style.height    = Math.round(wrap.clientHeight / scale) + 'px';
    ifr.style.transform = 'scale(' + scale + ')';
}

function acmRemoveSigPreview() {
    try {
        const doc = document.getElementById('acm-preview')?.contentDocument;
        doc?.querySelectorAll('[data-sig-preview]')?.forEach(n => n.remove());
    } catch (e) { /* cross-origin / not ready */ }
}

function acmInjectSigPreview(sigData) {
    try {
        const doc = document.getElementById('acm-preview')?.contentDocument;
        if (!doc) return;
        acmRemoveSigPreview();
        const box = doc.querySelector('.sig-box');
        if (box && !box.querySelector('img[data-signature]')) {
            const img = doc.createElement('img');
            img.setAttribute('data-sig-preview', '1');
            img.src = sigData;
            img.style.cssText = 'max-height:46px;max-width:200px;object-fit:contain;display:block;margin:2px auto 0;';
            box.innerHTML = '';
            box.appendChild(img);
            try { box.scrollIntoView({ behavior:'smooth', block:'center' }); } catch (e) {}
        }
    } catch (e) { /* fallback: the banner card already shows the signature */ }
}

function acmBuildResumen() {
    const canvas = document.getElementById('acm-sig-canvas');
    const sigData = canvas.toDataURL('image/png');
    const name = document.getElementById('acm-sig-name').value.trim();
    const whenStr = new Date().toLocaleString('es-AR', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) + ' hs';

    // Banner over the live preview
    document.getElementById('acm-resumen-sig-img').src = sigData;
    document.getElementById('acm-resumen-name').textContent = name || '—';
    document.getElementById('acm-resumen-date').textContent = whenStr;
    document.getElementById('acm-resumen-banner').style.display = '';

    // Summary panel on the right rail
    document.getElementById('acm-resumen-doc').textContent    = acmCurrentDoc?.title || '—';
    document.getElementById('acm-resumen-signer').textContent = name || '—';
    document.getElementById('acm-resumen-when').textContent   = whenStr;
    document.getElementById('acm-resumen-panel-img').src      = sigData;

    // Inject the drawn signature into the document preview itself
    acmInjectSigPreview(sigData);
}

function acmGoResumen() {
    const name = document.getElementById('acm-sig-name').value.trim();
    const accept = document.getElementById('acm-sig-accept').checked;
    if (name.length < 3) { acmToast('Escribí tu nombre completo.', 'err'); return; }
    if (!acmHasStroke)   { acmToast('Falta tu firma manuscrita.', 'err'); return; }
    if (!accept)         { acmToast('Aceptá los términos para continuar.', 'err'); return; }
    acmGoTab('resumen');
}

function openAcuerdoModal(id) {
    const doc = (window.__acuerdos || []).find(x => String(x.id) === String(id));
    if (!doc) return;
    acmCurrentDoc = doc;

    const m = document.getElementById('acuerdoModal');

    document.getElementById('acm-title').textContent     = doc.title;
    document.getElementById('acm-fullTitle').textContent = doc.title;
    document.getElementById('acm-type').textContent      = doc.type_label;
    document.getElementById('acm-meta').textContent      = `${doc.advisor} · ${doc.created || ''}`;
    document.getElementById('acm-advisor-msg').textContent  = doc.advisor_msg;
    document.getElementById('acm-advisor-date').textContent = doc.created || '';

    // Status pill
    const pill = document.getElementById('acm-status-pill');
    pill.textContent = doc.status_label;
    pill.className = 'acm-status-pill ' + (
        doc.signed ? 'bg-ok-soft text-ok-dark' :
        doc.accepted ? 'bg-info-soft text-info' :
        doc.awaiting_admin ? 'bg-info-soft text-info' :
        'bg-warn-soft text-warn-dark'
    );

    // Download button
    const dl = document.getElementById('acm-download');
    if (doc.download_url) {
        dl.href = doc.download_url;
        dl.style.display = '';
    } else {
        dl.style.display = 'none';
    }

    // Budget breakdown card
    const bk = doc.breakdown;
    const bkCard = document.getElementById('acm-budget-card');
    if (bk) {
        bkCard.style.display = '';
        document.getElementById('acm-bk-initial').textContent      = fmt$(bk.initial);
        document.getElementById('acm-bk-initial-meta').textContent = bk.initial_meta || '';
        document.getElementById('acm-bk-construction').textContent = fmt$(bk.construction);
        document.getElementById('acm-bk-construction-meta').textContent = bk.construction_meta || '';
        document.getElementById('acm-bk-delivery').textContent     = fmt$(bk.delivery);
        document.getElementById('acm-bk-delivery-meta').textContent = bk.delivery_meta || '';
        document.getElementById('acm-bk-total').textContent        = fmt$(bk.total);
        const notes = document.getElementById('acm-bk-notes');
        if (bk.notes) { notes.style.display = ''; notes.textContent = 'Notas: ' + bk.notes; }
        else notes.style.display = 'none';
    } else {
        bkCard.style.display = 'none';
    }

    // Preview iframe — auto-generates via /documents/{id}/preview if needed
    const iframe = document.getElementById('acm-preview');
    const wrap   = document.getElementById('acm-preview-wrap');
    const fb     = document.getElementById('acm-preview-fallback');
    document.getElementById('acm-resumen-banner').style.display = 'none';
    if (doc.preview_url) {
        iframe.onload = () => acmFitPreview();
        iframe.src = doc.preview_url;
        wrap.classList.remove('hidden');
        fb.classList.add('hidden');
        setTimeout(acmFitPreview, 80);
    } else {
        iframe.src = ''; wrap.classList.add('hidden');
        fb.classList.remove('hidden');
        // For budget, the breakdown card is the "preview"
        if (bk) fb.classList.add('hidden');
    }

    // History
    const hist = document.getElementById('acm-history');
    hist.innerHTML = '';
    const obs = doc.observations || [];
    document.getElementById('acm-conv-count').textContent = obs.length + ' ' + (obs.length === 1 ? 'mensaje' : 'mensajes');
    if (obs.length === 0) {
        hist.innerHTML = `<div class="text-[11px] text-ink-400">Sin actividad registrada todavía.</div>`;
    } else {
        obs.forEach(o => {
            const isClient = (o.from || '') === 'client';
            const isAccept = (o.kind || '') === 'accept';
            const cls = isAccept ? 'acm-bubble-accept' : (isClient ? 'acm-bubble-client' : 'acm-bubble-admin');
            const author = o.author || (isClient ? 'Vos' : doc.advisor);
            const text = (o.message || '').replace(/</g,'&lt;').replace(/\n/g,'<br>');
            const icon = isAccept ? '<i class="pi pi-check-circle text-[10px]"></i> ' : '';
            const wrap = document.createElement('div');
            wrap.style.display = 'flex';
            wrap.style.justifyContent = (isClient || isAccept) ? 'flex-end' : 'flex-start';
            wrap.innerHTML = `
                <div class="acm-bubble ${cls}">
                    <div class="meta">${icon}${author} · ${o.at || ''}</div>
                    <div>${text}</div>
                </div>`;
            hist.appendChild(wrap);
        });
        hist.scrollTop = hist.scrollHeight;
    }

    // Sign blocked reason
    const blockedEl = document.getElementById('acm-sign-blocked');
    if (doc.sign_blocked) { blockedEl.textContent = doc.sign_blocked; blockedEl.classList.remove('hidden'); }
    else blockedEl.classList.add('hidden');

    // Footer primary button
    const primary = document.getElementById('acm-btn-primary');
    const observe = document.getElementById('acm-btn-observe');
    const signTab = document.getElementById('acm-tab-sign');

    observe.style.display = doc.can_observe ? '' : 'none';
    signTab.style.display = (doc.can_sign || doc.signed) ? '' : 'none';

    if (doc.signed) {
        primary.innerHTML = '<i class="pi pi-check-circle text-[12px]"></i> ' + (doc.doc_type === 'budget' ? 'Plan aceptado' : 'Firmado');
        primary.disabled = true; primary.style.opacity = '.7'; primary.style.pointerEvents = 'none';
        primary.classList.remove('cli-btn-primary'); primary.classList.add('bg-ok-soft','text-ok-dark','border','border-ok/30');
    } else if (doc.can_accept) {
        primary.innerHTML = '<i class="pi pi-check text-[12px]"></i> ' + (doc.doc_type === 'budget' ? 'Aceptar plan' : 'Aceptar contrato');
        primary.disabled = false; primary.style.opacity = ''; primary.style.pointerEvents = '';
        primary.classList.add('cli-btn-primary'); primary.classList.remove('bg-ok-soft','text-ok-dark','border','border-ok/30');
        primary.onclick = submitAcceptAcuerdo;
    } else if (doc.can_sign) {
        primary.innerHTML = '<i class="pi pi-pencil text-[12px]"></i> Firmar documento';
        primary.disabled = false; primary.style.opacity = ''; primary.style.pointerEvents = '';
        primary.classList.add('cli-btn-primary'); primary.classList.remove('bg-ok-soft','text-ok-dark','border','border-ok/30');
        primary.onclick = () => acmGoTab('sign');
    } else {
        primary.style.display = 'none';
    }

    // Reset inputs
    document.getElementById('acm-obs-text').value = '';
    document.getElementById('acm-obs-count').textContent = '0';
    document.getElementById('acm-sig-name').value = '';
    document.getElementById('acm-sig-accept').checked = false;
    acmClearSig();

    // Reset to review tab
    acmGoTab('review');

    m.classList.remove('hidden');
    m.style.display = '';
    document.body.style.overflow = 'hidden';
}

function closeAcuerdoModal() {
    const m = document.getElementById('acuerdoModal');
    m.classList.add('hidden'); m.style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('acm-preview').src = '';
    acmCurrentDoc = null;
}

/* ---------- Observation submission ---------- */
document.getElementById('acm-obs-text').addEventListener('input', e => {
    if (e.target.value.length > 2000) e.target.value = e.target.value.slice(0, 2000);
    document.getElementById('acm-obs-count').textContent = e.target.value.length;
});

function submitAcuerdoObservation() {
    if (!acmCurrentDoc || !acmCurrentDoc.obs_url) return;
    const txt = document.getElementById('acm-obs-text').value.trim();
    if (txt.length < 5) { acmToast('Contanos al menos en una frase qué necesitás ajustar.', 'err'); return; }
    const btn = document.getElementById('acm-obs-send');
    btn.disabled = true; const original = btn.innerHTML;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner text-[11px]"></i> Enviando…';

    const fd = new FormData();
    fd.append('message', txt);
    fd.append('observation', txt);
    fd.append('_token', getCsrfToken());

    fetch(acmCurrentDoc.obs_url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
        credentials: 'same-origin',
    })
    .then(r => r.json().catch(() => ({})))
    .then(d => {
        if (d.success !== false) {
            acmToast(d.message || 'Observación enviada a tu asesor.', 'ok');
            setTimeout(() => window.location.reload(), 900);
        } else {
            acmToast(d.message || 'No se pudo enviar la observación.', 'err');
            btn.disabled = false; btn.innerHTML = original;
        }
    })
    .catch(() => { acmToast('Error de red al enviar la observación.', 'err'); btn.disabled = false; btn.innerHTML = original; });
}

/* ---------- Accept (budget or contract) ---------- */
function submitAcceptAcuerdo() {
    if (!acmCurrentDoc || !acmCurrentDoc.accept_url) return;
    const btn = document.getElementById('acm-btn-primary');
    btn.disabled = true; const original = btn.innerHTML;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner text-[12px]"></i> Procesando…';

    const fd = new FormData();
    fd.append('_token', getCsrfToken());

    fetch(acmCurrentDoc.accept_url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
        credentials: 'same-origin',
    })
    .then(r => r.json().catch(() => ({})))
    .then(d => {
        if (d.success !== false) {
            acmToast(d.message || 'Aceptado correctamente.', 'ok');
            setTimeout(() => window.location.reload(), 900);
        } else {
            acmToast(d.message || 'No se pudo aceptar.', 'err');
            btn.disabled = false; btn.innerHTML = original;
        }
    })
    .catch(() => { acmToast('Error de red al aceptar.', 'err'); btn.disabled = false; btn.innerHTML = original; });
}

/* ---------- Signature canvas ---------- */
let acmCtx = null, acmDrawing = false, acmHasStroke = false;
function acmInitCanvas() {
    const canvas = document.getElementById('acm-sig-canvas');
    if (!canvas) return;
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    if (canvas.width !== rect.width * ratio) {
        canvas.width  = Math.round(rect.width * ratio);
        canvas.height = Math.round(rect.height * ratio);
    }
    acmCtx = canvas.getContext('2d');
    acmCtx.scale(ratio, ratio);
    acmCtx.lineCap = 'round'; acmCtx.lineJoin = 'round';
    acmCtx.strokeStyle = '#171717'; acmCtx.lineWidth = 2;
}
function acmPt(e, canvas) {
    const r = canvas.getBoundingClientRect();
    const cx = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
    const cy = (e.touches ? e.touches[0].clientY : e.clientY) - r.top;
    return [cx, cy];
}
function acmStart(e) {
    e.preventDefault();
    const canvas = document.getElementById('acm-sig-canvas');
    if (!acmCtx) acmInitCanvas();
    acmDrawing = true; acmHasStroke = true;
    document.getElementById('acm-canvas-wrap').classList.add('has-stroke');
    const [x, y] = acmPt(e, canvas);
    acmCtx.beginPath();
    acmCtx.moveTo(x, y);
}
function acmMove(e) {
    if (!acmDrawing) return;
    e.preventDefault();
    const canvas = document.getElementById('acm-sig-canvas');
    const [x, y] = acmPt(e, canvas);
    acmCtx.lineTo(x, y);
    acmCtx.stroke();
}
function acmEnd() { acmDrawing = false; }

(function bindCanvas() {
    const canvas = document.getElementById('acm-sig-canvas');
    if (!canvas) return;
    canvas.addEventListener('mousedown', acmStart);
    canvas.addEventListener('mousemove', acmMove);
    window.addEventListener('mouseup', acmEnd);
    canvas.addEventListener('touchstart', acmStart, { passive: false });
    canvas.addEventListener('touchmove', acmMove, { passive: false });
    window.addEventListener('touchend', acmEnd);
})();

function acmClearSig() {
    const canvas = document.getElementById('acm-sig-canvas');
    if (canvas && acmCtx) acmCtx.clearRect(0, 0, canvas.width, canvas.height);
    acmHasStroke = false;
    document.getElementById('acm-canvas-wrap')?.classList.remove('has-stroke');
}

function submitSignAcuerdo() {
    if (!acmCurrentDoc || !acmCurrentDoc.sign_url) return;
    const name = document.getElementById('acm-sig-name').value.trim();
    const accept = document.getElementById('acm-sig-accept').checked;
    if (name.length < 3) { acmToast('Escribí tu nombre completo.', 'err'); return; }
    if (!acmHasStroke)   { acmToast('Falta tu firma manuscrita.', 'err'); return; }
    if (!accept)         { acmToast('Aceptá los términos para continuar.', 'err'); return; }

    const canvas = document.getElementById('acm-sig-canvas');
    const sigData = canvas.toDataURL('image/png');

    const btn = document.getElementById('acm-sig-confirm');
    btn.disabled = true; const original = btn.innerHTML;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner text-[11px]"></i> Firmando…';

    const notesPayload = JSON.stringify({
        signer_name: name,
        signature_image: sigData,
        accepted_terms: true,
        timestamp: new Date().toISOString(),
        user_agent: navigator.userAgent,
    });

    const fd = new FormData();
    fd.append('notes', notesPayload);
    fd.append('_token', getCsrfToken());

    fetch(acmCurrentDoc.sign_url, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
        credentials: 'same-origin',
    })
    .then(r => r.json().catch(() => ({})))
    .then(d => {
        if (d.success !== false) {
            document.querySelectorAll('.acm-step-pill').forEach(s => { s.classList.remove('is-active'); s.classList.add('is-done'); });
            acmToast(d.message || 'Documento firmado.', 'ok');
            setTimeout(() => window.location.reload(), 1100);
        } else {
            acmToast(d.message || 'No se pudo firmar el documento.', 'err');
            btn.disabled = false; btn.innerHTML = original;
        }
    })
    .catch(() => { acmToast('Error de red al intentar firmar.', 'err'); btn.disabled = false; btn.innerHTML = original; });
}

document.addEventListener('click', e => {
    const trig = e.target.closest('[data-open-acuerdo]');
    if (trig) {
        e.preventDefault();
        openAcuerdoModal(trig.dataset.openAcuerdo);
    }
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAcuerdoModal(); });
window.addEventListener('resize', () => { if (document.getElementById('acuerdoModal').style.display !== 'none') { acmInitCanvas(); acmFitPreview(); } });
</script>
@endpush
@endsection
