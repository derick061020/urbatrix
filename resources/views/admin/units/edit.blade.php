@extends('layouts.admin_crm')
@section('title', 'Editar unidad — CRM Duna Makai')
@section('page_title', 'Editar unidad')
@section('page_breadcrumb', 'Proyectos · Unidades · ' . ($unit->custom_id ?? $unit->name))
@php $activeRoute = 'units'; @endphp

@section('content')
@php
    $statusPill = [
        'AVAILABLE' => ['Disponible','ok'],
        'PENDING'   => ['Pendiente','info'],
        'RESERVED'  => ['Reservada','warn'],
        'HELD'      => ['En espera','warn'],
        'SOLD'      => ['Vendida','err'],
    ];
    $sp = $statusPill[strtoupper($unit->status ?? 'AVAILABLE')] ?? ['—','ink-500'];
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="px-4 py-3 rounded-lg bg-err-soft border border-err/30 text-err text-[12px] space-y-0.5">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    {{-- Header --}}
    <div class="crm-card p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <a href="{{ route('admin.units') }}" class="w-10 h-10 rounded-full border border-ink-200 flex items-center justify-center text-ink-600 hover:bg-ink-50 shrink-0"><i class="pi pi-arrow-left text-[12px]"></i></a>
        <div class="flex-1 min-w-0">
            <div class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">Unidad</div>
            <div class="text-[20px] font-bold text-ink-950 leading-tight truncate">{{ $unit->custom_id ?? $unit->name }}</div>
            <div class="text-[12px] text-ink-500 mt-0.5">{{ ucfirst($unit->type ?? '—') }} · ${{ number_format($unit->price ?? 0) }}</div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="crm-pill bg-{{ $sp[1] }}-soft text-{{ $sp[1] }}">{{ strtoupper($sp[0]) }}</span>
            <button type="submit" form="unit-edit-form" class="crm-btn crm-btn-primary"><i class="pi pi-save"></i> Guardar cambios</button>
            <button type="submit" form="unit-delete-form" onclick="return confirm('¿Eliminar esta unidad? Esta acción no se puede deshacer.');" class="crm-btn crm-btn-ghost text-err"><i class="pi pi-trash"></i> Eliminar</button>
        </div>
    </div>

    {{-- Main form --}}
    <form id="unit-edit-form" method="POST" action="{{ route('admin.units.update', $unit->id) }}" class="space-y-4">
        @csrf @method('PUT')
        @include('admin.units._partials.form_fields', ['unit' => $unit, 'agents' => $agents])
    </form>

    {{-- Delete form (outside main form to avoid nesting) --}}
    <form id="unit-delete-form" method="POST" action="{{ route('admin.units.delete', $unit->id) }}" class="hidden">@csrf @method('DELETE')</form>

    {{-- ===================== VIEW HISTORY ===================== --}}
    <div id="historial-vistas" class="crm-card scroll-mt-24">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
            <i class="pi pi-eye text-ink-500"></i>
            <div class="text-[13px] font-bold text-ink-700">Historial de vistas</div>
            <span class="ml-auto text-[11px] text-ink-500">Última actualización: {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @php
                    $stats = [
                        ['Hoy',          $viewStats['today']  ?? 0, '#5c7c68'],
                        ['Últimos 7d',   $viewStats['week']   ?? 0, '#335cff'],
                        ['Últimos 30d',  $viewStats['month']  ?? 0, '#fa7319'],
                        ['Total',        $viewStats['total']  ?? 0, '#717784'],
                    ];
                @endphp
                @foreach($stats as [$label, $val, $color])
                    <div class="rounded-xl border border-ink-200 bg-white p-4">
                        <div class="text-[10px] uppercase font-semibold tracking-wide text-ink-500">{{ $label }}</div>
                        <div class="text-[22px] font-bold mt-1" style="color:{{ $color }}">{{ number_format($val) }}</div>
                    </div>
                @endforeach
            </div>

            @if(($recentViews ?? collect())->isEmpty())
                <div class="text-center py-6 text-[12px] text-ink-500">
                    <i class="pi pi-info-circle"></i> Esta unidad aún no registra vistas.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-ink-200">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>IP</th>
                                <th class="text-right">User agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentViews as $v)
                                <tr>
                                    <td>
                                        <div class="text-[12px] text-ink-900">{{ \Carbon\Carbon::parse($v->viewed_at)->format('d/m/Y H:i:s') }}</div>
                                        <div class="text-[10px] text-ink-500">{{ \Carbon\Carbon::parse($v->viewed_at)->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        @if($v->user)
                                            <div class="text-[12px] font-semibold text-ink-900">{{ $v->user->name }}</div>
                                            <div class="text-[10px] text-ink-500">{{ $v->user->email }}</div>
                                        @else
                                            <span class="text-[12px] text-ink-500 italic">Anónimo · sesión {{ \Illuminate\Support\Str::limit($v->session_id, 8, '') }}…</span>
                                        @endif
                                    </td>
                                    <td><code class="text-[11px] text-ink-700">{{ $v->ip ?? '—' }}</code></td>
                                    <td class="text-right text-[11px] text-ink-500 max-w-[260px] truncate" title="{{ $v->user_agent }}">{{ $v->user_agent ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== IMAGES ===================== --}}
    <div class="crm-card">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
            <i class="pi pi-image text-ink-500"></i>
            <div class="text-[13px] font-bold text-ink-700">Imágenes de la unidad</div>
            <span class="ml-auto text-[11px] text-ink-500">{{ $unit->images->count() }} imágenes</span>
        </div>

        <div class="p-5 space-y-4">
            {{-- Upload area --}}
            <div id="image-upload-container"
                 data-upload-url="{{ route('admin.units.images.upload', $unit->id) }}"
                 data-csrf="{{ csrf_token() }}"
                 class="border-2 border-dashed border-ink-200 rounded-xl p-6 text-center hover:border-brand/40 transition-colors">
                <i class="pi pi-cloud-upload text-[28px] text-ink-400"></i>
                <p class="text-[13px] text-ink-700 mt-2">Arrastrá imágenes aquí o</p>
                <input type="file" multiple accept="image/*" class="hidden" id="image-upload">
                <label for="image-upload" class="crm-btn crm-btn-ghost mt-3 inline-flex"><i class="pi pi-plus text-[10px]"></i> Elegir archivos</label>
                <p class="mt-3 text-[11px] text-ink-400">JPG, PNG, WEBP o GIF · máx. 5 MB · hasta 10 imágenes por carga</p>

                <div id="upload-progress" class="hidden mt-4 max-w-md mx-auto">
                    <div class="crm-progress"><span id="progress-bar" class="bg-brand" style="width:0%"></span></div>
                    <p class="text-[11px] text-ink-500 mt-1">Subiendo… <span id="progress-text">0%</span></p>
                </div>
                <div id="upload-status" class="hidden mt-3"></div>
            </div>

            {{-- Sortable images table --}}
            <div class="overflow-x-auto">
                <table class="w-full crm-table">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="w-8"></th>
                            <th>Preview</th>
                            <th>Nombre</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="unit-images-tbody"
                           data-reorder-url="{{ route('admin.units.images.reorder', $unit->id) }}"
                           data-delete-url-template="{{ route('admin.units.images.delete', [$unit->id, '__ID__']) }}"
                           data-csrf="{{ csrf_token() }}">
                        @forelse($unit->images as $image)
                            <tr data-image-id="{{ $image->id }}">
                                <td>
                                    <span class="image-drag-handle cursor-grab active:cursor-grabbing text-ink-400" title="Arrastrar para reordenar">
                                        <i class="pi pi-bars"></i>
                                    </span>
                                </td>
                                <td>
                                    <img src="{{ $image->path ?: '#' }}" alt="{{ $image->name }}" class="w-16 h-16 object-cover rounded-lg bg-ink-100 border border-ink-200">
                                </td>
                                <td class="text-[12px] text-ink-700">
                                    <div class="max-w-xs truncate" title="{{ $image->name }}">{{ $image->name }}</div>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="image-delete-btn inline-flex items-center gap-1 px-2 py-1 rounded-md text-err hover:bg-err-soft text-[11px] font-semibold"><i class="pi pi-trash text-[10px]"></i> Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr id="unit-images-empty"><td colspan="4" class="text-center text-[12px] text-ink-500 py-6">Sin imágenes todavía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== HISTORY ===================== --}}
    <div class="grid grid-cols-1 gap-4">

        {{-- Unit history --}}
        <div class="crm-card">
            <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
                <i class="pi pi-history text-ink-500"></i>
                <div class="text-[13px] font-bold text-ink-700">Historial de la unidad</div>
                <span class="ml-auto text-[11px] text-ink-500">{{ $unit->histories->count() }} eventos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full crm-table">
                    <thead class="bg-white">
                        <tr><th>Fecha</th><th>Acción</th><th>Autor</th><th>Rol</th></tr>
                    </thead>
                    <tbody>
                        @forelse($unit->histories as $row)
                            <tr>
                                <td class="text-[12px] text-ink-700">{{ $row->datetime?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td><span class="crm-pill bg-info-soft text-info">{{ $row->action }}</span></td>
                                <td class="text-[12px] text-ink-700">{{ $row->author ?? '—' }}</td>
                                <td>
                                    @php $rolePill = match(strtoupper($row->author_role ?? '')) {
                                        'SUPERADMIN' => ['SUPERADMIN','err'],
                                        'ADMIN'      => ['ADMIN','warn'],
                                        default      => [$row->author_role ?? '—','ink-500'],
                                    }; @endphp
                                    <span class="crm-pill bg-{{ $rolePill[1] }}-soft text-{{ $rolePill[1] }}">{{ $rolePill[0] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-[12px] text-ink-500 py-6">Sin historial.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payment history --}}
        <div class="crm-card">
            <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
                <i class="pi pi-credit-card text-ink-500"></i>
                <div class="text-[13px] font-bold text-ink-700">Historial de pagos</div>
                <span class="ml-auto text-[11px] text-ink-500">{{ $unit->paymentHistories->count() }} eventos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full crm-table">
                    <thead class="bg-white">
                        <tr>
                            <th>Creado</th>
                            <th>Por</th>
                            <th>Modificado</th>
                            <th>Por</th>
                            <th>Monto (USD)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unit->paymentHistories as $row)
                            <tr>
                                <td class="text-[12px] text-ink-700">{{ $row->created_at_event?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-[12px] text-ink-700">{{ $row->created_by ?: '—' }}</td>
                                <td class="text-[12px] text-ink-700">{{ $row->modified_at_event?->format('Y-m-d H:i') ?: '—' }}</td>
                                <td class="text-[12px] text-ink-700">{{ $row->modified_by ?: '—' }}</td>
                                <td class="text-[13px] font-bold text-ok-dark">${{ number_format($row->amount, 0, '.', ',') }}</td>
                                <td>
                                    @if(strtoupper($row->status ?? '') === 'SUCCESS')
                                        <span class="crm-pill bg-ok-soft text-ok">EXITOSO</span>
                                    @else
                                        <span class="crm-pill bg-warn-soft text-warn">{{ strtoupper($row->status ?? '—') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">Sin pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-2">
        <a href="{{ route('admin.units') }}" class="crm-btn crm-btn-ghost">Volver a unidades</a>
    </div>
</div>

{{-- ===================== FLOATING AUTOSAVE BAR ===================== --}}
<div id="unit-save-bar" class="fixed bottom-5 right-5 z-50 flex items-center gap-2 bg-white border border-ink-200 shadow-panel rounded-full pl-4 pr-2 py-2">
    <span id="unit-save-status" class="flex items-center gap-2 text-[12px] font-semibold text-ink-500 whitespace-nowrap">
        <i class="pi pi-check-circle text-ok"></i> <span data-save-text>Guardado</span>
    </span>
    <button type="button" id="unit-discard-btn" class="crm-btn crm-btn-ghost text-err text-[12px] py-1.5 px-3 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
        <i class="pi pi-undo text-[11px]"></i> Descartar cambios
    </button>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const tbody = document.getElementById('unit-images-tbody');
    if (!tbody) return;

    const csrf         = tbody.dataset.csrf;
    const reorderUrl   = tbody.dataset.reorderUrl;
    const deleteUrlTpl = tbody.dataset.deleteUrlTemplate;

    function currentOrder() {
        return Array.from(tbody.querySelectorAll('tr[data-image-id]'))
            .map(tr => parseInt(tr.dataset.imageId, 10));
    }

    function sendReorder() {
        const order = currentOrder();
        if (order.length === 0) return;
        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ order }),
        }).catch(err => console.error('Reorder failed', err));
    }

    Sortable.create(tbody, {
        handle: '.image-drag-handle',
        animation: 150,
        ghostClass: 'opacity-40',
        onEnd: sendReorder,
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.image-delete-btn');
        if (!btn) return;
        const tr = btn.closest('tr[data-image-id]');
        if (!tr) return;
        const id = tr.dataset.imageId;
        if (!confirm('¿Eliminar esta imagen?')) return;

        fetch(deleteUrlTpl.replace('__ID__', id), {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); tr.remove();
            if (!tbody.querySelector('tr[data-image-id]')) {
                tbody.insertAdjacentHTML('beforeend',
                    '<tr id="unit-images-empty"><td colspan="4" class="text-center text-[12px] text-ink-500 py-6">Sin imágenes todavía.</td></tr>');
            }
        })
        .catch(err => { console.error('Delete failed', err); alert('No se pudo eliminar la imagen.'); });
    });
})();

