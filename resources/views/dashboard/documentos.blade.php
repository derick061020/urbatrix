@extends('layouts.client')
@section('title', __('Mis documentos').' — MAKAI')
@section('page_title', __('Mis documentos'))
@section('page_breadcrumb', __('Mi propiedad').' · '.__('Documentos'))
@php $activeRoute = 'documents'; @endphp

@section('content')
@php
    $reservation = $reservation ?? null;
    $userId = auth()->id();

    /* Pull docs linked to the reservation + KYC docs uploaded at register time
       (reservation_id = null, metadata->user_id matches us). */
    $reservationDocs = $reservation ? $reservation->documents : collect();
    $userDocs = \App\Models\Document::whereNull('reservation_id')
        ->where(function($q) use ($userId) {
            $q->where('metadata->user_id', $userId)
              ->orWhere('metadata->source', 'register');
        })
        ->get();
    $all = $reservationDocs->merge($userDocs)->unique('id');

    /* Only completed/visible documents — signed/approved contracts & plans. */
    $signedDocs = $all->filter(function($d) {
        return in_array($d->status, ['signed', 'approved', 'completed'])
            && in_array($d->document_type, ['payment_plan', 'purchase_promise', 'contract']);
    })->sortByDesc('signed_at');

    $kycDocs = $all->whereIn('document_type', ['id_front', 'id_back', 'kyc'])
        ->sortByDesc('created_at');

    $typeLabel = [
        'payment_plan'     => __('Plan de pagos'),
        'purchase_promise' => __('Promesa de compraventa'),
        'contract'         => __('Contrato'),
        'kyc'              => __('Identidad (KYC)'),
        'id_front'         => __('Documento de identidad (Frente)'),
        'id_back'          => __('Documento de identidad (Reverso)'),
    ];

    $statusPill = [
        'signed'    => [__('FIRMADO'),     'ok'],
        'approved'  => [__('APROBADO'),    'ok'],
        'completed' => [__('COMPLETADO'),  'ok'],
        'pending'   => [__('EN REVISIÓN'), 'warn'],
        'generated' => [__('GENERADO'),    'info'],
        'rejected'  => [__('RECHAZADO'),   'err'],
    ];
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    {{-- Header --}}
    <div>
        <h2 class="font-display text-[20px] font-semibold text-ink-950 leading-tight">{{ __('Tus documentos') }}</h2>
        <p class="text-[13px] text-ink-500 mt-1">{!! __('Acá ves los documentos firmados o aprobados, listos para descargar. Los pendientes de revisión están en :acuerdos.', ['acuerdos' => '<a href="'.route('dashboard.acuerdos').'" class="text-brand font-semibold hover:underline">'.__('Acuerdos').'</a>']) !!}</p>
    </div>

    {{-- ============ Contratos firmados / aprobados ============ --}}
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ok-soft/60 border-b border-ok/20">
            <div class="w-8 h-8 rounded-full bg-ok-soft border border-ok/30 flex items-center justify-center text-ok-dark"><i class="pi pi-check-circle"></i></div>
            <div class="flex-1">
                <div class="text-[14px] font-bold text-ink-950">{{ __('Contratos y planes firmados') }}</div>
                <div class="text-[11px] text-ink-500">{{ __('Documentos finalizados de tu expediente') }}</div>
            </div>
            <span class="text-[11px] text-ink-500">{{ trans_choice('{0} :count archivos|{1} :count archivo|[2,*] :count archivos', $signedDocs->count(), ['count' => $signedDocs->count()]) }}</span>
        </div>

        @if($signedDocs->isEmpty())
            <div class="px-5 py-8 text-center text-[12px] text-ink-500">
                {!! __('Todavía no hay documentos firmados o aprobados. Cuando firmes en :acuerdos, aparecerán acá.', ['acuerdos' => '<a href="'.route('dashboard.acuerdos').'" class="text-brand font-semibold hover:underline">'.__('Acuerdos').'</a>']) !!}
            </div>
        @else
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
                            $st = $statusPill[$d->status] ?? ['COMPLETADO','ok'];
                            $when = $d->signed_at ?? $d->updated_at ?? $d->created_at;
                        @endphp
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-10 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-file"></i></div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-950">{{ $d->title ?? ($typeLabel[$d->document_type] ?? __('Documento')) }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $typeLabel[$d->document_type] ?? $d->document_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4"><span class="cli-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="px-3 py-4 text-[12px] text-ink-700">{{ $when ? \Carbon\Carbon::parse($when)->locale(app()->getLocale())->isoFormat(app()->getLocale()==='es' ? 'D MMM YYYY' : 'MMM D, YYYY') : '—' }}</td>
                            <td class="px-3 py-4 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    @if($d->file_path)
                                        <button type="button" onclick="openDocumentPreview({{ json_encode(['url' => route('documents.preview', $d->id), 'title' => $d->title ?? ($typeLabel[$d->document_type] ?? __('Documento')), 'filename' => $d->filename ?? basename((string) $d->file_path)]) }})" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
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
        @endif
    </div>

    {{-- ============ Documentos de identidad (KYC) ============ --}}
    <div class="cli-card overflow-hidden">
        <div class="px-5 py-3 flex items-center gap-3 bg-ink-50/60 border-b border-ink-100">
            <div class="w-8 h-8 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-id-card"></i></div>
            <div class="flex-1">
                <div class="text-[14px] font-bold text-ink-950">{{ __('Documentos de identidad') }}</div>
                <div class="text-[11px] text-ink-500">{{ __('Tus archivos KYC entregados al expediente') }}</div>
            </div>
            <span class="text-[11px] text-ink-500">{{ trans_choice('{0} :count archivos|{1} :count archivo|[2,*] :count archivos', $kycDocs->count(), ['count' => $kycDocs->count()]) }}</span>
        </div>

        @if($kycDocs->isEmpty())
            <div class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('No subiste documentos de identidad todavía.') }}</div>
        @else
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Documento') }}</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Estado') }}</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Subido') }}</th>
                        <th class="text-right px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach($kycDocs as $d)
                        @php $st = $statusPill[$d->status] ?? ['EN REVISIÓN','warn']; @endphp
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-10 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-id-card"></i></div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-950">{{ $d->title ?? ($typeLabel[$d->document_type] ?? __('Documento')) }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $typeLabel[$d->document_type] ?? $d->document_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4"><span class="cli-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="px-3 py-4 text-[12px] text-ink-700">{{ optional($d->created_at)->locale(app()->getLocale())->isoFormat(app()->getLocale()==='es' ? 'D MMM YYYY' : 'MMM D, YYYY') }}</td>
                            <td class="px-3 py-4 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    @if($d->file_path)
                                        <button type="button" onclick="openDocumentPreview({{ json_encode(['url' => route('documents.preview', $d->id), 'title' => $d->title ?? ($typeLabel[$d->document_type] ?? __('Documento')), 'filename' => $d->filename ?? basename((string) $d->file_path)]) }})" class="cli-btn cli-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
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
        @endif
    </div>

    {{-- Trust footer --}}
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-ink-100/60 border border-ink-200 text-[12px] text-ink-600">
        <i class="pi pi-lock text-ink-500"></i>
        {{ __('Tus documentos están protegidos con cifrado de extremo a extremo. Solo el equipo legal de Duna Development Group tiene acceso a tus archivos.') }}
        <span class="ml-auto font-semibold text-ink-950">{{ __('¿Tienes preguntas?') }} <a href="{{ route('dashboard.messages') }}" class="text-brand hover:underline">{{ __('Contáctanos') }} &rarr;</a></span>
    </div>
</div>

{{-- Document Preview Modal --}}
<dialog id="document-preview-modal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <div class="w-[min(1040px,95vw)] bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600">
                <i class="pi pi-file"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div id="document-preview-title" class="text-[15px] font-bold text-ink-900 truncate">{{ __('Vista previa') }}</div>
                <div id="document-preview-filename" class="text-[11px] text-ink-500 truncate"></div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1">
                <i class="pi pi-times text-[12px]"></i>
            </button>
        </div>

        <div class="bg-ink-50 p-4">
            <div class="bg-white border border-ink-100 rounded-xl overflow-hidden min-h-[70vh] flex items-center justify-center">
                <iframe id="document-preview-frame" class="hidden w-full h-[70vh] bg-white" title="{{ __('Vista previa del documento') }}"></iframe>
                <img id="document-preview-image" class="hidden max-h-[70vh] max-w-full object-contain" alt="{{ __('Vista previa del documento') }}">
                <div id="document-preview-empty" class="hidden text-center px-6 py-12">
                    <div class="w-12 h-12 rounded-xl bg-ink-100 text-ink-500 flex items-center justify-center mx-auto mb-3">
                        <i class="pi pi-file text-[18px]"></i>
                    </div>
                    <div class="text-[14px] font-semibold text-ink-900">{{ __('Vista previa no disponible') }}</div>
                    <div class="text-[12px] text-ink-500 mt-1">{{ __('Este tipo de archivo no se puede mostrar directamente en el navegador.') }}</div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center justify-end bg-white">
            <button type="button" onclick="this.closest('dialog').close()" class="cli-btn cli-btn-ghost">{{ __('Cerrar') }}</button>
        </div>
    </div>
</dialog>

<script>
(function () {
    if (window.openDocumentPreview) return;

    window.openDocumentPreview = function (payload) {
        const modal = document.getElementById('document-preview-modal');
        const title = document.getElementById('document-preview-title');
        const filename = document.getElementById('document-preview-filename');
        const frame = document.getElementById('document-preview-frame');
        const image = document.getElementById('document-preview-image');
        const empty = document.getElementById('document-preview-empty');

        if (!modal || !payload?.url) return;

        const cleanFilename = payload.filename || payload.title || 'archivo';
        const extension = cleanFilename.split('.').pop().toLowerCase();
        const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        title.textContent = payload.title || 'Vista previa';
        filename.textContent = cleanFilename;

        frame.classList.add('hidden');
        image.classList.add('hidden');
        empty.classList.add('hidden');
        frame.removeAttribute('src');
        image.removeAttribute('src');

        if (['pdf', 'doc', 'docx'].includes(extension)) {
            frame.src = payload.url;
            frame.classList.remove('hidden');
        } else if (imageTypes.includes(extension)) {
            image.src = payload.url;
            image.classList.remove('hidden');
        } else {
            empty.classList.remove('hidden');
        }

        modal.showModal();
    };

    document.addEventListener('close', function (event) {
        if (event.target?.id !== 'document-preview-modal') return;
        document.getElementById('document-preview-frame')?.removeAttribute('src');
        document.getElementById('document-preview-image')?.removeAttribute('src');
    }, true);
})();
</script>
@endsection
