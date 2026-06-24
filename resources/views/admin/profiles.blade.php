@extends('layouts.admin_crm')
@section('title', 'Usuarios — CRM Duna Makai')
@section('page_title', 'Usuarios')
@section('page_breadcrumb', 'Equipo · Usuarios del sistema')
@php $activeRoute = 'profiles'; @endphp

@section('content')
@php
    $clientsQuery = \App\Models\User::where('role', 'user');
    $users = (clone $clientsQuery)->orderBy('created_at', 'desc')->paginate(50);
    $userIds = $users->getCollection()->pluck('id');
    $reservationsByUser = \App\Models\Reservation::with(['unit','documents'])
        ->whereIn('user_id', $userIds)
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('user_id');

    $totalUsers = (clone $clientsQuery)->count();
    $conUnidad  = \App\Models\Reservation::whereNotNull('user_id')
        ->whereIn('user_id', (clone $clientsQuery)->pluck('id'))
        ->distinct('user_id')->count('user_id');
    $sinUnidad  = $totalUsers - $conUnidad;
    $admins     = \App\Models\User::where('role', 'admin')->count();

    // Client users with pending KYC verification (uploaded docs during register)
    $pendingKyc = collect();
    if (\Schema::hasColumn('users', 'verification_status')) {
        $pendingKyc = \App\Models\User::where('role', 'user')
            ->where('verification_status', 'pending')
            ->whereNotNull('kyc_id_document')
            ->orderBy('created_at', 'desc')
            ->get();
    }
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $totalUsers }} usuarios registrados · {{ $conUnidad }} con unidad</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-usuarios').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nuevo usuario') }}</button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpis = [
            ['Total registrados', $totalUsers, '#5c7c68'],
            ['Con unidad',        $conUnidad,  '#1fc16b'],
            ['Sin unidad',        $sinUnidad,  '#fa7319'],
            ['Administradores',   $admins,     '#335cff'],
        ]; @endphp
        @foreach($kpis as $k)
            <div class="crm-card p-4 border-t-[3px]" style="border-top-color:{{ $k[2] }}">
                <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ $k[0] }}</div>
                <div class="text-[26px] font-bold text-ink-900 leading-tight mt-1">{{ $k[1] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ====== KYC pendiente (verificación de registro) ====== --}}
    @if($pendingKyc->isNotEmpty())
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 flex items-center gap-3 bg-warn-soft/50 border-b border-warn/20">
                <i class="pi pi-exclamation-circle text-warn"></i>
                <div class="text-[14px] font-bold text-ink-950 flex-1">{{ __('Verificación de identidad pendiente') }}</div>
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-err text-white text-[10px] font-bold">{{ $pendingKyc->count() }}</span>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach($pendingKyc as $u)
                    @php
                        $init = strtoupper(substr($u->first_name ?? $u->name ?? 'U', 0, 1) . substr($u->last_name ?? '', 0, 1));
                        $docUrl = $u->kyc_id_document ? \Storage::disk('public')->url($u->kyc_id_document) : null;
                    @endphp
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-[12px] font-bold shrink-0" style="background:#fce4cb;color:#b66922">{{ $init }}</div>
                            <div class="min-w-0">
                                <div class="text-[14px] font-bold text-ink-950 truncate">{{ $u->name }}</div>
                                <div class="text-[11px] text-ink-500 truncate">{{ $u->email }} · Registrado {{ $u->created_at?->diffForHumans() }}</div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-info-soft text-info text-[10px] font-semibold uppercase tracking-wider shrink-0">{{ $u->role }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($docUrl)
                                <a href="{{ $docUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white border border-ink-200 text-[11px] font-semibold text-ink-700 hover:bg-ink-50"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver doc') }}</a>
                            @endif
                            <form method="POST" action="{{ route('admin.users.verify-kyc', $u->id) }}" class="m-0">
                                @csrf
                                <button type="submit" name="decision" value="approved" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-ok-soft text-ok-dark text-[11px] font-semibold hover:bg-ok/20"><i class="pi pi-check text-[10px]"></i> {{ __('Aprobar') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.verify-kyc', $u->id) }}" class="m-0">
                                @csrf
                                <button type="submit" name="decision" value="rejected" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-err-soft text-err text-[11px] font-semibold hover:bg-err/20"><i class="pi pi-times text-[10px]"></i> {{ __('Rechazar') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['Todos','Con unidad','En gestión','Al día'] as $i => $tab)
                    <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="{{ __('Buscar usuario…') }}" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> {{ __('Filtros') }}</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>{{ __('Usuario') }}</th>
                        <th>{{ __('Rol') }}</th>
                        <th>{{ __('Unidad') }}</th>
                        <th>{{ __('Progreso de compra') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Registrado') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $avBg = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3','#5c7c68']; @endphp
                    @forelse($users as $u)
                        @php
                            $fullName = trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: ($u->name ?? '—');
                            $initSrc  = $u->first_name ? ($u->first_name.' '.$u->last_name) : $u->name;
                            $parts    = preg_split('/\s+/', trim($initSrc ?? 'U'));
                            $init     = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
                            $userReservations = $reservationsByUser->get($u->id, collect());
                            $r = $userReservations->first();
                            $docs = $r ? $r->documents : collect();
                            $totalDocs = $docs->count();
                            $approved  = $docs->where('status', 'approved')->count();
                            $pct       = $totalDocs > 0 ? round(($approved / $totalDocs) * 100) : 0;
                            $verif     = $u->verification_status ?? 'approved';
                            if ($verif === 'pending') {
                                $estado = ['Verificación pendiente', 'warn'];
                            } elseif ($verif === 'rejected') {
                                $estado = ['Rechazado', 'err'];
                            } elseif (! $r) {
                                $estado = ['Sin unidad', 'info'];
                            } elseif ($totalDocs === 0) {
                                $estado = ['KYC pendiente', 'warn'];
                            } elseif ($pct === 100) {
                                $estado = ['Al día', 'ok'];
                            } else {
                                $estado = ['En revisión', 'info'];
                            }
                            $bg = $avBg[$u->id % count($avBg)];
                            $rolePill = match($u->role) {
                                'admin'   => ['Admin', 'err'],
                                default   => ['Usuario', 'ok'],
                            };
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init ?: 'U' }}</div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-ink-900">{{ $fullName }}</div>
                                        <div class="text-[11px] text-ink-500">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="crm-pill bg-{{ $rolePill[1] }}-soft text-{{ $rolePill[1] }}">{{ $rolePill[0] }}</span></td>
                            <td>
                                @if($r)
                                    <div class="text-[13px] font-semibold text-ink-900">{{ $r->unit->name ?? $r->unit->custom_id ?? '—' }}</div>
                                    <div class="text-[11px] text-ink-500">{{ $userReservations->count() > 1 ? '+'.($userReservations->count()-1).' más' : 'Makai Residences' }}</div>
                                @else
                                    <span class="text-[12px] text-ink-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($r)
                                    <div class="flex items-center gap-2 min-w-[160px]">
                                        <div class="crm-progress flex-1"><span class="bg-brand" style="width:{{ $pct }}%"></span></div>
                                        <span class="text-[11px] font-semibold text-ink-700 w-9 text-right">{{ $pct }}%</span>
                                    </div>
                                @else
                                    <span class="text-[12px] text-ink-400">—</span>
                                @endif
                            </td>
                            <td><span class="crm-pill bg-{{ $estado[1] }}-soft text-{{ $estado[1] }}">{{ $estado[0] }}</span></td>
                            <td class="text-[12px] text-ink-500">{{ $u->created_at?->diffForHumans() }}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3">
                                    <button type="button"
                                            onclick="openUserDetail({{ $u->id }})"
                                            class="inline-flex items-center gap-1 text-[12px] text-ink-600 font-semibold hover:text-brand">
                                        <i class="pi pi-user text-[11px]"></i> Ver perfil
                                    </button>
                                    <button type="button"
                                            onclick="openEditUser(this)"
                                            data-id="{{ $u->id }}"
                                            data-first="{{ $u->first_name }}"
                                            data-last="{{ $u->last_name }}"
                                            data-name="{{ $u->name }}"
                                            data-email="{{ $u->email }}"
                                            data-phone="{{ $u->phone }}"
                                            data-country="{{ $u->country }}"
                                            data-role="{{ $u->role }}"
                                            class="inline-flex items-center gap-1 text-[12px] text-ink-600 font-semibold hover:text-brand">
                                        <i class="pi pi-pencil text-[11px]"></i> Editar
                                    </button>
                                    @if($r)
                                        <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-ink-200 bg-white text-ink-500 hover:text-brand hover:border-brand hover:bg-brand-tint transition-colors" title="{{ __('Ver expediente') }}" aria-label="{{ __('Ver expediente') }}"><i class="pi pi-folder-open text-[14px]"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-[12px] text-ink-500 py-8">{{ __('No hay usuarios.') }} <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="text-brand font-semibold hover:underline">{{ __('Crear uno') }}</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-ink-100">{{ $users->withQueryString()->links() }}</div>
    </div>
