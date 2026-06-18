@extends('layouts.main_admin')

@section('title', 'Mapear columnas - Admin Panel')

@php
    $activeRoute = 'data-import';
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8 max-w-6xl">

        <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">{{ __('Mapear columnas') }} — {{ $resource->label() }}</h1>
        <p class="text-gray-500 mb-6">{{ __('Asociá cada columna de tu CSV con un campo de la plataforma. Las columnas que dejes en «Ignorar» no se importan.') }}</p>
        <hr class="border-gray-300 mb-6">

        @if ($errors->any())
            <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.data-import.run', $resource->key()) }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-6 overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#eef0ea] text-left text-gray-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">{{ __('Columna del CSV') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('Ejemplo de datos') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('Campo destino') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($headers as $i => $header)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $header }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    @php
                                        $examples = collect($preview)->pluck($i)->filter(fn ($v) => $v !== null && $v !== '')->take(2);
                                    @endphp
                                    {{ $examples->isNotEmpty() ? $examples->implode(', ') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <select name="mapping[{{ $i }}]"
                                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-[#5c6b4a] focus:outline-none">
                                        <option value="">— {{ __('Ignorar') }} —</option>
                                        @foreach ($fields as $field => $def)
                                            <option value="{{ $field }}" @selected(($suggestions[$i] ?? null) === $field)>
                                                {{ $def['label'] }}{{ ($def['required'] ?? false) ? ' *' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-6 grid gap-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm md:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-800">{{ __('Modo de importación') }}</h3>
                    <label class="mb-2 flex items-start gap-2 text-sm text-gray-700">
                        <input type="radio" name="mode" value="create" checked class="mt-1 accent-[#5c6b4a]">
                        <span><strong>{{ __('Crear nuevos') }}</strong> — {{ __('agrega registros; salta los que ya existan.') }}</span>
                    </label>
                    <label class="mb-2 flex items-start gap-2 text-sm text-gray-700">
                        <input type="radio" name="mode" value="update" class="mt-1 accent-[#5c6b4a]">
                        <span><strong>{{ __('Actualizar existentes') }}</strong> — {{ __('sólo modifica los que coinciden; no crea.') }}</span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700">
                        <input type="radio" name="mode" value="upsert" class="mt-1 accent-[#5c6b4a]">
                        <span><strong>{{ __('Ambos (crear y actualizar)') }}</strong> — {{ __('actualiza si existe, crea si no.') }}</span>
                    </label>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-800">{{ __('Campo de coincidencia') }}</h3>
                    <p class="mb-2 text-xs text-gray-500">{{ __('Se usa para detectar duplicados al actualizar. Debe estar mapeado arriba.') }}</p>
                    <select name="match_field"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-[#5c6b4a] focus:outline-none">
                        @foreach ($resource->matchKeys() as $mk)
                            <option value="{{ $mk }}">{{ $fields[$mk]['label'] ?? $mk }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.data-import') }}"
                   class="rounded border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                    {{ __('Cancelar') }}
                </a>
                <button type="submit"
                        class="rounded bg-[#5c6b4a] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4a5a3a]">
                    {{ __('Importar ahora') }}
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
