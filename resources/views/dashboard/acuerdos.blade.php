@extends('layouts.client')
@section('title', 'Acuerdos — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Acuerdos')
@php $activeRoute = 'acuerdos'; @endphp

@section('content')
@php
    $reservation = $reservation ?? null;
    $pending     = $pending ?? collect();
    $completed   = $completed ?? collect();
    $total       = $pending->count() + $completed->count();

    $typeMeta = [
        'budget'           => ['Presupuesto', 'warn',  'pi-file-edit'],
        'purchase_promise' => ['Promesa de Compraventa', 'info', 'pi-file'],
        'contract'         => ['Contrato', 'info', 'pi-file'],
        'payment_plan'     => ['Plan de Pagos', 'ok',  'pi-calendar'],
        'kyc'              => ['Identidad', 'ink-500', 'pi-id-card'],
    ];

    $advisor = \App\Models\Agent::where('active', true)->orderBy('id')->first();
    $advisorName = $advisor->name ?? 'Alejandro Morales';
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">{{ session('error') }}</div>@endif

    {{-- Header summary --}}
    <div class="px-5 py-4 rounded-2xl bg-ink-100/70 border border-ink-200">
        <div class="text-[15px] font-bold text-ink-950">{{ $total }} documentos · {{ $pending->count() }} pendientes de firma</div>
    </div>

    {{-- ============ PENDIENTES ============ --}}
    @if($pending->isNotEmpty())
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
                <i class="pi pi-file text-ink-500"></i>
                <div class="text-[14px] font-bold text-ink-700">Requiere tu atención</div>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach($pending as $doc)
                    @php
                        [$typeLabel, $typeColor, $typeIcon] = $typeMeta[$doc->document_type] ?? ['Documento', 'ink-500', 'pi-file'];
                        $createdAt = $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('es')->isoFormat('D MMM YYYY') : '';
                        $version   = data_get($doc->metadata, 'version', 1);
                        $expiresAt = data_get($doc->metadata, 'expires_at');
                        $amount    = data_get($doc->metadata, 'amount') ?? data_get($doc->metadata, 'price') ?? ($doc->document_type === 'budget' && $reservation ? (float) $reservation->unit?->price : null);
                        $amountLabel = data_get($doc->metadata, 'amount_label');
                    @endphp
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-ink-50/40 transition-colors">
                        {{-- Status stripe --}}
                        <span class="w-1 h-12 rounded-full bg-warn shrink-0"></span>
                        {{-- Type icon --}}
                        <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600 shrink-0"><i class="pi {{ $typeIcon }}"></i></div>
                        {{-- Title + meta --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="text-[14px] font-bold text-ink-950 truncate">{{ $doc->title ?? $typeLabel }}</div>
                                @if($version > 1)<span class="cli-pill bg-info-soft text-info">v{{ $version }}</span>@endif
                            </div>
                            <div class="text-[12px] text-ink-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1"><span class="dot bg-{{ $typeColor }}"></span> {{ $typeLabel }}</span>
                                <span>{{ $advisorName }}</span>
                                <span>·</span>
                                <span>{{ $createdAt }}</span>
                                @if($expiresAt)
                                    <span class="text-warn-dark inline-flex items-center gap-1">
                                        <i class="pi pi-clock text-[10px]"></i>
                                        Vence {{ \Carbon\Carbon::parse($expiresAt)->locale('es')->isoFormat('D MMM YYYY') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- Amount --}}
                        @if($amount)
                            <div class="text-right shrink-0 hidden sm:block">
                                <div class="text-[15px] font-bold text-ink-950">${{ number_format((float) $amount, ($amount >= 1000 ? 0 : 2)) }}</div>
                                <div class="text-[10px] text-ink-500">{{ $amountLabel ?? 'Precio total del inmueble' }}</div>
                            </div>
                        @endif
                        {{-- Action --}}
                        <button type="button" class="crm-btn bg-warn text-white border-warn hover:bg-warn-dark px-3 py-2 text-[12px] font-semibold rounded-lg inline-flex items-center gap-2 shrink-0"
                                data-open-acuerdo="{{ $doc->id }}">
                            <i class="pi pi-pencil text-[11px]"></i> Revisar y firma
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ COMPLETADOS ============ --}}
    @if($completed->isNotEmpty())
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-ok-soft/60 border-b border-ok/20 flex items-center gap-2">
                <i class="pi pi-check-circle text-ok-dark"></i>
                <div class="text-[14px] font-bold text-ok-dark">Completados</div>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach($completed as $doc)
                    @php
                        [$typeLabel, $typeColor, $typeIcon] = $typeMeta[$doc->document_type] ?? ['Documento', 'ink-500', 'pi-file'];
                        $signedAt = $doc->signed_at ? \Carbon\Carbon::parse($doc->signed_at)->locale('es')->isoFormat('D MMM YYYY') : ($doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->locale('es')->isoFormat('D MMM YYYY') : '');
                        $amount   = data_get($doc->metadata, 'monthly_amount') ?? data_get($doc->metadata, 'amount');
                        $amountSuffix = data_get($doc->metadata, 'monthly_amount') ? '/mes' : '';
                    @endphp
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-ink-50/40 transition-colors">
                        <span class="w-1 h-12 rounded-full bg-ok shrink-0"></span>
                        <div class="w-10 h-10 rounded-lg bg-ok-soft flex items-center justify-center text-ok-dark shrink-0"><i class="pi {{ $typeIcon }}"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-bold text-ink-950 truncate">{{ $doc->title ?? $typeLabel }}</div>
                            <div class="text-[12px] text-ink-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1"><span class="dot bg-ok"></span> {{ $typeLabel }}</span>
                                <span>{{ $advisorName }}</span>
                                <span>·</span>
                                <span>{{ $signedAt }}</span>
                            </div>
                        </div>
                        @if($amount)
                            <div class="text-right shrink-0 hidden sm:block">
                                <div class="text-[15px] font-bold text-ink-950">${{ number_format((float) $amount, 0) }}{{ $amountSuffix }}</div>
                                <div class="text-[10px] text-ink-500">{{ data_get($doc->metadata, 'installments') ? data_get($doc->metadata, 'installments').' cuotas mensuales' : 'Firmado' }}</div>
                            </div>
                        @endif
                        <a href="{{ route('documents.download', $doc->id) }}" class="cli-btn cli-btn-ghost text-[12px] py-2 px-3" target="_blank">Ver</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($pending->isEmpty() && $completed->isEmpty())
        <div class="cli-card p-10 text-center">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto"><i class="pi pi-folder-open text-[22px]"></i></div>
            <div class="mt-3 text-[15px] font-bold text-ink-950">Aún no tenés acuerdos disponibles</div>
            <p class="text-[12px] text-ink-500 mt-1 max-w-md mx-auto">Cuando tu asesor envíe un presupuesto o contrato, lo verás acá para revisarlo y firmarlo.</p>
        </div>
    @endif
