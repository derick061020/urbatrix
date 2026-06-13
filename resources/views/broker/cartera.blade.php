@extends('layouts.broker')
@section('title', 'Mi cartera — Portal Broker')
@section('page_title', 'Mi cartera de clientes')
@section('page_breadcrumb', 'Portal Broker · Clientes')

@section('content')
@php
    $st = [
        'cerr' => ['Cerrado',    'bg-ok-soft text-ok-dark'],
        'neg'  => ['Negociando', 'bg-warn-soft text-warn-dark'],
        'lead' => ['Lead',       'bg-ink-100 text-ink-600'],
    ];
@endphp
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500">Tus clientes, su avance y su comisión asociada.</p>

    <div class="brk-card overflow-hidden">
        <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 flex items-center justify-between">
            <span class="text-[14px] font-bold text-ink-950">Clientes</span>
            <a href="{{ route('broker.registro') }}" class="brk-btn brk-btn-primary">+ Registrar cliente</a>
        </div>
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    @foreach(['Cliente','Proyecto / Unidad','Estado','Comisión asociada','Registrado'] as $h)
                        <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($clients as $c)
                    <tr>
                        <td class="px-5 py-3.5 text-[13px] font-semibold text-ink-950">{{ $c['name'] }}</td>
                        <td class="px-5 py-3.5 text-[12px] text-ink-500">{{ $c['project'] ? $c['project'].' · ' : '' }}{{ $c['unit'] }}</td>
                        <td class="px-5 py-3.5"><span class="brk-pill {{ $st[$c['state']][1] }}">{{ $st[$c['state']][0] }}</span></td>
                        <td class="px-5 py-3.5 text-[13px] font-bold text-ink-900">{{ $c['commission'] ? '$'.number_format($c['commission'],0) : '—' }}</td>
                        <td class="px-5 py-3.5 text-[11px] text-ink-500">{{ $c['date'] ? \Carbon\Carbon::parse($c['date'])->format('d M Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-[12px] text-ink-400">
                        <i class="pi pi-users text-[26px] text-ink-300 block mb-3"></i>
                        Aún no tienes clientes en tu cartera. Registra el primero para empezar.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($clients->count())
            <div class="px-5 py-3 border-t border-ink-100 text-[11px] text-ink-400">
                La reserva va aparte y no genera comisión. La comisión se libera al ritmo en que el cliente paga su inicial.
            </div>
        @endif
    </div>
</div>
@endsection