(function() {
    const uploadContainer = document.getElementById('image-upload-container');
    const fileInput     = document.getElementById('image-upload');
    const progressBar   = document.getElementById('progress-bar');
    const progressText  = document.getElementById('progress-text');
    const uploadProgress= document.getElementById('upload-progress');
    const uploadStatus  = document.getElementById('upload-status');
    const imagesTbody   = document.getElementById('unit-images-tbody');

    if (!uploadContainer || !fileInput) return;
    const uploadUrl = uploadContainer.dataset.uploadUrl;
    const csrf      = uploadContainer.dataset.csrf;

    fileInput.addEventListener('change', e => { if (e.target.files.length) uploadFiles(e.target.files); });
    uploadContainer.addEventListener('dragover', e => { e.preventDefault(); uploadContainer.classList.add('bg-ink-50','border-brand'); });
    uploadContainer.addEventListener('dragleave', e => { e.preventDefault(); uploadContainer.classList.remove('bg-ink-50','border-brand'); });
    uploadContainer.addEventListener('drop', e => {
        e.preventDefault(); uploadContainer.classList.remove('bg-ink-50','border-brand');
        if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
    });

    function uploadFiles(files) {
        const formData = new FormData();
        const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
        if (imageFiles.length === 0)  return showStatus('Seleccioná archivos de imagen válidos', 'error');
        if (imageFiles.length > 10)   return showStatus('Máximo 10 imágenes por carga', 'error');
        imageFiles.forEach(f => formData.append('images[]', f));

        uploadProgress.classList.remove('hidden');
        uploadStatus.classList.add('hidden');
        progressBar.style.width = '0%'; progressText.textContent = '0%';

        fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                progressBar.style.width = '100%'; progressText.textContent = '100%';
                showStatus(`${data.images.length} imagen(es) subida(s) correctamente`, 'success');
                addImagesToTable(data.images);
                const emptyRow = document.getElementById('unit-images-empty');
                if (emptyRow) emptyRow.remove();
                fileInput.value = '';
                setTimeout(() => uploadProgress.classList.add('hidden'), 1800);
            } else {
                showStatus('Error al subir imágenes', 'error');
                uploadProgress.classList.add('hidden');
            }
        })
        .catch(err => { console.error('Upload error', err); showStatus('Error: ' + err.message, 'error'); uploadProgress.classList.add('hidden'); });
    }

    function addImagesToTable(images) {
        if (!imagesTbody) return;
        images.forEach(image => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-image-id', image.id);
            tr.innerHTML = `
                <td><span class="image-drag-handle cursor-grab active:cursor-grabbing text-ink-400" title="Arrastrar para reordenar"><i class="pi pi-bars"></i></span></td>
                <td><img src="${image.path}" alt="${image.name}" class="w-16 h-16 object-cover rounded-lg bg-ink-100 border border-ink-200"></td>
                <td class="text-[12px] text-ink-700"><div class="max-w-xs truncate" title="${image.name}">${image.name}</div></td>
                <td class="text-right"><button type="button" class="image-delete-btn inline-flex items-center gap-1 px-2 py-1 rounded-md text-err hover:bg-err-soft text-[11px] font-semibold"><i class="pi pi-trash text-[10px]"></i> Eliminar</button></td>
            `;
            imagesTbody.appendChild(tr);
        });
    }

    function showStatus(message, type) {
        uploadStatus.classList.remove('hidden');
        uploadStatus.className = `px-3 py-2 rounded-md text-[12px] ${type === 'success' ? 'bg-ok-soft text-ok-dark border border-ok/30' : 'bg-err-soft text-err border border-err/30'}`;
        uploadStatus.textContent = message;
        setTimeout(() => uploadStatus.classList.add('hidden'), 5000);
    }
})();