</div>

{{-- =============== MODAL REVIEW + SIGN =============== --}}
<div id="acuerdoModal" class="hidden fixed inset-0 z-[1100] bg-ink-950/55 backdrop-blur-sm" style="display:none;">
    <div class="absolute inset-0 flex items-stretch justify-center p-3 sm:p-6">
        <div class="cli-card bg-white w-full max-w-[1100px] my-auto max-h-[92vh] flex flex-col overflow-hidden">

            {{-- Header --}}
            <div class="px-5 py-3 border-b border-ink-100 flex items-center gap-3">
                <div id="acm-title" class="font-display text-[16px] font-semibold text-ink-950 truncate flex-1">Documento</div>
                <div id="acm-page-info" class="text-[11px] text-ink-500 hidden sm:inline">1 página</div>
                <a id="acm-download" href="#" target="_blank" class="cli-btn cli-btn-ghost text-[12px] py-1.5 px-3"><i class="pi pi-download text-[11px]"></i> Descargar</a>
                <button type="button" onclick="closeAcuerdoModal()" class="w-9 h-9 rounded-full border border-ink-200 text-ink-500 hover:bg-ink-50 flex items-center justify-center"><i class="pi pi-times text-[12px]"></i></button>
            </div>

            <div class="flex-1 grid grid-cols-1 lg:grid-cols-[1fr_360px] overflow-hidden">
                {{-- Preview --}}
                <div class="bg-ink-100/60 p-4 sm:p-6 overflow-auto">
                    <div id="acm-preview-fallback" class="cli-card bg-white p-8 max-w-[680px] mx-auto text-center text-ink-500">
                        <i class="pi pi-file text-[48px] text-ink-300"></i>
                        <div class="mt-3 text-[13px]">Seleccioná un documento para previsualizarlo.</div>
                    </div>
                    <iframe id="acm-preview" class="hidden w-full h-[68vh] bg-white rounded-xl border border-ink-200 mx-auto" style="max-width:760px;"></iframe>
                </div>

                {{-- Right rail --}}
                <aside class="border-l border-ink-100 flex flex-col bg-white">
                    <div class="px-5 pt-5 pb-3 border-b border-ink-100">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-warn-dark" id="acm-type">PRESUPUESTO</div>
                        <div class="font-display text-[18px] font-semibold text-ink-950 mt-1" id="acm-fullTitle">Documento</div>
                        <div class="text-[11px] text-ink-500 mt-1" id="acm-meta">{{ $advisorName }}</div>

                        {{-- Steps --}}
                        <div class="mt-4 flex items-center gap-1 text-[11px]">
                            <span class="acm-step is-active flex items-center gap-1.5 text-warn-dark"><span class="w-5 h-5 rounded-full bg-warn text-white flex items-center justify-center text-[10px] font-bold">1</span> Revisar</span>
                            <span class="flex-1 h-px bg-ink-200 mx-1"></span>
                            <span class="acm-step flex items-center gap-1.5 text-ink-400"><span class="w-5 h-5 rounded-full bg-ink-200 text-ink-500 flex items-center justify-center text-[10px] font-bold">2</span> Firmar</span>
                            <span class="flex-1 h-px bg-ink-200 mx-1"></span>
                            <span class="acm-step flex items-center gap-1.5 text-ink-400"><span class="w-5 h-5 rounded-full bg-ink-200 text-ink-500 flex items-center justify-center text-[10px] font-bold">3</span> Listo</span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                        <div id="acm-advisor-block">
                            <div class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mb-2">Mensaje de tu asesor</div>
                            <div class="rounded-xl border border-ink-200 bg-ink-50 p-3">
                                <div class="text-[12px] font-bold text-ink-950">{{ $advisorName }} · <span id="acm-advisor-date" class="font-medium text-ink-500"></span></div>
                                <div class="text-[12px] text-ink-700 mt-2 leading-relaxed" id="acm-advisor-msg">—</div>
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] uppercase font-bold tracking-wider text-ink-500 mb-2">Historial</div>
                            <div id="acm-history" class="space-y-3 text-[12px] text-ink-700"></div>
                        </div>
                    </div>

                    {{-- Footer actions --}}
                    <div class="px-5 py-3 border-t border-ink-100 grid grid-cols-2 gap-2 bg-white">
                        <button type="button" id="acm-btn-reject" class="cli-btn border border-err text-err bg-white hover:bg-err-soft py-2.5 inline-flex items-center justify-center gap-2"><i class="pi pi-times text-[12px]"></i> Rechazar</button>
                        <button type="button" id="acm-btn-sign" class="cli-btn cli-btn-primary py-2.5 inline-flex items-center justify-center gap-2"><i class="pi pi-pencil text-[12px]"></i> Firmar documento</button>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
