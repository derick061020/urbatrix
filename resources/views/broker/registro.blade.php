@extends('layouts.broker')
@section('title', 'Registrar cliente — Portal Broker')
@section('page_title', 'Registrar cliente')
@section('page_breadcrumb', 'Portal Broker · Clientes')

@push('styles')
<style>
    .brk-field label{ font-size:11.5px; font-weight:600; color:#525866; margin-bottom:5px; display:block; }
    .brk-field input, .brk-field select{ font-size:13.5px; color:#222530; border:1px solid #eaecf0; border-radius:8px; background:#fff; padding:9px 11px; width:100%; outline:none; }
    .brk-field input:focus, .brk-field select:focus{ border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
    .brk-field .req{ color:#fb3748; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500 max-w-2xl">{{ __('Quedará en la base de datos asignado a ti, protegiendo tu comisión. Reclamar un cliente no bloquea la unidad.') }}</p>

    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_1fr] gap-5">

        {{-- Formulario --}}
        <div class="brk-card p-5">
            <div class="text-[14px] font-bold text-ink-950 mb-4">{{ __('Datos del cliente') }}</div>
            <form method="POST" action="{{ route('broker.registro.store') }}" class="space-y-4">
                @csrf
                <div class="text-[10px] font-semibold uppercase tracking-wider text-ink-400">{{ __('Contacto') }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="brk-field"><label>{{ __('Nombre completo') }} <span class="req">*</span></label><input name="client_name" value="{{ old('client_name') }}" required></div>
                    <div class="brk-field"><label>{{ __('Teléfono / WhatsApp') }} <span class="req">*</span></label><input name="client_phone" value="{{ old('client_phone') }}" required></div>
                </div>
                <div class="brk-field"><label>{{ __('Email') }} <span class="req">*</span></label><input type="email" name="client_email" value="{{ old('client_email') }}" required></div>

                <div class="text-[10px] font-semibold uppercase tracking-wider text-ink-400 pt-3 border-t border-ink-100">{{ __('Negociación') }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="brk-field">
                        <label>{{ __('Unidad de interés') }}</label>
                        <select name="unit_id">
                            <option value="">{{ __('Sin definir') }}</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" @selected(old('unit_id')==$u->id)>{{ $u->custom_id ?? $u->name }} · ${{ number_format($u->price,0) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="brk-field">
                        <label>{{ __('Etapa') }} <span class="req">*</span></label>
                        <select name="stage">
                            <option value="LEAD">{{ __('Interesado') }}</option>
                            <option value="PENDING" selected>{{ __('Negociando') }}</option>
                            <option value="RESERVAR">{{ __('Listo para reservar') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-start gap-3 bg-warn-soft border border-warn/20 rounded-xl p-3">
                    <input type="checkbox" name="consent" value="1" id="cons" class="w-4 h-4 mt-0.5 accent-brand" {{ old('consent') ? 'checked' : '' }}>
                    <label for="cons" class="text-[12px] text-ink-600">{{ __('Confirmo que el cliente me autorizó a registrar sus datos (Ley 172-13).') }}</label>
                </div>

                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="brk-btn brk-btn-primary">{{ __('Registrar y asignármelo') }}</button>
                    <a href="{{ route('broker.cartera') }}" class="brk-btn brk-btn-ghost">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>

        {{-- Cómo funciona la atribución --}}
        <div class="brk-card p-5 h-fit">
            <div class="text-[14px] font-bold text-ink-950 mb-3">{{ __('Cómo funciona la atribución') }}</div>
            <p class="text-[12.5px] text-ink-500 mb-3">{{ __('Al registrarlo, el cliente queda') }} <b>{{ __('asignado a ti') }}</b>{{ __(': tienes la prioridad comercial y tu comisión queda protegida. El sistema verifica duplicados por email y teléfono.') }}</p>
            <div class="flex items-start gap-3 bg-err-soft border border-err/20 rounded-xl p-3">
                <i class="pi pi-exclamation-triangle text-err mt-0.5"></i>
                <div class="text-[12px] text-ink-600">
                    <b>{{ __('Si ya estuviera asignado a otro colaborador') }}</b>, el registro se bloquea (no se revela a quién) y puedes solicitar revisión al administrador.
                    <span class="block text-[11px] text-ink-400 mt-1">{{ __('La prioridad expira a los 45 días sin actividad.') }}</span>
                </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-ink-100">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-ink-400 mb-2">{{ __('Tu enlace de referido') }}</div>
                <div class="font-mono text-[12px] text-brand-dark break-all bg-brand-tint border border-brand/20 rounded-lg px-3 py-2">{{ url('/r/'.$referral) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
