@extends('layouts.client')
@section('title', 'Mis Documentos — MAKAI')
@section('page_title', 'Mis documentos')
@section('page_breadcrumb', 'Mi Propiedad · Documentos')
@php $activeRoute = 'documents'; @endphp

@section('content')
@php
    $reservation = $reservation ?? null;
    $userId = auth()->id();
    
    // Get ALL documents: those linked to the reservation AND those uploaded during registration (reservation_id = null, metadata->user_id)
    $reservationDocs = $reservation ? $reservation->documents : collect();
    $userDocs = \App\Models\Document::whereNull('reservation_id')
        ->where('metadata->user_id', $userId)
        ->orWhere('metadata->source', 'register')
        ->get();
    // Merge and deduplicate by id
    $uploaded = $reservationDocs->merge($userDocs)->unique('id');
    
    // Only show system-generated docs that exist (payment_plan, purchase_promise, contract)
    // Include pending documents so clients can see them
    $generatedDocs = $uploaded->whereIn('document_type', ['payment_plan', 'purchase_promise', 'contract']);
    
    // Required upload list applies only to brokers/agencies; buyers submit their KYC
    // through /form (consolidated into a single 'kyc' Document, no separate id_front/id_back uploads).
    $userRole = auth()->user()?->role ?? 'user';
    $isVerifyingRole = in_array($userRole, ['broker', 'agency']);
    $required = $isVerifyingRole ? [
        ['id_front', 'Documento de identidad (Frente)',  'Pasaporte o Cédula de identidad (Frente)',  'id-card'],
        ['id_back',  'Documento de identidad (Reverso)', 'Pasaporte o Cédula de identidad (Reverso)', 'id-card'],
    ] : [];
    $uploadedTypes = $uploaded->pluck('document_type')->all();
    $missing = array_filter($required, fn($r) => ! in_array($r[0], $uploadedTypes));
    
    $statusLabel = ['pending' => ['PENDIENTE REVISIÓN','warn'],'generated' => ['GENERADO','info'],'signed' => ['FIRMADO','ok'],'approved' => ['APROBADO','ok'],'rejected' => ['RECHAZADO','err']];
    $budgetSent = $reservation ? ($reservation->isBudgetSent() || $reservation->budget_status === 'approved') : false;
    $contractSigned = $reservation ? in_array($reservation->status, ['contract_signed', 'signed']) : false;
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div>
        <h2 class="font-display text-[20px] font-semibold text-ink-950 leading-tight">Documentos de tu expediente</h2>
        <p class="text-[13px] text-ink-500 mt-1">Necesitamos verificar tu identidad e ingresos para continuar con el proceso de compra.</p>
    </div>

    {{-- Documentos requeridos --}}
    @if(count($missing) > 0)
    <div class="cli-card overflow-hidden border-err/30 bg-err-soft/40">
        <div class="px-5 py-3 flex items-center gap-3 border-b border-err/20">
            <div class="w-8 h-8 rounded-full bg-err-soft border border-err/30 flex items-center justify-center text-err"><i class="pi pi-file"></i></div>
            <div>
                <div class="text-[14px] font-bold text-ink-950">Documentos requeridos</div>
                <div class="text-[12px] text-ink-500">Completa estos documentos para avanzar tu expediente</div>
            </div>
        </div>
        <div class="divide-y divide-ink-100 bg-white">
            @foreach($missing as $r)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-{{ $r[3] }}"></i></div>
                    <div class="flex-1">
                        <div class="text-[14px] font-semibold text-ink-950">{{ $r[1] }}</div>
                        <div class="text-[12px] text-ink-500">{{ $r[2] }}</div>
                    </div>
                    <button type="button" onclick="document.getElementById('upload-{{ $r[0] }}').click()" class="cli-btn cli-btn-ghost">
                        Subir <i class="pi pi-upload text-[11px]"></i>
                    </button>
                    <form method="POST" action="{{ route('reservations.documents.upload', $reservation->id) }}" enctype="multipart/form-data" class="hidden m-0" id="form-{{ $r[0] }}">
                        @csrf
                        <input type="hidden" name="document_type" value="{{ $r[0] }}">
                        <input type="hidden" name="title" value="{{ $r[1] }}">
                        <input type="file" id="upload-{{ $r[0] }}" name="file" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Plan de Pagos — review + Conforme/Observación cycle --}}
    @if($reservation && ($reservation->isBudgetSent() || ! empty($reservation->budget_observations)))
        @include('dashboard._partials.plan_de_pagos_cliente', ['reservation' => $reservation])
    @endif

    {{-- Promesa de compraventa — only once the payment plan was signed --}}
    @php
        $planSignedDoc = $reservation?->documents->firstWhere('document_type', 'payment_plan');
        $planFullySigned = $planSignedDoc && in_array($planSignedDoc->status, ['signed', 'approved']);
        $promesaDoc = $reservation?->documents->firstWhere('document_type', 'purchase_promise');
    @endphp
    @if($planFullySigned && $promesaDoc)
        @include('dashboard._partials.contrato_cliente', ['document' => $promesaDoc, 'reservation' => $reservation])
    @endif

    {{-- Presupuesto y contratos --}}
    @if($budgetSent)
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ink-50/60 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-file-text"></i></div>
            <div class="text-[14px] font-bold text-ink-950">Documentos del contrato</div>
            <span class="ml-auto text-[11px] text-ink-500">
                @if(!$contractSigned)
                    <a href="{{ route('dashboard.budget', $reservation) }}" class="text-brand font-semibold hover:underline">Revisar presupuesto <i class="pi pi-arrow-right text-[10px]"></i></a>
                @endif
            </span>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Documento</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Estado</th>
                    <th class="text-right px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($generatedDocs as $d)
                    @php $st = $statusLabel[$d->status] ?? ['PENDIENTE','warn']; @endphp
                    <tr class="hover:bg-ink-50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-10 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-file"></i></div>
                                <div>
                                    <div class="text-[13px] font-semibold text-ink-950">{{ $d->title }}</div>
                                    <div class="text-[11px] text-ink-500">{{ $d->document_type === 'payment_plan' ? 'Plan de Pagos' : ($d->document_type === 'purchase_promise' ? 'Promesa de Compraventa' : 'Contrato') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4">{!! $d->status_label !!}</td>
                        <td class="px-3 py-4 text-right">
                            @php
                                $isPaymentPlan  = $d->document_type === 'payment_plan';
                                $planAccepted   = $reservation && ($reservation->budget_status === 'approved' || in_array($reservation->status, ['contract_signed', 'signed']));
                            @endphp
                            <div class="flex items-center gap-2 justify-end">
                                {{-- Payment plan: once the client accepted, allow Descargar + Firmar in parallel. --}}
                                @if($isPaymentPlan)
                                    @if($planAccepted)
                                        <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> Descargar</a>
                                        @if($d->isSigned())
                                            <span class="text-[11px] text-ok-dark font-semibold ml-1"><i class="pi pi-check-circle"></i> Firmado</span>
                                        @elseif($d->isApproved())
                                            <span class="text-[11px] text-ok-dark font-semibold ml-1"><i class="pi pi-check-circle"></i> Aprobado</span>
                                        @else
                                            <button type="button" onclick="signDocument({{ $d->id }})" class="cli-btn cli-btn-primary text-[11px] py-1 px-3"><i class="pi pi-pen text-[10px]"></i> Firmar</button>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-ink-400 inline-flex items-center gap-1" title="Disponible para firmar después de aceptar el plan."><i class="pi pi-lock text-[10px]"></i> Disponible al aceptar</span>
                                    @endif
                                @else
                                    {{-- Promesa de compraventa: only enabled after the plan is signed AND
                                         the client has accepted the promesa via the rich card above. --}}
                                    @php
                                        $isPromesa = $d->document_type === 'purchase_promise';
                                        $promesaAccepted = $isPromesa && ! empty(data_get($d->metadata, 'accepted_at'));
                                    @endphp
                                    @if($isPromesa)
                                        @if($d->isSigned())
                                            @if($d->file_path)
                                                <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> Descargar</a>
                                            @endif
                                            <span class="text-[11px] text-ok-dark font-semibold ml-1"><i class="pi pi-check-circle"></i> Firmado</span>
                                        @elseif(! $planFullySigned)
                                            <span class="text-[11px] text-ink-400 inline-flex items-center gap-1" title="Tenés que firmar el plan de pagos antes."><i class="pi pi-lock text-[10px]"></i> Disponible al firmar el plan</span>
                                        @elseif(! $promesaAccepted)
                                            <span class="text-[11px] text-ink-400 inline-flex items-center gap-1" title="Aceptá la promesa de compraventa desde el card de arriba."><i class="pi pi-lock text-[10px]"></i> Aceptá el contrato arriba</span>
                                        @else
                                            @if($d->file_path)
                                                <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> Descargar</a>
                                            @endif
                                            <button type="button" onclick="signDocument({{ $d->id }})" class="cli-btn cli-btn-primary text-[11px] py-1 px-3"><i class="pi pi-pen text-[10px]"></i> Firmar</button>
                                        @endif
                                    @else
                                        {{-- Generic contract / other doc types --}}
                                        @if($d->file_path)
                                            <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> Descargar</a>
                                        @endif
                                        @if($d->isGenerated())
                                            <button type="button" onclick="signDocument({{ $d->id }})" class="cli-btn cli-btn-primary text-[11px] py-1 px-3"><i class="pi pi-pen text-[10px]"></i> Firmar</button>
                                        @elseif($d->isSigned())
                                            <span class="text-[11px] text-ok-dark font-semibold"><i class="pi pi-check-circle"></i> Firmado</span>
                                        @elseif($d->isApproved())
                                            <span class="text-[11px] text-ok-dark font-semibold"><i class="pi pi-check-circle"></i> Aprobado</span>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-[12px] text-ink-500">
                        Los documentos se generarán cuando aceptes el presupuesto enviado por tu asesor.
                        <br><a href="{{ route('dashboard.budget', $reservation) }}" class="text-brand font-semibold hover:underline mt-2 inline-block">Ver presupuesto &rarr;</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- Documentos subidos (KYC) --}}
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ink-50/60 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-upload"></i></div>
            <div class="text-[14px] font-bold text-ink-950">Documentos de identidad</div>
            <span class="ml-auto text-[11px] text-ink-500">{{ $uploaded->whereIn('document_type', ['id_front','id_back'])->count() }} archivos</span>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Documento</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Estado</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">Subido</th>
                    <th class="px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($uploaded->whereIn('document_type', ['id_front','id_back','kyc']) as $d)
                    @php $st = $statusLabel[$d->status] ?? ['PENDIENTE','warn']; @endphp
                    <tr class="hover:bg-ink-50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-10 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-id-card"></i></div>
                                <div>
                                    <div class="text-[13px] font-semibold text-ink-950">{{ $d->title }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4"><span class="cli-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                        <td class="px-3 py-4 text-[12px] text-ink-700">{{ optional($d->created_at)->format('Y-m-d') }}</td>
                        <td class="px-3 py-4 text-right">
                            @if($d->file_path)
                                <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-[12px] text-ink-500">Aún no has subido documentos de identidad.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sign document modal --}}
    <dialog id="sign-modal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
        <form onsubmit="return submitSign(event)" class="w-[460px] bg-white rounded-2xl overflow-hidden">
            @csrf
            <input type="hidden" id="sign-doc-id" name="document_id" value="">
            <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-pen"></i></div>
                <div class="text-[15px] font-bold text-ink-900 flex-1">Firmar documento</div>
                <button type="button" onclick="document.getElementById('sign-modal').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="px-4 py-3 rounded-xl bg-ink-50 border border-ink-200 text-[12px] text-ink-700">
                    <i class="pi pi-info-circle text-brand"></i> Al firmar este documento, confirmas que has leído y aceptas los términos del contrato.
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Notas (opcional)</label>
                    <textarea id="sign-notes" rows="2" class="cli-input mt-1" placeholder="Cualquier comentario adicional…"></textarea>
                </div>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" id="sign-confirm" required class="w-4 h-4 mt-0.5 accent-brand">
                    <span class="text-[12px] text-ink-600">Confirmo que he leído y acepto los términos del documento.</span>
                </label>
            </div>
            <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
                <button type="button" onclick="document.getElementById('sign-modal').close()" class="cli-btn cli-btn-ghost">Cancelar</button>
                <button type="submit" id="sign-submit" class="cli-btn cli-btn-primary"><i class="pi pi-pen"></i> Firmar documento</button>
            </div>
        </form>
    </dialog>

    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-ink-100/60 border border-ink-200 text-[12px] text-ink-600">
        <i class="pi pi-lock text-ink-500"></i>
        Tus documentos están protegidos con cifrado de extremo a extremo. Solo el equipo legal de Duna Development Group tiene acceso a tus archivos.
        <span class="ml-auto font-semibold text-ink-950">¿Tienes preguntas? <a href="{{ route('dashboard.messages') }}" class="text-brand hover:underline">Contáctanos &rarr;</a></span>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// Auto-poll SignNow when the client returns from signing.
// SignNow redirects with ?signnow_doc={id}; we ping our backend up to 5 times.
(function autoSyncSignNow() {
    const params = new URLSearchParams(window.location.search);
    const docId = params.get('signnow_doc');
    if (!docId) return;

    let attempts = 0;
    const tryPoll = () => {
        attempts++;
        fetch('/documents/' + docId + '/signnow/sync', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.signed) {
                // Remove the query param so we don't re-poll on reload, then refresh
                params.delete('signnow_doc');
                const next = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.location.replace(next);
            } else if (attempts < 5) {
                setTimeout(tryPoll, 2000);
            }
        })
        .catch(() => { if (attempts < 5) setTimeout(tryPoll, 2000); });
    };
    tryPoll();
})();

