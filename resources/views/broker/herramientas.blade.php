@extends('layouts.broker')
@section('title', 'Herramientas de venta — Portal Broker')
@section('page_title', 'Herramientas de venta')
@section('page_breadcrumb', 'Portal Broker · Ventas')

@push('styles')
<style>
    .brk-field label{ font-size:11.5px; font-weight:600; color:#525866; margin-bottom:5px; display:block; }
    .brk-field select{ font-size:13.5px; color:#222530; border:1px solid #eaecf0; border-radius:8px; background:#fff; padding:9px 11px; width:100%; outline:none; }
    .brk-field select:focus{ border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500 max-w-2xl">{{ __('Material para compartir, enlaces que te dicen quién está interesado, y propuestas para tu cliente.') }}</p>

    {{-- Material descargable --}}
    <div class="brk-card p-5">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[14px] font-bold text-ink-950">{{ __('Material descargable') }}</span>
            <span class="brk-pill bg-brand text-white">{{ __('Aprobado por Duna') }}</span>
        </div>
        @if($materials->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($materials as $m)
                    <div class="border border-ink-200 rounded-xl p-4 flex flex-col gap-2.5">
                        <div class="flex items-start justify-between">
                            <span class="w-9 h-9 rounded-lg bg-brand-tint flex items-center justify-center text-brand"><i class="pi {{ $m->icon }} text-[16px]"></i></span>
                            <span class="brk-pill bg-ink-100 text-ink-600">{{ strtoupper($m->format) }}</span>
                        </div>
                        <div class="text-[13px] font-bold text-ink-950 leading-tight">{{ $m->title }}</div>
                        <div class="text-[10.5px] text-ink-400">{{ $m->file_size ?: '—' }}@if($m->category) · {{ $m->category }}@endif</div>
                        @if($m->downloadUrl())
                            <a href="{{ $m->downloadUrl() }}" target="_blank" class="brk-btn brk-btn-ghost mt-auto">{{ __('Descargar') }}</a>
                        @else
                            <span class="text-[11px] text-ink-300 mt-auto">{{ __('No disponible') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-[12px] text-ink-400 text-center py-8">{{ __('Aún no hay material publicado. Duna lo cargará próximamente.') }}</div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Enlace de referido --}}
        <div class="brk-card p-5">
            <div class="text-[14px] font-bold text-ink-950 mb-1">{{ __('Tu enlace de referido') }}</div>
            <p class="text-[12px] text-ink-500 mb-3">{{ __('Compártelo con tus clientes: quienes entren por él quedan atribuidos a ti automáticamente.') }}</p>
            <div class="flex items-center gap-3 bg-brand-tint border border-brand/20 rounded-xl px-4 py-3">
                <span class="flex-1 font-mono text-[12.5px] font-semibold text-brand-dark break-all" id="brkToolRef">{{ url('/r/'.$referral) }}</span>
                <button type="button" class="brk-btn brk-btn-primary" onclick="brkCopyTool()">{{ __('Copiar') }}</button>
            </div>
        </div>

        {{-- Generar propuesta --}}
        <div class="brk-card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[14px] font-bold text-ink-950">{{ __('Generar propuesta') }}</span>
            </div>
            <p class="text-[12px] text-ink-500 mb-3">{{ __('Crea una propuesta con plan de pago para tu cliente. Tu comisión nunca aparece en el documento del cliente.') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="brk-field">
                    <label>{{ __('Cliente') }}</label>
                    <select id="propClient">
                        <option value="">— Selecciona —</option>
                        @foreach($clients as $c)
                            <option>{{ $c->client_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="brk-field">
                    <label>{{ __('Unidad') }}</label>
                    <select id="propUnit" onchange="brkBuildProp()">
                        <option value="">— Selecciona —</option>
                        @foreach($units as $u)
                            <option value="{{ (float)$u->price }}" data-label="{{ $u->custom_id ?? $u->name }}">{{ $u->custom_id ?? $u->name }} · ${{ number_format($u->price,0) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="button" class="brk-btn brk-btn-primary mt-3" onclick="brkBuildProp(true)">{{ __('Generar propuesta') }}</button>

            <div id="propBox" class="mt-4 border border-ink-200 rounded-xl overflow-hidden" style="display:none">
                <div class="bg-brand-tint border-b border-brand/20 px-4 py-3">
                    <div class="text-[13px] font-bold text-ink-950" id="propTitle">{{ __('Propuesta') }}</div>
                    <div class="text-[11px] text-brand-dark" id="propFor">Preparada por {{ auth()->user()->name }}</div>
                </div>
                <div class="p-4 space-y-1">
                    <div class="flex justify-between text-[13px] py-1"><span class="text-ink-500">{{ __('Precio') }}</span><span class="font-semibold text-ink-900" id="propPrice">—</span></div>
                    <div class="flex justify-between text-[13px] py-1"><span class="text-ink-500">Inicial 20%</span><span class="font-semibold text-ink-900" id="propIni">—</span></div>
                    <div class="flex justify-between text-[13px] py-1"><span class="text-ink-500">{{ __('Saldo (construcción + entrega)') }}</span><span class="font-semibold text-ink-900" id="propRest">—</span></div>
                    <p class="text-[10.5px] text-ink-400 mt-2">{{ __('Simulación informativa, no vinculante. CONFOTUR aplica exención fiscal al comprador.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function brkCopyTool(){
        var t=document.getElementById('brkToolRef').textContent.trim();
        navigator.clipboard && navigator.clipboard.writeText(t);
        event.target.textContent='¡Copiado!'; setTimeout(function(){ event.target.textContent='Copiar'; },1500);
    }
    function brkMoney(x){ return '$'+Math.round(x).toLocaleString('en-US'); }
    function brkBuildProp(force){
        var sel=document.getElementById('propUnit');
        var price=parseFloat(sel.value)||0;
        if(!price){ if(force) alert('{{ __("Selecciona una unidad.") }}'); return; }
        var label=sel.options[sel.selectedIndex].getAttribute('data-label')||'';
        var client=document.getElementById('propClient').value||'tu cliente';
        document.getElementById('propTitle').textContent='Propuesta · '+label;
        document.getElementById('propFor').textContent='Para '+client+' · preparada por {{ auth()->user()->name }}';
        document.getElementById('propPrice').textContent=brkMoney(price);
        document.getElementById('propIni').textContent=brkMoney(price*0.2);
        document.getElementById('propRest').textContent=brkMoney(price*0.8);
        document.getElementById('propBox').style.display='block';
    }
</script>
@endsection