/* ===================== AUTOSAVE + DISCARD ===================== */
(function () {
    const form = document.getElementById('unit-edit-form');
    const bar  = document.getElementById('unit-save-bar');
    if (!form || !bar) return;

    const statusWrap   = document.getElementById('unit-save-status');
    const statusText   = statusWrap.querySelector('[data-save-text]');
    const statusIcon   = statusWrap.querySelector('i');
    const discardBtn   = document.getElementById('unit-discard-btn');
    const csrf         = document.querySelector('meta[name=csrf-token]')?.content;
    const DEBOUNCE_MS  = 1200;

    const controls = () => form.querySelectorAll('input, select, textarea');

    function snapshot() {
        const map = new Map();
        controls().forEach(el => {
            if (!el.name) return;
            map.set(el, (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value);
        });
        return map;
    }
    function restore(map) {
        map.forEach((val, el) => {
            if (el.type === 'checkbox' || el.type === 'radio') el.checked = val;
            else el.value = val;
        });
    }
    function differs(map) {
        for (const [el, val] of map) {
            const cur = (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value;
            if (cur !== val) return true;
        }
        return false;
    }

    // initial = state at page load (target for "discard"); saved = last persisted state.
    const initial = snapshot();
    let savedState = snapshot();
    let timer = null;
    let saving = false;

    function setStatus(icon, text, color) {
        statusIcon.className = 'pi ' + icon + ' ' + color;
        statusText.textContent = text;
        statusWrap.className = 'flex items-center gap-2 text-[12px] font-semibold whitespace-nowrap ' + color;
    }
    function refreshDiscard() {
        discardBtn.disabled = saving || !differs(initial);
    }

    function doSave() {
        if (saving) return;
        if (!differs(savedState)) { setStatus('pi-check-circle', 'Guardado', 'text-ok'); refreshDiscard(); return; }
        saving = true;
        setStatus('pi-spin pi-spinner', 'Guardando…', 'text-ink-500');
        refreshDiscard();

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        })
        .then(r => {
            if (r.status === 422) return r.json().then(d => { throw { validation: d }; });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json().catch(() => ({}));
        })
        .then(data => {
            savedState = snapshot();
            const at = (data && data.saved_at) ? ' ' + data.saved_at : '';
            setStatus('pi-check-circle', 'Guardado' + at, 'text-ok');
        })
        .catch(err => {
            if (err && err.validation) {
                const first = Object.values(err.validation.errors || {})[0];
                setStatus('pi-exclamation-triangle', first ? first[0] : 'Error de validación', 'text-err');
            } else {
                console.error('Autosave failed', err);
                setStatus('pi-exclamation-triangle', 'Error al guardar', 'text-err');
            }
        })
        .finally(() => { saving = false; refreshDiscard(); });
    }

    function scheduleSave() {
        setStatus('pi-pencil', 'Cambios sin guardar…', 'text-warn');
        refreshDiscard();
        clearTimeout(timer);
        timer = setTimeout(doSave, DEBOUNCE_MS);
    }

    form.addEventListener('input', scheduleSave);
    form.addEventListener('change', scheduleSave);

    // Submitting manually shouldn't double-fire; let autosave own it.
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        doSave();
    });

    discardBtn.addEventListener('click', function () {
        clearTimeout(timer);
        restore(initial);
        if (differs(savedState)) {
            doSave(); // persist the revert so the DB matches the page-load state
        } else {
            setStatus('pi-check-circle', 'Guardado', 'text-ok');
            refreshDiscard();
        }
    });

    // Warn if leaving with an in-flight/pending change
    window.addEventListener('beforeunload', function (e) {
        if (saving || differs(savedState)) { e.preventDefault(); e.returnValue = ''; }
    });

    refreshDiscard();
})();
</script>
@endpush
@endsection
