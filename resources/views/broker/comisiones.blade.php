@extends('layouts.broker')
@section('title', 'Estado de cuenta — Portal Broker')
@section('page_title', 'Estado de cuenta')
@section('page_breadcrumb', 'Portal Broker · Comisiones')

@section('content')
@php
    $st = [
        'paid'    => ['Pagada',    'bg-ok-soft text-ok-dark',  '#1fc16b'],
        'pending' => ['Pendiente', 'bg-warn-soft text-warn-dark','#fa7319'],
        'overdue' => ['Vencida',   'bg-err-soft text-err',     '#fb3748'],
    ];
    $actionable = $commissions->whereIn('status', ['overdue','pending'])->sortBy('date');
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <div class="flex items-center justify-between">
        <div class="text-[12px] text-ink-500">
            {{ $agent->name ?? 'Broker' }} · Tasa {{ rtrim(rtrim(number_format($rate,2),'0'),'.') }}%
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $cards = [
            ['Cobradas',        $kpis['paid'],    '#1fc16b', $kpis['paid']['count'].' liquidaciones'],
            ['Por cobrar',      $kpis['pending'], '#fa7319', $kpis['pending']['count'].' en proceso'],
            ['Vencidas',        $kpis['overdue'], '#fb3748', $kpis['overdue']['count'].' en mora'],
            ['Total acumulado', $kpis['total'],   '#cacfd8', $kpis['total']['count'].' pagos'],
        ]; @endphp
        @foreach($cards as [$label, $data, $color, $sub])
            <div class="brk-card p-4" style="border-top:3px solid {{ $color }}">
                <div class="text-[10px] uppercase tracking-wider text-ink-500 mb-2">{{ $label }}</div>
                <div class="text-[22px] font-bold leading-none mb-1.5" style="color:{{ $color === '#cacfd8' ? '#222530' : $color }}">${{ number_format($data['total'], 0) }}</div>
                <div class="text-[11px] text-ink-400">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    {{-- Alerta acciones pendientes --}}
    @if($actionable->count())
        <div class="p-4 rounded-xl bg-warn-soft border border-warn/20">
            <div class="text-[12px] font-semibold text-warn-dark mb-2">
                {{ $kpis['overdue']['count'] ? $kpis['overdue']['count'].' vencida(s) · ' : '' }}{{ $kpis['pending']['count'] }} pendiente(s) de cobro
            </div>
            <div class="space-y-1.5">
                @foreach($actionable as $c)
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="brk-pill {{ $st[$c['status']][1] }} shrink-0">{{ $st[$c['status']][0] }}</span>
                        <span class="font-semibold text-ink-900">${{ number_format($c['commission'], 0) }}</span>
                        <span class="text-ink-500 truncate">{{ $c['client'] }} · {{ $c['concept'] }}</span>
                        <span class="text-ink-400 ml-auto whitespace-nowrap">{{ \Carbon\Carbon::parse($c['date'])->format('d M Y') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="brk-card overflow-hidden">
        <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 text-[14px] font-bold text-ink-950">{{ __('Historial de comisiones') }}</div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    @foreach(['Cliente','Concepto','Comisión','Fecha','Estado'] as $h)
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($commissions as $c)
                    <tr>
                        <td class="px-5 py-3.5">
                            <div class="text-[13px] font-semibold text-ink-950">{{ $c['client'] }}</div>
                            <div class="text-[11px] text-ink-400">{{ $c['unit'] }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-[12px] text-ink-700">{{ $c['concept'] }}</div>
                            <div class="text-[10px] text-ink-400 mt-0.5">base ${{ number_format($c['base'], 0) }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-[15px] font-bold" style="color:{{ $st[$c['status']][2] }}">${{ number_format($c['commission'], 0) }}</td>
                        <td class="px-5 py-3.5 text-[11px] text-ink-500">{{ \Carbon\Carbon::parse($c['date'])->format('d M Y') }}</td>
                        <td class="px-5 py-3.5"><span class="brk-pill {{ $st[$c['status']][1] }}">{{ $st[$c['status']][0] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-[12px] text-ink-400">
                        @if(!$agent)
                            Tu usuario aún no está vinculado a un perfil de agente. Contacta a Duna para activar tus comisiones.
                        @else
                            Todavía no tienes cierres registrados.
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
