@extends('layouts.admin_crm')
@section('title', 'Unidades — CRM Duna Makai')
@section('page_title', 'Unidades')
@section('page_breadcrumb', 'Proyectos · Gestión de unidades')
@php $activeRoute = 'units'; @endphp

@section('content')
@php
    $units = \App\Models\Unit::with('agent')->orderBy('custom_id')->orderBy('id')->paginate(50);
    $countAvailable = \App\Models\Unit::whereIn('status', ['AVAILABLE','available'])->count();
    $countReserved  = \App\Models\Unit::whereIn('status', ['RESERVED','reserved'])->count();
    $countSold      = \App\Models\Unit::whereIn('status', ['SOLD','sold'])->count();

    // Estadísticas de vistas por unidad (mismas métricas que el formulario), en una
    // sola consulta agrupada para las unidades de la página actual.
    $viewStatsByUnit = \App\Models\UnitView::query()
        ->selectRaw('unit_id, COUNT(*) as total')
        ->selectRaw('SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as today', [today()])
        ->selectRaw('SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as week', [now()->subDays(7)])
        ->selectRaw('SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as month', [now()->subDays(30)])
        ->whereIn('unit_id', $units->pluck('id'))
        ->groupBy('unit_id')
        ->get()
        ->keyBy('unit_id');
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $units->total() }} unidades totales</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-unidades').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-config-unidades').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-cog"></i> {{ __('Configuraciones') }}</button>
            <a href="{{ route('admin.units.create') }}" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nueva unidad') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#1fc16b">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Unidades disponibles') }}</div>
            <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $countAvailable }}</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#fa7319">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Unidades reservadas') }}</div>
            <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $countReserved }}</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#5c7c68">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Unidades vendidas') }}</div>
            <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $countSold }}</div>
        </div>
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['Todos','Disponibles','Reservados','Vendidas'] as $i => $tab)
                    <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="{{ __('Buscar unidad…') }}" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> {{ __('Filtros') }}</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>{{ __('Unidad') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Planta') }}</th>
                        <th>{{ __('Camas/Baños') }}</th>
                        <th>{{ __('Sqft Int.') }}</th>
                        <th>{{ __('Sqft Terraza') }}</th>
                        <th>{{ __('Precio') }}</th>
                        <th>{{ __('Vistas') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Cliente') }}</th>
                        <th>{{ __('Público') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusMap = [
                            'AVAILABLE' => ['DISPONIBLE','ok'], 'available' => ['DISPONIBLE','ok'],
                            'RESERVED'  => ['RESERVADA','warn'],  'reserved'  => ['RESERVADA','warn'],
                            'SOLD'      => ['VENDIDA','err'],     'sold'      => ['VENDIDA','err'],
                        ];
                    @endphp
                    @forelse($units as $u)
                        @php $st = $statusMap[$u->status] ?? ['—','ink-500']; @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td class="text-[13px] font-semibold text-ink-900">{{ $u->custom_id ?? $u->name }}</td>
                            <td class="text-[12px] text-ink-700">{{ $u->layout ?? $u->type ?? '—' }}</td>
                            <td class="text-[12px] text-ink-700">{{ $u->floor ?? '—' }}</td>
                            <td class="text-[12px] text-ink-700">{{ $u->bedrooms ?? '—' }}B · {{ $u->bathrooms ?? '—' }}Ba</td>
                            <td class="text-[12px] text-ink-700">{{ $u->internal_area ?? '—' }}</td>
                            <td class="text-[12px] text-ink-700">{{ $u->expense_1 ?? '—' }}</td>
                            <td class="text-[13px] font-bold text-ok-dark">${{ number_format($u->price ?? 0, 0) }}</td>
                            @php
                                $vs      = $viewStatsByUnit[$u->id] ?? null;
                                $vToday  = (int) ($vs->today ?? 0);
                                $vWeek   = (int) ($vs->week  ?? 0);
                                $vMonth  = (int) ($vs->month ?? 0);
                                $vTotal  = (int) ($vs->total ?? 0);
                            @endphp
                            <td>
                                <a href="{{ route('admin.units.edit', $u->id) }}#historial-vistas"
                                   class="inline-flex flex-col group"
                                   title="Hoy: {{ $vToday }} · 7d: {{ $vWeek }} · 30d: {{ $vMonth }} · Total: {{ $vTotal }}">
                                    <span class="flex items-center gap-1.5">
                                        <i class="pi pi-eye text-ink-400 text-[11px] group-hover:text-brand"></i>
                                        <span class="text-[13px] font-bold text-ink-900">{{ number_format($vTotal) }}</span>
                                        @if($vToday > 0)
                                            <span class="crm-pill bg-ok-soft text-ok-dark text-[10px]">+{{ $vToday }} hoy</span>
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-ink-400 mt-0.5">7d {{ $vWeek }} · 30d {{ $vMonth }}</span>
                                </a>
                            </td>
                            <td>
                                <span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span>
                                @if($u->reserved_until && $u->reserved_until->isFuture())
                                    <div class="text-[10px] text-warn mt-1 whitespace-nowrap"><i class="pi pi-clock text-[9px]"></i> Vence {{ $u->reserved_until->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="text-[12px] text-ink-700">{{ ($u->first_name ? ($u->first_name.' '.$u->last_name) : '—') }}</td>
                            <td>
                                <button type="button"
                                        class="crm-toggle {{ $u->public ? 'on' : '' }} unit-toggle-public"
                                        data-unit-id="{{ $u->id }}"
                                        title="{{ $u->public ? 'Visible al público — clic para ocultar' : 'Oculto — clic para hacer público' }}"
                                        aria-pressed="{{ $u->public ? 'true' : 'false' }}"></button>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.units.edit', $u->id) }}" class="text-[12px] text-brand font-semibold hover:underline">{{ __('Editar &rarr;') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="13" class="text-center text-[12px] text-ink-500 py-8">{{ __('No hay unidades creadas.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-ink-100">{{ $units->withQueryString()->links() }}</div>
    </div>
</div>

@include('admin.crm._partials.modal_exportar', ['name' => 'Unidades', 'id' => 'modal-exportar-unidades'])

{{-- ===================== MODAL: CONFIGURACIÓN DE OPCIONES ===================== --}}
@php
    $cfgSections = [
        'types'     => ['label' => 'Tipos de unidad', 'icon' => 'pi-th-large',  'hint' => 'Aparecen en el selector "Tipo" del formulario.'],
        'floors'    => ['label' => 'Plantas / Pisos',  'icon' => 'pi-building',  'hint' => 'Selector "Planta" del formulario y filtro de la home.'],
        'outlooks'  => ['label' => 'Vistas',           'icon' => 'pi-eye',       'hint' => 'Selector "Vista" del formulario y filtro de la home.'],
        'addresses' => ['label' => 'Direcciones',      'icon' => 'pi-map-marker','hint' => 'Sugerencias para el campo "Dirección".'],
        'amenities' => ['label' => 'Amenidades',       'icon' => 'pi-star',      'hint' => 'Tarjetas de amenidades del formulario y front.'],
    ];
    $amenityIcons = \App\Support\UnitOptions::amenityIcons();
@endphp
<dialog id="modal-config-unidades" class="rounded-2xl p-0 w-full max-w-3xl backdrop:bg-black/40">
    <form method="POST" action="{{ route('admin.units.options') }}" class="flex flex-col max-h-[88vh]">
        @csrf
        <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="pi pi-cog text-brand"></i>
                <h2 class="text-[15px] font-bold text-ink-900">{{ __('Configuración de opciones') }}</h2>
            </div>
            <button type="button" onclick="document.getElementById('modal-config-unidades').close()" class="text-ink-400 hover:text-ink-700"><i class="pi pi-times"></i></button>
        </div>

        <div class="px-6 pt-3 border-b border-ink-100 flex items-center gap-1 overflow-x-auto">
            @foreach($cfgSections as $cat => $meta)
                <button type="button" data-cfg-tab="{{ $cat }}" class="cfg-tab px-3 py-2 text-[12px] font-semibold whitespace-nowrap border-b-2 {{ $loop->first ? 'border-brand text-brand' : 'border-transparent text-ink-500' }}">
                    <i class="pi {{ $meta['icon'] }} text-[11px]"></i> {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            @foreach($cfgSections as $cat => $meta)
                <section data-cfg-section="{{ $cat }}" class="{{ $loop->first ? '' : 'hidden' }}">
                    <p class="text-[12px] text-ink-500 mb-3">{{ $meta['hint'] }}</p>
                    <div data-cfg-rows="{{ $cat }}" class="space-y-2"></div>
                    <button type="button" data-cfg-add="{{ $cat }}" class="crm-btn crm-btn-ghost mt-3 text-[12px]"><i class="pi pi-plus"></i> {{ __('Agregar') }}</button>
                </section>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-config-unidades').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Guardar cambios') }}</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
    const urlTpl = "{{ url('admin/units') }}/__ID__/toggle-public";

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.unit-toggle-public');
        if (!btn) return;
        const id = btn.dataset.unitId;
        if (!id) return;

        // Optimistic UI flip
        const wasOn = btn.classList.contains('on');
        btn.classList.toggle('on');
        btn.setAttribute('aria-pressed', String(!wasOn));
        btn.disabled = true;

        fetch(urlTpl.replace('__ID__', id), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            const isOn = !!data.public;
            btn.classList.toggle('on', isOn);
            btn.setAttribute('aria-pressed', String(isOn));
            btn.title = isOn ? 'Visible al público — clic para ocultar' : 'Oculto — clic para hacer público';
        })
        .catch(err => {
            console.error('Toggle public failed', err);
            // Revert
            btn.classList.toggle('on', wasOn);
            btn.setAttribute('aria-pressed', String(wasOn));
            alert('{{ __("No se pudo cambiar la visibilidad. Intentá de nuevo.") }}');
        })
        .finally(() => { btn.disabled = false; });
    });
})();
</script>

{{-- Editor de opciones globales (tipos, plantas, vistas, direcciones, amenidades) --}}
<script>
(function () {
    const data  = @json($unitOptions);
    const icons = @json($amenityIcons);
    const iconKeys = Object.keys(icons);
    const counters = {};

    const esc = s => String(s ?? '').replace(/"/g, '&quot;');

    function iconOptions(selected) {
        return iconKeys.map(k => `<option value="${k}" ${k === selected ? 'selected' : ''}>${k}</option>`).join('');
    }

    function iconPreview(key) {
        return icons[key] || icons['check'] || '';
    }

    function makeRow(cat, row) {
        row = row || {};
        const i = (counters[cat] = (counters[cat] ?? -1) + 1);
        const base = `${cat}[${i}]`;
        const wrap = document.createElement('div');
        wrap.className = 'flex items-center gap-2';

        if (cat === 'addresses') {
            wrap.innerHTML = `
                <input type="text" name="${base}[label]" value="${esc(row.label)}" placeholder="Dirección" class="crm-input pl-3 flex-1">
                <input type="hidden" name="${base}[value]" value="${esc(row.value)}">
                <button type="button" class="crm-btn crm-btn-ghost px-2 cfg-del" title="Quitar"><i class="pi pi-trash text-err"></i></button>`;
        } else if (cat === 'amenities') {
            const sel = row.icon || 'check';
            wrap.innerHTML = `
                <span class="cfg-icon-prev w-8 h-8 flex items-center justify-center text-ink-500 shrink-0">${iconPreview(sel)}</span>
                <input type="text" name="${base}[label]" value="${esc(row.label)}" placeholder="Etiqueta" class="crm-input pl-3 flex-1">
                <input type="text" name="${base}[value]" value="${esc(row.value)}" placeholder="valor (opcional)" class="crm-input pl-3 w-32">
                <select name="${base}[icon]" class="crm-input pl-3 w-28 cfg-icon-sel">${iconOptions(sel)}</select>
                <button type="button" class="crm-btn crm-btn-ghost px-2 cfg-del" title="Quitar"><i class="pi pi-trash text-err"></i></button>`;
        } else {
            wrap.innerHTML = `
                <input type="text" name="${base}[label]" value="${esc(row.label)}" placeholder="Etiqueta" class="crm-input pl-3 flex-1">
                <input type="text" name="${base}[value]" value="${esc(row.value)}" placeholder="valor (opcional)" class="crm-input pl-3 w-40">
                <button type="button" class="crm-btn crm-btn-ghost px-2 cfg-del" title="Quitar"><i class="pi pi-trash text-err"></i></button>`;
        }
        return wrap;
    }

    // Render inicial
    Object.keys(data).forEach(cat => {
        const host = document.querySelector(`[data-cfg-rows="${cat}"]`);
        if (!host) return;
        (data[cat] || []).forEach(row => host.appendChild(makeRow(cat, row)));
    });

    // Delegación de eventos dentro del modal
    const modal = document.getElementById('modal-config-unidades');
    modal.addEventListener('click', function (e) {
        const add = e.target.closest('[data-cfg-add]');
        if (add) {
            const cat = add.dataset.cfgAdd;
            document.querySelector(`[data-cfg-rows="${cat}"]`).appendChild(makeRow(cat));
            return;
        }
        const del = e.target.closest('.cfg-del');
        if (del) { del.closest('.flex').remove(); return; }

        const tab = e.target.closest('[data-cfg-tab]');
        if (tab) {
            const cat = tab.dataset.cfgTab;
            modal.querySelectorAll('[data-cfg-tab]').forEach(t => {
                const on = t.dataset.cfgTab === cat;
                t.classList.toggle('border-brand', on);
                t.classList.toggle('text-brand', on);
                t.classList.toggle('border-transparent', !on);
                t.classList.toggle('text-ink-500', !on);
            });
            modal.querySelectorAll('[data-cfg-section]').forEach(s => {
                s.classList.toggle('hidden', s.dataset.cfgSection !== cat);
            });
        }
    });

    // Actualiza el preview del ícono al cambiar el select
    modal.addEventListener('change', function (e) {
        const sel = e.target.closest('.cfg-icon-sel');
        if (!sel) return;
        const prev = sel.closest('.flex').querySelector('.cfg-icon-prev');
        if (prev) prev.innerHTML = iconPreview(sel.value);
    });
})();
</script>
@endpush
@endsection
