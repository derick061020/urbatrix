@extends('layouts.main_admin')

@section('title', 'Extras - Admin Panel')

@php
    $activeRoute = 'extras';
@endphp


@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

    <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Extras') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('Edit the global interactive extras settings') }}</p>
    <hr class="border-gray-300 mb-6">

    <div class="space-y-6">

        {{-- Parking --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-1">{{ __('Parking') }}</h2>
            <p class="text-sm text-gray-400 mb-5">{{ __('Configure settings related to parking bays.') }}</p>

            <div class="space-y-5">
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('One Parking Bay') }}</p>
                    <div class="w-11 h-6 bg-gray-200 rounded-full relative cursor-pointer">
                        <div class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 shadow"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Tandem Parking bay') }}</p>
                    <div class="w-11 h-6 bg-gray-200 rounded-full relative cursor-pointer">
                        <div class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 shadow"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Extras Settings --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-5">{{ __('Additional Extras Settings') }}</h2>

            <div class="flex items-start gap-4 justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ __('Additional Extras') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('Enable this to allow users to select Additional Extras as an optional extra in the checkout process.') }}<br>{{ __('This will add the Additional Extras Price to their total Purchase Price.') }}</p>
                </div>
                <div class="w-11 h-6 bg-gray-200 rounded-full relative cursor-pointer flex-shrink-0 mt-1">
                    <div class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 shadow"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-6 flex justify-end">
        <button class="px-6 py-2 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">{{ __('Save') }}</button>
    </div>

</div>

</div>
@endsection