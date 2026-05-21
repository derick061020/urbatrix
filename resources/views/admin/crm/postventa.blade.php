@extends('layouts.main_admin')
@section('title', 'Postventa - Admin Panel')
@php $activeRoute = 'crm.postventa'; @endphp

@section('content')
<div class="w-full bg-[#f9f8f6] px-2 py-4 sm:p-10 overflow-auto min-h-screen">
    <div class="flex justify-between items-start flex-wrap gap-3">
        <div>
            <h1 class="text-4xl font-semibold text-[#625441]">Postventa</h1>
            <p class="text-[#625441] mt-1">Entregas, garantías y trámites notariales tras la firma.</p>
        </div>
        <button type="button" onclick="document.getElementById('newAftersaleModal').classList.remove('hidden')"
            class="bg-[#667b6a] text-white rounded-md px-4 py-2 text-sm font-semibold hover:bg-[#5a6d5e]">
            + Nuevo caso
        </button>
    </div>

    @if(session('success'))
        <div class="my-4 rounded p-3 text-sm" style="background:#eaf6ec;color:#3d8048;border:1px solid #bedcc4;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="my-4 rounded p-3 text-sm" style="background:#fbeaea;color:#a83838;border:1px solid #eebcbc;">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <div class="relative flex items-center my-5 w-full px-5">
        <div class="absolute left-0 top-1/2 w-full border-t border-gray-200"></div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @php
            $kpis = [
                ['label' => 'Entregas programadas', 'val' => $stats['programadas'], 'color' => '#2c6aa0'],
                ['label' => 'Garantías abiertas',   'val' => $stats['garantias'],   'color' => '#9b6f1d'],
                ['label' => 'En trámite notarial',  'val' => $stats['tramite'],     'color' => '#5a3d99'],
                ['label' => 'Resueltas este mes',   'val' => $stats['resueltas'],   'color' => '#3d8048'],
            ];
        @endphp
        @foreach($kpis as $k)
            <div class="bg-white border border-gray-200 rounded p-4 text-center" style="border-top:3px solid {{ $k['color'] }};">
                <div class="text-2xl font-bold" style="color:{{ $k['color'] }};font-family:monospace;">{{ $k['val'] }}</div>
                <div class="text-xs text-[#806f56] mt-1" style="font-family:monospace;">{{ $k['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="border border-gray-200 rounded p-2 sm:p-6 my-4 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full border-spacing-0 border-separate">
                <thead>
                    <tr>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-[rgba(102,123,106,0.1)] text-[#38433a] border-gray-200 whitespace-nowrap">Tipo</th>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-white text-gray-700 border-gray-200 whitespace-nowrap">Cliente</th>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-white text-gray-700 border-gray-200 whitespace-nowrap">Unidad</th>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-white text-gray-700 border-gray-200 whitespace-nowrap">Fecha</th>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-white text-gray-700 border-gray-200 whitespace-nowrap">Estado</th>
                        <th class="font-semibold text-left border-b py-2 px-3 bg-white text-gray-700 border-gray-200 whitespace-nowrap"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $p)
                        @php
                            $icon = match($p->type) {
                                'Entrega' => '🏠',
                                'Garantía' => '🔧',
                                'Escritura' => '📜',
                                default => '📋',
                            };
                            $accent = match($p->type) {
                                'Garantía' => '#9b6f1d',
                                'Escritura' => '#5a3d99',
                                default => '#2c6aa0',
                            };
                            $unitLabel = $p->unit_label ?? optional($p->unit)->name ?? '—';
                            $clientLabel = $p->client_name ?? (optional($p->reservation)->first_name . ' ' . optional($p->reservation)->last_name);
                            $clientLabel = trim($clientLabel) ?: '—';
                        @endphp
                        <tr class="hover:bg-[#f9f8f6]">
                            <td class="border-b border-gray-100 py-2.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $icon }}</span>
                                    <span class="text-sm font-semibold" style="color:{{ $accent }};">{{ $p->type }}</span>
                                </div>
                            </td>
                            <td class="border-b border-gray-100 py-2.5 px-3 text-sm text-[#625441]">{{ $clientLabel }}</td>
                            <td class="border-b border-gray-100 py-2.5 px-3 text-sm text-[#806f56]" style="font-family:monospace;">{{ $unitLabel }}</td>
                            <td class="border-b border-gray-100 py-2.5 px-3 text-xs text-[#9b9b9b]" style="font-family:monospace;">{{ $p->scheduled_date?->format('d M Y') ?? '—' }}</td>
                            <td class="border-b border-gray-100 py-2.5 px-3">
                                <form method="POST" action="{{ route('admin.crm.postventa.update', $p) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded bg-white">
                                        <option value="programada"  @selected($p->status === 'programada')>Programada</option>
                                        <option value="en_atencion" @selected($p->status === 'en_atencion')>En atención</option>
                                        <option value="en_tramite"  @selected($p->status === 'en_tramite')>En trámite</option>
                                        <option value="resuelta"    @selected($p->status === 'resuelta')>Resuelta</option>
                                    </select>
                                </form>
                            </td>
                            <td class="border-b border-gray-100 py-2.5 px-3">
                                <div class="flex gap-2">
                                    @if($p->reservation_id)
                                        <a href="/dashboard?reservation={{ $p->reservation_id }}" class="text-xs border border-[#cdd5cf] rounded px-2 py-1 text-[#667b6a] hover:bg-[rgba(102,123,106,0.05)]">Ver</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.crm.postventa.delete', $p) }}" onsubmit="return confirm('¿Eliminar este caso?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs border border-[#eebcbc] text-[#a83838] rounded px-2 py-1 hover:bg-[#fdf6f6]">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">No hay casos de postventa registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- New Aftersale Modal --}}
<div id="newAftersaleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full">
            <div class="bg-[#667b6a] px-6 py-4 flex justify-between items-center rounded-t-lg">
                <h3 class="text-xl font-bold text-white">Nuevo caso de postventa</h3>
                <button type="button" onclick="document.getElementById('newAftersaleModal').classList.add('hidden')" class="text-white hover:text-gray-200">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.crm.postventa.store') }}" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                        <option value="Entrega">Entrega</option>
                        <option value="Garantía">Garantía</option>
                        <option value="Escritura">Escritura</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                        <option value="programada">Programada</option>
                        <option value="en_atencion">En atención</option>
                        <option value="en_tramite">En trámite</option>
                        <option value="resuelta">Resuelta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <input name="client_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Etiqueta unidad</label>
                    <input name="unit_label" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" name="scheduled_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expediente</label>
                    <select name="reservation_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]">
                        <option value="">— Ninguno —</option>
                        @foreach($reservations as $r)
                            <option value="{{ $r->id }}">EXP-{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }} · {{ trim($r->first_name . ' ' . $r->last_name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#667b6a]"></textarea>
                </div>
                <div class="md:col-span-2 flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('newAftersaleModal').classList.add('hidden')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Cancelar</button>
                    <button type="submit" class="bg-[#667b6a] text-white px-4 py-2 rounded-md hover:bg-[#5a6d5e]">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
