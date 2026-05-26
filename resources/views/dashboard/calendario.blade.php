@extends('layouts.client')
@section('title', 'Calendario — MAKAI')
@section('page_title', 'Mi Propiedad')
@section('page_breadcrumb', 'Mi Propiedad · Calendario')
@php $activeRoute = 'calendario'; @endphp

@section('content')
@php
    $events = $events ?? collect();

    $start = request()->date('start') ? \Carbon\Carbon::parse(request('start'))->startOfWeek() : \Carbon\Carbon::now()->startOfWeek();
    $end   = $start->copy()->addDays(4); // Mon-Fri shown (5 cols)
    $days  = [];
    for ($i = 0; $i < 5; $i++) {
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

    $today = \Carbon\Carbon::today();
    $prevWeek = $start->copy()->subWeek()->toDateString();
    $nextWeek = $start->copy()->addWeek()->toDateString();

    $topCards = $events->take(4); // upcoming featured
@endphp

<div class="p-4 sm:p-6 lg:p-7 space-y-5">

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <button class="cli-btn cli-btn-ghost text-[12px]" id="btn-today">Hoy</button>
        <div class="relative">
            <select class="cli-input pl-3 pr-9 text-[12px] !h-9 w-auto" id="select-range">
                <option value="week">Vista semanal</option>
                <option value="last7">Últimos 7 días</option>
                <option value="next7">Próximos 7 días</option>
                <option value="month">Este mes</option>
            </select>
        </div>
        <div class="cli-btn cli-btn-ghost text-[12px] inline-flex items-center gap-2">
            <i class="pi pi-calendar text-[11px]"></i>
            <span id="date-range-label">{{ $start->locale('es')->isoFormat('D MMM') }} - {{ $end->locale('es')->isoFormat('D MMM YYYY') }}</span>
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
                    <div class="px-4 pt-3 pb-2 flex items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-bold text-ink-950 truncate">{{ $e->title }}</div>
                            <div class="text-[11px] text-ink-500">{{ $e->start->format('H:i') }} - {{ $e->end->format('H:i') }}</div>
                        </div>
                        <button class="text-ink-400 hover:text-ink-700"><i class="pi pi-angle-down text-[12px]"></i></button>
                    </div>
                    <div class="px-4 py-2 flex items-center justify-between text-[11px]" style="background:{{ $bg }}40;">
                        <span class="flex items-center gap-1.5" style="color:{{ $statusColor }};">
                            <span class="dot" style="background:{{ $statusColor }}"></span> {{ $whenLabel }}
                        </span>
                        @if($e->start->isToday() && $e->type === 'video')
                            <a class="font-semibold text-ok-dark hover:underline">Unirse a la reunión</a>
                        @elseif($e->type === 'payment')
                            <a href="{{ route('dashboard.payments') }}" class="font-semibold text-warn-dark hover:underline">Ver pago</a>
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
            <a href="?start={{ $prevWeek }}" class="w-9 h-9 rounded-lg border border-ink-200 inline-flex items-center justify-center text-ink-500 hover:bg-ink-50"><i class="pi pi-angle-left text-[12px]"></i></a>
            <a href="?start={{ $nextWeek }}" class="w-9 h-9 rounded-lg border border-ink-200 inline-flex items-center justify-center text-ink-500 hover:bg-ink-50"><i class="pi pi-angle-right text-[12px]"></i></a>

            <div class="grid grid-cols-5 flex-1 ml-2 text-[11px] text-ink-500 uppercase font-semibold tracking-wider">
                @foreach($days as $d)
                    <div class="text-center px-2 py-1 {{ $d->isToday() ? 'text-ok-dark' : '' }}">{{ $d->locale('es')->isoFormat('DD ddd') }}</div>
                @endforeach
            </div>
        </div>

        {{-- Hour grid --}}
        <div class="grid grid-cols-[60px_repeat(5,1fr)] divide-x divide-ink-100">
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
                        <div class="absolute left-1 right-1 rounded-lg px-2 py-1.5 text-[11px] overflow-hidden shadow-xs cursor-pointer hover:shadow-card transition-shadow cal-event"
                             style="top:{{ $topRem }}rem; height:{{ $heightRem }}rem; background:{{ $bg }}; color:{{ $fg }};"
                             title="{{ $e->title }}"
                             data-type="{{ $e->type }}">
                            <div class="font-semibold truncate">{{ $e->title }}</div>
                            <div class="text-[10px] opacity-80">{{ $e->start->format('H:i') }} - {{ $e->end->format('H:i') }}</div>
                        </div>
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

    // ── "Hoy" button ──
    if (btnToday) {
        btnToday.addEventListener('click', function() {
            var params = new URLSearchParams(window.location.search);
            params.delete('start');
            window.location.search = params.toString();
        });
    }

    // ── Range selector ──
    if (selectRange) {
        selectRange.addEventListener('change', function() {
            var val = this.value;
            var params = new URLSearchParams(window.location.search);
            var today = new Date();
            var start = null;
            if (val === 'last7') {
                start = new Date(today);
                start.setDate(today.getDate() - 7);
                start = start.toISOString().split('T')[0];
            } else if (val === 'next7') {
                start = today.toISOString().split('T')[0];
            } else if (val === 'month') {
                start = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';
            } else {
                // 'week' — current week (Monday)
                var day = today.getDay();
                var diff = day === 0 ? 6 : day - 1;
                var monday = new Date(today);
                monday.setDate(today.getDate() - diff);
                start = monday.toISOString().split('T')[0];
            }
            params.set('start', start);
            window.location.search = params.toString();
        });
    }
})();
</script>
@endpush
@endsection