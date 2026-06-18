@extends('layouts.main_admin')

@section('title', 'Importar datos - Admin Panel')

@php
    $activeRoute = 'data-import';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8 max-w-4xl">

        <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Importar datos') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('Migrá usuarios y unidades desde un archivo CSV (Excel, otra plataforma, etc.).') }}</p>
        <hr class="border-gray-300 mb-6">

        @if ($errors->any())
            <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <ol class="mb-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
            <li><span class="font-semibold text-[#5c6b4a]">1.</span> Descargá el CSV de ejemplo</li>
            <li><span class="font-semibold text-[#5c6b4a]">2.</span> Subí tu CSV</li>
            <li><span class="font-semibold text-[#5c6b4a]">3.</span> Mapeá las columnas</li>
            <li><span class="font-semibold text-[#5c6b4a]">4.</span> Importá</li>
        </ol>

        @foreach ($resources as $key => $resource)
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $resource->label() }}</h2>
                        <p class="text-sm text-gray-500">{{ count($resource->fields()) }} campos importables disponibles.</p>
                    </div>
                    <a href="{{ route('admin.data-import.sample', $resource->key()) }}"
                       class="inline-flex items-center gap-2 rounded border border-[#5c6b4a] px-4 py-2 text-sm font-semibold text-[#5c6b4a] transition hover:bg-[#5c6b4a] hover:text-white">
                        ⬇ {{ __('Descargar CSV de ejemplo') }}
                    </a>
                </div>

                <form action="{{ route('admin.data-import.upload', $resource->key()) }}"
                      method="POST" enctype="multipart/form-data"
                      class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    <input type="file" name="file" accept=".csv,text/csv" required
                           class="block w-full text-sm text-gray-600 file:mr-4 file:rounded file:border-0 file:bg-[#eef0ea] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#5c6b4a] hover:file:bg-[#e2e6dc]">
                    <button type="submit"
                            class="whitespace-nowrap rounded bg-[#5c6b4a] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4a5a3a]">
                        {{ __('Subir y mapear') }}
                    </button>
                </form>
            </div>
        @endforeach

    </div>
</div>
@endsection
