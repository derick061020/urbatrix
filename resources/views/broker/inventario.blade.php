@extends('layouts.broker')
@section('title', 'Inventario en vivo — Portal Broker')
@section('page_title', 'Inventario en vivo')
@section('page_breadcrumb', 'Portal Broker · Ventas')

@section('content')
@php
    $stMap = [
        'AVAILABLE' => ['Disponible',     'bg-info-soft text-info-dark'],
        'RESERVED'  => ['Reservada',      'bg-warn-soft text-warn-dark'],
        'SOLD'      => ['Vendida',        'bg-ink-100 text-ink-600'],
    ];
@endphp
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    <p class="text-[12px] text-ink-500 max-w-2xl">Disponibilidad y precios en tiempo real. Comparte la ficha o reserva en nombre de tu cliente — sin dobles ventas.</p>

    <div class="brk-card overflow-hidden">
        <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 flex items-center justify-between">
            <span class="text-[14px] font-bold text-ink-950">Unidades</span>
            <span class="text-[11px] text-ink-400">actualizado en vivo · {{ $units->count() }} unidades</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px]">
                <thead class="bg-white">
                    <tr>
                        @foreach(['Unidad','Proyecto','Tipología','Precio','$/m²','Estado',''] as $h)
                            <th class="text-left px-5 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500 {{ in_array($h,['Precio','$/m²']) ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse($units as $u)
                        @php
                            $key = strtoupper($u->status ?? 'AVAILABLE');
                            $badge = $stMap[$key] ?? ['—','bg-ink-100 text-ink-600'];
                            $area = $u->total_area ?: $u->internal_area;
                            $ppm  = ($area && $u->price) ? $u->price / $area : null;
                        @endphp
                        <tr>
                            <td class="px-5 py-3.5 text-[13px] font-semibold text-ink-950">{{ $u->custom_id ?? $u->name ?? 'Unidad '.$u->id }}</td>
                            <td class="px-5 py-3.5 text-[12px] text-ink-500">{{ optional($u->project)->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-[12px] text-ink-700">{{ $u->bedrooms ? $u->bedrooms.' hab' : ($u->layout ?? '—') }}{{ $area ? ' · '.number_format($area,0).' m²' : '' }}</td>
                            <td class="px-5 py-3.5 text-[13px] font-bold text-ink-900 text-right">${{ number_format((float)$u->price, 0) }}</td>
                            <td class="px-5 py-3.5 text-[12px] text-ink-500 text-right">{{ $ppm ? '$'.number_format($ppm,0) : '—' }}</td>
                            <td class="px-5 py-3.5"><span class="brk-pill {{ $badge[1] }}">{{ $badge[0] }}</span></td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('property.pdf', $u->id) }}" target="_blank" class="brk-btn brk-btn-ghost" style="padding:6px 10px;font-size:11.5px">Compartir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-[12px] text-ink-400">No hay unidades publicadas todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-ink-100 flex items-start gap-2.5 bg-info-soft/40">
            <i class="pi pi-info-circle text-info mt-0.5"></i>
            <p class="text-[11px] text-ink-600 m-0">Disponibilidad real en tiempo real. Una unidad solo queda <b>bloqueada con una reserva con depósito</b>; hasta entonces sigue disponible.</p>
        </div>
    </div>
</div>
@endsection
