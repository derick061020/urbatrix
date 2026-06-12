@extends('layouts.client')
@section('title', 'Calendario — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Calendario')
@php $activeRoute = 'calendario'; @endphp

@section('content')
@php
    $events = $events ?? collect();

    // ── View / range mode ──────────────────────────────────────────
    // week  → full week, Mon–Sun (7 días)
    // work  → semana laboral, Lun–Vie (5 días)
    // next7 → próximos 7 días desde hoy
    // last7 → últimos 7 días hasta hoy
    $range = in_array(request('range'), ['week', 'work', 'next7', 'last7']) ? request('range') : 'week';

    $today = \Carbon\Carbon::today();

    switch ($range) {
        case 'work':
            $start    = request('start') ? \Carbon\Carbon::parse(request('start'))->startOfWeek() : \Carbon\Carbon::now()->startOfWeek();
            $dayCount = 5;
            break;
        case 'next7':
            $start    = request('start') ? \Carbon\Carbon::parse(request('start'))->startOfDay() : $today->copy();
            $dayCount = 7;
            break;
        case 'last7':
            $start    = request('start') ? \Carbon\Carbon::parse(request('start'))->startOfDay() : $today->copy()->subDays(6);
            $dayCount = 7;
            break;
        case 'week':
        default:
            $start    = request('start') ? \Carbon\Carbon::parse(request('start'))->startOfWeek() : \Carbon\Carbon::now()->startOfWeek();
            $dayCount = 7;
            break;
    }

    $end  = $start->copy()->addDays($dayCount - 1);
    $days = [];
    for ($i = 0; $i < $dayCount; $i++) {
        $days[] = $start->copy()->addDays($i);
    }
    $hours = range(9, 14); // 9 AM to 2 PM

    // Group events by date Y-m-d
    $byDay = [];
    foreach ($events as $e) {
        $d = $e->start->format('Y-m-d');
        $byDay[$d] = $byDay[$d] ?? [];
        $byDay[$d][] = $e;
    }

    $typeColors = [
        'task'    => ['#dbeafe', '#1e40af'],
        'payment' => ['#fff3eb', '#b75310'],
        'meeting' => ['#e3f7ec', '#1daf61'],
        'video'   => ['#e3f7ec', '#1daf61'],
    ];

    $prevWeek = $start->copy()->subDays($dayCount)->toDateString();
    $nextWeek = $start->copy()->addDays($dayCount)->toDateString();

    $topCards = $events->take(4); // upcoming featured
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        {{-- Date range as a title (not a button) --}}
        <div class="flex items-baseline gap-2 mr-1">
            <h2 class="text-[18px] font-bold text-ink-950 leading-none" id="date-range-label">
                @if($start->isSameMonth($end))
                    {{ $start->locale('es')->isoFormat('D') }} – {{ $end->locale('es')->isoFormat('D MMMM YYYY') }}
                @else
                    {{ $start->locale('es')->isoFormat('D MMM') }} – {{ $end->locale('es')->isoFormat('D MMM YYYY') }}
                @endif
            </h2>
        </div>

        <button class="cli-btn cli-btn-ghost text-[12px]" id="btn-today">Hoy</button>
        <div class="relative">
            <select class="cli-input pl-3 pr-9 text-[12px] !h-9 w-auto" id="select-range">
                <option value="week"  @selected($range === 'week')>Semana completa</option>
                <option value="work"  @selected($range === 'work')>Semana laboral</option>
                <option value="last7" @selected($range === 'last7')>Últimos 7 días</option>
                <option value="next7" @selected($range === 'next7')>Próximos 7 días</option>
            </select>
        </div>

        <div class="ml-auto flex items-center gap-3">
            <div class="relative w-64">
                <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400 text-[12px]"></i>
                <input class="cli-input pr-3" id="search-events" placeholder="Buscar eventos…">
            </div>
            <div class="relative">
                <button class="cli-btn cli-btn-ghost text-[12px]" id="btn-filter"><i class="pi pi-sliders-h text-[11px]"></i> Filtrar</button>
                <div id="filter-dropdown" class="absolute right-0 top-full mt-1 z-20 hidden" style="min-width:200px;">
                    <div class="cli-card p-3 space-y-2">
                        <div class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider mb-1">Tipo de evento</div>
                        <label class="flex items-center gap-2 text-[12px] cursor-pointer"><input type="checkbox" class="filter-type" value="task" checked> <span>Tareas</span></label>
                        <label class="flex items-center gap-2 text-[12px] cursor-pointer"><input type="checkbox" class="filter-type" value="payment" checked> <span>Pagos</span></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reunión recién agendada desde la home --}}
    @if(!empty($highlightMeeting))
        <div class="cli-card overflow-hidden border border-ok/30" id="new-meeting-banner" style="background:#e3f7ec40;">
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-ok/15 text-ok-dark flex items-center justify-center shrink-0">
                    <i class="pi pi-video text-[18px]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-bold text-ink-950">¡Videollamada agendada!</div>
                    <div class="text-[12px] text-ink-500">
                        {{ $highlightMeeting->start->locale('es')->isoFormat('ddd D [de] MMMM, HH:mm') }} hs · Este es tu link de Google Meet
                    </div>
                    @if($highlightMeeting->meet_link)
                        <div class="text-[11px] text-ok-dark truncate mt-0.5">{{ $highlightMeeting->meet_link }}</div>
                    @endif
                </div>
                @if($highlightMeeting->meet_link)
                    <a href="{{ $highlightMeeting->meet_link }}" target="_blank" rel="noopener"
                       class="cli-btn cli-btn-primary text-[12px] shrink-0 whitespace-nowrap">
                        <i class="pi pi-external-link text-[11px]"></i> Abrir Google Meet
                    </a>
                @else
                    <span class="text-[12px] text-ink-500 shrink-0">El link llegará a tu email</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Top cards (upcoming) --}}
    @if($topCards->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3" id="top-cards-grid">
            @foreach($topCards as $e)
                @php
                    [$bg, $fg] = $typeColors[$e->type] ?? ['#f2f5f8', '#525866'];
                    $whenLabel = $e->start->isToday() ? 'Hoy' : $e->start->locale('es')->isoFormat('D MMM YYYY');
                    $statusColor = match(true) {
                        $e->start->isToday()    => '#1fc16b',
                        $e->start->isPast()     => '#fb3748',
                        default                 => '#fa7319',
                    };
                @endphp
                <div class="cli-card overflow-hidden top-card" data-type="{{ $e->type }}">
                    <div class="px-4 pt-3 pb-2">
                        <div class="text-[13px] font-bold text-ink-950 truncate">{{ $e->title }}</div>
                        <div class="text-[11px] text-ink-500">{{ $e->start->format('H:i') }} - {{ $e->end->format('H:i') }}</div>
                    </div>
                    <div class="px-4 py-2 flex items-center justify-between text-[11px]" style="background:{{ $bg }}40;">
                        <span class="flex items-center gap-1.5" style="color:{{ $statusColor }};">
                            <span class="dot" style="background:{{ $statusColor }}"></span> {{ $whenLabel }}
                        </span>
                        @if($e->start->isToday() && $e->type === 'video')
                            <a class="font-semibold text-ok-dark hover:underline">Unirse a la reunión</a>
                        @elseif($e->type === 'payment')
                            <a href="{{ route('dashboard.payments') }}?pay=1" class="font-semibold text-warn-dark hover:underline">Pagar</a>
                        @else
                            <span class="text-ink-500">{{ $e->meta ?? '' }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Week grid --}}
    <div class="cli-card overflow-hidden">
        <div class="px-3 py-2 flex items-center gap-2 border-b border-ink-100 bg-white">
            <a href="?range={{ $range }}&start={{ $prevWeek }}" aria-label="Anterior" class="shrink-0 w-9 h-9 rounded-lg border border-ink-200 inline-flex items-center justify-center text-ink-500 hover:bg-ink-50"><i class="pi pi-angle-left text-[12px]"></i></a>
            <a href="?range={{ $range }}&start={{ $nextWeek }}" aria-label="Siguiente" class="shrink-0 w-9 h-9 rounded-lg border border-ink-200 inline-flex items-center justify-center text-ink-500 hover:bg-ink-50"><i class="pi pi-angle-right text-[12px]"></i></a>

            {{-- Spacer matching the time column --}}
            <div class="shrink-0" style="width:60px;"></div>

            <div class="grid flex-1 text-[11px] text-ink-500 uppercase font-semibold tracking-wider" style="grid-template-columns: repeat({{ $dayCount }}, minmax(0,1fr));">
                @foreach($days as $d)
                    <div class="text-center px-1 py-1 {{ $d->isToday() ? 'text-ok-dark' : '' }}">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($d->isToday())
                                <span class="dot" style="background:#1fc16b"></span>
                            @endif
                            <span>{{ $d->locale('es')->isoFormat('ddd DD') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Hour grid --}}
        <div class="grid divide-x divide-ink-100" style="grid-template-columns: 60px repeat({{ $dayCount }}, minmax(0,1fr));">
            {{-- Time column --}}
            <div class="bg-ink-50/40">
                @foreach($hours as $h)
                    <div class="h-24 border-b border-ink-100 text-[11px] text-ink-500 px-2 pt-1 font-semibold">{{ $h }} {{ $h >= 12 ? 'PM' : 'AM' }}</div>
                @endforeach
            </div>

            @foreach($days as $d)
                @php
                    $dayKey = $d->format('Y-m-d');
                    $dayEvents = $byDay[$dayKey] ?? [];
                    $isWeekendStripe = $d->isWeekend();
                @endphp
                <div class="relative {{ $isWeekendStripe ? 'bg-stripes' : '' }}" style="height:{{ count($hours) * 6 }}rem;">
                    {{-- Hour cell guides --}}
                    @foreach($hours as $i => $h)
                        <div class="absolute left-0 right-0 border-b border-ink-100" style="top:{{ $i * 6 }}rem; height:6rem;"></div>
                    @endforeach

                    {{-- Events --}}
                    @foreach($dayEvents as $e)
                        @php
                            $hStart = (int) $e->start->format('G') + (int) $e->start->format('i') / 60;
                            $hEnd   = (int) $e->end->format('G')   + (int) $e->end->format('i')   / 60;
                            if ($hEnd <= $hStart) $hEnd = $hStart + 0.5;
                            $topRem    = max(0, ($hStart - $hours[0]) * 6);
                            $heightRem = max(2.5, ($hEnd - $hStart) * 6);
                            [$bg, $fg] = $typeColors[$e->type] ?? ['#f2f5f8', '#525866'];
                        @endphp
                        @php $isPayEvent = $e->type === 'payment'; @endphp
                        <{{ $isPayEvent ? 'a' : 'div' }}
                             @if($isPayEvent) href="{{ route('dashboard.payments') }}?pay=1" @endif
                             class="absolute left-1 right-1 rounded-lg px-2 py-1.5 text-[11px] overflow-hidden shadow-xs cursor-pointer hover:shadow-card transition-shadow cal-event {{ $isPayEvent ? 'block no-underline' : '' }}"
                             style="top:{{ $topRem }}rem; height:{{ $heightRem }}rem; background:{{ $bg }}; color:{{ $fg }};"
                             title="{{ $isPayEvent ? $e->title.' · Click para pagar' : $e->title }}"
                             data-type="{{ $e->type }}">
                            <div class="font-semibold truncate">{{ $e->title }}</div>
                            <div class="text-[10px] opacity-80">{{ $e->start->format('H:i') }} - {{ $e->end->format('H:i') }}@if($isPayEvent) · Pagar @endif</div>
                        </{{ $isPayEvent ? 'a' : 'div' }}>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    @if($events->isEmpty())
        <div class="cli-card p-10 text-center" id="empty-state">
            <div class="w-14 h-14 rounded-full bg-ink-100 text-ink-400 flex items-center justify-center mx-auto"><i class="pi pi-calendar text-[22px]"></i></div>
            <div class="mt-3 text-[15px] font-bold text-ink-950">Tu calendario está vacío</div>
            <p class="text-[12px] text-ink-500 mt-1 max-w-md mx-auto">Cuando tu asesor agende una videollamada o cuando se cargue una nueva cuota, vas a verla acá.</p>
        </div>
    @endif
