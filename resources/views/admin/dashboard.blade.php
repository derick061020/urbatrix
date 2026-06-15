@extends('layouts.main_admin')

@section('title', 'Dashboard - Admin Panel')

@php
    $activeRoute = 'dashboard';
@endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .card { box-shadow: 0 2px 8px 0 rgba(0,0,0,.10); }
</style>
@endpush

@section('content')
<div class="w-full bg-surface-50 px-2 py-4 sm:p-10 overflow-auto flex-1">

    <!-- PAGE HEADER -->
    <div>
        <header class="mb-4">
            <h1 class="text-3xl font-semibold text-surface-700">{{ __('Makai Residences Sales') }}</h1>
            <p class="text-surface-700 text-base">{{ __('Manage your interactive price list and development sales') }}</p>
        </header>

        <!-- ALL UNITS card -->
        <div class="flex flex-col md:flex-row gap-2 mb-5">
            <div class="w-full rounded-md border-l-4 border-secondary bg-white p-4 flex flex-col gap-2 shadow-lg">
                <span class="text-xl font-bold uppercase text-surface-700">{{ __('ALL UNITS') }}</span>
                <div class="text-sm">
                    <span class="block font-bold text-surface-700">{{ $stats['total_units'] }} Units</span>
                    <span class="block font-bold text-surface-700">${{ number_format($stats['total_units'] * 450000, 0) }}</span>
                    <span class="block text-xs text-surface-700">{{ __('(Estimated total value)') }}</span>
                </div>
            </div>
        </div>

        <!-- SALES card (dark green) -->
        <div class="flex flex-col w-full mb-5">
            <div class="card rounded-md p-4 relative shadow-lg bg-primary-500 border-l-4 border-secondary" style="min-height: 130px;">
                <p class="text-white uppercase font-bold text-xl">SALES</p>

                <span class="text-white absolute right-4 top-4 text-base">
                    {{ $stats['completed_deals'] }} <sup class="font-semibold">{{ $stats['total_deals'] > 0 ? round(($stats['completed_deals'] / $stats['total_deals']) * 100) : 0 }}%</sup>
                </span>

                <div class="text-sm text-white font-semibold flex flex-col mt-2 gap-1">
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" checked class="mx-2 rounded border-surface-200 text-primary shadow-sm" />
                        Include $20 000 Launch Discount
                    </label>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" checked class="mx-2 rounded border-surface-200 text-primary shadow-sm" />
                        Include Extras
                    </label>
                </div>

                <p class="text-white font-bold pt-3">$0</p>
                <p class="text-white text-sm">{{ __('$0 ex vat') }}</p>

                <span class="text-3xl text-white absolute bottom-4 right-4 font-bold">0%</span>
            </div>
        </div>

        <!-- AVAILABLE / PENDING row -->
        <div class="flex flex-col md:flex-row gap-2 mb-5">
            <!-- AVAILABLE -->
            <div class="flex flex-col w-full lg:w-1/2">
                <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-secondary" style="min-height: 120px;">
                    <p class="text-xl uppercase font-bold text-surface-700">AVAILABLE</p>
                    <p class="text-primary-500 font-semibold text-xs">{{ __('Units Remaining') }}</p>

                    <span class="absolute right-4 top-4 text-base text-surface-700">
                        102 <sup class="font-semibold text-primary-500">100%</sup>
                    </span>

                    <p class="uppercase font-bold pt-3 text-surface-700">$45 988 000</p>
                    <p class="text-xs text-surface-700">{{ __('$39 989 565 ex vat') }}</p>

                    <span class="text-3xl text-primary-500 absolute bottom-4 right-4 font-bold">100%</span>
                </div>
            </div>

            <!-- PENDING -->
            <div class="flex flex-col w-full lg:w-1/2">
                <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-secondary" style="min-height: 120px;">
                    <p class="text-xl uppercase font-bold text-surface-700">PENDING</p>
                    <p class="text-primary-500 font-semibold text-xs">{{ __('Booked but not confirmed') }}</p>

                    <span class="absolute right-4 top-4 text-base text-surface-700">
                        0 <sup class="font-semibold text-primary-500">0%</sup>
                    </span>

                    <p class="uppercase font-bold pt-3 text-surface-700">$0</p>
                    <p class="text-xs text-surface-700">{{ __('$0 ex vat') }}</p>

                    <span class="text-3xl text-primary-500 absolute bottom-4 right-4 font-bold">0%</span>
                </div>
            </div>
        </div>

        <!-- RESERVED / SOLD row -->
        <div class="flex flex-col md:flex-row gap-2 mb-5">
            <!-- RESERVED -->
            <div class="flex flex-col w-full lg:w-1/2">
                <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-secondary" style="min-height: 120px;">
                    <p class="text-xl uppercase font-bold text-surface-700">RESERVED</p>
                    <p class="text-primary-500 font-semibold text-xs">{{ __('Confirmed &amp; reservation deposit paid') }}</p>

                    <span class="absolute right-4 top-4 text-base text-surface-700">
                        {{ $stats['pending_deals'] }} <sup class="font-semibold text-primary-500">{{ $stats['total_deals'] > 0 ? round(($stats['pending_deals'] / $stats['total_deals']) * 100) : 0 }}%</sup>
                    </span>

                    <p class="uppercase font-bold pt-3 text-surface-700">${{ number_format($stats['pending_deals'] * 450000, 0) }}</p>
                    <p class="text-xs text-surface-700">${{ number_format($stats['pending_deals'] * 450000 * 0.85, 0) }} ex vat</p>

                    <span class="text-3xl text-primary-500 absolute bottom-4 right-4 font-bold">{{ $stats['total_deals'] > 0 ? round(($stats['pending_deals'] / $stats['total_deals']) * 100) : 0 }}%</span>
                </div>
            </div>

            <!-- SOLD -->
            <div class="flex flex-col w-full lg:w-1/2">
                <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-secondary" style="min-height: 120px;">
                    <p class="text-xl uppercase font-bold text-surface-700">SOLD</p>
                    <p class="text-primary-500 font-semibold text-xs">{{ __('Contract Received') }}</p>

                    <span class="absolute right-4 top-4 text-base text-surface-700">
                        {{ $stats['completed_deals'] }} <sup class="font-semibold text-primary-500">{{ $stats['total_deals'] > 0 ? round(($stats['completed_deals'] / $stats['total_deals']) * 100) : 0 }}%</sup>
                    </span>

                    <p class="uppercase font-bold pt-3 text-surface-700">${{ number_format($stats['completed_deals'] * 450000, 0) }}</p>
                    <p class="text-xs text-surface-700">${{ number_format($stats['completed_deals'] * 450000 * 0.85, 0) }} ex vat</p>

                    <span class="text-3xl text-primary-500 absolute bottom-4 right-4 font-bold">{{ $stats['total_deals'] > 0 ? round(($stats['completed_deals'] / $stats['total_deals']) * 100) : 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- GOOGLE ANALYTICS section -->
        <div>
            <h1 class="text-3xl font-semibold text-surface-700 mb-0">{{ __('Google Analytics') }}</h1>

            <div class="mt-5">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                    <!-- Left column: stats cards -->
                    <div class="col-span-12 md:col-span-3 flex flex-col gap-3">

                        <!-- ONLINE USERS -->
                        <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-green-500">
                            <span class="font-bold text-base uppercase text-surface-700">{{ __('Online Users') }}</span>
                            <span class="block text-xs font-semibold text-surface-700">{{ __('Active users on the site') }}</span>
                            <div class="text-3xl font-bold text-right text-green-500 mt-2">1</div>
                        </div>

                        <!-- ALL USERS -->
                        <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-blue-500">
                            <span class="font-bold text-base uppercase text-surface-700">{{ __('All Users') }}</span>
                            <span class="block text-xs font-semibold text-surface-700">{{ __('All the users over the last 14 days') }}</span>
                            <div class="text-3xl font-bold text-right text-blue-500 mt-2">7231</div>
                        </div>

                        <!-- NEW USERS -->
                        <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-blue-500">
                            <span class="font-bold text-base uppercase text-surface-700">{{ __('New Users') }}</span>
                            <span class="block text-xs font-semibold text-surface-700">{{ __('Over the last 14 days') }}</span>
                            <div class="text-3xl font-bold text-right text-blue-500 mt-2">7099</div>
                        </div>

                        <!-- AVG SESSION DURATION -->
                        <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-blue-500">
                            <span class="font-bold text-base uppercase text-surface-700">{{ __('Avg. Session Duration') }}</span>
                            <span class="block text-xs font-semibold text-surface-700">{{ __('Over the last 14 days') }}</span>
                            <div class="text-3xl font-bold text-right text-blue-500 mt-2">00:00:24</div>
                        </div>
                    </div>

                    <!-- Right column: Pageviews chart -->
                    <div class="col-span-12 md:col-span-9 pr-2 md:px-2">
                        <div class="card rounded-md p-4 bg-white relative shadow-lg border-l-4 border-blue-500 h-full">
                            <p class="uppercase font-bold text-surface-700">PAGEVIEWS</p>
                            <p class="text-xs mb-2 font-semibold text-blue-500">
                                Pageviews over the last 14 days (Data excludes current day)
                            </p>
                            <p class="float-right uppercase font-bold text-3xl text-blue-500 -mt-1">9191</p>
                            <div class="clear-both"></div>
                            <canvas id="pageviewsChart" height="220"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- RECENT DEALS section -->
        <div class="mt-8">
            <h1 class="text-3xl font-semibold text-surface-700 mb-4">{{ __('Recent Deals') }}</h1>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="px-4 py-3">{{ __('Deal Number') }}</th>
                                <th class="px-4 py-3">{{ __('Client') }}</th>
                                <th class="px-4 py-3">{{ __('Unit') }}</th>
                                <th class="px-4 py-3">{{ __('Agent') }}</th>
                                <th class="px-4 py-3">{{ __('Price') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentDeals as $deal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $deal->deal_number }}</td>
                                <td class="px-4 py-3">{{ $deal->client_name }}</td>
                                <td class="px-4 py-3">{{ $deal->unit->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $deal->agent->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">${{ number_format($deal->deal_price, 0) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        @if($deal->status == 'PENDING') bg-yellow-100 text-yellow-800
                                        @elseif($deal->status == 'COMPLETED') bg-green-100 text-green-800
                                        @elseif($deal->status == 'CANCELLED') bg-red-100 text-red-800
                                        @endif">
                                        {{ $deal->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $deal->deal_date->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                            @if($recentDeals->isEmpty())
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No recent deals found
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('pageviewsChart').getContext('2d');

    const labels = [
        '2026-03-19','2026-03-20','2026-03-21','2026-03-22','2026-03-23',
        '2026-03-24','2026-03-25','2026-03-26','2026-03-27','2026-03-28',
        '2026-03-29','2026-03-30','2026-03-31','2026-04-01'
    ];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __("Pageviews") }}',
                data: [120, 200, 255, 320, 430, 490, 530, 490, 610, 700, 730, 860, 1420, 1230],
                fill: true,
                backgroundColor: 'rgba(147, 197, 253, 0.35)',
                borderColor: 'rgba(59, 130, 246, 0.8)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(59, 130, 246, 0.9)',
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    grid: { display: true, color: 'rgba(0,0,0,0.06)' },
                    ticks: { font: { family: 'Montserrat', size: 10 }, color: '#888', maxRotation: 45, minRotation: 30 }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: { font: { family: 'Montserrat', size: 11 }, color: '#888' }
                }
            }
        }
    });
</script>
@endpush
