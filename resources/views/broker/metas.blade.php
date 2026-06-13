@extends('layouts.broker')
@section('title', 'Metas e incentivos — Portal Broker')
@section('page_title', 'Metas e incentivos')
@section('page_breadcrumb', 'Portal Broker · Crecimiento')

@section('content')
@php
    $goalPct  = $goalTarget ? min(100, round($goalProgress / $goalTarget * 100)) : 0;
    $levelPct = $levelTarget ? min(100, round($goalProgress / $levelTarget * 100)) : 0;
@endphp
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500">Tu progreso, tu nivel y el ranking del equipo. Todo sobre ventas reales.</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Meta del trimestre --}}
        <div class="brk-card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[14px] font-bold text-ink-950">Meta del trimestre</span>
                <span class="brk-pill bg-brand-tint text-brand-dark">Trimestre</span>
            </div>
            <div class="flex items-center justify-between text-[12px] mb-1.5"><span class="font-semibold text-ink-900">{{ $goalTarget }} ventas</span><span class="text-ink-500">{{ $goalProgress }} / {{ $goalTarget }}</span></div>
            <div class="h-2.5 rounded-full bg-ink-100 overflow-hidden"><div class="h-full rounded-full bg-brand" style="width:{{ $goalPct }}%"></div></div>
            <p class="text-[12px] text-ink-500 mt-2.5">
                @if($goalProgress >= $goalTarget) ¡Meta superada! 🎯 @else Te faltan <b>{{ $goalTarget - $goalProgress }}</b> cierres para la meta. @endif
            </p>
        </div>

        {{-- Tu nivel --}}
        <div class="brk-card p-5">
            <div class="text-[14px] font-bold text-ink-950 mb-3">Tu nivel</div>
            <div class="flex items-center justify-between text-[12px] mb-1.5"><span class="font-semibold text-ink-900">Plata → Oro</span><span class="text-ink-500">{{ $goalProgress }} / {{ $levelTarget }} ventas/trim</span></div>
            <div class="h-2.5 rounded-full bg-ink-100 overflow-hidden"><div class="h-full rounded-full" style="width:{{ $levelPct }}%;background:linear-gradient(90deg,#d8b669,#b8902f)"></div></div>
            <p class="text-[12px] text-ink-500 mt-2.5">Oro desbloquea <b>+1% de comisión</b> y acceso anticipado a nuevo inventario.</p>
        </div>
    </div>

    {{-- Campaña activa --}}
    <div class="brk-card p-4">
        <div class="flex items-center gap-3 bg-[#FBF3DF] border border-[#EFE0BD] rounded-xl p-3.5">
            <span class="w-9 h-9 rounded-lg bg-white flex items-center justify-center text-[#C39A4B] shrink-0"><i class="pi pi-megaphone text-[18px]"></i></span>
            <div>
                <div class="text-[13px] font-bold text-[#6b5410]">Campaña activa · +2% de comisión</div>
                <div class="text-[11.5px] text-[#8a6f25]">En reservas cerradas durante el período de campaña. Acumulable con tu % base.</div>
            </div>
        </div>
    </div>

    {{-- Ranking --}}
    <div class="brk-card overflow-hidden">
        <div class="px-5 py-3 border-b border-ink-100 flex items-center justify-between">
            <span class="text-[14px] font-bold text-ink-950">Ranking · este trimestre</span>
            <span class="text-[11px] text-ink-400">por cierres</span>
        </div>
        <div class="p-3 sm:p-4">
            @forelse($leaderboard as $i => $row)
                @php $isMe = $agent && $row['id'] === $agent->id; @endphp
                <div class="flex items-center gap-3 py-2.5 px-2 rounded-lg {{ $isMe ? 'bg-brand-tint' : '' }} {{ !$loop->first ? 'border-t border-ink-100' : '' }}">
                    <span class="w-6 text-center text-[13px] font-bold {{ $i < 3 ? 'text-[#C39A4B]' : 'text-ink-400' }}">{{ $i + 1 }}</span>
                    <span class="w-8 h-8 rounded-full bg-ink-100 text-ink-600 text-[11px] font-bold flex items-center justify-center">{{ strtoupper(\Illuminate\Support\Str::substr($row['name'],0,2)) }}</span>
                    <span class="flex-1 text-[13px] font-semibold {{ $isMe ? 'text-brand-dark' : 'text-ink-950' }}">{{ $isMe ? 'Tú ('.$row['name'].')' : $row['name'] }}</span>
                    <span class="text-[12.5px] font-bold text-ink-900">{{ $row['sales'] }} {{ $row['sales'] == 1 ? 'venta' : 'ventas' }}</span>
                </div>
            @empty
                <div class="text-[12px] text-ink-400 text-center py-6">Aún no hay datos de ranking.</div>
            @endforelse
            <p class="text-[11px] text-ink-400 mt-3">El ranking premia cierres reales — no solo actividad — para no incentivar ventas forzadas.</p>
        </div>
    </div>

    {{-- Logros --}}
    <div class="brk-card p-5">
        <div class="text-[14px] font-bold text-ink-950 mb-3">Tus logros</div>
        <div class="flex flex-wrap gap-2">
            @php
                $first = $goalProgress >= 1;
                $three = $goalProgress >= 3;
                $badges = [
                    ['Primera venta', $first],
                    ['3 cierres en el trimestre', $three],
                    ['Nivel Oro', $goalProgress >= $levelTarget],
                    ['$100k en comisiones', false],
                ];
            @endphp
            @foreach($badges as [$name, $unlocked])
                <span class="inline-flex items-center gap-2 text-[11.5px] font-semibold rounded-full px-3 py-1.5 border {{ $unlocked ? 'text-ink-900 bg-white border-ink-200' : 'text-ink-400 bg-ink-50 border-dashed border-ink-200' }}">
                    <i class="pi {{ $unlocked ? 'pi-star-fill text-[#C39A4B]' : 'pi-lock' }} text-[12px]"></i>{{ $name }}
                </span>
            @endforeach
        </div>
    </div>
</div>
@endsection
