@extends('layouts.broker')
@section('title', 'Dashboard — Portal Broker')
@section('page_title', 'Dashboard')
@section('page_breadcrumb', 'Portal Broker · Resumen')

@section('content')
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <div>
        <h1 class="font-display text-[20px] font-bold text-ink-950 leading-tight">Hola, {{ $agent->name ?? auth()->user()->name }} 👋</h1>
        <p class="text-[12px] text-ink-500 mt-0.5">{{ __('Tu actividad, tus comisiones y tu progreso.') }}</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        @php $cards = [
            ['Comisión cobrada · mes', '$'.number_format($kpis['collected_month'], 0), '#1fc16b', 'liberada al pagar tus clientes'],
            ['Comisión acumulada',     '$'.number_format($kpis['accumulated'], 0),     '#5c7c68', 'histórico'],
            ['Por liberar',            '$'.number_format($kpis['pending'], 0),          '#fa7319', 'según avance del inicial'],
            ['Clientes activos',       $kpis['clients'],                                '#335cff', 'en tu cartera'],
            ['Ventas cerradas',        $kpis['closed'],                                 '#0EA5A4', 'este histórico'],
            ['Conversión de cartera',  $kpis['conversion'].'%',                         '#6366F1', $kpis['closed'].' de '.max($kpis['clients'],1)],
        ]; @endphp
        @foreach($cards as [$label, $val, $color, $sub])
            <div class="brk-card p-4" style="border-top:3px solid {{ $color }}">
                <div class="text-[10px] uppercase tracking-wider text-ink-500 mb-2">{{ $label }}</div>
                <div class="text-[24px] font-bold leading-none mb-1.5 text-ink-950">{{ $val }}</div>
                <div class="text-[11px] text-ink-400">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Próximas liberaciones --}}
        <div class="brk-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 text-[14px] font-bold text-ink-950">{{ __('Próximas liberaciones de comisión') }}</div>
            <div class="divide-y divide-ink-100">
                @forelse($upcoming as $u)
                    <div class="px-5 py-3.5 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-ink-950 truncate">{{ $u['client'] }} · {{ $u['unit'] }}</div>
                            <div class="text-[11px] text-ink-400">{{ $u['concept'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[13.5px] font-bold text-ok">+${{ number_format($u['commission'], 0) }}</div>
                            <div class="text-[10.5px] text-ink-400">{{ $u['date'] ? \Carbon\Carbon::parse($u['date'])->format('d M') : 'estimado' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[12px] text-ink-400">{{ __('Sin comisiones pendientes de liberar.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Tu trimestre --}}
        <div class="brk-card p-5">
            @php $pct = $goalTarget ? min(100, round($goalProgress / $goalTarget * 100)) : 0; @endphp
            <div class="flex items-center justify-between mb-3">
                <span class="text-[14px] font-bold text-ink-950">{{ __('Tu trimestre') }}</span>
                <a href="{{ route('broker.metas') }}" class="brk-btn brk-btn-ghost" style="padding:6px 10px;font-size:11.5px">{{ __('Ver metas') }}</a>
            </div>
            <div class="flex items-center justify-between text-[12px] mb-1.5">
                <span class="font-semibold text-ink-900">Meta: {{ $goalTarget }} ventas</span>
                <span class="text-ink-500">{{ $goalProgress }} / {{ $goalTarget }}</span>
            </div>
            <div class="h-2 rounded-full bg-ink-100 overflow-hidden"><div class="h-full rounded-full bg-brand" style="width:{{ $pct }}%"></div></div>
            <p class="text-[11.5px] text-ink-500 mt-2.5">{{ __('Cierres del trimestre contabilizados sobre ventas reales. 🎯') }}</p>

            <div class="mt-4 pt-3.5 border-t border-ink-100">
                <div class="flex items-center justify-between text-[12px] mb-1.5">
                    <span class="font-semibold text-ink-900">{{ __('Nivel Plata → Oro') }}</span>
                    <span class="text-ink-500">{{ $goalProgress }} / 8 ventas</span>
                </div>
                <div class="h-2 rounded-full bg-ink-100 overflow-hidden"><div class="h-full rounded-full" style="width:{{ min(100, round($goalProgress/8*100)) }}%;background:linear-gradient(90deg,#d8b669,#b8902f)"></div></div>
                <p class="text-[11px] text-ink-400 mt-1.5">{{ __('Oro desbloquea') }} <b>{{ __('+1% de comisión') }}</b> {{ __('y acceso anticipado a inventario.') }}</p>
            </div>
        </div>
    </div>

    {{-- Enlace de referido --}}
    <div class="brk-card p-5">
        <div class="text-[14px] font-bold text-ink-950 mb-1">{{ __('Tu enlace de referido') }}</div>
        <p class="text-[12.5px] text-ink-500 mb-3">{{ __('Los clientes que entren por tu enlace quedan atribuidos a ti automáticamente.') }}</p>
        <div class="flex items-center gap-3 bg-brand-tint border border-brand/20 rounded-xl px-4 py-3">
            <span class="flex-1 font-mono text-[13px] font-semibold text-brand-dark break-all" id="brkRefLink">{{ url('/r/'.$referral) }}</span>
            <button type="button" class="brk-btn brk-btn-primary" onclick="brkCopyRef()">{{ __('Copiar') }}</button>
        </div>
    </div>
</div>

<script>
    function brkCopyRef(){
        var t = document.getElementById('brkRefLink').textContent.trim();
        navigator.clipboard && navigator.clipboard.writeText(t);
        event.target.textContent = '¡Copiado!';
        setTimeout(function(){ event.target.textContent = 'Copiar'; }, 1500);
    }
</script>
@endsection