$__acuerdosData = $pending->merge($completed)->map(function($d) use ($typeMeta, $advisorName, $reservation) {
    [$typeLabel] = $typeMeta[$d->document_type] ?? ['Documento'];
    $observations = data_get($d->metadata, 'observations', []);
    return [
        'id'        => $d->id,
        'title'     => $d->title ?? $typeLabel,
        'type'      => $d->document_type,
        'type_label'=> strtoupper($typeLabel),
        'file_url'  => $d->file_path ? asset('storage/'.$d->file_path) : null,
        'status'    => $d->status,
        'created'   => $d->created_at?->locale('es')->isoFormat('D MMM YYYY · h:mm A'),
        'advisor'   => $advisorName,
        'advisor_msg' => data_get($d->metadata, 'advisor_message', 'Te dejo este documento para que lo revises. Cualquier consulta, me avisás por el chat.'),
        'observations' => $observations,
        'sign_url'  => route('documents.sign', $d->id),
        'reject_url'=> $d->document_type === 'budget' && $reservation
                        ? route('dashboard.budget.observation', $reservation->id)
                        : route('dashboard.contract.observation', $d->id),
        'download'  => route('documents.download', $d->id),
        'can_sign'  => in_array($d->status, ['pending','generated','awaiting_signature','in_review']),
    ];
})->values();
@endphp
<script>
// Documents data passed from server
window.__acuerdos = @json($__acuerdosData);

