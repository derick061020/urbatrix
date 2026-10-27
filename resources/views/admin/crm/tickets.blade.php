@extends('layouts.admin_crm')
@section('title', __('Tickets').' — CRM '.config('company.brand'))
@section('page_title', __('Tickets'))
@section('page_breadcrumb', __('Equipo · Tickets del salvavidas'))
@php $activeRoute = 'crm.tickets'; @endphp

@section('content')
@php
    $pillCls = ['abierto' => 'bg-warn-soft text-warn', 'en_curso' => 'bg-ink-100 text-ink-700', 'resuelto' => 'bg-ok-soft text-ok-dark'];
    $tabs = ['pendientes' => __('Pendientes')] + collect(\App\Models\SupportTicket::STATUSES)->map(fn ($l) => __($l))->all() + ['todos' => __('Todos')];
    $count = fn ($k) => match ($k) {
        'pendientes' => ($stats['abierto'] ?? 0) + ($stats['en_curso'] ?? 0),
        'todos'      => $stats->sum(),
        default      => $stats[$k] ?? 0,
    };
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="text-[13px] text-ink-500">{{ __('Lo que envían clientes, brokers, visitantes y el equipo desde el botón salvavidas de abajo a la izquierda: captura de la zona, comentario y adjuntos.') }}</div>

    <div class="flex items-center gap-2 flex-wrap">
        @foreach($tabs as $slug => $label)
            <a href="?estado={{ $slug }}" class="crm-tab {{ $filtro === $slug ? 'active' : '' }}">{{ $label }} <span class="text-ink-400">{{ $count($slug) }}</span></a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse($items as $t)
            <div class="crm-card overflow-hidden">
                <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2 flex-wrap">
                    <span class="text-[13px] font-bold text-ink-900">{{ $t->code }}</span>
                    <span class="crm-pill {{ $pillCls[$t->status] ?? 'bg-ink-100 text-ink-700' }}">{{ $t->statusLabel() }}</span>
                    <span class="text-[12px] text-ink-700 min-w-0 truncate">
                        <i class="pi pi-user text-[10px]"></i>
                        {{ $t->name ?: __('Visitante') }} · {{ $t->email }}
                        @if($t->user) <span class="text-ink-400">({{ $t->user->role }})</span> @else <span class="text-ink-400">({{ __('sin cuenta') }})</span> @endif
                    </span>
                    <span class="ml-auto text-[11px] text-ink-500">{{ $t->created_at->translatedFormat('j M Y, H:i') }}</span>
                </div>
                <div class="p-5 grid gap-4 lg:grid-cols-[1fr_320px]">
                    <div class="space-y-3 min-w-0">
                        <p class="text-[13px] text-ink-900 whitespace-pre-wrap break-words m-0">{{ $t->comment }}</p>
                        @if($t->page_url)
                            <div class="text-[11px] text-ink-500 break-all">
                                <i class="pi pi-link text-[10px]"></i>
                                <a href="{{ $t->page_url }}" target="_blank" rel="noopener" class="underline">{{ $t->page_title ?: $t->page_url }}</a>
                                @if($t->viewport) · {{ $t->viewport }} @endif
                            </div>
                        @endif
                        @if($t->user_agent)<div class="text-[10.5px] text-ink-400 break-all">{{ $t->user_agent }}</div>@endif
                        @if($t->screenshot || $t->attachments)
                            <div class="flex flex-wrap gap-2 items-start">
                                @if($t->screenshot)
                                    <a href="{{ route('salvavidas.file', [$t, 'captura']) }}" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-ink-100" title="{{ __('Captura') }}">
                                        <img src="{{ route('salvavidas.file', [$t, 'captura']) }}" alt="{{ __('Captura') }}" loading="lazy" class="block max-h-[220px] max-w-full sm:max-w-[420px] object-contain bg-ink-50">
                                    </a>
                                @endif
                                @foreach($t->attachments ?? [] as $i => $a)
                                    <a href="{{ route('salvavidas.file', [$t, $i]) }}" target="_blank" rel="noopener" class="crm-btn crm-btn-ghost max-w-full"><i class="pi pi-paperclip"></i> <span class="truncate max-w-[200px]">{{ $a['name'] }}</span></a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.crm.tickets.update', $t) }}" class="space-y-2 m-0">
                        @csrf
                        <label class="block text-[11px] font-semibold text-ink-500">{{ __('Estado') }}</label>
                        <select name="status" class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-[13px] text-ink-900 focus:outline-none focus:border-brand">
                            @foreach(\App\Models\SupportTicket::STATUSES as $k => $l)
                                <option value="{{ $k }}" @selected($t->status === $k)>{{ __($l) }}</option>
                            @endforeach
                        </select>
                        <label class="block text-[11px] font-semibold text-ink-500">{{ __('Respuesta (la ve en «Mis tickets»)') }}</label>
                        <textarea name="reply" rows="3" class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-[13px] text-ink-900 focus:outline-none focus:border-brand" placeholder="{{ __('Escribe una respuesta…') }}">{{ $t->reply }}</textarea>
                        @if($t->replied_at)<div class="text-[10.5px] text-ink-400">{{ __('Respondido por :n el :f', ['n' => $t->replier?->name ?? '—', 'f' => $t->replied_at->translatedFormat('j M Y, H:i')]) }}</div>@endif
                        <button type="submit" class="crm-btn crm-btn-primary w-full justify-center">{{ __('Guardar') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="crm-card px-5 py-10 text-center text-[12px] text-ink-500">{{ __('No hay tickets aquí.') }}</div>
        @endforelse
    </div>

    {{ $items->links() }}
</div>
@endsection
