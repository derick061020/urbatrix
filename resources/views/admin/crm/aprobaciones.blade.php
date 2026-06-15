@extends('layouts.admin_crm')
@section('title', 'Aprobaciones — CRM Duna Makai')
@section('page_title', 'Aprobaciones')
@section('page_breadcrumb', 'Equipo · Cola de aprobaciones')
@php $activeRoute = 'crm.aprobaciones'; @endphp

@section('content')
@php
    $approvals = \App\Models\Approval::with('reservation')->orderBy('created_at', 'desc')->get();
    $pendingUsersList = ($pendingUsers ?? collect());
    $pendingKycList   = ($pendingKycDocs ?? collect());
    $pendingPayments = \App\Models\Payment::with(['reservation', 'approver'])->where('approval_status', 'pending')->whereNotNull('receipt_path')->orderBy('created_at', 'desc')->get();
    $pendientes = $approvals->where('status', 'pendiente')->count() + $pendingUsersList->count() + $pendingKycList->count() + $pendingPayments->count();
    $totales = $approvals->count() + $pendingUsersList->count() + $pendingKycList->count() + $pendingPayments->count();
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $pendientes }} pendientes de revisión · {{ $totales }} totales</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-nueva-aprob').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Nueva aprobación') }}</button>
        </div>
    </div>

    <div class="p-3 rounded-lg bg-info-soft border border-info/20 text-[11px] text-ink-600 flex items-center gap-2">
        <i class="pi pi-info-circle text-info"></i>
        Las aprobaciones de descuentos, comisiones y/o cambios en plan de pagos requieren la firma de dos personas para poder completar el flujo.
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2">
            @foreach (['Todos','KYC','Promesas','Contratos','Pagos'] as $i => $tab)
                <button class="crm-tab {{ $i === 0 ? 'active' : '' }}">{{ $tab }}</button>
            @endforeach
            <div class="ml-auto relative w-64">
                <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                <input type="text" placeholder="Buscar…" class="crm-input pr-3">
            </div>
        </div>

        <div class="divide-y divide-ink-100">
            @foreach($pendingUsersList as $u)
                @php
                    $uInit = strtoupper(substr($u->first_name ?? $u->name ?? 'U', 0, 1) . substr($u->last_name ?? '', 0, 1));
                    if (trim($uInit) === '') { $uInit = strtoupper(substr($u->name ?? 'U', 0, 2)); }
                    $uAv = ['#7cb8e7','#f3b04f','#a5b0c5','#cdd6df','#d6a3c6','#d56a6a'];
                    $uBg = $uAv[$u->id % count($uAv)];
                    $uRole = $u->role === 'broker' ? 'Broker' : ($u->role === 'admin' ? 'Administrador' : 'Cliente');
                @endphp
                <div class="px-5 py-4 flex items-center gap-4">
                    <input type="checkbox" class="w-4 h-4 accent-brand">
                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $uBg }}">{{ $uInit ?: 'U' }}</div>
                    <div class="flex-1">
                        <div class="text-[13px] font-semibold text-ink-900">{{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: $u->name }}</div>
                        <div class="text-[11px] text-ink-500 mt-0.5">
                            Nuevo registro · {{ $u->email }} · {{ $uRole }}
                            @if($u->kyc_id_document) · <i class="pi pi-paperclip"></i> documento adjunto @endif
                        </div>
                    </div>
                    <span class="crm-pill bg-info-soft text-info">{{ __('NUEVO USUARIO') }}</span>
                    <span class="crm-pill bg-warn-soft text-warn">MEDIA</span>
                    <span class="crm-pill bg-warn-soft text-warn">PENDIENTE</span>
                    <span class="text-[10px] text-ink-400 w-20 text-right">{{ $u->created_at?->diffForHumans() }}</span>
                    <form method="POST" action="{{ route('admin.users.verify-kyc', $u->id) }}" class="flex items-center gap-1 m-0">@csrf
                        <button type="submit" name="decision" value="approved" class="crm-btn crm-btn-primary text-[11px] py-1 px-3">{{ __('Aprobar') }}</button>
                        <button type="submit" name="decision" value="rejected" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Rechazar') }}</button>
                    </form>
                </div>
            @endforeach

            {{-- Pending KYC documents per expediente (3-button row: Expediente · Aprobar · Ver) --}}
            @foreach($pendingKycList as $k)
                @php
                    $r = $k->reservation;
                    $kInit = strtoupper(substr($r?->first_name ?? 'C', 0, 1) . substr($r?->last_name ?? '', 0, 1));
                    $kAv = ['#7cb8e7','#f3b04f','#a5b0c5','#cdd6df','#d6a3c6','#d56a6a'];
                    $kBg = $kAv[$k->id % count($kAv)];
                @endphp
                <div class="px-5 py-4 flex items-center gap-4">
                    <input type="checkbox" class="w-4 h-4 accent-brand">
                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $kBg }}">{{ $kInit ?: 'K' }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-ink-900">{{ trim(($r?->first_name ?? '').' '.($r?->last_name ?? '')) ?: 'Cliente' }}</div>
                        <div class="text-[11px] text-ink-500 mt-0.5 truncate">
                            KYC del expediente {{ $r?->reservation_code ?? '—' }}
                            @if($r?->unit_name) · Unidad {{ $r->unit_name }} @endif
                            @if($k->file_path) · <i class="pi pi-paperclip"></i> doc adjunto @endif
                        </div>
                    </div>
                    <span class="crm-pill bg-info-soft text-info">KYC</span>
                    <span class="crm-pill bg-warn-soft text-warn">MEDIA</span>
                    <span class="crm-pill bg-warn-soft text-warn">PENDIENTE</span>
                    <span class="text-[10px] text-ink-400 w-20 text-right">{{ $k->created_at?->diffForHumans() }}</span>
                    <div class="flex items-center gap-1">
                        @if($r)
                            <a href="{{ route('admin.crm.expediente.detalle', $r->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Ver expediente') }}"><i class="pi pi-folder text-[10px]"></i> {{ __('Expediente') }}</a>
                        @endif
                        <button type="button" onclick="document.getElementById('modal-kyc-aprob-{{ $k->id }}').showModal()" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Ver detalles del KYC') }}"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
                        <form method="POST" action="{{ route('documents.approve', $k->id) }}" class="m-0">@csrf
                            <button type="submit" class="crm-btn crm-btn-primary text-[11px] py-1 px-3" title="{{ __('Aprobar KYC') }}"><i class="pi pi-check text-[10px]"></i> {{ __('Aprobar') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach

            {{-- Pending Payments --}}
            @foreach($pendingPayments as $p)
                @php
                    $r = $p->reservation;
                    $pInit = strtoupper(substr($r?->first_name ?? 'C', 0, 1) . substr($r?->last_name ?? '', 0, 1));
                    $pAv = ['#7cb8e7','#f3b04f','#a5b0c5','#cdd6df','#d6a3c6','#d56a6a'];
                    $pBg = $pAv[$p->id % count($pAv)];
                @endphp
                <div class="px-5 py-4 flex items-center gap-4">
                    <input type="checkbox" class="w-4 h-4 accent-brand">
                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $pBg }}">{{ $pInit ?: 'P' }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-ink-900">{{ trim(($r?->first_name ?? '').' '.($r?->last_name ?? '')) ?: 'Cliente' }}</div>
                        <div class="text-[11px] text-ink-500 mt-0.5 truncate">
                            Pago: {{ $p->label ?? 'Cuota' }} · ${{ number_format($p->amount, 2) }}
                            @if($r?->unit_name) · Unidad {{ $r->unit_name }} @endif
                            @if($p->receipt_path) · <i class="pi pi-paperclip"></i> comprobante @endif
                        </div>
                    </div>
                    <span class="crm-pill bg-err-soft text-err">PAGO</span>
                    <span class="crm-pill bg-warn-soft text-warn">MEDIA</span>
                    <span class="crm-pill bg-warn-soft text-warn">PENDIENTE</span>
                    <span class="text-[10px] text-ink-400 w-20 text-right">{{ $p->created_at?->diffForHumans() }}</span>
                    <div class="flex items-center gap-1">
                        @if($p->receipt_path)
                            @php
                                $receiptPreviewPayload = [
                                    'url' => asset('storage/'.$p->receipt_path),
                                    'title' => 'Comprobante de pago',
                                    'filename' => basename((string) $p->receipt_path),
                                ];
                            @endphp
                            <button type="button" onclick="openDocumentPreview(@js($receiptPreviewPayload))" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Ver comprobante') }}"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
                        @endif
                        @if($r)
                            <a href="{{ route('admin.crm.pagos', $r->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Ver expediente') }}"><i class="pi pi-folder text-[10px]"></i> {{ __('Expediente') }}</a>
                        @endif
                        <form method="POST" action="{{ route('admin.payments.approve', $p->id) }}" class="m-0">@csrf
                            <button type="submit" name="decision" value="approved" class="crm-btn crm-btn-primary text-[11px] py-1 px-3" title="{{ __('Aprobar pago') }}"><i class="pi pi-check text-[10px]"></i> {{ __('Aprobar') }}</button>
                            <button type="submit" name="decision" value="rejected" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Rechazar pago') }}"><i class="pi pi-times text-[10px]"></i> {{ __('Rechazar') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach

            @forelse($approvals as $a)
                @php
                    $colors = ['descuento' => 'info','comision' => 'ok','contrato' => 'warn','pagos' => 'err','kyc' => 'info'];
                    $color = $colors[strtolower($a->type)] ?? 'warn';
                    $prio  = ['alta' => ['ALTA','err'],'media' => ['MEDIA','warn'],'baja' => ['BAJA','info']];
                    $pp    = $prio[strtolower($a->priority ?? 'media')] ?? ['MEDIA','warn'];
                    $estadoLabel = match($a->status) { 'aprobada' => ['APROBADA','ok'], 'rechazada' => ['RECHAZADA','err'], default => ['PENDIENTE','warn'] };
                    $av = ['#7cb8e7','#f3b04f','#a5b0c5','#cdd6df','#d6a3c6','#d56a6a'];
                    $bg = $av[$a->id % count($av)];
                    $init = strtoupper(substr($a->requested_by ?? 'A',0,2));
                @endphp
                <div class="px-5 py-4 flex items-center gap-4">
                    <input type="checkbox" class="w-4 h-4 accent-brand">
                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                    <div class="flex-1">
                        <div class="text-[13px] font-semibold text-ink-900">{{ $a->requested_by ?? '—' }}</div>
                        <div class="text-[11px] text-ink-500 mt-0.5">{{ $a->amount_or_condition ?? '' }} · {{ $a->notes ?? '' }}</div>
                    </div>
                    <span class="crm-pill bg-{{ $color }}-soft text-{{ $color }}">{{ strtoupper($a->type ?? 'GENERAL') }}</span>
                    <span class="crm-pill bg-{{ $pp[1] }}-soft text-{{ $pp[1] }}">{{ $pp[0] }}</span>
                    <span class="crm-pill bg-{{ $estadoLabel[1] }}-soft text-{{ $estadoLabel[1] }}">{{ $estadoLabel[0] }}</span>
                    <span class="text-[10px] text-ink-400 w-20 text-right">{{ $a->created_at?->diffForHumans() }}</span>
                    @if($a->status === 'pendiente')
                        <form method="POST" action="{{ route('admin.crm.aprobaciones.decide', $a->id) }}" class="flex items-center gap-1 m-0">@csrf
                            <button type="submit" name="decision" value="aprobada" class="crm-btn crm-btn-primary text-[11px] py-1 px-3">{{ __('Aprobar') }}</button>
                            <button type="submit" name="decision" value="rechazada" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Rechazar') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                @if($pendingUsersList->isEmpty() && $pendingKycList->isEmpty())
                    <div class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('No hay aprobaciones registradas.') }}</div>
                @endif
            @endforelse
        </div>
    </div>
