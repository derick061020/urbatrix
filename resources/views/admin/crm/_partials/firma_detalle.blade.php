{{--
    Signature evidence card for a signed auto-generated document.
    Shows the captured signature image, signer name, IP, device and timestamp.
    Required: $document (Document, status=signed)
--}}
@php
    $sig      = json_decode($document->notes ?? '', true);
    $sig      = is_array($sig) ? $sig : [];
    $sigImg   = $sig['signature_image'] ?? null;
    $signer   = $sig['signer_name'] ?? optional($document->signedByUser)->name;
    $sigIp    = $sig['ip'] ?? null;
    $sigUa    = $sig['user_agent'] ?? null;
    $sigTs    = $sig['timestamp'] ?? $sig['signed_server_at'] ?? null;
    try { $sigWhen = $sigTs ? \Carbon\Carbon::parse($sigTs) : $document->signed_at; }
    catch (\Throwable $e) { $sigWhen = $document->signed_at; }

    $sigTypeLabel = [
        'payment_plan'     => 'Plan de pagos',
        'purchase_promise' => 'Promesa de compraventa',
        'contract'         => 'Contrato',
    ][$document->document_type] ?? ($document->title ?: 'Documento');
@endphp

<div class="crm-card overflow-hidden">
    <div class="px-4 py-3 bg-ok-soft/40 border-b border-ok/20 flex items-center gap-2">
        <i class="pi pi-verified text-ok-dark"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ $sigTypeLabel }} · firmado</div>
        <span class="crm-pill bg-ok-soft text-ok ml-auto">Firmado</span>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-[200px_1fr] gap-4">
        {{-- Signature image --}}
        <div>
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400 mb-1.5">Firma manuscrita</div>
            <div class="rounded-xl border border-ink-200 bg-white h-[88px] flex items-center justify-center overflow-hidden p-2">
                @if($sigImg)
                    <img src="{{ $sigImg }}" alt="Firma del cliente" class="max-h-full max-w-full object-contain">
                @else
                    <span class="text-[11px] text-ink-400">Sin imagen de firma</span>
                @endif
            </div>
        </div>

        {{-- Evidence grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
            <div>
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Firmante</div>
                <div class="text-[13px] font-semibold text-ink-900 mt-0.5">{{ $signer ?: '—' }}</div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Fecha y hora</div>
                <div class="text-[13px] font-semibold text-ink-900 mt-0.5">
                    {{ $sigWhen ? \Carbon\Carbon::parse($sigWhen)->locale('es')->isoFormat('D MMM YYYY · HH:mm') . ' hs' : '—' }}
                </div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Dirección IP</div>
                <div class="text-[13px] font-semibold text-ink-900 mt-0.5 font-mono">{{ $sigIp ?: '—' }}</div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Términos aceptados</div>
                <div class="text-[13px] font-semibold mt-0.5 {{ ($sig['accepted_terms'] ?? false) ? 'text-ok-dark' : 'text-ink-500' }}">
                    {{ ($sig['accepted_terms'] ?? false) ? 'Sí · firma legalmente vinculante' : '—' }}
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Dispositivo / navegador</div>
                <div class="text-[12px] text-ink-600 mt-0.5 break-all leading-snug">{{ $sigUa ?: '—' }}</div>
            </div>
        </div>
    </div>
</div>