function openAcuerdoModal(id) {
    const doc = (window.__acuerdos || []).find(x => String(x.id) === String(id));
    if (!doc) return;
    const m = document.getElementById('acuerdoModal');

    document.getElementById('acm-title').textContent     = doc.title;
    document.getElementById('acm-fullTitle').textContent = doc.title;
    document.getElementById('acm-type').textContent      = doc.type_label;
    document.getElementById('acm-meta').textContent      = `${doc.advisor} · ${doc.created || ''}`;
    document.getElementById('acm-advisor-msg').textContent  = doc.advisor_msg;
    document.getElementById('acm-advisor-date').textContent = doc.created || '';

    const dl = document.getElementById('acm-download');
    dl.href = doc.download; dl.style.display = doc.file_url ? '' : 'none';

    const iframe = document.getElementById('acm-preview');
    const fb     = document.getElementById('acm-preview-fallback');
    if (doc.file_url) {
        iframe.src = doc.file_url;
        iframe.classList.remove('hidden');
        fb.classList.add('hidden');
    } else {
        iframe.src = ''; iframe.classList.add('hidden');
        fb.classList.remove('hidden');
    }

    // History
    const hist = document.getElementById('acm-history');
    hist.innerHTML = '';
    if (Array.isArray(doc.observations) && doc.observations.length) {
        doc.observations.forEach(o => {
            const row = document.createElement('div');
            row.className = 'flex gap-2';
            row.innerHTML = `
                <div class="w-2 h-2 rounded-full bg-info mt-1.5 shrink-0"></div>
                <div>
                    <div class="text-[12px] font-bold text-ink-950">${o.author || doc.advisor} · <span class="font-medium text-ink-500">${o.at || ''}</span></div>
                    <div class="text-[12px] text-ink-700 mt-0.5">${(o.text || '').replace(/</g,'&lt;')}</div>
                </div>`;
            hist.appendChild(row);
        });
    } else {
        hist.innerHTML = `<div class="text-[11px] text-ink-400">Sin actividad registrada todavía.</div>`;
    }

    // Buttons
    const signBtn = document.getElementById('acm-btn-sign');
    const rejBtn  = document.getElementById('acm-btn-reject');
    signBtn.disabled = !doc.can_sign;
    rejBtn.disabled  = !doc.can_sign;
    signBtn.style.opacity = doc.can_sign ? '' : '.5';
    rejBtn.style.opacity  = doc.can_sign ? '' : '.5';
    signBtn.onclick = () => submitSignAcuerdo(doc);
    rejBtn.onclick  = () => submitRejectAcuerdo(doc);

    m.classList.remove('hidden');
    m.style.display = '';
    document.body.style.overflow = 'hidden';
}

function closeAcuerdoModal() {
    const m = document.getElementById('acuerdoModal');
    m.classList.add('hidden'); m.style.display = 'none';
    document.body.style.overflow = '';
}

function getCsrfToken() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'; }

function submitSignAcuerdo(doc) {
    if (!confirm('Confirmás que querés firmar "' + doc.title + '"? Esta acción queda registrada.')) return;
    fetch(doc.sign_url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
        credentials: 'same-origin',
    }).then(r => r.json()).then(d => {
        if (d.success !== false) window.location.reload();
        else alert(d.message || 'No se pudo firmar el documento.');
    }).catch(() => alert('Error de red al intentar firmar.'));
}

function submitRejectAcuerdo(doc) {
    const obs = prompt('Contanos por qué rechazás o querés revisar este documento:');
    if (!obs || obs.trim() === '') return;
    const fd = new FormData();
    fd.append('observation', obs);
    fd.append('_token', getCsrfToken());
    fetch(doc.reject_url, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: fd,
        credentials: 'same-origin',
    }).then(r => r.json()).then(d => {
        if (d.success !== false) window.location.reload();
        else alert(d.message || 'No se pudo enviar la observación.');
    }).catch(() => alert('Error de red al intentar rechazar.'));
}

document.addEventListener('click', e => {
    const trig = e.target.closest('[data-open-acuerdo]');
    if (trig) {
        e.preventDefault();
        openAcuerdoModal(trig.dataset.openAcuerdo);
    }
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAcuerdoModal(); });
</script>
@endpush
@endsection
