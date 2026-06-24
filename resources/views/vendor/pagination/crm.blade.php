@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3 flex-wrap">
        {{-- Results summary --}}
        <p class="text-[12px] text-ink-500 leading-none">
            {!! __('Mostrando') !!}
            <span class="font-semibold text-ink-700">{{ $paginator->firstItem() }}</span>
            {!! __('a') !!}
            <span class="font-semibold text-ink-700">{{ $paginator->lastItem() }}</span>
            {!! __('de') !!}
            <span class="font-semibold text-ink-700">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-ink-100 text-ink-300 cursor-not-allowed">
                    <i class="pi pi-angle-left text-[12px]"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Anterior') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-ink-200 text-ink-600 hover:bg-ink-50 hover:text-ink-900 transition-colors">
                    <i class="pi pi-angle-left text-[12px]"></i>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-[13px] text-ink-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-lg bg-brand text-white text-[13px] font-semibold border border-brand">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded-lg border border-ink-200 text-ink-600 text-[13px] font-medium hover:bg-ink-50 hover:text-ink-900 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Siguiente') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-ink-200 text-ink-600 hover:bg-ink-50 hover:text-ink-900 transition-colors">
                    <i class="pi pi-angle-right text-[12px]"></i>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-ink-100 text-ink-300 cursor-not-allowed">
                    <i class="pi pi-angle-right text-[12px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
