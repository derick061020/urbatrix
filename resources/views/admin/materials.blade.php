@extends('layouts.admin_crm')
@section('title', 'Material de Brokers — CRM Duna Makai')
@section('page_title', 'Material de Brokers')
@section('page_breadcrumb', 'Brokers · Gestión de materiales')
@php $activeRoute = 'materials'; @endphp

@push('styles')
<style>
    /* Campos del formulario (sin el padding de ícono de .crm-input) */
    .mat-field {
        width:100%; height:40px; padding:0 12px;
        border:1px solid #eaecf0; border-radius:8px;
        font-size:13px; color:#222530; background:#fff;
        outline:none; transition:border-color .15s, box-shadow .15s;
    }
    .mat-field::placeholder { color:#9aa1ad; }
    .mat-field:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
    textarea.mat-field { height:auto; min-height:64px; padding:10px 12px; resize:vertical; line-height:1.5; }
    .mat-label { display:block; font-size:12px; font-weight:600; color:#3a4150; margin-bottom:6px; }
    /* Dropzone de archivo */
    .mat-drop {
        display:flex; align-items:center; gap:12px;
        border:1.5px dashed #d6dae1; border-radius:10px; padding:14px 16px;
        background:#fafbfc; cursor:pointer; transition:border-color .15s, background-color .15s;
    }
    .mat-drop:hover { border-color:#5c7c68; background:#f5f8f6; }
    .mat-drop input[type=file] { display:none; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))
        <div class="p-3 rounded-lg bg-ok-soft border border-ok/20 text-[12px] text-ok-dark">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="p-3 rounded-lg bg-err-soft border border-err/20 text-[12px] text-err">{{ $errors->first() }}</div>
    @endif

    <div class="flex items-center justify-between">
        <div class="text-[13px] text-ink-500">{{ $materials->count() }} recursos · {{ $materials->where('visible', true)->count() }} visibles para brokers</div>
        <button type="button" class="crm-btn crm-btn-primary" id="matAddBtn"><i class="pi pi-plus"></i> {{ __('Agregar material') }}</button>
    </div>

    <div class="crm-card overflow-hidden">
        <table class="w-full">
            <thead class="bg-ink-50/60 border-b border-ink-100">
                <tr>
                    @foreach(['Recurso','Categoría','Formato','Tamaño','Descargas','Visible',''] as $h)
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($materials as $m)
                    <tr>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi {{ $m->icon }}"></i></span>
                                <div>
                                    <div class="text-[13px] font-semibold text-ink-950">{{ $m->title }}</div>
                                    <div class="text-[11px] text-ink-400">{{ \Illuminate\Support\Str::limit($m->description, 60) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-[12px] text-ink-600">{{ $m->category ?: '—' }}</td>
                        <td class="px-5 py-3.5"><span class="crm-pill {{ $m->badge_color }}">{{ strtoupper($m->format ?: '—') }}</span></td>
                        <td class="px-5 py-3.5 text-[12px] text-ink-600">{{ $m->file_size ?: '—' }}</td>
                        <td class="px-5 py-3.5 text-[12px] text-ink-600">{{ $m->downloads }}</td>
                        <td class="px-5 py-3.5">
                            <form method="POST" action="{{ route('admin.materials.toggle', $m) }}">@csrf
                                <button class="crm-pill {{ $m->visible ? 'bg-ok-soft text-ok-dark' : 'bg-ink-100 text-ink-500' }}">
                                    <i class="pi {{ $m->visible ? 'pi-eye' : 'pi-eye-slash' }} text-[10px]"></i> {{ $m->visible ? 'Visible' : 'Oculto' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @if($m->fileUrl())
                                @php $previewJson = \Illuminate\Support\Js::from([
                                    'title'    => $m->title,
                                    'format'   => strtoupper($m->format ?: ''),
                                    'kind'     => $m->previewKind(),
                                    'url'      => $m->fileUrl(),
                                    'download' => $m->downloadUrl(),
                                ]); @endphp
                                <button type="button" class="text-ink-500 hover:text-brand mr-3" title="{{ __('Ver') }}"
                                    onclick="openPreviewMaterial({{ $previewJson }})"><i class="pi pi-eye"></i></button>
                            @endif
                            @php $matJson = \Illuminate\Support\Js::from($m->only(['id','title','description','category','external_url','sort_order','visible'])); @endphp
                            <button type="button" class="text-ink-500 hover:text-brand mr-3" title="{{ __('Editar') }}"
                                onclick="openEditMaterial({{ $matJson }})"><i class="pi pi-pencil"></i></button>
                            <button type="button" class="text-ink-400 hover:text-err" title="{{ __('Eliminar') }}"
                                onclick="openDeleteMaterial({{ \Illuminate\Support\Js::from(['url' => route('admin.materials.destroy', $m), 'title' => $m->title]) }})"><i class="pi pi-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-[12px] text-ink-400">{{ __('Aún no hay materiales. Agrega el primero para que tus brokers lo vean.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Modal: alta / edición ===== --}}
<dialog id="matModal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[540px] max-w-[94vw]">
    <form id="matForm" method="POST" action="{{ route('admin.materials.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="_method" id="matMethod" value="POST">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-folder-open"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1" id="matTitle">{{ __('Agregar material') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[72vh] overflow-y-auto">
            <div>
                <label class="mat-label">{{ __('Título') }} <span class="text-err">*</span></label>
                <input type="text" name="title" id="matInputTitle" required placeholder="{{ __('Ej. Brochure comercial 2026') }}" class="mat-field">
            </div>
            <div>
                <label class="mat-label">{{ __('Descripción') }}</label>
                <textarea name="description" id="matInputDesc" rows="2" placeholder="{{ __('Breve nota sobre el recurso (opcional)') }}" class="mat-field"></textarea>
            </div>
            <div>
                <label class="mat-label">{{ __('Categoría') }}</label>
                <input type="text" name="category" id="matInputCat" placeholder="{{ __('Renders, Brochure, Contrato…') }}" class="mat-field">
            </div>

            <div class="pt-1">
                <label class="mat-label">{{ __('Archivo') }}</label>
                <label class="mat-drop" id="matDrop">
                    <span class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-ink-500 shrink-0"><i class="pi pi-cloud-upload"></i></span>
                    <span class="min-w-0">
                        <span class="block text-[13px] font-semibold text-ink-800" id="matDropName">{{ __('Subir un archivo') }}</span>
                        <span class="block text-[11px] text-ink-400">{{ __('El formato y el tamaño se detectan automáticamente.') }}</span>
                    </span>
                    <input type="file" name="file" id="matInputFile">
                </label>
            </div>

            <div class="relative flex items-center gap-3 py-1">
                <span class="flex-1 h-px bg-ink-100"></span>
                <span class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">{{ __('o enlace externo') }}</span>
                <span class="flex-1 h-px bg-ink-100"></span>
            </div>
            <div>
                <input type="url" name="external_url" id="matInputUrl" placeholder="https://…" class="mat-field">
            </div>

            <div class="grid grid-cols-2 gap-4 items-end pt-1">
                <div>
                    <label class="mat-label">{{ __('Orden') }}</label>
                    <input type="number" name="sort_order" id="matInputOrder" value="0" min="0" class="mat-field">
                </div>
                <label class="flex items-center gap-2 text-[13px] text-ink-700 h-10 px-1">
                    <input type="checkbox" name="visible" id="matInputVisible" value="1" checked class="accent-brand w-4 h-4"> Visible para brokers
                </label>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Guardar') }}</button>
        </div>
    </form>
</dialog>

{{-- ===== Modal: confirmar eliminación ===== --}}
<dialog id="matDelete" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[420px] max-w-[94vw]">
    <form method="POST" id="matDeleteForm" class="bg-white rounded-2xl overflow-hidden">
        @csrf @method('DELETE')
        <div class="p-6 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-err-soft flex items-center justify-center text-err mb-3"><i class="pi pi-trash text-[20px]"></i></div>
            <div class="text-[15px] font-bold text-ink-900">{{ __('Eliminar material') }}</div>
            <p class="text-[13px] text-ink-500 mt-1.5">{{ __('¿Seguro que querés eliminar') }} <b class="text-ink-700" id="matDeleteName">{{ __('este material') }}</b>{{ __('? Esta acción no se puede deshacer.') }}</p>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-center gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary" style="background:#d92d20;border-color:#d92d20"><i class="pi pi-trash"></i> {{ __('Eliminar') }}</button>
        </div>
    </form>
</dialog>

{{-- ===== Modal: previsualización ===== --}}
<dialog id="matPreview" class="rounded-2xl p-0 backdrop:bg-black/50 m-auto w-[860px] max-w-[95vw]">
    <div class="bg-white rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-eye"></i></div>
            <div class="min-w-0 flex-1">
                <div class="text-[15px] font-bold text-ink-900 truncate" id="matPreviewTitle">{{ __('Vista previa') }}</div>
                <div class="text-[11px] text-ink-400 uppercase tracking-wider" id="matPreviewFmt"></div>
            </div>
            <a href="#" id="matPreviewDownload" class="crm-btn crm-btn-primary"><i class="pi pi-download"></i> {{ __('Descargar') }}</a>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1 ml-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="flex-1 overflow-auto bg-ink-50 flex items-center justify-center p-4" id="matPreviewBody" style="min-height:420px"></div>
    </div>
</dialog>

<script>
    const matForm = document.getElementById('matForm');
    const storeAction = "{{ route('admin.materials.store') }}";
    const matFileInput = document.getElementById('matInputFile');
    const matDropName = document.getElementById('matDropName');

    matFileInput.addEventListener('change', () => {
        matDropName.textContent = matFileInput.files.length ? matFileInput.files[0].name : 'Subir un archivo';
    });

    function openEditMaterial(m) {
        document.getElementById('matTitle').textContent = 'Editar material';
        document.getElementById('matMethod').value = 'PUT';
        matForm.action = "{{ url('admin/materials') }}/" + m.id;
        document.getElementById('matInputTitle').value = m.title || '';
        document.getElementById('matInputDesc').value = m.description || '';
        document.getElementById('matInputCat').value = m.category || '';
        document.getElementById('matInputUrl').value = m.external_url || '';
        document.getElementById('matInputOrder').value = m.sort_order ?? 0;
        document.getElementById('matInputVisible').checked = !!m.visible;
        matFileInput.value = '';
        matDropName.textContent = 'Subir un archivo';
        document.getElementById('matModal').showModal();
    }

    document.getElementById('matAddBtn').addEventListener('click', () => {
        document.getElementById('matTitle').textContent = 'Agregar material';
        document.getElementById('matMethod').value = 'POST';
        matForm.action = storeAction;
        matForm.reset();
        matDropName.textContent = 'Subir un archivo';
        document.getElementById('matInputVisible').checked = true;
        document.getElementById('matModal').showModal();
    });

    function openDeleteMaterial(m) {
        document.getElementById('matDeleteForm').action = m.url;
        document.getElementById('matDeleteName').textContent = m.title ? '«' + m.title + '»' : 'este material';
        document.getElementById('matDelete').showModal();
    }

    function openPreviewMaterial(m) {
        document.getElementById('matPreviewTitle').textContent = m.title || 'Vista previa';
        document.getElementById('matPreviewFmt').textContent = m.format || '';
        document.getElementById('matPreviewDownload').href = m.download || m.url;
        const body = document.getElementById('matPreviewBody');

        if (m.kind === 'pdf') {
            body.innerHTML = '<iframe src="' + m.url + '" class="w-full rounded-lg bg-white" style="height:72vh;border:0"></iframe>';
        } else if (m.kind === 'image') {
            body.innerHTML = '<img src="' + m.url + '" alt="" class="max-w-full max-h-[72vh] rounded-lg object-contain shadow-sm">';
        } else if (m.kind === 'video') {
            body.innerHTML = '<video src="' + m.url + '" controls class="max-w-full max-h-[72vh] rounded-lg bg-black"></video>';
        } else {
            body.innerHTML = '<div class="text-center py-10">'
                + '<div class="w-14 h-14 mx-auto rounded-xl bg-ink-100 flex items-center justify-center text-ink-500 mb-3"><i class="pi pi-file text-[22px]"></i></div>'
                + '<div class="text-[13px] text-ink-600">Este formato no admite vista previa.</div>'
                + '<div class="text-[12px] text-ink-400 mt-1">Usá el botón <b>Descargar</b> para abrir el recurso.</div>'
                + '</div>';
        }
        document.getElementById('matPreview').showModal();
    }
    document.getElementById('matPreview').addEventListener('close', () => {
        document.getElementById('matPreviewBody').innerHTML = '';
    });
</script>
@endsection
