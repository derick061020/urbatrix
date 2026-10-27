@extends('layouts.admin_crm')
@section('title', __('Alta de broker') . ' — ' . $user->name)
@section('page_title', __('Ficha de alta de broker'))
@section('page_breadcrumb', __('Equipo · Aprobaciones · Alta de broker'))
@php $activeRoute = 'crm.aprobaciones'; @endphp

@section('content')
@php
    $d      = (array) ($profile->data ?? []);
    $f      = (array) ($profile->fiscal ?? []);
    $docs   = \App\Models\BrokerProfile::requiredDocs($profile->kind);
    $have   = (array) ($profile->docs ?? []);
    $estado = [
        'borrador'    => [__('Sin enviar'), 'bg-ink-100 text-ink-600'],
        'en_revision' => [__('En revisión'), 'bg-warn-soft text-warn'],
        'verificado'  => [__('Verificado'), 'bg-ok-soft text-ok-dark'],
        'rechazado'   => [__('Devuelto para corregir'), 'bg-err-soft text-err'],
    ][$profile->status] ?? [$profile->status, 'bg-ink-100 text-ink-600'];
    $fila = fn ($label, $valor) => [$label, filled($valor) ? $valor : '—'];
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4 max-w-5xl">

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <a href="{{ route('admin.crm.aprobaciones') }}" class="crm-btn crm-btn-ghost w-fit"><i class="pi pi-arrow-left"></i> {{ __('Aprobaciones') }}</a>
        <div class="flex-1 min-w-0">
            <div class="text-[16px] font-semibold text-ink-900 truncate">{{ trim(($d['nombre'] ?? $user->name).' '.($d['apellido'] ?? '')) }}</div>
            <div class="text-[12px] text-ink-500">{{ $profile->isAgency() ? __('Agencia inmobiliaria') : __('Broker independiente') }} · {{ $user->email }}
                @if($profile->submitted_at) · {{ __('enviada :fecha', ['fecha' => $profile->submitted_at->diffForHumans()]) }} @endif</div>
        </div>
        <span class="crm-pill {{ $estado[1] }} w-fit">{{ $estado[0] }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="crm-card p-5">
            <div class="text-[13px] font-semibold text-ink-900 mb-3">{{ __('Datos personales y profesionales') }}</div>
            <dl class="divide-y divide-ink-100 text-[12.5px]">
                @foreach ([
                    $fila(__('Nombre / Razón social'), $d['nombre'] ?? null),
                    $fila(__('Apellido'), $d['apellido'] ?? null),
                    $fila(__('Email profesional'), $d['email'] ?? null),
                    $fila(__('Teléfono / WhatsApp'), $d['tel'] ?? null),
                    $fila(__('País de residencia'), $d['pais'] ?? null),
                    $fila(__('Dirección fiscal'), $d['direccion'] ?? null),
                    $fila(__('Nº de licencia inmobiliaria'), $d['licencia'] ?? null),
                    $fila(__('Sitio web / redes'), $d['web'] ?? null),
                ] as [$l, $v])
                    <div class="py-2 flex justify-between gap-4"><dt class="text-ink-500 shrink-0">{{ $l }}</dt><dd class="text-ink-900 font-medium text-right break-all">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="crm-card p-5">
            <div class="text-[13px] font-semibold text-ink-900 mb-3">{{ __('Datos fiscales y bancarios') }}</div>
            <dl class="divide-y divide-ink-100 text-[12.5px]">
                @foreach ([
                    $fila(__(config('company.broker_alta.tax_id_label', 'Número de identificación fiscal')), $f['tax_id'] ?? null),
                    $fila(__('Banco'), $f['banco'] ?? null),
                    $fila(__('Número de cuenta / IBAN'), $f['cuenta'] ?? null),
                    $fila(__('SWIFT / BIC'), $f['swift'] ?? null),
                    $fila(__('Titular de la cuenta'), $f['titular'] ?? null),
                    $fila(__('Moneda de la cuenta'), $f['moneda'] ?? null),
                ] as [$l, $v])
                    <div class="py-2 flex justify-between gap-4"><dt class="text-ink-500 shrink-0">{{ $l }}</dt><dd class="text-ink-900 font-medium text-right break-all">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="crm-card p-5">
        <div class="text-[13px] font-semibold text-ink-900 mb-1">{{ __('Documentos KYC') }}</div>
        <div class="text-[11px] text-ink-500 mb-3">{{ __(':n de :t entregados', ['n' => count(array_intersect_key($have, $docs)), 't' => count($docs)]) }}</div>
        <div class="divide-y divide-ink-100">
            @foreach ($docs as $key => $label)
                <div class="py-2.5 flex items-center gap-3 text-[12.5px]">
                    <i class="pi {{ !empty($have[$key]) ? 'pi-check-circle text-ok' : 'pi-circle text-ink-300' }}"></i>
                    <span class="flex-1 min-w-0 text-ink-800">{{ __($label) }}</span>
                    @if(!empty($have[$key]))
                        <a href="{{ route('admin.users.broker-alta.doc', [$user->id, $key]) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-download"></i> {{ __('Descargar') }}</a>
                    @elseif($user->kyc_id_document && in_array($key, ['id_doc', 'rep_id'], true))
                        <a href="{{ asset('storage/'.$user->kyc_id_document) }}" target="_blank" rel="noopener" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3"><i class="pi pi-external-link"></i> {{ __('Subido en el registro') }}</a>
                    @else
                        <span class="text-[11px] text-ink-400">{{ __('No entregado') }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if(($user->verification_status ?? null) === 'pending')
        <form method="POST" action="{{ route('admin.users.verify-kyc', $user->id) }}" class="crm-card p-5 flex flex-col sm:flex-row sm:items-end gap-3 m-0">@csrf
            <div class="flex-1">
                <label class="text-[12px] font-semibold text-ink-700" for="alta-notes">{{ __('Nota para el broker si la devuelves (opcional)') }}</label>
                <input id="alta-notes" type="text" name="notes" maxlength="300" class="crm-input pl-3 mt-1" placeholder="{{ __('Ej.: la carta de referencia bancaria no es legible') }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" name="decision" value="rejected" class="crm-btn crm-btn-ghost">{{ __('Devolver') }}</button>
                <button type="submit" name="decision" value="approved" class="crm-btn crm-btn-primary" @disabled(! $profile->isSubmitted())><i class="pi pi-check"></i> {{ __('Aprobar alta') }}</button>
            </div>
        </form>
    @endif
</div>
@endsection
