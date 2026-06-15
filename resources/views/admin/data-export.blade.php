@extends('layouts.main_admin')

@section('title', 'Data Export - Admin Panel')

@php
    $activeRoute = 'data-export';
@endphp


@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

    <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Data') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('Export data') }}</p>
    <hr class="border-gray-300 mb-6">

    <div class="flex gap-3">
        <button class="px-5 py-2.5 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">{{ __('User Data') }}</button>
        <button class="px-5 py-2.5 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">{{ __('Unit Data') }}</button>
        <button class="px-5 py-2.5 bg-[#5c6b4a] text-white text-sm font-semibold rounded hover:bg-[#4a5a3a] transition">{{ __('Raw Unit CSV Data') }}</button>
    </div>

</div>

</div>
@endsection