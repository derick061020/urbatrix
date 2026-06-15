@extends('layouts.admin_crm')
@section('title', 'Transacciones — CRM Duna Makai')
@section('page_title', 'Transacciones')
@section('page_breadcrumb', 'Gestión · Transacciones y pagos')
@php $activeRoute = 'transactions-report'; @endphp

@section('content')
@php
    $currentTab = $tab ?? request('tab', 'todos');
    $hasFilters = filled($search ?? null) || filled($unitId ?? null) || filled($method ?? null) || filled($dateFrom ?? null) || filled($dateTo ?? null);
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">{{ $payments->total() }} movimientos</div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-exportar-transacciones').showModal()" class="crm-btn crm-btn-ghost"><i class="pi pi-upload"></i> {{ __('Exportar') }}</button>
            <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="crm-btn crm-btn-primary"><i class="pi pi-plus"></i> {{ __('Registrar pago') }}</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#1fc16b">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Total cobrado') }}</div>
            <div class="text-[26px] font-bold text-ok-dark leading-tight mt-1">${{ number_format($totalCobrado, 0) }}</div>
            <div class="text-[11px] text-ink-500">{{ $countPaid }} transacciones</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#fa7319">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Pendiente de cobro') }}</div>
            <div class="text-[26px] font-bold text-warn leading-tight mt-1">${{ number_format($pendienteCobro, 0) }}</div>
            <div class="text-[11px] text-ink-500">{{ $countPending }} pagos próximos</div>
        </div>
        <div class="crm-card p-4 border-t-[3px]" style="border-top-color:#fb3748">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-ink-400">{{ __('Pagos vencidos') }}</div>
            <div class="text-[26px] font-bold text-err leading-tight mt-1">${{ number_format($pagosVencidos, 0) }}</div>
            <div class="text-[11px] text-ink-500">{{ $countOverdue }} en mora</div>
        </div>
    </div>

    <div class="crm-card">
        <div class="p-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
            <div class="flex items-center gap-1 overflow-x-auto -mx-1 px-1">
                @foreach (['todos' => 'Todos','confirmados' => 'Confirmados','pendientes' => 'Pendientes','vencidos' => 'Vencidos'] as $slug => $label)
                    <a href="{{ route('admin.transactions-report', array_merge(request()->except(['page', 'tab']), ['tab' => $slug])) }}" class="crm-tab {{ $currentTab === $slug ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.transactions-report') }}" class="flex flex-wrap items-center gap-2 sm:ml-auto w-full sm:w-auto m-0">
                <input type="hidden" name="tab" value="{{ $currentTab }}">
                <div class="relative w-full sm:w-64">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('Buscar pago…') }}" class="crm-input pr-3">
                </div>
                <select name="unit_id" class="crm-input pl-3 w-full sm:w-44">
                    <option value="">{{ __('Todas las unidades') }}</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected((string)($unitId ?? '') === (string)$u->id)>{{ $u->custom_id ?? $u->name }} {{ $u->name && $u->custom_id ? '· '.$u->name : '' }}</option>
                    @endforeach
                </select>
                <select name="method" class="crm-input pl-3 w-full sm:w-40">
                    <option value="">{{ __('Todos los métodos') }}</option>
                    @foreach($methods as $paymentMethod)
                        <option value="{{ $paymentMethod }}" @selected(($method ?? '') === $paymentMethod)>{{ strtoupper($paymentMethod) }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="crm-input pl-3 w-full sm:w-36" title="{{ __('Desde') }}">
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="crm-input pl-3 w-full sm:w-36" title="Hasta">
                <button type="submit" class="crm-btn crm-btn-ghost"><i class="pi pi-filter"></i> {{ __('Filtros') }}</button>
                @if($hasFilters)
                    <a href="{{ route('admin.transactions-report', ['tab' => $currentTab]) }}" class="crm-btn crm-btn-ghost"><i class="pi pi-times"></i> {{ __('Limpiar') }}</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full crm-table">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-6"><input type="checkbox" class="w-4 h-4 accent-brand"></th>
                        <th>{{ __('Cliente') }}</th>
                        <th>{{ __('Unidad') }}</th>
                        <th>{{ __('Concepto') }}</th>
                        <th>{{ __('Monto') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Fecha') }}</th>
                        <th>{{ __('Método') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        @php
                            $statusMap = ['paid' => ['CONFIRMADO','ok'], 'pending' => ['PENDIENTE','warn'], 'overdue' => ['PAGO VENCIDO','err']];
                            $st = $statusMap[$p->status] ?? ['DESCONOCIDO','ink-500'];
                            $r = $p->reservation;
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="w-4 h-4 accent-brand"></td>
                            <td class="text-[13px] font-semibold text-ink-900">{{ $r?->first_name }} {{ $r?->last_name }}</td>
                            <td>
                                <div class="text-[13px] text-ink-900">{{ $r?->unit?->name ?? $r?->unit?->custom_id ?? '—' }}</div>
                                <div class="text-[11px] text-ink-500">{{ __('Makai Residences') }}</div>
                            </td>
                            <td class="text-[13px] text-ink-700">{{ $p->label ?? $p->payment_type }}</td>
                            <td class="text-[13px] font-bold text-ok-dark">${{ number_format($p->amount, 0) }}</td>
                            <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            <td class="text-[12px] text-ink-700">{{ optional($p->paid_at ?? $p->due_date)->format('Y-m-d') }}</td>
                            <td class="text-[12px] text-ink-500"><i class="pi pi-credit-card text-[10px]"></i> {{ $p->payment_method }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if($p->receipt_path)
                                    @php
                                        $receiptPreviewPayload = [
                                            'url' => asset('storage/'.$p->receipt_path),
                                            'title' => 'Comprobante de pago',
                                            'filename' => basename((string) $p->receipt_path),
                                        ];
                                    @endphp
                                    <button type="button" onclick="openDocumentPreview(@js($receiptPreviewPayload))" class="text-[12px] text-brand font-semibold hover:underline mr-3" title="{{ __('Ver comprobante') }}"><i class="pi pi-eye text-[10px]"></i> {{ __('Comprobante') }}</button>
                                @endif
                                <a href="{{ route('admin.crm.expediente.detalle', $r?->id) }}?tab=pagos" class="text-[12px] text-brand font-semibold hover:underline">{{ __('Ver &rarr;') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-[12px] text-ink-500 py-8">{{ __('No hay pagos registrados.') }} <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="text-brand font-semibold hover:underline">{{ __('Registrar pago') }}</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-ink-100">{{ $payments->withQueryString()->links('admin.crm._partials.pagination') }}</div>
    </div>
</div>

@include('admin.crm._partials.modal_registrar_pago')
@include('admin.crm._partials.modal_exportar', ['name' => 'Transacciones', 'id' => 'modal-exportar-transacciones'])
@include('admin.crm._partials.document_preview_modal')
@endsection
