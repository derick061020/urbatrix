@extends('layouts.admin_crm')
@section('title', 'Material de Brokers — CRM Duna Makai')
@section('page_title', 'Material de Brokers')
@section('page_breadcrumb', 'Brokers · Gestión de materiales')
@php $activeRoute = 'materials'; @endphp

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
        <button type="button" class="crm-btn crm-btn-primary" onclick="document.getElementById('matModal').showModal()"><i class="pi pi-plus"></i> Agregar material</button>
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
                        <td class="px-5 py-3.5"><span class="crm-pill {{ $m->badge_color }}">{{ strtoupper($m->format) }}</span></td>
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
                            @if($m->downloadUrl())
                                <a href="{{ $m->downloadUrl() }}" target="_blank" class="text-ink-500 hover:text-brand mr-3" title="Ver"><i class="pi pi-external-link"></i></a>
                            @endif
                            @php $matJson = \Illuminate\Support\Js::from($m->only(['id','title','description','category','format','external_url','sort_order','visible'])); @endphp
                            <button type="button" class="text-ink-500 hover:text-brand mr-3" title="Editar"
                                onclick="openEditMaterial({{ $matJson }})"><i class="pi pi-pencil"></i></button>
                            <form method="POST" action="{{ route('admin.materials.destroy', $m) }}" class="inline" onsubmit="return confirm('¿Eliminar este material?')">
                                @csrf @method('DELETE')
                                <button class="text-ink-400 hover:text-err" title="Eliminar"><i class="pi pi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-[12px] text-ink-400">Aún no hay materiales. Agrega el primero para que tus brokers lo vean.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Modal: alta / edición ===== --}}
<dialog id="matModal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[520px] max-w-[94vw]">
    <form id="matForm" method="POST" action="{{ route('admin.materials.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="_method" id="matMethod" value="POST">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-folder-open"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1" id="matTitle">Agregar material</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Título *</label>
                <input type="text" name="title" id="matInputTitle" required class="crm-input mt-1 w-full">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Descripción</label>
                <textarea name="description" id="matInputDesc" rows="2" class="crm-input mt-1 w-full"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Categoría</label>
                    <input type="text" name="category" id="matInputCat" placeholder="Renders, Brochure, Contrato…" class="crm-input mt-1 w-full">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Formato</label>
                    <input type="text" name="format" id="matInputFmt" placeholder="PDF / ZIP / MP4 / XLSX" class="crm-input mt-1 w-full">
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Archivo (opcional)</label>
                <input type="file" name="file" class="crm-input mt-1 w-full">
                <p class="text-[10px] text-ink-400 mt-1">Si subes un archivo, el tamaño y formato se calculan automáticamente.</p>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">o Enlace externo</label>
                <input type="url" name="external_url" id="matInputUrl" placeholder="https://…" class="crm-input mt-1 w-full">
            </div>
            <div class="grid grid-cols-2 gap-3 items-center">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Orden</label>
                    <input type="number" name="sort_order" id="matInputOrder" value="0" min="0" class="crm-input mt-1 w-full">
                </div>
                <label class="flex items-center gap-2 text-[12px] text-ink-700 mt-5">
                    <input type="checkbox" name="visible" id="matInputVisible" value="1" checked class="accent-brand"> Visible para brokers
                </label>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar</button>
        </div>
    </form>
</dialog>

<script>
    const matForm = document.getElementById('matForm');
    const storeAction = "{{ route('admin.materials.store') }}";
    function openEditMaterial(m) {
        document.getElementById('matTitle').textContent = 'Editar material';
        document.getElementById('matMethod').value = 'PUT';
        matForm.action = "{{ url('admin/materials') }}/" + m.id;
        document.getElementById('matInputTitle').value = m.title || '';
        document.getElementById('matInputDesc').value = m.description || '';
        document.getElementById('matInputCat').value = m.category || '';
        document.getElementById('matInputFmt').value = m.format || '';
        document.getElementById('matInputUrl').value = m.external_url || '';
        document.getElementById('matInputOrder').value = m.sort_order ?? 0;
        document.getElementById('matInputVisible').checked = !!m.visible;
        document.getElementById('matModal').showModal();
    }
    // Reset to create mode when opening via "Agregar"
    document.querySelector('[onclick*="matModal"]')?.addEventListener('click', () => {
        document.getElementById('matTitle').textContent = 'Agregar material';
        document.getElementById('matMethod').value = 'POST';
        matForm.action = storeAction;
        matForm.reset();
        document.getElementById('matInputVisible').checked = true;
    });
</script>
@endsection
