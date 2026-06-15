{{--
    Admin-side contract card (purchase_promise / contract).
    Lets the admin upload a modified version, see the observation thread, and reply.
    Required: $document (Document), $reservation (Reservation)
--}}
@php
    $d = $document;
    $r = $reservation;
    $meta = $d->metadata ?? [];
    $obs  = $meta['observations'] ?? [];
    $accepted = ! empty($meta['accepted_at']);
    $signed   = $d->isSigned();
    $lastObs  = ! empty($obs) ? end($obs) : null;
    $awaitingClient = $lastObs && ($lastObs['from'] ?? '') === 'admin' && ($lastObs['kind'] ?? null) !== 'upload' && ! $accepted;

    $title = $d->document_type === 'purchase_promise' ? 'Promesa de compraventa' : 'Contrato';
    if ($signed)            { $stateLabel = ['Firmado por el cliente', 'ok']; }
    elseif ($accepted)      { $stateLabel = ['Aceptado · pendiente de firma', 'info']; }
    elseif ($awaitingClient){ $stateLabel = ['Esperando respuesta del cliente', 'info']; }
    elseif (! empty($obs))  { $stateLabel = ['Cliente envió observación', 'warn']; }
    else                    { $stateLabel = ['En revisión por el cliente', 'info']; }
    $previewPayload = [
        'url' => route('documents.preview', $d->id),
        'title' => $title,
        'filename' => $d->filename ?: basename((string) $d->file_path),
    ];
@endphp

<div class="crm-card overflow-hidden">
    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-3">
        <i class="pi pi-file-edit text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ $title }}</div>
        <span class="crm-pill bg-{{ $stateLabel[1] }}-soft text-{{ $stateLabel[1] }} ml-2">{{ $stateLabel[0] }}</span>
        @if($d->file_path)
            <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="ml-auto crm-btn crm-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
            <a href="{{ route('documents.download', $d->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> {{ __('Descargar') }}</a>
        @endif
    </div>

    @if(! empty($obs))
        <div class="px-5 py-4 bg-warn-soft/20 border-b border-warn/20 space-y-2">
            <div class="text-[11px] uppercase tracking-wide font-semibold text-warn-dark">{{ __('Conversación con el cliente') }}</div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($obs as $o)
                    @php
                        $fromAdmin = ($o['from'] ?? '') === 'admin';
                        $isUpload = ($o['kind'] ?? null) === 'upload';
                        $isAccept = ($o['kind'] ?? null) === 'accept';
                    @endphp
                    <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-xl px-3 py-2 text-[12px] {{ $isAccept ? 'bg-ok-soft border border-ok/30 text-ok-dark' : ($isUpload ? 'bg-warn-soft border border-warn/30 text-warn-dark' : ($fromAdmin ? 'bg-brand text-white' : 'bg-white border border-ink-200 text-ink-800')) }}">
                            <div class="text-[10px] uppercase tracking-wide opacity-70 mb-1 flex items-center gap-1">
                                @if($isAccept)<i class="pi pi-check-circle text-[10px]"></i>@endif
                                @if($isUpload)<i class="pi pi-upload text-[10px]"></i>@endif
                                {{ $o['author'] ?? ($fromAdmin ? 'Asesor' : 'Cliente') }} · {{ \Carbon\Carbon::parse($o['at'] ?? now())->diffForHumans() }}
                            </div>
                            <div>{{ $o['message'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(! $signed)
        <div class="p-5 space-y-3">
            <form method="POST" action="{{ route('admin.crm.contract.upload', $d->id) }}" enctype="multipart/form-data" class="space-y-3 m-0">
                @csrf
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Subir versión modificada del contrato') }}</label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx" class="block w-full mt-1 text-[12px] text-ink-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/15">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Mensaje al cliente (opcional)') }}</label>
                    <textarea name="admin_reply" rows="2" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="{{ __('Explicá qué cambios incluye esta versión…') }}"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-upload"></i> {{ __('Enviar versión modificada') }}</button>
                </div>
            </form>

            @if(! empty($obs))
                <div class="h-px bg-ink-100"></div>
                <form method="POST" action="{{ route('admin.crm.contract.reply', $d->id) }}" class="space-y-2 m-0">
                    @csrf
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Responder sin subir archivo nuevo') }}</label>
                    <textarea name="admin_reply" required rows="2" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="{{ __('Responder a la última observación…') }}"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="crm-btn crm-btn-ghost"><i class="pi pi-send"></i> {{ __('Enviar respuesta') }}</button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
