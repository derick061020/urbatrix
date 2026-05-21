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
            <button type="button" onclick="document.getElementById('modal-exportar-unidades').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <a href="{{ route('admin.units.create') }}" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nueva unidad</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#1fc16b">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Unidades disponibles</div>
            <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $countAvailable }}</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#fa7319">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Unidades reservadas</div>
            <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $countReserved }}</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#5c7c68">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">Unidades vendidas</div>
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
                    <input type="text" placeholder="Buscar unidad…" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> Filtros</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>Unidad</th>
                        <th>Tipo</th>
                        <th>Planta</th>
                        <th>Camas/Baños</th>
                        <th>Sqft Int.</th>
                        <th>Sqft Terraza</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Público</th>
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
                                <a href="{{ route('admin.units.edit', $u->id) }}" class="text-[12px] text-brand font-semibold hover:underline">Editar &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-[12px] text-ink-500 py-8">No hay unidades creadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-ink-100">{{ $units->withQueryString()->links() }}</div>
    </div>
</div>

@include('admin.crm._partials.modal_exportar', ['name' => 'Unidades', 'id' => 'modal-exportar-unidades'])

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
            alert('No se pudo cambiar la visibilidad. Intentá de nuevo.');
        })
        .finally(() => { btn.disabled = false; });
    });
})();
</script>
@endpush
@endsection
