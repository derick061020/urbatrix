@extends('layouts.broker')
@section('title', 'Mi Contrato — Portal Broker')
@section('page_title', 'Mi Contrato')
@section('page_breadcrumb', 'Portal Broker · Mi contrato')

@section('content')
@php $c = config('company'); @endphp
<div class="p-4 sm:p-6 lg:p-7 space-y-5" x-data>

    {{-- Hero --}}
    <div class="brk-card overflow-hidden" style="border-top:3px solid #5c7c68">
        <div class="p-6 grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-6 items-center">
            <div>
                <div class="text-[10px] uppercase tracking-[0.12em] text-ink-500 mb-3">Contrato de colaboración · #BR-{{ str_pad((string)($agent->id ?? 0), 4, '0', STR_PAD_LEFT) }}</div>
                <div class="font-display text-[24px] font-semibold text-ink-950 leading-tight mb-3">
                    {{ $agent->name ?? 'Broker' }} <span class="text-brand font-light mx-1">×</span> {{ $c['group'] }}
                </div>
                <div class="flex items-center gap-2">
                    <span class="brk-pill {{ ($agent->active ?? true) ? 'bg-ok-soft text-ok-dark' : 'bg-ink-100 text-ink-500' }}">
                        {{ ($agent->active ?? true) ? 'Contrato activo' : 'Inactivo' }}
                    </span>
                    @if($agent && $agent->license)<span class="text-[11px] text-ink-400">Licencia {{ $agent->license }}</span>@endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase tracking-wider text-ink-500 mb-2">Tu comisión</div>
                <div class="font-display text-[56px] font-bold text-brand leading-none">{{ rtrim(rtrim(number_format($rate,2),'0'),'.') }}%</div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    @php $totalDocs = $brokerDocs->count() + $contractDocs->count(); @endphp
    <div x-data="{ tab: '{{ $totalDocs ? 'documentos' : 'terminos' }}' }">
        <div class="flex items-center gap-1 border-b border-ink-200">
            @foreach(['terminos'=>'Términos','documentos'=>'Documentos','ejecutivo'=>'Tu ejecutivo'] as $key=>$label)
                <button @click="tab='{{ $key }}'"
                    :class="tab==='{{ $key }}' ? 'text-brand border-brand' : 'text-ink-500 border-transparent'"
                    class="px-4 py-2.5 text-[13px] font-semibold border-b-2 -mb-px transition-colors">
                    {{ $label }}
                    @if($key==='documentos' && $totalDocs)
                        <span class="brk-pill bg-warn-soft text-warn-dark ml-1">{{ $totalDocs }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Términos --}}
        <div x-show="tab==='terminos'" class="brk-card mt-4 divide-y divide-ink-100">
            @foreach($terms as [$label, $val])
                <div class="px-5 py-3.5 flex items-start justify-between gap-4">
                    <span class="text-[12px] font-semibold text-ink-500 uppercase tracking-wide">{{ $label }}</span>
                    <span class="text-[13px] text-ink-900 text-right">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Documentos --}}
        <div x-show="tab==='documentos'" class="mt-4 space-y-4" style="display:none">
            @if($brokerDocs->count())
                <div class="brk-card overflow-hidden">
                    <div class="px-5 py-3 border-b border-ink-100 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Tus contratos</div>
                    <div class="divide-y divide-ink-100">
                        @foreach($brokerDocs as $doc)
                            <div class="px-5 py-3.5 flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi {{ $doc->icon }}"></i></span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-ink-950 truncate">{{ $doc->title }}</div>
                                    <div class="text-[11px] text-ink-400">{{ $doc->category }} · {{ $doc->file_size ?: $doc->format }}</div>
                                </div>
                                <a href="{{ $doc->downloadUrl() }}" target="_blank" class="brk-btn brk-btn-ghost"><i class="pi pi-download"></i> Descargar</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="brk-card overflow-hidden">
                @if($brokerDocs->count())
                    <div class="px-5 py-3 border-b border-ink-100 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Documentos generales</div>
                @endif
                <div class="divide-y divide-ink-100">
                    @forelse($contractDocs as $doc)
                        <div class="px-5 py-3.5 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi {{ $doc->icon }}"></i></span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] font-semibold text-ink-950 truncate">{{ $doc->title }}</div>
                                <div class="text-[11px] text-ink-400">{{ $doc->category }} · {{ $doc->file_size ?: $doc->format }}</div>
                            </div>
                            @if($doc->downloadUrl())
                                <a href="{{ $doc->downloadUrl() }}" target="_blank" class="brk-btn brk-btn-ghost"><i class="pi pi-download"></i> Descargar</a>
                            @endif
                        </div>
                    @empty
                        @if(! $brokerDocs->count())
                            <div class="px-5 py-10 text-center text-[12px] text-ink-400">No hay documentos de contrato disponibles. Duna los publicará aquí cuando estén listos.</div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Ejecutivo --}}
        <div x-show="tab==='ejecutivo'" class="brk-card mt-4 p-6" style="display:none">
            <div class="flex items-center gap-4 mb-5">
                <span class="brk-avatar" style="width:52px;height:52px;font-size:18px">D</span>
                <div>
                    <div class="text-[15px] font-bold text-ink-950">{{ $c['signer_name'] }}</div>
                    <div class="text-[12px] text-ink-500">{{ $c['signer_title'] }} · {{ $c['group'] }}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
                <div class="flex items-center gap-2 text-ink-700"><i class="pi pi-envelope text-brand"></i> {{ $c['support_email'] }}</div>
                <div class="flex items-center gap-2 text-ink-700"><i class="pi pi-phone text-brand"></i> {{ $c['phone'] }}</div>
                <div class="flex items-center gap-2 text-ink-700"><i class="pi pi-globe text-brand"></i> {{ $c['website'] }}</div>
                <div class="flex items-center gap-2 text-ink-700"><i class="pi pi-map-marker text-brand"></i> {{ $c['location'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