</div>

{{-- ====== Modal: Editar usuario ====== --}}
<dialog id="modal-editar-usuario" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form id="form-editar-usuario" method="POST" action="" class="w-[560px] max-w-[95vw] bg-white rounded-2xl overflow-hidden">
        @csrf
        <input type="hidden" name="edited_user_id" id="eu-id">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-user-edit"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Editar usuario') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
            @if($errors->any())
                <div class="px-4 py-3 rounded-lg bg-err-soft border border-err/30 text-err text-[12px]">
                    <div class="flex items-center gap-2 mb-1 font-semibold"><i class="pi pi-exclamation-circle"></i> {{ __('Revisa los datos:') }}</div>
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre') }}</label>
                    <input type="text" name="first_name" id="eu-first" class="crm-input pl-3 mt-1" placeholder="{{ __('Nombre') }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Apellido') }}</label>
                    <input type="text" name="last_name" id="eu-last" class="crm-input pl-3 mt-1" placeholder="{{ __('Apellido') }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre para mostrar') }} <span class="text-ink-400 font-normal">(opcional)</span></label>
                    <input type="text" name="name" id="eu-name" class="crm-input pl-3 mt-1" placeholder="{{ __('Nombre completo') }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Correo electrónico') }}</label>
                    <input type="email" name="email" id="eu-email" required class="crm-input pl-3 mt-1" placeholder="usuario@correo.com">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Teléfono') }}</label>
                    <input type="text" name="phone" id="eu-phone" class="crm-input pl-3 mt-1" placeholder="+57 300 000 0000">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('País') }}</label>
                    <input type="text" name="country" id="eu-country" class="crm-input pl-3 mt-1" placeholder="{{ __('Colombia') }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Rol') }}</label>
                    <select name="role" id="eu-role" class="crm-input pl-3 mt-1">
                        <option value="user">{{ __('Usuario') }}</option>
                        <option value="admin">{{ __('Admin') }}</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-ink-100 pt-4">
                <div class="text-[13px] font-semibold text-ink-900 mb-1">{{ __('Restablecer contraseña') }}</div>
                <div class="text-[11px] text-ink-500 mb-3">{{ __('Déjalo en blanco para no cambiarla.') }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">{{ __('Nueva contraseña') }}</label>
                        <input type="password" name="password" autocomplete="new-password" class="crm-input pl-3 mt-1" placeholder="{{ __('Mín. 8 caracteres') }}">
                    </div>
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">{{ __('Confirmar') }}</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="crm-input pl-3 mt-1" placeholder="{{ __('Repite la contraseña') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Guardar cambios') }}</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    const EU_BASE = "{{ url('admin/users') }}";
    function openEditUserObj(d) {
        const f = document.getElementById('form-editar-usuario');
        f.action = EU_BASE + '/' + d.id;
        document.getElementById('eu-id').value      = d.id || '';
        document.getElementById('eu-first').value   = d.first || '';
        document.getElementById('eu-last').value    = d.last || '';
        document.getElementById('eu-name').value    = d.name || '';
        document.getElementById('eu-email').value   = d.email || '';
        document.getElementById('eu-phone').value   = d.phone || '';
        document.getElementById('eu-country').value = d.country || '';
        document.getElementById('eu-role').value    = d.role || 'user';
        f.querySelector('input[name=password]').value = '';
        f.querySelector('input[name=password_confirmation]').value = '';
        document.getElementById('modal-editar-usuario').showModal();
    }
    function openEditUser(btn) { openEditUserObj(btn.dataset); }

    // ── Modal de detalle de usuario (carga vía fetch) ──
    const UD_BASE = "{{ url('admin/users') }}";
    function closeUserDetail() { document.getElementById('modal-user-detail').close(); }
    function switchUserTab(tab) {
        document.querySelectorAll('#modal-user-detail .udt-tab').forEach(b => {
            const on = b.dataset.tab === tab;
            b.classList.toggle('text-brand', on);
            b.classList.toggle('border-brand', on);
            b.classList.toggle('text-ink-500', !on);
            b.classList.toggle('border-transparent', !on);
        });
        document.querySelectorAll('#modal-user-detail .udt-panel').forEach(p => {
            p.style.display = (p.dataset.panel === tab) ? '' : 'none';
        });
    }
    async function openUserDetail(id, tab = 'info') {
        const modal = document.getElementById('modal-user-detail');
        const body  = document.getElementById('user-detail-body');
        body.innerHTML = '<div class="py-16 text-center text-[13px] text-ink-400"><i class="pi pi-spin pi-spinner mr-2"></i> Cargando…</div>';
        modal.showModal();
        try {
            const res = await fetch(UD_BASE + '/' + id + '/detail', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            body.innerHTML = data.html;
            if (tab !== 'info') switchUserTab(tab);
        } catch (e) {
            body.innerHTML = '<div class="py-16 text-center text-[13px] text-err">No se pudo cargar el detalle.</div>';
        }
    }

    @if($errors->any() && old('edited_user_id'))
    document.addEventListener('DOMContentLoaded', function () {
        const f = document.getElementById('form-editar-usuario');
        f.action = EU_BASE + '/' + @json(old('edited_user_id'));
        document.getElementById('eu-id').value      = @json(old('edited_user_id'));
        document.getElementById('eu-first').value   = @json(old('first_name'));
        document.getElementById('eu-last').value    = @json(old('last_name'));
        document.getElementById('eu-name').value    = @json(old('name'));
        document.getElementById('eu-email').value   = @json(old('email'));
        document.getElementById('eu-phone').value   = @json(old('phone'));
        document.getElementById('eu-country').value = @json(old('country'));
        document.getElementById('eu-role').value    = @json(old('role', 'user'));
        document.getElementById('modal-editar-usuario').showModal();
    });
    @endif
</script>
@endpush

{{-- ====== Modal: Detalle de usuario (Información / Propiedad / Documentos / Actividad) ====== --}}
<dialog id="modal-user-detail" class="rounded-2xl p-0 backdrop:bg-black/50 m-auto w-[820px] max-w-[95vw]">
    <div id="user-detail-body" class="bg-white rounded-2xl overflow-hidden"></div>
</dialog>

@php $units = \App\Models\Unit::orderBy('custom_id')->get(['id','custom_id','name','price']); @endphp
@include('admin.crm._partials.modal_nueva_reserva', ['units' => $units])
@include('admin.crm._partials.modal_exportar', ['name' => 'Usuarios', 'id' => 'modal-exportar-usuarios'])
@endsection
