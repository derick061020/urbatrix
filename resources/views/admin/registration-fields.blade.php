@extends('layouts.main_admin')


@section('title', 'Registration Fields - Admin Panel')

@php
    $activeRoute = 'registration-fields';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

    <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Registration Fields') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('Add and edit registration fields. Once created, the registration fields can be sorted, edited or removed via the table below.') }}</p>
    <hr class="border-gray-300 mb-6">

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-semibold text-gray-700">{{ __('Registration Fields') }}</h2>
            <button class="flex items-center gap-1.5 px-4 py-2 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Registration Field
            </button>
        </div>

        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                    <th class="py-3 pr-4 font-semibold">{{ __('Edit') }}</th>
                    <th class="py-3 pr-4 font-semibold">{{ __('Enabled') }}</th>
                    <th class="py-3 pr-4 font-semibold">Type</th>
                    <th class="py-3 pr-4 font-semibold">Label</th>
                    <th class="py-3 pr-4 font-semibold">Placeholder</th>
                    <th class="py-3 pr-4 font-semibold">{{ __('Delete') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400 text-sm">{{ __('No registration fields found.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>
@endsection