@extends('layouts.admin_crm')
@section('title', __('Statistics').' — CRM Duna Makai')
@section('page_title', __('Statistics'))
@section('page_breadcrumb', __('System · Platform statistics'))
@php $activeRoute = 'estadisticas'; @endphp

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-5">

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
</div>
@endsection
