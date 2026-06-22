@extends('layouts.admin_crm')
@section('title', 'Brokers — CRM Duna Makai')
@section('page_title', 'Brokers')
@section('page_breadcrumb', 'Equipo · Brokers con acceso al panel')
@php $activeRoute = 'agents'; @endphp

@section('content')
@php
    $totalBrokers = $brokers->count();
    $activos      = $brokers->where('verification_status', 'approved')->count();
    $pendientes   = $brokers->where('verification_status', 'pending')->count();
    $sinUnidades  = $brokers->filter(fn($b) => $b->assignedUnits->isEmpty())->count();
    $totalAsignaciones = $brokers->sum(fn($b) => $b->assignedUnits->count());
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $totalBrokers }} brokers registrados</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-brokers').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-nuevo-broker').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nuevo broker') }}</button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpis = [
            ['Activos',           $activos,           '#1fc16b'],
            ['Pendientes',        $pendientes,        '#fa7319'],
            ['Sin unidades',      $sinUnidades,       '#fb3748'],
            ['Asignaciones',      $totalAsignaciones, '#335cff'],
        ]; @endphp
        @foreach($kpis as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color:{{ $k[2] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $k[1] }}</div>
            </div>
        @endforeach
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['Todos','Activos','Pendientes','Sin unidades'] as $i => $tab)
                    <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="{{ __('Buscar broker…') }}" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> {{ __('Filtros') }}</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>{{ __('Broker') }}</th>
                        <th>{{ __('Unidades asignadas') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Registrado') }}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $avBg = ['#a6c5b3','#f3b04f','#cdd6df','#d6a3c6','#7cb8e7','#d56a6a']; @endphp
                    @forelse($brokers as $b)
                        @php
                            $init = strtoupper(substr($b->first_name ?? $b->name ?? 'B', 0, 1) . substr($b->last_name ?? '', 0, 1));
                            if (! trim($init)) $init = strtoupper(substr($b->name ?? 'B', 0, 2));
                            $bg = $avBg[$b->id % count($avBg)];
                            $verif = $b->verification_status ?? 'approved';
                            $st = match($verif) {
                                'approved' => ['ACTIVO','ok'],
                                'pending'  => ['PENDIENTE','warn'],
                                'rejected' => ['RECHAZADO','err'],
                                default    => ['ACTIVO','ok'],
                            };
                            $assignedIds = $b->assignedUnits->pluck('id')->all();
                            $rate = (float) ($rates[$b->email] ?? 0);
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-900">{{ $b->name }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $b->email }}{{ $b->phone ? ' · '.$b->phone : '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($b->assignedUnits->isEmpty())
                                    <span class="text-[12px] text-ink-400">{{ __('Sin asignar') }}</span>
                                @else
                                    <div class="flex flex-wrap gap-1 max-w-[320px]">
                                        @foreach($b->assignedUnits->take(4) as $u)
                                            <span class="crm-pill bg-info-soft text-info">{{ $u->custom_id ?? $u->name }}</span>
                                        @endforeach
                                        @if($b->assignedUnits->count() > 4)
                                            <span class="crm-pill bg-ink-100 text-ink-600">+{{ $b->assignedUnits->count() - 4 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="text-[12px] text-ink-500">{{ $b->created_at?->diffForHumans() }}</td>
                            <td class="text-right">
                                <button type="button"
                                        onclick="document.getElementById('modal-broker-{{ $b->id }}').showModal()"
                                        class="crm-btn crm-btn-ghost text-[11px]">
                                    <i class="pi pi-eye"></i> Ver broker
                                </button>
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.agents.delete', $b->id) }}" class="m-0 inline"
                                      onsubmit="return confirm('¿Eliminar broker {{ $b->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[12px] text-err font-semibold hover:underline">{{ __('Eliminar') }}</button>
                                </form>
                            </td>
                        </tr>

                        @include('admin._partials.broker_modal', ['b' => $b, 'rate' => $rate, 'init' => $init, 'bg' => $bg, 'units' => $units, 'assignedIds' => $assignedIds])
                    @empty
                        <tr><td colspan="7" class="text-center text-[12px] text-ink-500 py-8">{{ __('No hay brokers todavía.') }} <button type="button" onclick="document.getElementById('modal-nuevo-broker').showModal()" class="text-brand font-semibold hover:underline">{{ __('Crear uno') }}</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<dialog id="modal-nuevo-broker" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.agents.store') }}" class="w-[560px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">{{ __('Nuevo broker') }}</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre completo') }}</label><input type="text" name="name" required class="crm-input pl-3 mt-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Email') }}</label><input type="email" name="email" required class="crm-input pl-3 mt-1"></div>
                <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Teléfono') }}</label><input type="text" name="phone" class="crm-input pl-3 mt-1"></div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Contraseña temporal') }}</label>
                <input type="text" name="password" placeholder="{{ __('(se genera una si la dejas vacía)') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Unidades asignadas (opcional)') }}</label>
                <div class="mt-1 max-h-[200px] overflow-y-auto border border-ink-100 rounded-lg p-2 space-y-1">
                    @forelse($units as $u)
                        <label class="flex items-center gap-2 text-[12px] px-2 py-1 hover:bg-ink-50 rounded cursor-pointer">
                            <input type="checkbox" name="unit_ids[]" value="{{ $u->id }}" class="w-4 h-4 accent-brand">
                            <span class="font-semibold text-ink-900">{{ $u->custom_id ?? $u->name }}</span>
                            <span class="text-ink-500">· {{ $u->status }}</span>
                        </label>
                    @empty
                        <div class="text-[12px] text-ink-500 text-center py-3">{{ __('No hay unidades.') }}</div>
                    @endforelse
                </div>
            </div>
            <div class="flex items-center gap-2"><input type="checkbox" name="active" value="1" checked class="w-4 h-4 accent-brand"><label class="text-[12px] text-ink-700">{{ __('Activar inmediatamente') }}</label></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Crear broker') }}</button>
        </div>
    </form>
