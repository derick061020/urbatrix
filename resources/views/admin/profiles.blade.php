@extends('layouts.admin_crm')
@section('title', 'Usuarios — CRM Duna Makai')
@section('page_title', 'Usuarios')
@section('page_breadcrumb', 'Equipo · Usuarios del sistema')
@php $activeRoute = 'profiles'; @endphp

@section('content')
@php
    $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(50);
    $userIds = $users->getCollection()->pluck('id');
    $reservationsByUser = \App\Models\Reservation::with(['unit','documents'])
        ->whereIn('user_id', $userIds)
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('user_id');

    $totalUsers = \App\Models\User::count();
    $conUnidad  = \App\Models\Reservation::whereNotNull('user_id')->distinct('user_id')->count('user_id');
    $sinUnidad  = $totalUsers - $conUnidad;
    $admins     = \App\Models\User::where('role', 'admin')->count();

    // Users with pending KYC verification (uploaded docs during register)
    $pendingKyc = collect();
    if (\Schema::hasColumn('users', 'verification_status')) {
        $pendingKyc = \App\Models\User::where('verification_status', 'pending')
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
            <button type="button" onclick="document.getElementById('modal-exportar-usuarios').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> Exportar</button>
            <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> Nuevo usuario</button>
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
                <div class="text-[14px] font-bold text-ink-950 flex-1">Verificación de identidad pendiente</div>
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
                                <a href="{{ $docUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white border border-ink-200 text-[11px] font-semibold text-ink-700 hover:bg-ink-50"><i class="pi pi-eye text-[10px]"></i> Ver doc</a>
                            @endif
                            <form method="POST" action="{{ route('admin.users.verify-kyc', $u->id) }}" class="m-0">
                                @csrf
                                <button type="submit" name="decision" value="approved" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-ok-soft text-ok-dark text-[11px] font-semibold hover:bg-ok/20"><i class="pi pi-check text-[10px]"></i> Aprobar</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.verify-kyc', $u->id) }}" class="m-0">
                                @csrf
                                <button type="submit" name="decision" value="rejected" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-err-soft text-err text-[11px] font-semibold hover:bg-err/20"><i class="pi pi-times text-[10px]"></i> Rechazar</button>
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
                    <input type="text" placeholder="Buscar usuario…" class="crm-input pr-3">
                </div>
                <button class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> Filtros</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Unidad</th>
                        <th>Progreso KYC</th>
                        <th>Estado</th>
                        <th>Registrado</th>
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
                                'broker'  => ['Broker', 'info'],
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
                                @if($r)
                                    <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="text-[12px] text-brand font-semibold hover:underline">Ver &rarr;</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-[12px] text-ink-500 py-8">No hay usuarios. <button type="button" onclick="document.getElementById('modal-nueva-reserva').showModal()" class="text-brand font-semibold hover:underline">Crear uno</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-ink-100">{{ $users->withQueryString()->links() }}</div>
    </div>
</div>

@php $units = \App\Models\Unit::orderBy('custom_id')->get(['id','custom_id','name','price']); @endphp
@include('admin.crm._partials.modal_nueva_reserva', ['units' => $units])
@include('admin.crm._partials.modal_exportar', ['name' => 'Usuarios', 'id' => 'modal-exportar-usuarios'])
@endsection
