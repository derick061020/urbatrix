@extends('layouts.main_admin')

@section('title', 'Deals - Admin Panel')

@php
    $activeRoute = 'deals';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

    <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Deals') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('View and edit all the deals.') }}</p>
    <hr class="border-gray-300 mb-6">

    {{-- Stats Cards --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-red-500 tracking-widest">PENDING</span>
                    <span class="text-xs text-gray-400">3/102</span>
                </div>
                <div class="text-3xl font-bold text-red-500">3%</div>
                <div class="text-sm text-gray-400">$150,000</div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-green-600 tracking-widest">RESERVED</span>
                    <span class="text-xs text-gray-400">8/102</span>
                </div>
                <div class="text-3xl font-bold text-green-600">8%</div>
                <div class="text-sm text-gray-400">$2,800,000</div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-[#5c4a32] tracking-widest">SOLD</span>
                    <span class="text-xs text-gray-400">12/102</span>
                </div>
                <div class="text-3xl font-bold text-[#5c4a32]">12%</div>
                <div class="text-sm text-gray-400">$4,200,000</div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-gray-700 tracking-widest">TOTAL</span>
                    <span class="text-xs text-gray-400">23/102</span>
                </div>
                <div class="text-3xl font-bold text-gray-700">23%</div>
                <div class="text-sm text-gray-400">$7,150,000 / $45,988,000</div>
            </div>
        </div>

        {{-- Deals Breakdown --}}
        <div class="mb-4">
            <p class="text-xs font-bold text-gray-500 tracking-widest mb-2">{{ __('DEALS BREAKDOWN') }}</p>
            <div class="flex rounded overflow-hidden h-4 bg-gray-100">
                <div class="bg-red-400 h-full" style="width: 3%"></div>
                <div class="bg-green-500 h-full" style="width: 8%"></div>
                <div class="bg-[#7a6248] h-full" style="width: 12%"></div>
            </div>
            <div class="flex gap-4 mt-2 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-red-400"></span> Pending (3)</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-green-500"></span> Reserved (8)</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-[#7a6248]"></span> Sold (12)</span>
            </div>
        </div>

        {{-- Sales Progress --}}
        <div>
            <p class="text-xs font-bold text-gray-500 tracking-widest mb-2">{{ __('SALES PROGRESS') }}</p>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="bg-[#6b7c5c] h-3 rounded-full" style="width: 23%"></div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-lg shadow p-6">
        {{-- Filters + Search --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex gap-2">
                <button class="px-4 py-1.5 text-xs font-semibold border-2 border-red-400 text-red-500 rounded">PENDING</button>
                <button class="px-4 py-1.5 text-xs font-semibold border-2 border-green-500 text-green-600 rounded">RESERVED</button>
                <button class="px-4 py-1.5 text-xs font-semibold border-2 border-[#7a6248] text-[#7a6248] rounded">SOLD</button>
            </div>
            <div class="relative">
                <input type="text" placeholder="{{ __('Search') }}" class="border border-gray-300 rounded px-3 py-1.5 text-sm pr-8 focus:outline-none focus:ring-1 focus:ring-gray-400">
                <svg class="absolute right-2 top-2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                        <th class="py-3 pr-4 font-semibold">{{ __('Edit') }}</th>
                        <th class="py-3 pr-4 font-semibold">Unit ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">Status ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">User ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">Contact ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">{{ __('Deal Start ↑↓') }}</th>
                        <th class="py-3 pr-4 font-semibold">Price ($) ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">Agent ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">{{ __('Will Pay Manually ↑↓') }}</th>
                        <th class="py-3 pr-4 font-semibold">Notes ↑↓</th>
                        <th class="py-3 pr-4 font-semibold">Email ↑↓</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 pr-4"><button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button></td>
                        <td class="py-3 pr-4">A-101</td>
                        <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-600">PENDING</span></td>
                        <td class="py-3 pr-4">{{ __('John Doe') }}</td>
                        <td class="py-3 pr-4">+18095551234</td>
                        <td class="py-3 pr-4">2026-03-15</td>
                        <td class="py-3 pr-4">$350,000</td>
                        <td class="py-3 pr-4">{{ __('Vanessa Garcia') }}</td>
                        <td class="py-3 pr-4 text-center">No</td>
                        <td class="py-3 pr-4">—</td>
                        <td class="py-3 pr-4">john.doe@email.com</td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 pr-4"><button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button></td>
                        <td class="py-3 pr-4">B-205</td>
                        <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">RESERVED</span></td>
                        <td class="py-3 pr-4">{{ __('Maria Lopez') }}</td>
                        <td class="py-3 pr-4">+18095559876</td>
                        <td class="py-3 pr-4">2026-03-18</td>
                        <td class="py-3 pr-4">$420,000</td>
                        <td class="py-3 pr-4">{{ __('Angel Ramirez') }}</td>
                        <td class="py-3 pr-4 text-center">Yes</td>
                        <td class="py-3 pr-4">{{ __('VIP client') }}</td>
                        <td class="py-3 pr-4">maria.lopez@email.com</td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 pr-4"><button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button></td>
                        <td class="py-3 pr-4">PH-301</td>
                        <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#e8e0d4] text-[#5c4a32]">SOLD</span></td>
                        <td class="py-3 pr-4">{{ __('Carlos Perez') }}</td>
                        <td class="py-3 pr-4">+18095554321</td>
                        <td class="py-3 pr-4">2026-02-20</td>
                        <td class="py-3 pr-4">$895,000</td>
                        <td class="py-3 pr-4">{{ __('Ernesto Rivas') }}</td>
                        <td class="py-3 pr-4 text-center">No</td>
                        <td class="py-3 pr-4">—</td>
                        <td class="py-3 pr-4">carlos.perez@email.com</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
            <div class="flex items-center gap-1">
                <button class="px-2 py-1 border rounded hover:bg-gray-100">«</button>
                <button class="px-2 py-1 border rounded hover:bg-gray-100">‹</button>
                <span class="px-3">{{ __('Record 1 - 3 of 3') }}</span>
                <button class="px-2 py-1 border rounded hover:bg-gray-100">›</button>
                <button class="px-2 py-1 border rounded hover:bg-gray-100">»</button>
                <select class="ml-2 border border-gray-300 rounded px-2 py-1 text-sm">
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
            </div>
            <button class="flex items-center gap-2 px-4 py-1.5 border border-gray-300 rounded text-sm hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Export
            </button>
        </div>
    </div>

</div>

</div>
@endsection