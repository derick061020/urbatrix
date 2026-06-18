@extends('layouts.main_admin')

@section('title', 'Resultado de importación - Admin Panel')

@php
    $activeRoute = 'data-import';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8 max-w-4xl">

        <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Importación completada') }}</h1>
        <p class="text-gray-500 mb-6">{{ $resource->label() }}</p>
        <hr class="border-gray-300 mb-6">

        <div class="mb-6 grid grid-cols-3 gap-4">
            <div class="rounded-lg border border-green-200 bg-green-50 p-5 text-center">
                <div class="text-3xl font-bold text-green-700">{{ $summary['created'] }}</div>
                <div class="text-sm text-green-700">{{ __('Creados') }}</div>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 text-center">
                <div class="text-3xl font-bold text-blue-700">{{ $summary['updated'] }}</div>
                <div class="text-sm text-blue-700">{{ __('Actualizados') }}</div>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-center">
                <div class="text-3xl font-bold text-amber-700">{{ $summary['skipped'] }}</div>
                <div class="text-sm text-amber-700">{{ __('Saltados') }}</div>
            </div>
        </div>

        @if (! empty($summary['errors']))
            <div class="mb-6 rounded-lg border border-red-200 bg-white shadow-sm">
                <div class="border-b border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ __('Filas con problemas') }} ({{ count($summary['errors']) }})
                </div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-2 font-semibold">{{ __('Fila') }}</th>
                            <th class="px-4 py-2 font-semibold">{{ __('Motivo') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($summary['errors'] as $error)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-700">{{ $error['row'] }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $error['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mb-6 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ __('No hubo errores. Todos los registros válidos fueron procesados.') }}
            </div>
        @endif

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-import') }}"
               class="rounded bg-[#5c6b4a] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4a5a3a]">
                {{ __('Importar otro archivo') }}
            </a>
            <a href="{{ route('admin.units') }}"
               class="rounded border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                {{ __('Ver unidades') }}
            </a>
        </div>

    </div>
</div>
@endsection