async function signDocument(docId) {
    document.getElementById('sign-doc-id').value = docId;
    document.getElementById('sign-notes').value = '';
    document.getElementById('sign-confirm').checked = false;
    document.getElementById('sign-submit').disabled = false;
    document.getElementById('sign-submit').innerHTML = '<i class="pi pi-pen"></i> Firmar documento';
    document.getElementById('sign-modal').showModal();
}

// Inline modal shown after a SignNow email invite goes out
function showSignNowSentToast(email, customMessage) {
    let toast = document.getElementById('signnow-sent-modal');
    if (!toast) {
        toast = document.createElement('dialog');
        toast.id = 'signnow-sent-modal';
        toast.className = 'rounded-2xl p-0 m-auto';
        toast.style.cssText = 'backdrop-filter: blur(2px); max-width: 480px; width: 92%; border: none; box-shadow: 0 24px 60px rgba(10,13,20,0.30);';
        toast.innerHTML = `
            <div style="background:#fff;border-radius:16px;overflow:hidden;font-family:'Inter','Poppins',sans-serif;">
                <div style="padding:28px 28px 18px;text-align:center;">
                    <div style="width:56px;height:56px;border-radius:50%;background:#e6f4ea;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2f9e44" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div style="font-size:18px;font-weight:700;color:#0a0d14;letter-spacing:-0.02em;">Correo de firma enviado</div>
                    <p id="signnow-sent-body" style="font-size:13px;color:#525866;margin:8px 0 0;line-height:1.55;"></p>
                    <div id="signnow-sent-email" style="display:inline-block;margin-top:12px;font-size:12px;font-weight:600;color:#2b303b;background:#f2f5f8;border:1px solid #eaecf0;border-radius:999px;padding:6px 12px;"></div>
                </div>
                <div style="padding:14px 22px 18px;display:flex;gap:10px;justify-content:center;border-top:1px solid #f2f5f8;background:#fafbfc;">
                    <button type="button" onclick="document.getElementById('signnow-sent-modal').close();" style="background:#5c7c68;color:#fff;border:1px solid #5c7c68;border-radius:8px;padding:8px 22px;font-size:13px;font-weight:600;cursor:pointer;">Entendido</button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
    }
    const defaultMsg = email
        ? `Te enviamos el documento por correo. Revisalo en tu bandeja de entrada (también el spam) y abrí el link para firmarlo. Cuando termines, este expediente se actualizará automáticamente.`
        : `Te enviamos un correo con el link para firmar. Revisalo en tu bandeja de entrada (también el spam).`;
    document.getElementById('signnow-sent-body').textContent = customMessage || defaultMsg;
    const emailEl = document.getElementById('signnow-sent-email');
    if (email) { emailEl.textContent = email; emailEl.style.display = 'inline-block'; }
    else        { emailEl.style.display = 'none'; }
    toast.showModal();
}

async function submitSign(e) {
    e.preventDefault();
    const docId = document.getElementById('sign-doc-id').value;
    const notes = document.getElementById('sign-notes').value;
    const btn = document.getElementById('sign-submit');

    if (!document.getElementById('sign-confirm').checked) {
        alert('Debes confirmar que aceptas los términos.');
        return false;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Firmando…';

    try {
        const res = await fetch('/documents/' + docId + '/sign', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ notes: notes }),
        });
        const data = await res.json();
        if (data.success) {
            // SignNow email-only flow: tell the user to check their inbox.
            if (data.email_sent) {
                const modal = document.getElementById('sign-modal');
                if (modal) modal.close();
                showSignNowSentToast(data.signer_email || '', data.message);
                return false;
            }
            // Local signing fallback
            btn.innerHTML = '<i class="pi pi-check"></i> ¡Firmado!';
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            alert(data.message || 'Error al firmar.');
            btn.disabled = false;
            btn.innerHTML = '<i class="pi pi-pen"></i> Firmar documento';
        }
    } catch (err) {
        alert('Error de red. Intenta de nuevo.');
        btn.disabled = false;
        btn.innerHTML = '<i class="pi pi-pen"></i> Firmar documento';
    }
    return false;
}
</script>
@endsection
