@extends('layouts.admin_crm')
@section('title', __('Statistics').' — CRM Duna Makai')
@section('page_title', __('Statistics'))
@section('page_breadcrumb', __('System · Platform statistics'))
@php $activeRoute = 'estadisticas'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8">

    {{-- Tabs --}}
    <div class="flex items-center gap-5 border-b border-ink-100 mb-5 overflow-x-auto">
        <button type="button" class="crm-tab-line active" data-stat-tab="comportamiento">{{ __('User behavior') }}</button>
        <button type="button" class="crm-tab-line" data-stat-tab="ventas">{{ __('Sales & platform') }}</button>
    </div>

<div id="stat-comportamiento" class="space-y-5">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php $kpis = [
            [__('Visits this month'), number_format($viewsThisMonth), 'pi-eye', '#335cff', number_format($viewsTotal).' '.__('total')],
            [__('Active users'), number_format($activeUsers), 'pi-circle-fill', '#1fc16b', __('last 5 min')],
            [__('Viewed properties'), number_format($popularUnits->count()), 'pi-building', '#fa7319', __('with views')],
            [__('Total users'), number_format($totalUsers), 'pi-users', '#5c7c68', '+'.number_format($newThisMonth).' '.__('this month')],
            [__('New this month'), number_format($newThisMonth), 'pi-user-plus', '#222530', __('registrations')],
        ]; @endphp
        @foreach($kpis as [$label, $val, $icon, $color, $sub])
            <div class="crm-card p-4" style="border-top:3px solid {{ $color }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[10px] uppercase tracking-wider text-ink-500">{{ $label }}</div>
                    <i class="pi {{ $icon }} text-[12px]" style="color:{{ $color }}"></i>
                </div>
                <div class="text-[24px] font-bold text-ink-950 leading-none">{{ $val }}</div>
                <div class="text-[11px] text-ink-400 mt-1.5">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Tendencia de registros --}}
        <div class="crm-card overflow-hidden lg:col-span-2">
            <div class="px-5 py-3 border-b border-ink-100 text-[14px] font-semibold text-ink-900">{{ __('User registrations — last 6 months') }}</div>
            <div class="p-5">
                @php $maxTrend = max(1, $trend->max('count')); @endphp
                <div class="flex items-end justify-between gap-3 h-40">
                    @foreach($trend as $t)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="text-[11px] font-bold text-ink-700">{{ $t['count'] }}</div>
                            <div class="w-full rounded-t-md bg-brand/80" style="height:{{ max(4, round($t['count'] / $maxTrend * 130)) }}px"></div>
                            <div class="text-[10px] text-ink-400 uppercase">{{ $t['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Distribución por país --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 text-[14px] font-semibold text-ink-900">{{ __('Buyers by country') }}</div>
            <div class="p-5 space-y-3">
                @forelse($byCountry as $row)
                    @php $pct = round($row->total / $byCountryTotal * 100); @endphp
                    <div>
                        <div class="flex items-center justify-between text-[12px] mb-1">
                            <span class="text-ink-700 font-medium">{{ $row->country }}</span>
                            <span class="text-ink-500">{{ $row->total }} · {{ $pct }}%</span>
                        </div>
                        <div class="crm-progress"><span style="background:#5c7c68;width:{{ $pct }}%"></span></div>
                    </div>
                @empty
                    <div class="text-[12px] text-ink-400 text-center py-6">{{ __('No country data yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Propiedades más vistas --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 text-[14px] font-semibold text-ink-900">{{ __('Most viewed properties') }}</div>
            <div class="divide-y divide-ink-100">
                @forelse($popularUnits as $i => $unit)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-ink-100 text-ink-600 text-[11px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                        <span class="flex-1 text-[13px] font-semibold text-ink-900">{{ $unit->custom_id ?? $unit->name ?? __('Unit').' '.$unit->id }}</span>
                        <span class="text-[12px] text-ink-500"><i class="pi pi-eye text-[11px] mr-1"></i>{{ number_format($unit->views_total) }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[12px] text-ink-400">{{ __('No views recorded.') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Actividad reciente de usuarios --}}
        <div class="crm-card overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 text-[14px] font-semibold text-ink-900">{{ __('Recent activity') }}</div>
            <div class="divide-y divide-ink-100">
                @forelse($recentUsers as $u)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-brand/15 text-brand text-[12px] font-bold flex items-center justify-center">{{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $u->name }}</div>
                            <div class="text-[11px] text-ink-400 truncate">{{ $u->email }}</div>
                        </div>
                        <span class="text-[11px] text-ink-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($u->last_seen)->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[12px] text-ink-400">{{ __('No recent activity.') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="p-3 rounded-lg bg-info-soft border border-info/20 text-[11px] text-ink-600 flex items-center gap-2">
        <i class="pi pi-info-circle text-info"></i>
        {{ __('Metrics based on real platform data (unit views, active sessions and registrations). Average session and bounce rate require integration with external web analytics.') }}
    </div>
</div>{{-- /stat-comportamiento --}}

{{-- ════════════════════ VENTAS Y PLATAFORMA ════════════════════ --}}
<div id="stat-ventas" class="space-y-5 hidden">

    {{-- KPIs comerciales --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @php $salesKpis = [
            [__('Sales this month'), '$'.number_format($salesMonth, 0), '#5c7c68', $unitsMonth.' '.__('units')],
            [__('Cumulative sales'), '$'.number_format($salesTotal, 0), '#0EA5A4', $unitsTotal.' '.__('units sold')],
            [__('Pipeline'), '$'.number_format($pipelineValue, 0), '#E2A33B', $pipelineCount.' '.__('active')],
            [__('Collected this month'), '$'.number_format($collectedMonth, 0), '#1fc16b', __('confirmed payments')],
            [__('Receivables'), '$'.number_format($receivables, 0), '#9AAE8C', __('balance in progress')],
            [__('Overdue'), '$'.number_format($overdueAmount, 0).' · '.$overduePct.'%', '#fb3748', $overdueCount.' '.__('overdue installments')],
        ]; @endphp
        @foreach($salesKpis as [$label, $val, $color, $sub])
            <div class="crm-card p-4" style="border-top:3px solid {{ $color }}">
                <div class="text-[10px] uppercase tracking-wider text-ink-500 mb-2">{{ $label }}</div>
                <div class="text-[20px] font-bold text-ink-950 leading-none">{{ $val }}</div>
                <div class="text-[11px] text-ink-400 mt-1.5">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    {{-- Inventario por proyecto --}}
    <div class="crm-card overflow-hidden">
        <div class="px-5 py-3 border-b border-ink-100 flex items-center justify-between">
            <span class="text-[14px] font-semibold text-ink-900">{{ __('Inventory by project') }}</span>
            <span class="text-[11px] text-ink-400">{{ __('full portfolio') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px]">
                <thead>
                    <tr>
                        @foreach([__('Project'),__('Inventory status'),__('Sold'),__('Res.'),__('Avail.'),__('Construction'),__('Sold value')] as $h)
                            <th class="px-4 py-2.5 text-[10px] font-semibold uppercase tracking-wider text-ink-500 {{ in_array($h,[__('Sold'),__('Res.'),__('Avail.'),__('Sold value')]) ? 'text-right' : 'text-left' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse($inventory as $p)
                        @php $t = max(1, $p['total']); @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="text-[13px] font-semibold text-ink-950">{{ $p['name'] }}</div>
                                <div class="text-[10.5px] text-ink-400">{{ $p['type'] ?? '—' }} · {{ $p['total'] }} uds</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex h-3 w-40 rounded-full overflow-hidden bg-ink-100">
                                    <div style="width:{{ round($p['sold']/$t*100) }}%;background:#3F6F62"></div>
                                    <div style="width:{{ round($p['reserved']/$t*100) }}%;background:#E2A33B"></div>
                                    <div style="width:{{ round($p['available']/$t*100) }}%;background:#CBD5E1"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-[13px] text-ink-700">{{ $p['sold'] }}</td>
                            <td class="px-4 py-3 text-right text-[13px] text-ink-700">{{ $p['reserved'] }}</td>
                            <td class="px-4 py-3 text-right text-[13px] text-ink-700">{{ $p['available'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-14 h-1.5 rounded-full bg-ink-100 overflow-hidden"><div class="h-full" style="width:{{ $p['progress'] }}%;background:#0EA5A4"></div></div>
                                    <span class="text-[11px] text-ink-500">{{ $p['progress'] }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-[13px] font-semibold text-ink-900">${{ number_format($p['value'], 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-[12px] text-ink-400">{{ __('No projects registered yet.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($inventory->count())
                    <tfoot>
                        <tr class="border-t-2 border-ink-200">
                            <td class="px-4 py-3 text-[13px] font-bold text-ink-950">{{ __('Total portfolio') }}</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-right text-[13px] font-bold text-ink-950">{{ $invTotals['sold'] }}</td>
                            <td class="px-4 py-3 text-right text-[13px] font-bold text-ink-950">{{ $invTotals['reserved'] }}</td>
                            <td class="px-4 py-3 text-right text-[13px] font-bold text-ink-950">{{ $invTotals['available'] }}</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-right text-[13px] font-bold text-ink-950">${{ number_format($invTotals['value'], 0) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="px-5 py-3 border-t border-ink-100 flex flex-wrap gap-4 text-[11px] text-ink-500">
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#3F6F62"></span>{{ __('Sold') }}</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#E2A33B"></span>{{ __('Reserved') }}</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm" style="background:#CBD5E1"></span>{{ __('Available') }}</span>
        </div>
    </div>

    {{-- Morosidad --}}
    <div class="crm-card overflow-hidden">
        <div class="px-5 py-3 border-b border-ink-100 flex items-center justify-between">
            <span class="text-[14px] font-semibold text-ink-900">{{ __('Overdue receivables — needs action') }}</span>
            <span class="crm-pill bg-err-soft text-err">{{ $overdueCount }} {{ __('overdue') }}</span>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse($overduePayments as $pay)
                @php
                    $r = $pay->reservation;
                    $client = $r ? trim(($r->first_name ?? '').' '.($r->last_name ?? '')) : '—';
                    $days = $pay->due_date ? \Carbon\Carbon::parse($pay->due_date)->diffInDays(now()) : 0;
                @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $client ?: '—' }}</div>
                        <div class="text-[11px] text-ink-400">{{ $pay->label ?? $pay->payment_type }}{{ optional(optional($r)->unit)->custom_id ? ' · '.$r->unit->custom_id : '' }}</div>
                    </div>
                    <span class="text-[13px] font-bold text-ink-900">${{ number_format((float)$pay->amount, 0) }}</span>
                    <span class="text-[11px] font-bold text-err whitespace-nowrap">{{ $days }} d</span>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-[12px] text-ink-400">{{ __('No overdue installments. 🎉') }}</div>
            @endforelse
        </div>
    </div>

    <div class="p-3 rounded-lg bg-info-soft border border-info/20 text-[11px] text-ink-600 flex items-start gap-2">
        <i class="pi pi-info-circle text-info mt-0.5"></i>
        {{ __('Sales come from closed deals; collections from confirmed payments. Projected and received amounts are kept separate. Values shown in USD.') }}
    </div>

</div>{{-- /stat-ventas --}}

</div>

<script>
    (function(){
        var btns = document.querySelectorAll('[data-stat-tab]');
        btns.forEach(function(b){
            b.addEventListener('click', function(){
                var tab = b.dataset.statTab;
                btns.forEach(function(x){ x.classList.toggle('active', x === b); });
                document.getElementById('stat-comportamiento').classList.toggle('hidden', tab !== 'comportamiento');
                document.getElementById('stat-ventas').classList.toggle('hidden', tab !== 'ventas');
            });
        });
    })();
</script>
@endsection