</div>

<dialog id="modal-nueva-aprob" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.aprobaciones.store') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">@csrf
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900">{{ __('Nueva aprobación') }}</div>
        <div class="p-6 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Tipo') }}</label>
                    <select name="type" required class="crm-input pl-3 mt-1">
                        <option value="descuento">{{ __('Descuento') }}</option>
                        <option value="comision">{{ __('Comisión') }}</option>
                        <option value="contrato">{{ __('Contrato') }}</option>
                        <option value="pagos">{{ __('Pagos') }}</option>
                        <option value="kyc">KYC</option>
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Prioridad') }}</label>
                    <select name="priority" required class="crm-input pl-3 mt-1">
                        <option value="alta">Alta</option>
                        <option value="media" selected>{{ __('Media') }}</option>
                        <option value="baja">{{ __('Baja') }}</option>
                    </select>
                </div>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Solicitante') }}</label><input type="text" name="requested_by" required class="crm-input pl-3 mt-1"></div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Monto/Condición') }}</label><input type="text" name="amount_or_condition" class="crm-input pl-3 mt-1" placeholder="$8,000 / 8% descuento"></div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Expediente vinculado') }}</label>
                <select name="reservation_id" class="crm-input pl-3 mt-1">
                    <option value="">{{ __('Sin expediente') }}</option>
                    @foreach(\App\Models\Reservation::orderBy('first_name')->get() as $r)
                        <option value="{{ $r->id }}">{{ $r->first_name }} {{ $r->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Notas') }}</label><textarea name="notes" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea></div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Crear aprobación') }}</button>
        </div>
    </form>
</dialog>

{{-- KYC view modal(s) — one per pending KYC document --}}
@foreach($pendingKycList as $k)
    @if($k->reservation)
        @include('admin.crm._partials.modal_kyc', [
            'id'          => 'modal-kyc-aprob-'.$k->id,
            'reservation' => $k->reservation,
            'kycDoc'      => $k,
        ])
    @endif
@endforeach
@include('admin.crm._partials.document_preview_modal')
@endsection
