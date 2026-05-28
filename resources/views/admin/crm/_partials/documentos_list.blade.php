@php
    $statusLabel = ['pending' => ['Pendiente revisión','warn'], 'generated' => ['Generado','info'], 'signed' => ['Firmado','ok'], 'approved' => ['Aprobado','ok'], 'rejected' => ['Vencido','err']];
@endphp
<div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
    <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
        @foreach (['todos' => 'Todos','pending' => 'Pendientes de revisión','signed' => 'Firmados','rejected' => 'Vencidos','approved' => 'Aprobados'] as $slug => $label)
            <a href="?estado={{ $slug }}" data-docs-filter class="crm-tab {{ $tab === $slug ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
        <div class="relative w-full sm:w-64">
            <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
            <input type="text" placeholder="Buscar documento…" class="crm-input pr-3">
        </div>
        <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> Filtros</button>
        <button class="crm-btn crm-btn-ghost">Acciones en lote <i class="pi pi-angle-down text-[10px]"></i></button>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="w-full crm-table">
        <thead class="bg-ink-50">
            <tr>
                <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                <th>Documento</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Archivo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $d)
                @php
                    $st = $statusLabel[$d->status] ?? ['Pendiente','warn'];
                    $previewPayload = [
                        'url' => route('documents.preview', $d->id),
                        'title' => $d->title ?: 'Documento',
                        'filename' => $d->filename ?: basename((string) $d->file_path),
                    ];
                @endphp
                <tr>
                    <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-9 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-file"></i></div>
                            <div>
                                <div class="text-[13px] font-semibold text-ink-900">{{ $d->title }}</div>
                                <div class="text-[11px] text-ink-500">Subido {{ optional($d->generated_at ?? $d->created_at)->format('Y-m-d') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-[13px] text-ink-700">{{ $d->reservation->first_name ?? '—' }} {{ $d->reservation->last_name ?? '' }}</td>
                    <td><span class="crm-pill bg-ink-100 text-ink-600">{{ ucfirst($d->document_type ?? '—') }}</span></td>
                    <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                    <td class="text-[12px] text-ink-700">{{ optional($d->updated_at)->format('Y-m-d') }}</td>
                    <td class="text-[12px] text-ink-500"><i class="pi pi-paperclip text-[10px]"></i> {{ $d->filename ?? 'archivo' }}</td>
                    <td class="text-right whitespace-nowrap">
                        @if($d->status === 'pending')
                            <form method="POST" action="{{ route('documents.approve', $d->id) }}" class="inline m-0">@csrf
                                <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1">Aprobar</button>
                            </form>
                        @endif
                        @if($d->status === 'generated')
                            <form method="POST" action="{{ route('documents.sign', $d->id) }}" class="inline m-0">@csrf
                                <button class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1">Firmar</button>
                            </form>
                        @endif
                        @if($d->file_path)
                            <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="text-[12px] text-brand font-semibold hover:underline mr-2"><i class="pi pi-eye text-[10px]"></i> Ver</button>
                            <a href="{{ route('documents.download', $d->id) }}" class="text-[12px] text-brand font-semibold hover:underline mr-2">Descargar</a>
                        @endif
                        <a href="{{ route('admin.crm.expediente.detalle', $d->reservation_id ?? 0) }}?tab=documentos" class="text-[12px] text-brand font-semibold hover:underline">Ir a expediente &rarr;</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-[12px] text-ink-500 py-8">No hay documentos. <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="text-brand font-semibold hover:underline">Subir documento</button></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-3 border-t border-ink-100">
    {{ $documents->withQueryString()->links() }}
</div>
