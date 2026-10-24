@extends('layouts.admin_crm')
@section('title', __('Anuncios — CRM Landmass Bahía Mar'))
@section('page_title', __('Anuncios'))
@section('page_breadcrumb', __('Comunicación · Anuncios internos'))
@php $activeRoute = 'crm.anuncios'; @endphp

@section('content')
@php
    $audLabels = ['equipo' => __('Equipo interno'), 'compradores' => __('Compradores'), 'brokers' => __('Brokers')];
    $audColors = ['equipo' => 'ok', 'compradores' => 'info', 'brokers' => 'away'];
    $total = $pinned->count() + $recent->count();
@endphp
<div class="p-4 sm:p-6 lg:p-8 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-[14px] font-semibold text-ink-700">
            @if($archived)
                {{ trans_choice(':count archivado|:count archivados', $total, ['count' => $total]) }}
            @else
                {{ trans_choice(':count publicado|:count publicados', $total, ['count' => $total]) }} · {{ trans_choice(':count fijado|:count fijados', $pinned->count(), ['count' => $pinned->count()]) }}
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if($archived)
                <a href="{{ route('admin.crm.anuncios') }}" class="crm-btn crm-btn-ghost"><i class="pi pi-arrow-left text-[11px]"></i> {{ __('Publicados') }}</a>
            @elseif($archivedCount)
                <a href="{{ route('admin.crm.anuncios', ['archivados' => 1]) }}" class="crm-btn crm-btn-ghost">{{ __('Archivados') }} <span class="opacity-60">{{ $archivedCount }}</span></a>
            @endif
            <button type="button" class="crm-btn crm-btn-primary" onclick="anuncioAbrir()"><i class="pi pi-plus"></i> {{ __('Nuevo anuncio') }}</button>
        </div>
    </div>

    @if($total === 0)
        <div class="crm-card px-5 py-10 text-center">
            <i class="pi pi-megaphone text-[22px] text-ink-300"></i>
            <div class="text-[14px] font-semibold text-ink-800 mt-2">{{ $archived ? __('No hay anuncios archivados.') : __('Todavía no hay anuncios.') }}</div>
            @unless($archived)
                <div class="text-[12px] text-ink-500 mt-1">{{ __('Publica avisos para el equipo: cambios de precio, procesos nuevos, formaciones…') }}</div>
            @endunless
        </div>
    @endif

    @foreach([['pin', $pinned, __('Fijados')], ['rec', $recent, __('Recientes')]] as [$key, $list, $label])
        @if($list->isNotEmpty())
            <div class="text-[10px] uppercase font-semibold text-ink-400 tracking-wider px-2">
                @if($key === 'pin')<i class="pi pi-bookmark-fill text-err"></i>@endif {{ $label }}
            </div>
            @foreach($list as $a)
                <div class="crm-card p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-bold text-ink-900">@if($a->pinned)<span class="text-err">📌</span> @endif{{ $a->title }}</div>
                            <div class="text-[12px] text-ink-600 mt-1 whitespace-pre-line">{{ $a->body }}</div>
                            @if($a->audience)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($a->audience as $aud)
                                        <span class="crm-pill bg-{{ $audColors[$aud] ?? 'info' }}-soft text-{{ $audColors[$aud] ?? 'info' }}"><i class="pi pi-user text-[9px]"></i> {{ $audLabels[$aud] ?? $aud }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="text-[10px] text-ink-400 mt-3">{{ __('Por :autor', ['autor' => $a->author->name ?? __('Administración')]) }} · {{ $a->created_at->locale(app()->getLocale())->isoFormat('LL') }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('admin.crm.anuncios.archive', $a) }}" class="m-0">@csrf
                                <button type="submit" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ $a->archived_at ? __('Recuperar') : __('Archivar') }}</button>
                            </form>
                            <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" onclick="anuncioAbrir(@js(['id' => $a->id, 'title' => $a->title, 'body' => $a->body, 'audience' => $a->audience ?? [], 'pinned' => $a->pinned, 'action' => route('admin.crm.anuncios.update', $a)]))">{{ __('Editar') }}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach
</div>

<dialog id="modal-anuncio" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto w-[min(560px,calc(100vw-32px))]">
    <form id="anuncio-form" method="POST" action="{{ route('admin.crm.anuncios.store') }}" class="bg-white rounded-2xl overflow-hidden">@csrf
        <input type="hidden" name="_method" value="POST" id="anuncio-method">
        <div class="px-6 py-4 border-b border-ink-100 text-[15px] font-bold text-ink-900" id="anuncio-titulo">{{ __('Nuevo anuncio') }}</div>
        <div class="p-6 space-y-3">
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Título') }}</label><input type="text" name="title" required maxlength="160" class="crm-input pl-3 mt-1"></div>
            <div><label class="text-[12px] font-semibold text-ink-700">{{ __('Mensaje') }}</label><textarea name="body" required maxlength="4000" rows="5" class="crm-input pl-3 pt-2 mt-1 h-auto"></textarea></div>
            <div>
                <div class="text-[12px] font-semibold text-ink-700">{{ __('Dirigido a') }}</div>
                <div class="flex flex-wrap gap-3 mt-1.5">
                    @foreach($audLabels as $k => $l)
                        <label class="inline-flex items-center gap-2 text-[12px] text-ink-700"><input type="checkbox" name="audience[]" value="{{ $k }}" class="w-4 h-4 accent-brand"> {{ $l }}</label>
                    @endforeach
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-[12px] text-ink-700"><input type="checkbox" name="pinned" value="1" class="w-4 h-4 accent-brand"> {{ __('Fijar arriba') }}</label>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex justify-end gap-2">
            <button type="button" class="crm-btn crm-btn-ghost" onclick="document.getElementById('modal-anuncio').close()">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Guardar') }}</button>
        </div>
    </form>
</dialog>

<script>
function anuncioAbrir(a) {
    var f = document.getElementById('anuncio-form');
    f.reset();
    f.action = a ? a.action : @json(route('admin.crm.anuncios.store'));
    document.getElementById('anuncio-method').value = a ? 'PUT' : 'POST';
    document.getElementById('anuncio-titulo').textContent = a ? @json(__('Editar anuncio')) : @json(__('Nuevo anuncio'));
    if (a) {
        f.elements['title'].value = a.title;
        f.elements['body'].value = a.body;
        f.elements['pinned'].checked = !!a.pinned;
        f.querySelectorAll('input[name="audience[]"]').forEach(function (c) { c.checked = a.audience.indexOf(c.value) !== -1; });
    }
    document.getElementById('modal-anuncio').showModal();
}
</script>
@endsection
