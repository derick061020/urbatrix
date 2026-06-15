@extends('layouts.broker')
@section('title', 'Calculadora — Portal Broker')
@section('page_title', 'Calculadora de comisión')
@section('page_breadcrumb', 'Portal Broker · Comisiones')

@push('styles')
<style>
    .brk-inp{ display:flex; align-items:center; border:1px solid #eaecf0; border-radius:8px; background:#fff; overflow:hidden; }
    .brk-inp span{ padding:0 11px; font-size:13px; color:#99a0ae; background:#f5f7fa; align-self:stretch; display:flex; align-items:center; }
    .brk-inp input{ font-size:14px; font-weight:600; color:#222530; border:0; outline:0; padding:10px 11px; width:100%; -moz-appearance:textfield; }
    .brk-inp input::-webkit-outer-spin-button,.brk-inp input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
    .brk-lbl{ display:block; font-size:11.5px; font-weight:600; color:#525866; margin-bottom:5px; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">
    <p class="text-[12px] text-ink-500">{{ __('Cuánto ganas por una venta. Ajusta los valores.') }}</p>

    <div class="brk-card p-5">
        <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
            {{-- Inputs --}}
            <div class="space-y-3.5">
                <div><label class="brk-lbl">{{ __('Precio del inmueble') }}</label><div class="brk-inp"><span>$</span><input id="c_precio" type="number" value="400000" step="1000"></div></div>
                <div><label class="brk-lbl">{{ __('Descuento aprobado') }}</label><div class="brk-inp"><input id="c_desc" type="number" value="0" step="0.5"><span>%</span></div><div class="text-[10.5px] text-ink-400 mt-1">{{ __('CONFOTUR no reduce la base.') }}</div></div>
                <div><label class="brk-lbl">{{ __('Tu comisión') }}</label><div class="brk-inp"><input id="c_com" type="number" value="{{ rtrim(rtrim(number_format($rate,2),'0'),'.') }}" step="0.1"><span>%</span></div></div>
                <div><label class="brk-lbl">{{ __('Pago inicial') }}</label><div class="brk-inp"><input id="c_ini" type="number" value="20" min="20" step="1"><span>%</span></div><div class="text-[10.5px] text-ink-400 mt-1">{{ __('Mínimo 20%.') }}</div></div>
            </div>

            {{-- Resultado --}}
            <div>
                <div class="border border-ink-200 rounded-xl overflow-hidden">
                    <div class="bg-brand-tint border-b border-brand/20 p-5">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-brand-dark">{{ __('Tu comisión por esta venta') }}</div>
                        <div class="text-[32px] font-bold text-ink-950 leading-none mt-1.5" id="c_comision">$28,000</div>
                        <div class="text-[12px] text-brand-dark mt-1.5">{{ __('se libera al mismo % que el cliente pague su inicial') }}</div>
                    </div>
                    <div class="p-4 space-y-0.5">
                        <div class="flex justify-between py-1.5 text-[13px]"><span class="text-ink-500">{{ __('Precio neto (base)') }}</span><span class="font-bold text-ink-900" id="c_neto">$400,000</span></div>
                        <div class="flex justify-between py-1.5 text-[13px] border-t border-ink-100"><span class="text-ink-500">{{ __('Pago inicial del cliente') }}</span><span class="font-bold text-ink-900" id="c_inicial">$80,000</span></div>
                        <div class="flex justify-between py-1.5 text-[13px] border-t border-ink-100"><span class="text-ink-500">{{ __('Comisión al 100% del inicial') }}</span><span class="font-bold text-ink-900" id="c_comision2">$28,000</span></div>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 bg-info-soft border border-info/20 rounded-xl p-3 mt-3.5">
                    <i class="pi pi-check-circle text-info mt-0.5"></i>
                    <span class="text-[11.5px] text-ink-600"><b>Regla:</b> {{ __('% de comisión liberada = % del inicial cobrado. La reserva va aparte. Estimación, no vinculante.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        var ids=['c_precio','c_desc','c_com','c_ini'];
        function num(id){ return parseFloat(document.getElementById(id).value)||0; }
        function money(x){ return '$'+Math.round(x).toLocaleString('en-US'); }
        function set(id,t){ var el=document.getElementById(id); if(el) el.textContent=t; }
        function calc(){
            var p=num('c_precio'), d=num('c_desc'), c=num('c_com'), i=Math.max(20,num('c_ini'));
            var neto=p*(1-d/100);
            set('c_neto',money(neto)); set('c_inicial',money(neto*i/100));
            set('c_comision',money(neto*c/100)); set('c_comision2',money(neto*c/100));
        }
        ids.forEach(function(id){ var el=document.getElementById(id); if(el) el.addEventListener('input',calc); });
        calc();
    })();
</script>
@endsection
