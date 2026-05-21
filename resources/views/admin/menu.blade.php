@extends('layouts.main_admin')

@section('title', 'Menu - Admin Panel')

@php
    $activeRoute = 'menu';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-semibold text-[#5c4a32]">Menu</h1>
        <button class="px-5 py-2 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">Add</button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden ">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-[#5c6b4a] text-xs text-white uppercase">
                    <th class="py-3 px-6 font-semibold text-center">Title</th>
                    <th class="py-3 px-6 font-semibold text-center">Order</th>
                    <th class="py-3 px-6 font-semibold text-center">Visible</th>
                    <th class="py-3 px-6 font-semibold text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Main Website (EN)</td>
                    <td class="py-3 px-6 text-center text-gray-500">1</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Brochure (EN)</td>
                    <td class="py-3 px-6 text-center text-gray-500">2</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Floor Plans</td>
                    <td class="py-3 px-6 text-center text-gray-500">3</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">ROIs (EN)</td>
                    <td class="py-3 px-6 text-center text-gray-500">4</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">FAQs (EN)</td>
                    <td class="py-3 px-6 text-center text-gray-500">5</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Specifications (EN)</td>
                    <td class="py-3 px-6 text-center text-gray-500">6</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Main Website (ES)</td>
                    <td class="py-3 px-6 text-center text-gray-500">7</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Brochure (ES)</td>
                    <td class="py-3 px-6 text-center text-gray-500">8</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6 text-center text-gray-700">Makai Especificaciones (ES)</td>
                    <td class="py-3 px-6 text-center text-gray-500">10</td>
                    <td class="py-3 px-6 text-center text-gray-500">Yes</td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 0 1 2.828 2.828L11.828 15.828a2 2 0 0 1-1.415.586H7v-3.414a2 2 0 0 1 .586-1.415z"/></svg></button>
                            <button class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>
@endsection