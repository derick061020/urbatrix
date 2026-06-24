@extends('layouts.admin_crm')
@section('title', 'Expediente — CRM Duna Makai')
@section('page_title', 'Expedientes')
@section('page_breadcrumb', 'Gestión · Expedientes de clientes · Detalle')
@php $activeRoute = 'crm.expedientes'; @endphp

@section('content')
@php
    $tab = $tab ?? 'resumen';
    $initial = strtoupper(substr($reservation->first_name ?? 'C', 0, 1) . substr($reservation->last_name ?? 'M', 0, 1));
    $fullName = trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? ''));
    $email   = $reservation->email ?? '';
    $phone   = $reservation->phone ?? '';
    $unidad  = $reservation->unit?->custom_id ?? $reservation->unit?->name ?? '—';
    $proyecto= 'Makai Residences';
    $precio  = (float)($reservation->unit?->price ?? 0);
    $paid    = (float)($reservation->payments?->where('status', 'paid')->sum('amount') ?? 0);
    $pct     = $precio > 0 ? round(($paid / $precio) * 100) : 0;

    [$estado, $estadoColor, $stage] = $reservation->pipelineStage();
@endphp

<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    {{-- Client header --}}
    <div class="crm-card p-5">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.crm.expedientes') }}" class="w-10 h-10 rounded-full border border-ink-200 flex items-center justify-center text-ink-600 hover:bg-ink-50"><i class="pi pi-arrow-left text-[12px]"></i></a>
            <div class="crm-avatar" style="width:64px;height:64px;font-size:22px;background:#7cb8e7">{{ $initial }}</div>
            <div class="flex-1">
                <div class="text-[22px] font-bold text-ink-900 leading-tight">{{ $fullName }}</div>
                <div class="text-[12px] text-ink-500 mt-1">
                    {{ $reservation->country ?? 'Rep. Dominicana' }} &nbsp;·&nbsp; {{ $email }} &nbsp;·&nbsp; {{ $phone }}
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="crm-pill bg-ink-100 text-ink-600">Asesor: <span class="font-bold ml-1 text-ink-900">{{ $reservation->user?->name ?? 'Sin asignar' }}</span></span>
                <span class="crm-pill bg-{{ $estadoColor }}-soft text-{{ $estadoColor }}">● {{ $estado }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
            @php $info = [
                ['UNIDAD',      $unidad],
                ['PROYECTO',    $proyecto],
                ['PRECIO TOTAL','$'.number_format($precio)],
                ['PAGADO',      '$'.number_format($paid).' ('.$pct.'%)'],
            ]; @endphp
            @foreach($info as $idx => $i)
                <div class="border border-ink-200 rounded-lg px-4 py-3">
                    <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wide">{{ $i[0] }}</div>
                    <div class="text-[15px] font-bold mt-1 {{ $idx === 3 ? 'text-ok-dark' : 'text-ink-900' }}">{{ $i[1] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tabs --}}
    <div class="crm-card">
        <div class="px-6 border-b border-ink-200 flex items-center gap-8 overflow-x-auto">
            @php
                $tabs = [
                    ['resumen','Resumen'],
                    ['documentos','Documentos'],
                    ['pagos','Plan de Pagos'],
                    ['historial','Historial'],
                    ['comunicaciones','Comunicaciones'],
                ];
            @endphp
            @foreach($tabs as $tdata)
                <a href="?tab={{ $tdata[0] }}" class="crm-tab-line {{ $tab === $tdata[0] ? 'active' : '' }}">{{ $tdata[1] }}</a>
            @endforeach
        </div>

        <div class="p-6 bg-ink-50">

            {{-- ============ RESUMEN ============ --}}
            @if($tab === 'resumen')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="crm-card overflow-hidden">
                        <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 text-[13px] font-bold text-ink-700">{{ __('Datos de contacto') }}</div>
                        <div class="divide-y divide-ink-100">
                            @php $data = [
                                ['Nombre completo',  $fullName],
                                ['Email',            $email],
                                ['Teléfono',         $phone],
                                ['País / Origen',    $reservation->country ?? '—'],
                                ['Ciudad / Provincia', trim(($reservation->city ?? '') . ' ' . ($reservation->province ?? '')) ?: '—'],
                                ['Fecha de registro',optional($reservation->created_at)->format('Y-m-d')],
                                ['Última actividad', optional($reservation->updated_at)->diffForHumans()],
                            ]; @endphp
                            @foreach($data as $d)
                                <div class="px-4 py-3 flex items-center justify-between">
                                    <span class="text-[12px] text-ink-500">{{ $d[0] }}</span>
                                    <span class="text-[13px] font-semibold text-ink-900">{{ $d[1] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="crm-card overflow-hidden">
                        <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
                            <div class="text-[13px] font-bold text-ink-700">{{ __('Contrato activo') }}</div>
                            <a href="{{ route('admin.crm.contract.generate', $reservation->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Ver contrato') }}</a>
                        </div>
                        <div class="divide-y divide-ink-100">
                            @php $contract = [
                                ['Tipo',   ['RESERVA','info']],
                                ['Unidad', $unidad],
                                ['Total',  '$'.number_format($precio)],
                                ['Pagado', '$'.number_format($paid)],
                                ['Saldo',  '$'.number_format($precio - $paid)],
                                ['Estado', [$estado, $estadoColor]],
                                ['Firma',  optional($reservation->created_at)->format('Y-m-d')],
                            ]; @endphp
                            @foreach($contract as $c)
                                <div class="px-4 py-3 flex items-center justify-between">
                                    <span class="text-[12px] text-ink-500">{{ $c[0] }}</span>
                                    @if(is_array($c[1]))
                                        <span class="crm-pill bg-{{ $c[1][1] }}-soft text-{{ $c[1][1] }}">{{ $c[1][0] }}</span>
                                    @else
                                        <span class="text-[13px] font-semibold text-ink-900">{{ $c[1] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            {{-- ============ DOCUMENTOS ============ --}}
            @elseif($tab === 'documentos')

                @php
                    $planDoc = $reservation->documents->firstWhere('document_type', 'payment_plan');
                    $planSignedAdmin = $planDoc && in_array($planDoc->status, ['signed', 'approved']);
                    $promesaDocAdmin = $reservation->documents->firstWhere('document_type', 'purchase_promise');
                @endphp
                @if($planSignedAdmin && $promesaDocAdmin)
                    <div class="mb-4">
                        @include('admin.crm._partials.contrato_admin', ['document' => $promesaDocAdmin, 'reservation' => $reservation])
                    </div>
                @endif

                {{-- ===== Acuerdos firmados: firma, IP, hora y dispositivo ===== --}}
                @php
                    $firmados = $reservation->documents
                        ->whereIn('document_type', ['payment_plan', 'purchase_promise', 'contract'])
                        ->filter(fn($d) => in_array($d->status, ['signed', 'approved']))
                        ->sortByDesc('signed_at');
                @endphp
                @if($firmados->isNotEmpty())
                    <div class="mb-4 space-y-3">
                        <div class="text-[12px] font-bold text-ink-600 uppercase tracking-wide flex items-center gap-2">
                            <i class="pi pi-shield text-ink-400"></i> Acuerdos firmados
                        </div>
                        @foreach($firmados as $fd)
                            @include('admin.crm._partials.firma_detalle', ['document' => $fd])
                        @endforeach
                    </div>
                @endif

                @php
                    $statusLabel = ['pending' => ['Pendiente','warn'],'generated' => ['Generado','info'],'signed' => ['Firmado','ok'],'approved' => ['Aprobado','ok'],'rejected' => ['Rechazado','err']];
                @endphp

                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
                        <div class="text-[13px] font-bold text-ink-700"><i class="pi pi-file"></i> {{ __('Documentos del expediente') }}</div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.getElementById('modal-solicitar-documento').showModal()" class="crm-btn crm-btn-ghost text-[11px] py-1.5 px-3"><i class="pi pi-inbox text-[10px]"></i> {{ __('Solicitar documento') }}</button>
                            <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="crm-btn crm-btn-primary text-[11px] py-1.5 px-3"><i class="pi pi-plus text-[10px]"></i> {{ __('Subir documento') }}</button>
                        </div>
                    </div>
                    <table class="w-full crm-table">
                        <thead class="bg-white">
                            <tr><th>{{ __('Documento') }}</th><th>{{ __('Tipo') }}</th><th>{{ __('Estado') }}</th><th>{{ __('Fecha') }}</th><th>{{ __('Archivo') }}</th><th></th></tr>
                        </thead>
                        <tbody>
                            @php
                                // Include KYC docs uploaded at register (reservation_id=null, metadata.user_id) so admin can review them here too
                                $kycDocs = $reservation->user_id
                                    ? \App\Models\Document::whereNull('reservation_id')
                                        ->where('metadata->user_id', $reservation->user_id)
                                        ->get()
                                    : collect();
                                $allDocs = $reservation->documents
                                    ->merge($kycDocs)->unique('id')->sortByDesc('created_at');
                            @endphp
                            @forelse($allDocs as $d)
                                @php
                                    $isRequested = data_get($d->metadata, 'requested') === true;
                                    $hasFile     = $d->file_path && $d->file_path !== 'pending';
                                    if ($isRequested && ! $hasFile) {
                                        $st = ['Solicitado','ink-500'];
                                    } else {
                                        $st = $statusLabel[$d->status] ?? ['—','ink-500'];
                                    }
                                    $previewPayload = [
                                        'url' => route('documents.preview', $d->id),
                                        'title' => $d->title ?: 'Documento',
                                        'filename' => $d->filename ?: basename((string) $d->file_path),
                                    ];
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-9 rounded bg-ink-100 flex items-center justify-center text-ink-500"><i class="pi pi-file"></i></div>
                                            <div>
                                                <div class="text-[13px] font-semibold text-ink-900">{{ $d->title }}</div>
                                                @if($isRequested && ! $hasFile)
                                                    <div class="text-[11px] text-ink-500">{{ data_get($d->metadata, 'description') ?: 'Solicitado al cliente' }}</div>
                                                @else
                                                    <div class="text-[11px] text-ink-500">Subido {{ optional($d->generated_at ?? $d->created_at)->format('Y-m-d') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="crm-pill bg-ink-100 text-ink-600">{{ $isRequested ? 'Requerido' : ucfirst($d->document_type ?? '—') }}</span></td>
                                    <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                                    <td class="text-[12px] text-ink-700">{{ optional($d->updated_at)->format('Y-m-d') }}</td>
                                    <td class="text-[12px] text-ink-500">
                                        @if($hasFile)
                                            <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="text-brand hover:underline text-left">{{ $d->filename }}</button>
                                        @elseif($isRequested)
                                            <span class="text-ink-400">{{ __('Pendiente de subir') }}</span>
                                        @else
                                            {{ $d->filename }}
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        @php
                                            $isAutoGen   = in_array($d->document_type, ['payment_plan', 'purchase_promise', 'contract']);
                                            $hasSignNow  = (bool) data_get($d->metadata, 'signnow.document_id');
                                        @endphp
                                        @if($d->document_type === 'kyc')
                                            <button type="button" onclick="document.getElementById('modal-kyc-{{ $d->id }}').showModal()" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
                                        @endif
                                        @if($hasSignNow && ! $d->isSigned())
                                            <button type="button" onclick="syncSignNow({{ $d->id }}, this)" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1" title="{{ __('Verificar si el cliente ya firmó en SignNow') }}"><i class="pi pi-refresh text-[10px]"></i> {{ __('Sincronizar firma') }}</button>
                                        @endif
                                        @if($d->status === 'pending' && ! $isAutoGen && $hasFile)
                                            <form method="POST" action="{{ route('documents.approve', $d->id) }}" class="inline m-0">@csrf<button type="submit" class="crm-btn crm-btn-primary text-[11px] py-1 px-3 mr-1">{{ __('Aprobar') }}</button></form>
                                            <form method="POST" action="{{ route('documents.reject', $d->id) }}" class="inline m-0">@csrf<button type="submit" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1">{{ __('Rechazar') }}</button></form>
                                        @endif
                                        @if($d->document_type !== 'kyc' && $hasFile)
                                            <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1"><i class="pi pi-eye text-[10px]"></i> {{ __('Ver') }}</button>
                                            <a href="{{ route('documents.download', $d->id) }}" class="crm-btn crm-btn-primary text-[11px] py-1 px-3 mr-1"><i class="pi pi-download text-[10px]"></i> {{ __('Descargar') }}</a>
                                        @endif
                                        @if($isRequested)
                                            <form method="POST" action="{{ route('admin.crm.document.delete', $d->id) }}" class="inline m-0" onsubmit="return confirm('¿Eliminar esta solicitud de documento?');">@csrf<button type="submit" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 text-err" title="{{ __('Eliminar solicitud') }}"><i class="pi pi-trash text-[10px]"></i></button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">{{ __('Sin documentos.') }} <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="text-brand font-semibold hover:underline">{{ __('Subir el primero') }}</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- ============ PAGOS ============ --}}
            @elseif($tab === 'pagos')
                @include('admin.crm._partials.plan_de_pagos', ['reservation' => $reservation])

                @php
                    $pendingApprovals = $reservation->payments
                        ->where('approval_status', 'pending')
                        ->whereNotNull('receipt_path')
                        ->sortByDesc('created_at');
                @endphp
                @if($pendingApprovals->isNotEmpty())
                <div class="crm-card overflow-hidden mt-5">
                    <div class="px-4 py-3 bg-warn-soft/40 border-b border-warn/20 flex items-center gap-2">
                        <i class="pi pi-clock text-warn"></i>
                        <div class="text-[13px] font-bold text-ink-700">{{ __('Pagos pendientes de aprobación') }}</div>
                        <span class="crm-pill bg-warn-soft text-warn ml-1">{{ $pendingApprovals->count() }}</span>
                    </div>
                    <div class="divide-y divide-ink-100">
                        @foreach($pendingApprovals as $p)
                            <div class="px-5 py-4 flex items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-ink-900">{{ $p->label ?? $p->payment_type }} · ${{ number_format((float) $p->amount, 2) }}</div>
                                    <div class="text-[11px] text-ink-500 mt-0.5 truncate">
                                        {{ __('Subido') }} {{ $p->created_at?->diffForHumans() }}
                                        @if($p->payment_method) · {{ $p->payment_method_label }} @endif
                                        @if($p->receipt_path) · <i class="pi pi-paperclip"></i> {{ __('comprobante') }} @endif
                                    </div>
                                </div>
                                <span class="crm-pill bg-warn-soft text-warn">{{ __('PENDIENTE') }}</span>
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
                                    <form method="POST" action="{{ route('admin.payments.approve', $p->id) }}" class="flex items-center gap-1 m-0">@csrf
                                        <button type="submit" name="decision" value="approved" class="crm-btn crm-btn-primary text-[11px] py-1 px-3" title="{{ __('Aprobar pago') }}"><i class="pi pi-check text-[10px]"></i> {{ __('Aprobar') }}</button>
                                        <button type="submit" name="decision" value="rejected" class="crm-btn crm-btn-ghost text-err text-[11px] py-1 px-3" title="{{ __('Rechazar pago') }}" onclick="return confirm('¿Rechazar este pago?');"><i class="pi pi-times text-[10px]"></i> {{ __('Rechazar') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @php
                    $planDocPagos = $reservation->documents->firstWhere('document_type', 'payment_plan');
                    $planSignedPagos = $planDocPagos && in_array($planDocPagos->status, ['signed', 'approved']);
                    $showPaymentSchedule = $planSignedPagos
                        || in_array($reservation->status, ['contract_signed', 'signed'])
                        || $reservation->budget_status === 'approved';
                @endphp

                @if($showPaymentSchedule)
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-5 mt-5">
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">{{ __('Total contrato') }}</div>
                        <div class="text-[22px] font-bold text-ink-900 mt-1">${{ number_format($precio) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">{{ __('Total pagado') }}</div>
                        <div class="text-[22px] font-bold text-ok-dark mt-1">${{ number_format($paid) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">{{ __('Saldo') }}</div>
                        <div class="text-[22px] font-bold text-ink-900 mt-1">${{ number_format($precio - $paid) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">{{ __('Próximo pago') }}</div>
                        @php $nextPay = $reservation->payments->where('status', 'pending')->sortBy('due_date')->first(); @endphp
                        <div class="text-[22px] font-bold text-warn mt-1">${{ number_format($nextPay->amount ?? 0) }}</div>
                    </div>
                </div>

                <div class="crm-card mb-5 p-4">
                    <div class="flex items-center justify-between text-[12px] text-ink-700 mb-2">
                        <span class="font-semibold">{{ __('Progreso del plan de pagos') }}</span><span class="font-bold">{{ $pct }}%</span>
                    </div>
                    <div class="crm-progress"><span class="bg-brand" style="width:{{ $pct }}%"></span></div>
                </div>

                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
                        <div class="text-[13px] font-bold text-ink-700">{{ __('Calendario de pagos') }}</div>
                        <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="crm-btn crm-btn-primary text-[11px] py-1.5 px-3"><i class="pi pi-plus text-[10px]"></i> {{ __('Registrar pago') }}</button>
                    </div>
                    <table class="w-full crm-table">
                        <thead class="bg-white">
                            <tr><th>{{ __('Cuota') }}</th><th>{{ __('Fecha') }}</th><th>{{ __('Monto programado') }}</th><th>{{ __('Pagado') }}</th><th>{{ __('Estado') }}</th><th>{{ __('Método') }}</th></tr>
                        </thead>
                        <tbody>
                            @php $statusPay = ['paid' => ['Pagado','ok'],'pending' => ['Pendiente','warn'],'overdue' => ['Vencido','err']]; @endphp
                            @forelse($reservation->payments->sortBy('due_date') as $p)
                                @php $st = $statusPay[$p->status] ?? ['—','ink-500']; @endphp
                                <tr>
                                    <td class="text-[13px] font-semibold text-ink-900">{{ $p->label ?? $p->payment_type }}</td>
                                    <td class="text-[12px] text-ink-700">{{ optional($p->due_date)->format('Y-m-d') }}</td>
                                    <td class="text-[13px] text-ink-700">${{ number_format($p->amount) }}</td>
                                    <td class="text-[13px] font-bold {{ $p->status === 'paid' ? 'text-ok-dark' : 'text-ink-400' }}">${{ number_format($p->status === 'paid' ? $p->amount : 0) }}</td>
                                    <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                                    <td class="text-[12px] text-ink-500">{{ $p->payment_method ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">{{ __('Sin cuotas registradas.') }} <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="text-brand font-semibold hover:underline">{{ __('Registrar pago') }}</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

            {{-- ============ HISTORIAL ============ --}}
            @elseif($tab === 'historial')
                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 text-[13px] font-bold text-ink-700">{{ __('Actividad reciente') }}</div>
                    <div class="divide-y divide-ink-100">
                        @php
                            $events = collect();
                            foreach ($reservation->documents as $d) {
                                $events->push(['ok', $fullName.' subió documento "'.$d->title.'"', $d->updated_at, $d->status === 'approved' ? 'ok' : ($d->status === 'rejected' ? 'err' : 'warn')]);
                            }
                            foreach ($reservation->payments as $p) {
                                $events->push(['info', 'Pago registrado: '.$p->label.' por $'.number_format($p->amount), $p->paid_at ?? $p->created_at, $p->status === 'paid' ? 'ok' : 'warn']);
                            }
                            $events->push(['ok', 'Expediente creado', $reservation->created_at, 'ok']);
                            $events = $events->sortByDesc(fn($e) => $e[2]);
                        @endphp
                        @forelse($events as $e)
                            <div class="px-5 py-3 flex items-start gap-3">
                                <span class="dot bg-{{ $e[3] }} mt-2"></span>
                                <div class="flex-1 text-[13px] text-ink-700">{{ $e[1] }}</div>
                                <div class="text-[10px] text-ink-400 whitespace-nowrap mt-0.5">{{ optional($e[2])->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-[12px] text-ink-500">{{ __('Sin actividad registrada.') }}</div>
                        @endforelse
                    </div>
                </div>

            {{-- ============ COMUNICACIONES ============ --}}
            @elseif($tab === 'comunicaciones')
                <div class="grid grid-cols-3 gap-5">
                    <div class="crm-card col-span-2 flex flex-col h-[520px]">
                        <div class="px-5 py-3 border-b border-ink-100 flex items-center gap-3">
                            <div class="crm-avatar crm-avatar-sm" style="background:#7cb8e7">{{ $initial }}</div>
                            <div class="flex-1">
                                <div class="text-[14px] font-semibold text-ink-900">{{ $fullName }}</div>
                                <div class="text-[11px] text-ink-500">{{ __('Conversación activa') }}</div>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-ink-50" id="admin-msg-scroll">
                            @php
                                $threadMessages = $reservation->messages()->with('sender')->get();
                                // Mark client-sent messages as read by admin when this tab is open
                                $reservation->messages()
                                    ->where('sender_role', 'client')
                                    ->whereNull('read_at')
                                    ->update(['read_at' => now()]);
                            @endphp
                            @forelse($threadMessages as $msg)
                                @php
                                    $isAdmin = $msg->sender_role === 'admin';
                                    $senderName = $msg->sender?->name ?? ($isAdmin ? 'Asesor' : $fullName);
                                @endphp
                                @if($isAdmin)
                                    <div class="flex justify-end">
                                        <div class="max-w-[70%]">
                                            <div class="bg-brand text-white rounded-2xl rounded-br-md px-4 py-2 text-[12px] whitespace-pre-line">{{ $msg->body }}</div>
                                            <div class="text-[10px] text-ink-400 mt-1 text-right">{{ $senderName }} · {{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex justify-start">
                                        <div class="max-w-[70%]">
                                            <div class="bg-ink-200 text-ink-900 rounded-2xl rounded-bl-md px-4 py-2 text-[12px] whitespace-pre-line">{{ $msg->body }}</div>
                                            <div class="text-[10px] text-ink-400 mt-1">{{ $senderName }} · {{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center text-[12px] text-ink-500 mt-12">{{ __('Sin mensajes. Envía el primero abajo.') }}</div>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('admin.crm.message.send') }}" class="p-3 border-t border-ink-100 flex items-center gap-2 m-0">@csrf
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            <input type="hidden" name="channel" value="chat">
                            <input type="text" name="message" required placeholder="{{ __('Escribir mensaje al cliente…') }}" autocomplete="off" maxlength="5000" class="flex-1 h-9 border border-ink-200 rounded-lg px-3 text-[13px]">
                            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Enviar') }}</button>
                        </form>
                    </div>
                    <div class="space-y-4">
                        <div class="crm-card p-4">
                            <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">{{ __('Enviar por canal') }}</div>
                            <div class="flex gap-2 mt-2">
                                <form method="POST" action="{{ route('admin.crm.message.send') }}" class="flex-1 m-0">@csrf
                                    <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                                    <input type="hidden" name="channel" value="email">
                                    <input type="hidden" name="message" value="Email enviado al cliente desde acción rápida.">
                                    <button class="crm-btn crm-btn-ghost text-[11px] w-full justify-center"><i class="pi pi-envelope"></i> {{ __('Email') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.crm.message.send') }}" class="flex-1 m-0">@csrf
                                    <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                                    <input type="hidden" name="channel" value="whatsapp">
                                    <input type="hidden" name="message" value="WhatsApp enviado al cliente desde acción rápida.">
                                    <button class="crm-btn crm-btn-ghost text-[11px] w-full justify-center"><i class="pi pi-whatsapp"></i> WhatsApp</button>
                                </form>
                            </div>
                        </div>
                        <div class="crm-card p-4">
                            <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">{{ __('Datos rápidos') }}</div>
                            <div class="text-[12px] text-ink-700 space-y-1">
                                <div>• Email: <span class="font-semibold">{{ $email }}</span></div>
                                <div>• Tel: <span class="font-semibold">{{ $phone }}</span></div>
                                <div>{{ __('• País:') }} <span class="font-semibold">{{ $reservation->country ?? '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.crm._partials.modal_subir_documento', ['reservationId' => $reservation->id])
@include('admin.crm._partials.modal_solicitar_documento', ['reservation' => $reservation])
@include('admin.crm._partials.modal_registrar_pago', ['reservationId' => $reservation->id])
@include('admin.crm._partials.document_preview_modal')

{{-- Confirmación HTML reutilizable (subir versión firmada y aprobada, etc.) --}}
<dialog id="confirmSignedModal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <div class="w-[460px] max-w-[92vw] bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-brand"><i class="pi pi-verified"></i></div>
            <div id="confirmSignedTitle" class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Confirmar acción') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6">
            <p id="confirmSignedBody" class="text-[13px] text-ink-600 leading-relaxed"></p>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" id="confirmSignedCancel" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="button" id="confirmSignedOk" class="crm-btn crm-btn-primary"><i class="pi pi-upload"></i> {{ __('Subir y aprobar') }}</button>
        </div>
    </div>
</dialog>

{{-- Toast HTML para avisos/errores (reemplaza alert) --}}
<div id="crmToast" class="fixed bottom-5 right-5 z-[60] hidden">
    <div class="flex items-start gap-3 bg-white border border-ink-200 shadow-lg rounded-xl px-4 py-3 max-w-[360px]">
        <i id="crmToastIcon" class="pi pi-info-circle text-brand mt-0.5"></i>
        <div id="crmToastMsg" class="text-[12px] text-ink-700 leading-snug"></div>
        <button type="button" onclick="document.getElementById('crmToast').classList.add('hidden')" class="text-ink-400 hover:text-ink-700 ml-1"><i class="pi pi-times text-[11px]"></i></button>
    </div>
</div>

{{-- Wire Transfer Modal --}}
<div id="wireTransferModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#f2f5f8] flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg border border-[#eaecf0] flex items-center justify-center text-[#525866]"><i class="pi pi-building-columns"></i></div>
                <div class="text-[15px] font-bold text-[#222530] flex-1">{{ __('Datos para transferencia en USD') }}</div>
                <button type="button" onclick="closeWireTransferModal()" class="text-[#99a0ae] hover:text-[#2b303b] p-1"><i class="pi pi-times text-[12px]"></i></button>
            </div>
            <div id="wireTransferContent" style="width:794px;max-width:90vw;background:#f0efec">
                <div class="text-center py-8">
                    <i class="pi pi-spin pi-spinner text-[#99a0ae] text-[24px]"></i>
                    <div class="text-[13px] text-[#717784] mt-2">{{ __('Cargando datos...') }}</div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-[#f2f5f8] flex items-center gap-2 justify-end bg-[#f5f7fa]">
                <button type="button" onclick="downloadWireTransferPDF()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg text-[13px] font-semibold text-white bg-[#5c7c68] border border-[#5c7c68] hover:bg-[#4a6354] hover:border-[#4a6354] transition-colors"><i class="pi pi-download"></i> {{ __('Descargar PDF') }}</button>
                <button type="button" onclick="closeWireTransferModal()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg text-[13px] font-semibold text-[#525866] bg-white border border-[#eaecf0] hover:bg-[#f5f7fa] transition-colors">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- KYC view modal(s) — one per kyc Document in the expediente --}}
@foreach($reservation->documents->where('document_type', 'kyc') as $kycDoc)
    @include('admin.crm._partials.modal_kyc', [
        'id'          => 'modal-kyc-'.$kycDoc->id,
        'reservation' => $reservation,
        'kycDoc'      => $kycDoc,
    ])
@endforeach

@push('scripts')
<script>
const wireTransferUrl = "{{ route('reservations.wire', $reservation) }}";

// Auto-scroll the Comunicaciones chat to the bottom on load
(function() {
    const el = document.getElementById('admin-msg-scroll');
    if (el) el.scrollTop = el.scrollHeight;
})();

// Open wire transfer modal — render the print sheet inside an iframe so its own
// CSS (defined in the document <head>) is preserved and the design shows correctly.
function openWireTransferModal() {
    const modal = document.getElementById('wireTransferModal');
    const content = document.getElementById('wireTransferContent');

    modal.classList.remove('hidden');
    content.innerHTML = `<iframe id="wire-iframe" src="${wireTransferUrl}" title="Datos para transferencia en USD" style="width:794px;max-width:90vw;height:72vh;border:0;display:block;background:#fff"></iframe>`;
}

// Close wire transfer modal
function closeWireTransferModal() {
    document.getElementById('wireTransferModal').classList.add('hidden');
}

// Download wire transfer PDF — print the already-loaded iframe.
function downloadWireTransferPDF() {
    const frame = document.getElementById('wire-iframe');
    if (frame && frame.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } else {
        window.open(wireTransferUrl, '_blank');
    }
}

function syncSignNow(docId, btn) {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="pi pi-spin pi-spinner text-[10px]"></i> Verificando…';
    fetch('/documents/' + docId + '/signnow/sync', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.signed) {
            btn.innerHTML = '<i class="pi pi-check text-[10px]"></i> Firmado';
            setTimeout(() => window.location.reload(), 600);
        } else if (data.success) {
            btn.innerHTML = '<i class="pi pi-clock text-[10px]"></i> Pendiente';
            setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 1500);
        } else {
            alert(data.message || 'No se pudo sincronizar con SignNow.');
            btn.innerHTML = original;
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('Error de red: ' + err.message);
        btn.innerHTML = original;
        btn.disabled = false;
    });
}

// ── Confirmación HTML reutilizable (devuelve Promise<boolean>) ──
window.crmConfirm = function (opts) {
    opts = opts || {};
    const modal  = document.getElementById('confirmSignedModal');
    const okBtn  = document.getElementById('confirmSignedOk');
    const cancel = document.getElementById('confirmSignedCancel');
    document.getElementById('confirmSignedTitle').textContent = opts.title || 'Confirmar acción';
    document.getElementById('confirmSignedBody').textContent  = opts.body  || '¿Confirmás esta acción?';
    okBtn.innerHTML = (opts.icon ? '<i class="pi ' + opts.icon + '"></i> ' : '') + (opts.ok || 'Confirmar');

    return new Promise((resolve) => {
        const cleanup = (val) => {
            okBtn.removeEventListener('click', onOk);
            cancel.removeEventListener('click', onCancel);
            modal.removeEventListener('close', onClose);
            modal.close();
            resolve(val);
        };
        const onOk = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onClose = () => { okBtn.removeEventListener('click', onOk); cancel.removeEventListener('click', onCancel); resolve(false); };
        okBtn.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        modal.addEventListener('close', onClose, { once: true });
        modal.showModal();
    });
};

// ── Toast HTML para avisos/errores (reemplaza alert) ──
window.crmToast = function (msg, type) {
    const box  = document.getElementById('crmToast');
    const icon = document.getElementById('crmToastIcon');
    document.getElementById('crmToastMsg').textContent = msg;
    icon.className = 'pi mt-0.5 ' + (type === 'err' ? 'pi-exclamation-triangle text-err' : (type === 'ok' ? 'pi-check-circle text-ok' : 'pi-info-circle text-brand'));
    box.classList.remove('hidden');
    clearTimeout(window._crmToastT);
    window._crmToastT = setTimeout(() => box.classList.add('hidden'), 5000);
};

// ── Subida por chunks de documentos firmados (plan de pagos / contrato) ──
// Evita el 413 ("Too Large") partiendo el archivo en trozos de 512 KB, igual
// que la subida del menú del cliente. El backend reensambla y finaliza.
document.addEventListener('submit', async function(ev) {
    const form = ev.target.closest('form[data-signed-upload]');
    if (!form) return;
    ev.preventDefault();

    const input    = form.querySelector('input[type=file]');
    const btn      = form.querySelector('button[type=submit]');
    const progress = form.querySelector('.signed-upload-progress');
    const token    = form.querySelector('input[name=_token]')?.value
                     || document.querySelector('meta[name=csrf-token]')?.content;
    const url      = form.dataset.url;
    const file     = input?.files?.[0];

    if (!file) { crmToast('Seleccioná un archivo.', 'err'); return; }
    const okConfirm = await crmConfirm({
        title: 'Subir versión firmada',
        body: 'Se subirá la versión firmada y quedará aprobada sin esperar la confirmación del cliente. ¿Continuar?',
        ok: 'Subir y aprobar',
        icon: 'pi-upload',
    });
    if (!okConfirm) return;

    const chunkSize = 512 * 1024;
    const total     = Math.ceil(file.size / chunkSize) || 1;
    const uploadId  = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    const original  = btn.innerHTML;
    btn.disabled = true;

    try {
        for (let i = 0; i < total; i++) {
            const fd = new FormData();
            fd.append('chunk', file.slice(i * chunkSize, (i + 1) * chunkSize));
            fd.append('upload_id', uploadId);
            fd.append('index', i);
            fd.append('total', total);
            fd.append('name', file.name);
            fd.append('_token', token);

            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
                credentials: 'same-origin',
            });
            if (res.status === 413) throw new Error('El servidor rechazó el envío por tamaño (413). Subí client_max_body_size en nginx.');
            const d = await res.json().catch(() => ({}));
            if (!res.ok || d.success === false) throw new Error(d.message || 'No se pudo subir el archivo.');

            progress.textContent = 'Subiendo… ' + Math.round(((i + 1) / total) * 100) + '%';

            if (d.done) {
                progress.textContent = 'Listo, recargando…';
                window.location.reload();
                return;
            }
        }
    } catch (e) {
        const msg = e.message || 'No se pudo subir el archivo.';
        crmToast(msg, 'err');
        progress.textContent = msg;
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>
@endpush
@endsection
