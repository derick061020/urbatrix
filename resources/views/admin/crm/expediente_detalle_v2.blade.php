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
        <div class="px-6 border-b border-ink-200 flex items-center gap-8">
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
                        <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 text-[13px] font-bold text-ink-700">Datos de contacto</div>
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
                            <div class="text-[13px] font-bold text-ink-700">Contrato activo</div>
                            <a href="{{ route('admin.crm.contract.generate', $reservation->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">Ver contrato</a>
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

                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
                        <div class="text-[13px] font-bold text-ink-700"><i class="pi pi-file"></i> Documentos del expediente</div>
                        <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="crm-btn crm-btn-primary text-[11px] py-1.5 px-3"><i class="pi pi-plus text-[10px]"></i> Subir documento</button>
                    </div>
                    <table class="w-full crm-table">
                        <thead class="bg-white">
                            <tr><th>Documento</th><th>Tipo</th><th>Estado</th><th>Fecha</th><th>Archivo</th><th></th></tr>
                        </thead>
                        <tbody>
                            @php
                                $statusLabel = ['pending' => ['Pendiente','warn'],'generated' => ['Generado','info'],'signed' => ['Firmado','ok'],'approved' => ['Aprobado','ok'],'rejected' => ['Rechazado','err']];
                                // Include KYC docs uploaded at register (reservation_id=null, metadata.user_id) so admin can review them here too
                                $kycDocs = $reservation->user_id
                                    ? \App\Models\Document::whereNull('reservation_id')
                                        ->where('metadata->user_id', $reservation->user_id)
                                        ->get()
                                    : collect();
                                $allDocs = $reservation->documents->merge($kycDocs)->unique('id')->sortByDesc('created_at');
                            @endphp
                            @forelse($allDocs as $d)
                                @php
                                    $st = $statusLabel[$d->status] ?? ['—','ink-500'];
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
                                                <div class="text-[11px] text-ink-500">Subido {{ optional($d->generated_at ?? $d->created_at)->format('Y-m-d') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="crm-pill bg-ink-100 text-ink-600">{{ ucfirst($d->document_type ?? '—') }}</span></td>
                                    <td><span class="crm-pill bg-{{ $st[1] }}-soft text-{{ $st[1] }}">{{ $st[0] }}</span></td>
                                    <td class="text-[12px] text-ink-700">{{ optional($d->updated_at)->format('Y-m-d') }}</td>
                                    <td class="text-[12px] text-ink-500">
                                        @if($d->file_path)
                                            <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="text-brand hover:underline text-left">{{ $d->filename }}</button>
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
                                            <button type="button" onclick="document.getElementById('modal-kyc-{{ $d->id }}').showModal()" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1"><i class="pi pi-eye text-[10px]"></i> Ver</button>
                                        @endif
                                        @if($hasSignNow && ! $d->isSigned())
                                            <button type="button" onclick="syncSignNow({{ $d->id }}, this)" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1" title="Verificar si el cliente ya firmó en SignNow"><i class="pi pi-refresh text-[10px]"></i> Sincronizar firma</button>
                                        @endif
                                        @if($d->status === 'pending' && ! $isAutoGen)
                                            <form method="POST" action="{{ route('documents.approve', $d->id) }}" class="inline m-0">@csrf<button type="submit" class="crm-btn crm-btn-primary text-[11px] py-1 px-3 mr-1">Aprobar</button></form>
                                            <form method="POST" action="{{ route('documents.reject', $d->id) }}" class="inline m-0">@csrf<button type="submit" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1">Rechazar</button></form>
                                        @endif
                                        @if($d->document_type !== 'kyc')
                                            @if($d->file_path)
                                                <button type="button" onclick="openDocumentPreview(@js($previewPayload))" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mr-1"><i class="pi pi-eye text-[10px]"></i> Ver</button>
                                            @endif
                                            <a href="{{ route('documents.download', $d->id) }}" class="crm-btn crm-btn-primary text-[11px] py-1 px-3"><i class="pi pi-download text-[10px]"></i> Descargar</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">Sin documentos. <button type="button" onclick="document.getElementById('modal-subir-documento').showModal()" class="text-brand font-semibold hover:underline">Subir el primero</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- ============ PAGOS ============ --}}
            @elseif($tab === 'pagos')
                @include('admin.crm._partials.plan_de_pagos', ['reservation' => $reservation])

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
                        <div class="text-[10px] uppercase font-semibold text-ink-400">Total contrato</div>
                        <div class="text-[22px] font-bold text-ink-900 mt-1">${{ number_format($precio) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">Total pagado</div>
                        <div class="text-[22px] font-bold text-ok-dark mt-1">${{ number_format($paid) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">Saldo</div>
                        <div class="text-[22px] font-bold text-ink-900 mt-1">${{ number_format($precio - $paid) }}</div>
                    </div>
                    <div class="crm-card p-4">
                        <div class="text-[10px] uppercase font-semibold text-ink-400">Próximo pago</div>
                        @php $nextPay = $reservation->payments->where('status', 'pending')->sortBy('due_date')->first(); @endphp
                        <div class="text-[22px] font-bold text-warn mt-1">${{ number_format($nextPay->amount ?? 0) }}</div>
                    </div>
                </div>

                <div class="crm-card mb-5 p-4">
                    <div class="flex items-center justify-between text-[12px] text-ink-700 mb-2">
                        <span class="font-semibold">Progreso del plan de pagos</span><span class="font-bold">{{ $pct }}%</span>
                    </div>
                    <div class="crm-progress"><span class="bg-brand" style="width:{{ $pct }}%"></span></div>
                </div>

                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 flex items-center justify-between">
                        <div class="text-[13px] font-bold text-ink-700">Calendario de pagos</div>
                        <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="crm-btn crm-btn-primary text-[11px] py-1.5 px-3"><i class="pi pi-plus text-[10px]"></i> Registrar pago</button>
                    </div>
                    <table class="w-full crm-table">
                        <thead class="bg-white">
                            <tr><th>Cuota</th><th>Fecha</th><th>Monto programado</th><th>Pagado</th><th>Estado</th><th>Método</th></tr>
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
                                <tr><td colspan="6" class="text-center text-[12px] text-ink-500 py-6">Sin cuotas registradas. <button type="button" onclick="document.getElementById('modal-registrar-pago').showModal()" class="text-brand font-semibold hover:underline">Registrar pago</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

            {{-- ============ HISTORIAL ============ --}}
            @elseif($tab === 'historial')
                <div class="crm-card overflow-hidden">
                    <div class="px-4 py-3 bg-ink-50 border-b border-ink-100 text-[13px] font-bold text-ink-700">Actividad reciente</div>
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
                            <div class="px-5 py-8 text-center text-[12px] text-ink-500">Sin actividad registrada.</div>
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
                                <div class="text-[11px] text-ink-500">Conversación activa</div>
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
                                <div class="text-center text-[12px] text-ink-500 mt-12">Sin mensajes. Envía el primero abajo.</div>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('admin.crm.message.send') }}" class="p-3 border-t border-ink-100 flex items-center gap-2 m-0">@csrf
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                            <input type="hidden" name="channel" value="chat">
                            <input type="text" name="message" required placeholder="Escribir mensaje al cliente…" autocomplete="off" maxlength="5000" class="flex-1 h-9 border border-ink-200 rounded-lg px-3 text-[13px]">
                            <button type="submit" class="crm-btn crm-btn-primary">Enviar</button>
                        </form>
                    </div>
                    <div class="space-y-4">
                        <div class="crm-card p-4">
                            <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">Enviar por canal</div>
                            <div class="flex gap-2 mt-2">
                                <form method="POST" action="{{ route('admin.crm.message.send') }}" class="flex-1 m-0">@csrf
                                    <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                                    <input type="hidden" name="channel" value="email">
                                    <input type="hidden" name="message" value="Email enviado al cliente desde acción rápida.">
                                    <button class="crm-btn crm-btn-ghost text-[11px] w-full justify-center"><i class="pi pi-envelope"></i> Email</button>
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
                            <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">Datos rápidos</div>
                            <div class="text-[12px] text-ink-700 space-y-1">
                                <div>• Email: <span class="font-semibold">{{ $email }}</span></div>
                                <div>• Tel: <span class="font-semibold">{{ $phone }}</span></div>
                                <div>• País: <span class="font-semibold">{{ $reservation->country ?? '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.crm._partials.modal_subir_documento', ['reservationId' => $reservation->id])
@include('admin.crm._partials.modal_registrar_pago', ['reservationId' => $reservation->id])
@include('admin.crm._partials.document_preview_modal')

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
// Auto-scroll the Comunicaciones chat to the bottom on load
(function() {
    const el = document.getElementById('admin-msg-scroll');
    if (el) el.scrollTop = el.scrollHeight;
})();

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
</script>
@endpush
@endsection