</div>

@push('styles')
<style>
.bg-stripes {
    background-image: repeating-linear-gradient(45deg, rgba(202,207,216,.15) 0 8px, transparent 8px 16px);
}
#filter-dropdown {
    box-shadow: 0 4px 16px -4px rgba(10,13,20,.12), 0 1px 3px rgba(10,13,20,.06);
}
.cal-event.hidden,
.top-card.hidden {
    display: none !important;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    var filterDropdown = document.getElementById('filter-dropdown');
    var btnFilter = document.getElementById('btn-filter');
    var filterInputs = document.querySelectorAll('.filter-type');
    var searchInput = document.getElementById('search-events');
    var btnToday = document.getElementById('btn-today');
    var selectRange = document.getElementById('select-range');

    // ── Filter dropdown toggle ──
    if (btnFilter && filterDropdown) {
        btnFilter.addEventListener('click', function(e) {
            e.stopPropagation();
            filterDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function() {
            filterDropdown.classList.add('hidden');
        });
        filterDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // ── Apply all filters (type + search) ──
    function applyFilters() {
        var checked = Array.from(filterInputs).filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
        var q = searchInput ? searchInput.value.toLowerCase().trim() : '';

        // Filter grid events (.cal-event)
        document.querySelectorAll('.cal-event').forEach(function(el) {
            var type = el.getAttribute('data-type') || '';
            var title = (el.querySelector('.font-semibold')?.textContent || '').toLowerCase();
            var typeMatch = checked.includes(type);
            var searchMatch = !q || title.includes(q);
            el.classList.toggle('hidden', !(typeMatch && searchMatch));
        });

        // Filter top cards (.top-card)
        document.querySelectorAll('.top-card').forEach(function(el) {
            var type = el.getAttribute('data-type') || '';
            var title = (el.querySelector('.font-bold')?.textContent || '').toLowerCase();
            var typeMatch = checked.includes(type);
            var searchMatch = !q || title.includes(q);
            el.classList.toggle('hidden', !(typeMatch && searchMatch));
        });

        // Toggle empty state
        var visibleEvents = document.querySelectorAll('.cal-event:not(.hidden)').length;
        var visibleCards = document.querySelectorAll('.top-card:not(.hidden)').length;
        var emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.style.display = (visibleEvents === 0 && visibleCards === 0) ? '' : 'none';
        }
    }

    // ── Type checkboxes ──
    filterInputs.forEach(function(cb) {
        cb.addEventListener('change', applyFilters);
    });

    // ── Search input ──
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    // ── "Hoy" button (keeps current range, resets to current period) ──
    if (btnToday) {
        btnToday.addEventListener('click', function() {
            var params = new URLSearchParams(window.location.search);
            params.delete('start');
            window.location.search = params.toString();
        });
    }

    // ── Range selector — the server resolves the period from ?range ──
    if (selectRange) {
        selectRange.addEventListener('change', function() {
            var params = new URLSearchParams(window.location.search);
            params.set('range', this.value);
            params.delete('start'); // switch to the current period for the chosen view
            window.location.search = params.toString();
        });
    }
})();
</script>
@endpush
@endsection