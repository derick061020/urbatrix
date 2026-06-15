@extends('layouts.admin_crm')
@section('title', 'Documentos — CRM Duna Makai')
@section('page_title', 'Documentos')
@section('page_breadcrumb', 'Gestión · Documentos y archivos')
@php $activeRoute = 'crm.documentos'; @endphp

@section('content')
@php
    $countByStatus = \App\Models\Document::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $documents->total() }} archivos en total</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-documentos').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Subir documento') }}</button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpi = [
            ['n' => $countByStatus['pending'] ?? 0,                                  'label' => 'Pendientes revisión', 'color' => '#fa7319', 'tab' => 'pending'],
            ['n' => ($countByStatus['generated'] ?? 0)+($countByStatus['signed'] ?? 0), 'label' => 'Generados / Firmados','color' => '#335cff', 'tab' => 'generated'],
            ['n' => $countByStatus['rejected'] ?? 0,                                 'label' => 'Vencidos / Rechazados','color' => '#fb3748','tab' => 'rejected'],
            ['n' => $countByStatus['approved'] ?? 0,                                 'label' => 'Aprobados',           'color' => '#1fc16b','tab' => 'approved'],
        ]; @endphp
        @foreach($kpi as $k)
            <a href="?estado={{ $k['tab'] }}" data-docs-filter class="crm-card p-4 border-t-[3px] block hover:shadow-card transition-shadow" style="border-top-color: {{ $k['color'] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k['label'] }}</div>
                <div class="text-[28px] font-bold text-ink-900 leading-tight mt-1">{{ $k['n'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="crm-card" id="documentos-content">
        @include('admin.crm._partials.documentos_list', ['documents' => $documents, 'tab' => $tab])
    </div>
</div>

@include('admin.crm._partials.modal_subir_documento')
@include('admin.crm._partials.modal_exportar', ['name' => 'Documentos', 'id' => 'modal-exportar-documentos'])
@include('admin.crm._partials.document_preview_modal')

@push('scripts')
<script>
(function () {
    const content = document.getElementById('documentos-content');
    if (!content) return;

    function loadDocumentos(url, { push = true } = {}) {
        content.classList.add('opacity-60');
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
        })
            .then(r => {
                if (!r.ok) throw new Error('Error al cargar documentos');
                return r.text();
            })
            .then(html => {
                content.innerHTML = html;
                if (push) history.pushState({}, '', url);
            })
            .catch(err => console.error(err))
            .finally(() => content.classList.remove('opacity-60'));
    }

    // KPI cards (outside the swapped region) — intercept once
    document.querySelectorAll('[data-docs-filter]').forEach(a => {
        if (a.dataset.bound) return;
        a.dataset.bound = '1';
        a.addEventListener('click', e => {
            if (a.target === '_blank' || e.metaKey || e.ctrlKey) return;
            e.preventDefault();
            loadDocumentos(a.href);
        });
    });

    // Tabs + pagination (inside swapped region) — event delegation
    content.addEventListener('click', e => {
        const link = e.target.closest('[data-docs-filter], .pagination a, nav[role="navigation"] a');
        if (!link) return;
        if (link.target === '_blank' || e.metaKey || e.ctrlKey) return;
        e.preventDefault();
        loadDocumentos(link.href);
    });

    window.addEventListener('popstate', () => {
        loadDocumentos(window.location.href, { push: false });
    });
})();
</script>
@endpush
@endsection
