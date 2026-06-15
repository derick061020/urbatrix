@if ($paginator->hasPages())
    @php
        $linkBase = 'inline-flex items-center justify-center min-w-[34px] h-[34px] px-2 rounded-lg text-[13px] font-semibold transition-colors';
        $idle = 'text-ink-700 bg-white border border-ink-100 hover:bg-ink-50';
        $active = 'text-white bg-[#5c7c68] border border-[#5c7c68]';
        $disabled = 'text-ink-300 bg-white border border-ink-100 cursor-not-allowed';
    @endphp
    <nav class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" role="navigation" aria-label="{{ __('Paginación') }}">
        <p class="text-[12px] text-ink-500">
            Mostrando <span class="font-semibold text-ink-700">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-ink-700">{{ $paginator->lastItem() }}</span>
            de <span class="font-semibold text-ink-700">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $linkBase }} {{ $disabled }}" aria-disabled="true"><i class="pi pi-angle-left text-[12px]"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkBase }} {{ $idle }}" aria-label="{{ __('Anterior') }}"><i class="pi pi-angle-left text-[12px]"></i></a>
            @endif

            {{-- Números --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $linkBase }} {{ $disabled }}">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $linkBase }} {{ $active }}" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $linkBase }} {{ $idle }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkBase }} {{ $idle }}" aria-label="{{ __('Siguiente') }}"><i class="pi pi-angle-right text-[12px]"></i></a>
            @else
                <span class="{{ $linkBase }} {{ $disabled }}" aria-disabled="true"><i class="pi pi-angle-right text-[12px]"></i></span>
            @endif
        </div>
    </nav>
@endif
