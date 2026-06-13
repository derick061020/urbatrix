@extends('layouts.broker')
@section('title', 'Simulador de cobro — Portal Broker')
@section('page_title', 'Simulador de cobro')
@section('page_breadcrumb', 'Portal Broker · Comisiones')

@push('styles')
<style>
    .brk-inp{ display:flex; align-items:center; border:1px solid #eaecf0; border-radius:8px; background:#fff; overflow:hidden; }
    .brk-inp span{ padding:0 11px; font-size:13px; color:#99a0ae; background:#f5f7fa; align-self:stretch; display:flex; align-items:center; white-space:nowrap; }
    .brk-inp input{ font-size:14px; font-weight:600; color:#222530; border:0; outline:0; padding:10px 11px; width:100%; -moz-appearance:textfield; }
    .brk-inp input::-webkit-outer-spin-button,.brk-inp input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }
    .brk-lbl{ display:block; font-size:11.5px; font-weight:600; color:#525866; margin-bottom:5px; }
    #s_rows td{ padding:7px 6px; font-size:12px; border-top:1px solid #f2f5f8; }
    #s_rows td:first-child{ text-align:left; color:#222530; font-weight:600; }
    #s_rows td:not(:first-child){ text-align:right; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">
    <p class="text-[12px] text-ink-500">Cómo y cuándo cobras según el cliente fracciona su inicial.</p>

    <div class="brk-card p-5">
        <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
            {{-- Inputs --}}
            <div class="space-y-3.5">
                <div><label class="brk-lbl">Precio del inmueble</label><div class="brk-inp"><span>$</span><input id="s_precio" type="number" value="400000" step="1000"></div></div>
                <div><label class="brk-lbl">Descuento</label><div class="brk-inp"><input id="s_desc" type="number" value="0" step="0.5"><span>%</span></div></div>
                <div><label class="brk-lbl">Tu comisión</label><div class="brk-inp"><input id="s_com" type="number" value="{{ rtrim(rtrim(number_format($rate,2),'0'),'.') }}" step="0.1"><span>%</span></div></div>
                <div><label class="brk-lbl">Pago inicial</label><div class="brk-inp"><input id="s_ini" type="number" value="20" min="20" step="1"><span>%</span></div></div>
                <div><label class="brk-lbl">El cliente fracciona el inicial en…</label><div class="brk-inp"><input id="s_fr" type="number" value="4" min="1" max="12" step="1"><span>pagos</span></div></div>
                <div><label class="brk-lbl">¿Y si cierras varias?</label><div class="brk-inp"><input id="s_uds" type="number" value="3" min="1" step="1"><span>uds</span></div></div>
            </div>

            {{-- Resultado --}}
            <div>
                <div class="border border-ink-200 rounded-xl overflow-hidden">
                    <div class="bg-brand-tint border-b border-brand/20 p-5">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-brand-dark">Comisión por venta</div>
                        <div class="text-[32px] font-bold text-ink-950 leading-none mt-1.5" id="s_comision_total">$28,000</div>
                        <div class="text-[12px] text-brand-dark mt-1.5" id="s_inicial_line">sobre un inicial de $80,000</div>
                    </div>
                    <div class="p-4 overflow-x-auto">
                        <table class="w-full min-w-[420px]">
                            <thead>
                                <tr>
                                    @foreach(['Cobro','Inicial','% acum.','Comisión','Acumulado'] as $h)
                                        <th class="text-[10px] font-semibold uppercase tracking-wider text-ink-400 pb-2 {{ $loop->first ? 'text-left' : 'text-right' }}">{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="s_rows"></tbody>
                        </table>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white border border-ink-200 rounded-xl px-4 py-3 mt-3.5">
                    <span class="text-[12.5px] text-ink-500">Si cierras <b id="s_uds_l">3</b> ventas como esta:</span>
                    <span class="ml-auto text-[18px] font-bold text-ok" id="s_comision_uds">$84,000</span>
                </div>
                <div class="flex items-start gap-2.5 bg-info-soft border border-info/20 rounded-xl p-3 mt-3.5">
                    <i class="pi pi-check-circle text-info mt-0.5"></i>
                    <span class="text-[11.5px] text-ink-600">Al completar el inicial, tu comisión queda liquidada al 100%. Estimación, no vinculante.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        var ids=['s_precio','s_desc','s_com','s_ini','s_fr','s_uds'];
        function num(id){ return parseFloat(document.getElementById(id).value)||0; }
        function money(x){ return '$'+Math.round(x).toLocaleString('en-US'); }
        function set(id,t){ var el=document.getElementById(id); if(el) el.textContent=t; }
        function sim(){
            var p=num('s_precio'), d=num('s_desc'), c=num('s_com'), i=Math.max(20,num('s_ini')),
                fr=Math.max(1,Math.round(num('s_fr'))), u=Math.max(1,Math.round(num('s_uds')));
            var neto=p*(1-d/100), com=neto*c/100, ini=neto*i/100, cf=com/fr, inf=ini/fr, rows='', acc=0;
            for(var k=1;k<=fr;k++){
                acc+=cf;
                rows+='<tr><td>Pago '+k+'</td><td>'+money(inf)+'</td><td>'+Math.round(k/fr*100)+'%</td><td style="font-weight:700;color:#222530">'+money(cf)+'</td><td style="font-weight:700;color:#1fc16b">'+money(acc)+'</td></tr>';
            }
            var t=document.getElementById('s_rows'); if(t) t.innerHTML=rows;
            set('s_comision_total',money(com));
            set('s_inicial_line','sobre un inicial de '+money(ini));
            set('s_comision_uds',money(com*u)); set('s_uds_l',u);
        }
        ids.forEach(function(id){ var el=document.getElementById(id); if(el) el.addEventListener('input',sim); });
        sim();
    })();
</script>
@endsection