</dialog>

{{-- ===== Modal compartido: previsualización de contrato ===== --}}
<dialog id="brkDocPreview" class="rounded-2xl p-0 backdrop:bg-black/50 m-auto w-[860px] max-w-[95vw]">
    <div class="bg-white rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-eye"></i></div>
            <div class="min-w-0 flex-1">
                <div class="text-[15px] font-bold text-ink-900 truncate" id="brkDocTitle">{{ __('Vista previa') }}</div>
                <div class="text-[11px] text-ink-400 uppercase tracking-wider" id="brkDocFmt"></div>
            </div>
            <a href="#" id="brkDocDownload" class="crm-btn crm-btn-primary"><i class="pi pi-download"></i> {{ __('Descargar') }}</a>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1 ml-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="flex-1 overflow-auto bg-ink-50 flex items-center justify-center p-4" id="brkDocBody" style="min-height:420px"></div>
    </div>
</dialog>

{{-- ===== Modal compartido: confirmar eliminación de contrato ===== --}}
<dialog id="brkDocDelete" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[420px] max-w-[94vw]">
    <form method="POST" id="brkDocDeleteForm" class="bg-white rounded-2xl overflow-hidden">
        @csrf @method('DELETE')
        <div class="p-6 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-err-soft flex items-center justify-center text-err mb-3"><i class="pi pi-trash text-[20px]"></i></div>
            <div class="text-[15px] font-bold text-ink-900">{{ __('Eliminar contrato') }}</div>
            <p class="text-[13px] text-ink-500 mt-1.5">{{ __('¿Seguro que querés eliminar') }} <b class="text-ink-700" id="brkDocDeleteName">{{ __('este documento') }}</b>{{ __('? Esta acción no se puede deshacer.') }}</p>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-center gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary" style="background:#d92d20;border-color:#d92d20"><i class="pi pi-trash"></i> {{ __('Eliminar') }}</button>
        </div>
    </form>
</dialog>

<script>
    // Tabs del modal de broker
    function brkTab(id, name) {
        const root = document.getElementById('modal-broker-' + id);
        if (!root) return;
        root.querySelectorAll('[data-panel]').forEach(p => p.style.display = (p.dataset.panel === name ? '' : 'none'));
        root.querySelectorAll('[data-tab]').forEach(t => {
            const on = t.dataset.tab === name;
            t.classList.toggle('text-brand', on);
            t.classList.toggle('border-brand', on);
            t.classList.toggle('text-ink-500', !on);
            t.classList.toggle('border-transparent', !on);
        });
    }

    // Previsualización de contrato
    function openBrokerDoc(m) {
        document.getElementById('brkDocTitle').textContent = m.title || 'Vista previa';
        document.getElementById('brkDocFmt').textContent = m.format || '';
        document.getElementById('brkDocDownload').href = m.download || m.url;
        const body = document.getElementById('brkDocBody');
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
                + '<div class="text-[12px] text-ink-400 mt-1">Usá el botón <b>Descargar</b> para abrirlo.</div></div>';
        }
        document.getElementById('brkDocPreview').showModal();
    }
    document.getElementById('brkDocPreview').addEventListener('close', () => {
        document.getElementById('brkDocBody').innerHTML = '';
    });

    // Confirmar borrado de contrato
    function openBrokerDocDelete(m) {
        document.getElementById('brkDocDeleteForm').action = m.url;
        document.getElementById('brkDocDeleteName').textContent = m.title ? '«' + m.title + '»' : 'este documento';
        document.getElementById('brkDocDelete').showModal();
    }
</script>

@include('admin.crm._partials.modal_exportar', ['name' => 'Brokers', 'id' => 'modal-exportar-brokers'])
@endsection
