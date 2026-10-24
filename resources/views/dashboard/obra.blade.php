@extends('layouts.client')
@section('title', __('Avance de Obra — SIEBE'))
@section('page_title', __('Avance de obra'))
@section('page_breadcrumb', __('Mi portal').' · '.__('Avance de obra'))
@php $activeRoute = 'progress'; @endphp

@section('content')
@php
    // Sólo datos reales. Sin informe publicado se enseñaba un avance de
    // ejemplo (52 %, cimentación terminada en junio de 2025, entrega Q4 2026)
    // y una ubicación heredada de otro proyecto.
    $project  = optional($report)->project
             ?? optional(optional($reservation)->unit)->project
             ?? \App\Models\Project::orderBy('id')->first();
    $delivery = optional($report)->estimated_delivery;
    $overall  = (int) (optional($report)->overall_progress ?? optional($project)->progress ?? 0);
    $projName = optional($project)->name ?? 'Siebe Residences';
    $location = optional($project)->location;
@endphp
<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    @if($delivery)
    <div class="px-4 py-3 rounded-xl bg-ink-100/60 border border-ink-200">
        <div class="text-[14px] font-bold text-ok-dark">{{ __('Entrega estimada :fecha', ['fecha' => $delivery]) }}</div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Progreso general --}}
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 bg-ink-50/60 border-b border-ink-100 text-[14px] font-bold text-ink-950">{{ $projName }} — {{ __('Progreso general') }}</div>
            <div class="p-5">
                <div class="flex items-center gap-5 mb-5">
                    <div class="relative w-28 h-28 shrink-0">
                        <svg viewBox="0 0 36 36" class="w-28 h-28 -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eaecf0" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1fc16b" stroke-width="3" stroke-dasharray="{{ $overall }}, 100" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[26px] font-bold text-ink-950">{{ $overall }}%</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[14px] font-semibold text-ink-950">{{ __('Avance global de obra') }}</div>
                        @if($location)<div class="text-[12px] text-ink-500">{{ $location }}</div>@endif
                        @if($delivery)<div class="text-[11px] text-ink-400 mt-1">{{ __('Entrega estimada :fecha', ['fecha' => $delivery]) }}</div>@endif
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $reportPhases = optional($report)->phases ?: [];
                        if ($reportPhases) {
                            $colorMap = ['done' => '#1fc16b', 'active' => '#fa7319', 'pending' => '#cacfd8'];
                            $stages = collect($reportPhases)->map(function ($p) use ($colorMap) {
                                $st = $p['status'] ?? 'pending';
                                return [
                                    $p['name'] ?? '—',
                                    (int) ($p['pct'] ?? 0),
                                    $colorMap[$st] ?? '#cacfd8',
                                    $p['date'] ?? '—',
                                    $st === 'done',
                                    ...($st === 'active' ? ['curso'] : []),
                                ];
                            })->all();
                        } else {
                            $stages = [];
                        }
                    @endphp
                    @if(empty($stages))
                        <div class="text-[12px] text-ink-500 py-2">{{ __('Todavía no se han publicado las fases de la obra. Te avisaremos por correo con el primer reporte.') }}</div>
                    @endif
                    @foreach($stages as $s)
                        <div>
                            <div class="flex items-center justify-between text-[13px] mb-1.5">
                                <div class="flex items-center gap-2.5">
                                    @if($s[4])
                                        <span class="w-5 h-5 rounded-full bg-ok flex items-center justify-center"><i class="pi pi-check text-white text-[9px]"></i></span>
                                    @elseif(isset($s[5]))
                                        <span class="w-5 h-5 rounded-full bg-warn flex items-center justify-center"><i class="pi pi-spin pi-spinner text-white text-[9px]"></i></span>
                                    @else
                                        <span class="w-5 h-5 rounded-full border border-ink-300 bg-white"></span>
                                    @endif
                                    <span class="font-semibold {{ $s[1] > 0 ? 'text-ink-950' : 'text-ink-400' }}">{{ $s[0] }}</span>
                                </div>
                                <div class="text-[11px] {{ $s[1] > 0 ? 'text-ink-500' : 'text-ink-400' }}">
                                    {{ $s[3] }} <span class="ml-2 font-bold" style="color:{{ $s[2] }}">{{ $s[1] }}%</span>
                                </div>
                            </div>
                            <div class="cli-progress"><span style="background:{{ $s[2] }};width:{{ $s[1] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reportes publicados --}}
        <div class="cli-card overflow-hidden">
            <div class="px-5 py-3 flex items-center justify-between bg-ink-50/60 border-b border-ink-100">
                <div class="text-[14px] font-bold text-ink-950">{{ __('Reportes publicados') }}</div>
                <span class="cli-pill bg-err-soft text-err">{{ $reports->count() }}</span>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse($reports as $r)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="text-[14px] font-bold text-ink-950">{{ $r->period }} — {{ $r->title }}</div>
                                <div class="text-[12px] text-ink-500 mt-1">{{ $r->description }}</div>
                                @if($r->photos)
                                    <div class="flex items-center gap-2 mt-2.5">
                                        @foreach(array_slice($r->photos, 0, 4) as $photo)
                                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="block w-14 h-14 rounded-lg overflow-hidden bg-ink-100 border border-ink-200">
                                                <img src="{{ asset('storage/'.$photo) }}" alt="" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                        @if(count($r->photos) > 4)
                                            <span class="text-[11px] text-ink-400">+{{ count($r->photos) - 4 }}</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[12px] font-bold" style="color:#1fc16b">{{ $r->overall_progress }}%</span>
                                    <span class="text-[11px] text-ink-400">{{ __('avance global') }}</span>
                                    <button type="button" onclick="openReportModal({{ $r->id }})" class="ml-auto inline-flex items-center gap-1.5 text-[12px] font-semibold text-brand hover:underline">
                                        <i class="pi pi-eye text-[11px]"></i> Ver reporte
                                    </button>
                                </div>
                            </div>
                            <div class="text-[11px] text-ink-400 whitespace-nowrap">{{ optional($r->published_at)->format('Y-m-d') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-[12px] text-ink-400">{{ __('Aún no hay reportes de avance publicados. Te notificaremos por correo cuando publiquemos el primero.') }}</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@include('_partials.construction_report_modal', ['reports' => $reports])
@endsection
