@extends('layouts.main_admin')

@section('title', 'Landing - Admin Panel')

@php
    $activeRoute = 'landing';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

        <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Landing') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('Configure landing settings.') }}</p>
        <hr class="border-gray-300 mb-6">

        {{-- Views Card --}}
        <div class="bg-white rounded-lg shadow p-6 mb-4">

            <div class="flex items-center justify-between mb-1">
                <h2 class="text-base font-semibold text-gray-800">{{ __('Views') }}</h2>
                <button type="button"
                    class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    &minus;
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-5">{{ __('Configure the views shown to the end user.') }}</p>

            {{-- Enabled Sub-card --}}
            <div class="border border-gray-200 rounded-lg p-5 mb-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('Enabled') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('Enable / disable the views shown to the end user.') }}</p>

                <div class="flex flex-col gap-3">

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer" id="toggle-card">
                        <div class="relative w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#4a5240] transition-colors duration-200">
                            <div class="absolute top-[3px] left-[3px] w-[18px] h-[18px] bg-white rounded-full shadow transition-all duration-200 peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-gray-700">{{ __('Card') }}</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer" id="toggle-plan">
                        <div class="relative w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#4a5240] transition-colors duration-200">
                            <div class="absolute top-[3px] left-[3px] w-[18px] h-[18px] bg-white rounded-full shadow transition-all duration-200 peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-gray-700">{{ __('Plan') }}</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer" id="toggle-list">
                        <div class="relative w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#4a5240] transition-colors duration-200">
                            <div class="absolute top-[3px] left-[3px] w-[18px] h-[18px] bg-white rounded-full shadow transition-all duration-200 peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-gray-700">{{ __('List') }}</span>
                    </label>

                </div>
            </div>

            {{-- Default Sub-card --}}
            <div class="border border-gray-200 rounded-lg p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('Default') }}</h3>
                <p class="text-sm text-gray-500 mb-5">{{ __('Set the default view shown to the end user.') }}</p>

                <div class="flex items-center">

                    <label class="flex flex-col items-center gap-1 cursor-pointer" style="min-width: 120px;">
                        <input type="radio" name="default-view" checked class="accent-[#4a5240] w-5 h-5 cursor-pointer">
                        <span class="text-xs text-gray-700">{{ __('Card') }}</span>
                    </label>

                    <label class="flex flex-col items-center gap-1 cursor-pointer" style="min-width: 120px;">
                        <input type="radio" name="default-view" class="accent-[#4a5240] w-5 h-5 cursor-pointer">
                        <span class="text-xs text-gray-700">{{ __('Plan') }}</span>
                    </label>

                    <label class="flex flex-col items-center gap-1 cursor-pointer" style="min-width: 120px;">
                        <input type="radio" name="default-view" class="accent-[#4a5240] w-5 h-5 cursor-pointer">
                        <span class="text-xs text-gray-700">{{ __('List') }}</span>
                    </label>

                </div>
            </div>

        </div>
        {{-- End Views Card --}}

    </div>
</div>
@endsection