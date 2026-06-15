{{--
    KYC details modal — opened by an admin to review all KYC info submitted on /form.
    Required: $id (dialog id), $reservation, $kycDoc (Document|null)
--}}
@php
    $meta = $kycDoc?->metadata ?? [];
    $kycDocUrl = $kycDoc?->file_path
        ? (str_starts_with($kycDoc->file_path, 'documents/') || str_starts_with($kycDoc->file_path, 'http')
            ? asset($kycDoc->file_path)
            : \Storage::disk('public')->url($kycDoc->file_path))
        : null;
    $kycPreviewPayload = $kycDoc?->file_path ? [
        'url' => route('documents.preview', $kycDoc->id),
        'title' => 'Documento de identidad',
        'filename' => $kycDoc->filename ?: basename((string) $kycDoc->file_path),
    ] : null;

    $row = function ($label, $value) {
        $val = trim((string) ($value ?? ''));
        return [$label, $val === '' ? '—' : $val];
    };
    $sections = [
        'Identidad' => [
            $row('Tipo de documento',    $meta['id_type']         ?? $reservation->id_type ?? null),
            $row('Nº de documento',      $meta['document_number'] ?? $reservation->document_number ?? null),
            $row('Lugar de expedición',  $meta['expedition_place']?? $reservation->expedition_place ?? null),
            $row('Fecha de expedición',  $meta['expedition_date'] ?? $reservation->expedition_date?->format('Y-m-d') ?? null),
            $row('Fecha de nacimiento',  $meta['birth_date']      ?? $reservation->birth_date?->format('Y-m-d') ?? null),
            $row('Edad',                 $meta['age']             ?? $reservation->age ?? null),
            $row('Nacionalidad',         $meta['nationality']     ?? $reservation->nationality ?? null),
            $row('Estado civil',         $meta['marital_status']  ?? $reservation->marital_status ?? null),
        ],
        'Cónyuge' => [
            $row('Nombre',        $meta['spouse_name']        ?? $reservation->spouse_name ?? null),
            $row('Nacionalidad',  $meta['spouse_nationality'] ?? $reservation->spouse_nationality ?? null),
            $row('Documento',     $meta['spouse_document']    ?? $reservation->spouse_document ?? null),
        ],
        'Profesional' => [
            $row('Profesión',                $meta['profession']         ?? $reservation->profession ?? null),
            $row('Ocupación',                $meta['occupation']         ?? $reservation->occupation ?? null),
            $row('Dependencia económica',    $meta['economic_dependent'] ?? $reservation->economic_dependent ?? null),
        ],
        'Domicilio' => [
            $row('Dirección',       $meta['address']         ?? $reservation->address ?? null),
            $row('Provincia',       $meta['province']        ?? $reservation->province ?? null),
            $row('Sector',          $meta['neighborhood']    ?? $reservation->neighborhood ?? null),
            $row('Ciudad',          $meta['city']            ?? $reservation->city ?? null),
            $row('Edificio',        $meta['building_name']   ?? $reservation->building_name ?? null),
            $row('Apartamento',     $meta['apartment_number']?? $reservation->apartment_number ?? null),
            $row('Código postal',   $meta['postal_code']     ?? $reservation->postal_code ?? null),
            $row('País',            $meta['country']         ?? $reservation->country ?? null),
        ],
    ];

    $coBuyers = $meta['co_buyers'] ?? $reservation->co_buyers ?? [];
    if (!is_array($coBuyers)) $coBuyers = [];
@endphp
<dialog id="{{ $id }}" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <div class="w-[720px] max-w-[95vw] bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-id-card"></i></div>
            <div class="flex-1">
                <div class="text-[15px] font-bold text-ink-900">KYC · {{ trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: 'Cliente' }}</div>
                <div class="text-[11px] text-ink-500">{{ $reservation->reservation_code ?? '' }} · Estado:
                    @php $st = $kycDoc->status ?? 'pending'; @endphp
                    @if($st === 'approved')<span class="text-ok-dark font-semibold">{{ __('Aprobado') }}</span>
                    @elseif($st === 'rejected')<span class="text-err font-semibold">{{ __('Rechazado') }}</span>
                    @else<span class="text-warn font-semibold">{{ __('Pendiente') }}</span>@endif
                </div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>

        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
            @if($kycDocUrl)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-ink-50 border border-ink-100">
                    <i class="pi pi-paperclip text-ink-500"></i>
                    <div class="flex-1 text-[12px] text-ink-700 truncate">{{ __('Documento de identidad adjunto') }}</div>
                    <button type="button" onclick="openDocumentPreview(@js($kycPreviewPayload))" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver archivo') }}</button>
                </div>
            @else
                <div class="text-[12px] text-ink-500">{{ __('Sin archivo adjunto.') }}</div>
            @endif

            @foreach($sections as $sectionTitle => $rows)
                <div>
                    <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-400 mb-2">{{ $sectionTitle }}</div>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($rows as [$label, $value])
                            <div class="border border-ink-100 rounded-lg px-3 py-2">
                                <div class="text-[10px] uppercase text-ink-400 font-semibold tracking-wide">{{ $label }}</div>
                                <div class="text-[13px] text-ink-900 mt-0.5 break-words">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(count($coBuyers) > 0)
                <div>
                    <div class="text-[11px] uppercase tracking-wide font-semibold text-ink-400 mb-2 flex items-center gap-2">
                        Titulares adicionales
                        <span class="px-2 py-0.5 rounded-full bg-info-soft text-info text-[10px] font-bold">{{ count($coBuyers) }}</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($coBuyers as $i => $cb)
                            @php
                                $cbName = trim(($cb['first_name'] ?? '').' '.($cb['last_name'] ?? '')) ?: 'Titular #'.($i+2);
                            @endphp
                            <div class="rounded-xl border border-ink-100 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-[13px] font-bold text-ink-900">#{{ $i + 2 }} · {{ $cbName }}</div>
                                    @if(!empty($cb['relationship']))
                                        <span class="px-2 py-0.5 rounded-full bg-ink-100 text-ink-600 text-[10px] font-semibold uppercase tracking-wide">{{ $cb['relationship'] }}</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach([
                                        ['Documento', ($cb['id_type'] ?? '').' '.($cb['document_number'] ?? '')],
                                        ['E-mail', $cb['email'] ?? null],
                                        ['Teléfono', $cb['phone'] ?? null],
                                        ['Nacimiento', $cb['birth_date'] ?? null],
                                        ['Nacionalidad', $cb['nationality'] ?? null],
                                        ['% Copropiedad', isset($cb['ownership_pct']) ? $cb['ownership_pct'].'%' : null],
                                    ] as [$lbl, $val])
                                        @php $val = trim((string)$val); @endphp
                                        <div class="border border-ink-100 rounded-lg px-3 py-2 bg-ink-50/40">
                                            <div class="text-[10px] uppercase text-ink-400 font-semibold tracking-wide">{{ $lbl }}</div>
                                            <div class="text-[12px] text-ink-900 mt-0.5 break-words">{{ $val === '' ? '—' : $val }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            @if($kycDoc && $kycDoc->status === 'pending')
                <form method="POST" action="{{ route('documents.reject', $kycDoc->id) }}" class="m-0">@csrf
                    <button type="submit" class="crm-btn crm-btn-ghost text-err">{{ __('Rechazar') }}</button>
                </form>
                <form method="POST" action="{{ route('documents.approve', $kycDoc->id) }}" class="m-0">@csrf
                    <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Aprobar KYC') }}</button>
                </form>
            @endif
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cerrar') }}</button>
        </div>
    </div>
</dialog>
