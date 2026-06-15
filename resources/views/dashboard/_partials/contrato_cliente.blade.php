{{--
    Client-side contract card (purchase_promise / contract).
    Mirrors the plan-de-pagos cycle: review → conforme/observación → firmar.
    Required: $document (Document), $reservation (Reservation)
--}}
@php
    $d = $document;
    $r = $reservation;
    $meta = $d->metadata ?? [];
    $obs  = $meta['observations'] ?? [];
    $accepted = ! empty($meta['accepted_at']);
    $signed   = $d->isSigned();
    $approved = $d->isApproved();
    $lastObs  = ! empty($obs) ? end($obs) : null;
    $awaitingAdmin = $lastObs
        && ($lastObs['from'] ?? '') === 'client'
        && ($lastObs['kind'] ?? null) !== 'accept'
        && ! $accepted;

    $title = $d->document_type === 'purchase_promise' ? 'Promesa de compraventa' : 'Contrato';
    $obsRoute = route('dashboard.contract.observation', $d->id);
    $acceptRoute = route('dashboard.contract.accept', $d->id);
@endphp

<div class="cli-card overflow-hidden">
    <div class="px-5 py-3 flex items-center gap-3 bg-info-soft/40 border-b border-info/20">
        <div class="w-8 h-8 rounded-full bg-info-soft border border-info/30 flex items-center justify-center text-info"><i class="pi pi-file-edit"></i></div>
        <div class="flex-1">
            <div class="text-[14px] font-bold text-ink-950">{{ $title }}</div>
            <div class="text-[12px] text-ink-500">
                @if($signed)
                    Documento firmado. Podés descargarlo.
                @elseif($accepted)
                    Aceptaste el contrato. Ahora podés firmarlo.
                @elseif($awaitingAdmin)
                    Tu asesor está revisando tu observación.
                @else
                    Revisá el contrato y enviá observaciones o aceptalo para firmar.
                @endif
            </div>
        </div>
        @if($signed)
            <span class="cli-pill bg-ok-soft text-ok">{{ __('Firmado') }}</span>
        @elseif($accepted)
            <span class="cli-pill bg-info-soft text-info">{{ __('Pendiente de firma') }}</span>
        @elseif($awaitingAdmin)
            <span class="cli-pill bg-warn-soft text-warn">{{ __('Revisión por asesor') }}</span>
        @else
            <span class="cli-pill bg-info-soft text-info">{{ __('Pendiente de tu respuesta') }}</span>
        @endif
    </div>

    @if(! empty($obs))
        <div class="px-5 py-4 bg-ink-50 border-b border-ink-100">
            <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-500 mb-2">{{ __('Conversación con tu asesor') }}</div>
            <div class="space-y-2 max-h-56 overflow-y-auto">
                @foreach($obs as $o)
                    @php
                        $fromClient = ($o['from'] ?? '') === 'client';
                        $isUpload = ($o['kind'] ?? null) === 'upload';
                        $isAccept = ($o['kind'] ?? null) === 'accept';
                    @endphp
                    <div class="flex {{ $fromClient ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-xl px-3 py-2 text-[12px] {{ $isAccept ? 'bg-ok-soft border border-ok/30 text-ok-dark' : ($isUpload ? 'bg-warn-soft border border-warn/30 text-warn-dark' : ($fromClient ? 'bg-brand text-white' : 'bg-white border border-ink-200 text-ink-800')) }}">
                            <div class="text-[10px] uppercase tracking-wide opacity-70 mb-1 flex items-center gap-1">
                                @if($isAccept)<i class="pi pi-check-circle text-[10px]"></i>@endif
                                @if($isUpload)<i class="pi pi-upload text-[10px]"></i>@endif
                                {{ $o['author'] ?? ($fromClient ? 'Vos' : 'Asesor') }} · {{ \Carbon\Carbon::parse($o['at'] ?? now())->diffForHumans() }}
                            </div>
                            <div>{{ $o['message'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="px-5 py-4 flex flex-wrap items-center gap-2 justify-between border-t border-ink-100">
        <div class="flex items-center gap-2">
            @if($d->file_path)
                <a href="{{ route('documents.download', $d->id) }}" class="cli-btn cli-btn-ghost text-[12px]"><i class="pi pi-download text-[10px]"></i> {{ __('Descargar') }}</a>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($signed)
                <span class="text-[12px] text-ok-dark font-semibold"><i class="pi pi-check-circle"></i> {{ __('Firmado correctamente') }}</span>
            @elseif($accepted)
                <button type="button" onclick="signDocument({{ $d->id }})" class="cli-btn cli-btn-primary text-[12px]"><i class="pi pi-pen text-[10px]"></i> {{ __('Firmar') }}</button>
            @else
                <button type="button" onclick="document.getElementById('contract-obs-{{ $d->id }}').classList.toggle('hidden')" class="cli-btn cli-btn-ghost text-[12px]"><i class="pi pi-comments text-[10px]"></i> {{ __('Enviar observación') }}</button>
                <form method="POST" action="{{ $acceptRoute }}" class="m-0">@csrf
                    <button type="submit" class="cli-btn cli-btn-primary text-[12px]"><i class="pi pi-check text-[10px]"></i> {{ __('Aceptar contrato') }}</button>
                </form>
            @endif
        </div>
    </div>

    @if(! $accepted && ! $signed)
        <form id="contract-obs-{{ $d->id }}" method="POST" action="{{ $obsRoute }}" class="hidden px-5 pb-4 space-y-2 m-0">
            @csrf
            <textarea name="message" rows="3" required maxlength="2000" placeholder="{{ __('Indicá qué cambios necesitás en el contrato…') }}" class="cli-input w-full pl-3 pt-2 h-auto resize-none"></textarea>
            <div class="flex items-center gap-2 justify-end">
                <button type="button" onclick="document.getElementById('contract-obs-{{ $d->id }}').classList.add('hidden')" class="cli-btn cli-btn-ghost text-[12px]">{{ __('Cancelar') }}</button>
                <button type="submit" class="cli-btn cli-btn-primary text-[12px]"><i class="pi pi-send text-[10px]"></i> {{ __('Enviar observación') }}</button>
            </div>
        </form>
    @endif
</div>
