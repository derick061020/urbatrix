@extends('layouts.admin_crm')
@section('title', 'Mensajes — CRM Duna Makai')
@section('page_title', 'Mensajes')
@section('page_breadcrumb', 'Comunicación · Conversaciones')
@php $activeRoute = 'communication'; @endphp

@section('content')
@php
    $conversations = \App\Models\Reservation::with(['unit', 'latestMessage'])
        ->withCount(['messages as unread_count' => function ($q) {
            $q->where('sender_role', 'client')->whereNull('read_at');
        }])
        ->orderBy('updated_at', 'desc')->get();
    $active = request('r')
        ? $conversations->firstWhere('id', (int) request('r'))
        : $conversations->first();

    $threadMessages = collect();
    if ($active) {
        $threadMessages = $active->messages()->with('sender')->get();
        // Mark client-sent messages as read when admin opens this conversation
        $active->messages()
            ->where('sender_role', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    $avBg = ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3'];
@endphp
<div class="p-6">
    @if(session('success'))<div class="px-4 py-2 mb-3 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>@endif

    <div class="crm-card overflow-hidden h-[calc(100vh-152px)] grid grid-cols-12">

        {{-- Inbox list --}}
        <aside class="col-span-3 border-r border-ink-100 flex flex-col">
            <div class="p-3 border-b border-ink-100 flex items-center gap-2">
                <button class="crm-tab active">{{ __('Bandeja') }}</button>
                <span class="crm-pill bg-err-soft text-err">{{ $conversations->count() }}</span>
            </div>
            <div class="p-3 border-b border-ink-100">
                <div class="relative">
                    <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                    <input type="text" placeholder="Buscar…" class="crm-input pr-3">
                </div>
            </div>
            <div class="flex-1 overflow-y-auto">
                @forelse($conversations as $c)
                    @php
                        $init = strtoupper(substr($c->first_name ?? 'C',0,1).substr($c->last_name ?? 'M',0,1));
                        $bg = $avBg[$c->id % count($avBg)];
                        $lastMsg = $c->latestMessage;
                        $lastNote = $lastMsg?->body ?? 'Sin mensajes aún';
                        $lastWhen = $lastMsg?->created_at ?? $c->updated_at;
                        $isActive = $active && $c->id === $active->id;
                    @endphp
                    <a href="?r={{ $c->id }}" class="px-4 py-3 flex items-start gap-3 cursor-pointer border-b border-ink-100 {{ $isActive ? 'bg-brand-tint/40' : '' }} hover:bg-ink-50">
                        <div class="crm-avatar crm-avatar-sm" style="background:{{ $bg }}">{{ $init }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="text-[13px] font-semibold text-ink-900 truncate">{{ $c->first_name }} {{ $c->last_name }}</div>
                                <div class="text-[10px] text-ink-400">{{ optional($lastWhen)->diffForHumans() }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-ink-500 truncate flex-1">{{ \Illuminate\Support\Str::limit($lastNote, 50) }}</div>
                                @if($c->unread_count > 0)
                                    <span class="crm-pill bg-err text-white text-[10px]">{{ $c->unread_count }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-[12px] text-ink-500">{{ __('Sin conversaciones.') }}</div>
                @endforelse
            </div>
        </aside>

        {{-- Conversation --}}
        <section id="conversation-section" class="col-span-6 flex flex-col bg-ink-50">
            @if($active)
                @php
                    $aInit = strtoupper(substr($active->first_name ?? 'C',0,1).substr($active->last_name ?? 'M',0,1));
                    $aBg   = $avBg[$active->id % count($avBg)];
                @endphp
                <div class="px-5 py-3 border-b border-ink-100 bg-white flex items-center gap-3">
                    <div class="crm-avatar crm-avatar-sm" style="background:{{ $aBg }}">{{ $aInit }}</div>
                    <div class="flex-1">
                        <div class="text-[14px] font-semibold text-ink-900">{{ $active->first_name }} {{ $active->last_name }}</div>
                        <div class="text-[11px] text-ink-500">{{ $active->unit->name ?? '—' }} · {{ $active->email }}</div>
                    </div>
                    <a href="{{ route('admin.crm.expediente.detalle', $active->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">{{ __('Ver expediente') }}</a>
                    <button type="button" onclick="toggleRightRail()" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="{{ __('Expandir menú') }}">
                        <i class="pi pi-ellipsis-v"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-3" id="admin-comm-scroll">
                    @forelse($threadMessages as $msg)
                        @php
                            $isAdmin = $msg->sender_role === 'admin';
                            $senderName = $msg->sender?->name ?? ($isAdmin ? 'Asesor' : trim(($active->first_name ?? '').' '.($active->last_name ?? '')));
                        @endphp
                        @if($isAdmin)
                            <div class="flex justify-end">
                                <div class="max-w-[60%]">
                                    <div class="bg-brand text-white rounded-2xl rounded-br-md px-4 py-2.5 text-[12px] whitespace-pre-line">{{ $msg->body }}</div>
                                    <div class="text-[10px] text-ink-400 mt-1 text-right">{{ $senderName }} · {{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <div class="max-w-[60%]">
                                    <div class="bg-ink-200 text-ink-900 rounded-2xl rounded-bl-md px-4 py-2.5 text-[12px] whitespace-pre-line">{{ $msg->body }}</div>
                                    <div class="text-[10px] text-ink-400 mt-1">{{ $senderName }} · {{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-[12px] text-ink-500 mt-12">{{ __('Sin mensajes aún. Envía el primero abajo.') }}</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.crm.message.send') }}" class="p-3 border-t border-ink-100 bg-white flex items-center gap-2 m-0">@csrf
                    <input type="hidden" name="reservation_id" value="{{ $active->id }}">
                    <input type="hidden" name="channel" value="chat">
                    <input type="text" name="message" required maxlength="5000" autocomplete="off" placeholder="{{ __('Escribe un mensaje…') }}" class="flex-1 h-9 border border-ink-200 rounded-lg px-3 text-[13px] focus:outline-none focus:border-brand">
                    <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-send text-[11px]"></i> {{ __('Enviar') }}</button>
                </form>
            @else
                <div class="flex-1 flex items-center justify-center text-[12px] text-ink-500">{{ __('Selecciona una conversación') }}</div>
            @endif
        </section>

        {{-- Right rail --}}
        <aside id="right-rail" class="col-span-3 border-l border-ink-100 flex flex-col overflow-y-auto hidden">
            <div class="p-4 border-b border-ink-100">
                <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">{{ __('Enviar por canal') }}</div>
                @if($active)
                <form method="POST" action="{{ route('admin.crm.message.send') }}" class="space-y-2 m-0">@csrf
                    <input type="hidden" name="reservation_id" value="{{ $active->id }}">
                    <div class="flex gap-2">
                        <button type="submit" name="channel" value="email" class="crm-btn crm-btn-ghost text-[11px] py-1.5 flex-1 justify-center"><i class="pi pi-envelope"></i> {{ __('Email') }}</button>
                        <button type="submit" name="channel" value="whatsapp" class="crm-btn crm-btn-ghost text-[11px] py-1.5 flex-1 justify-center"><i class="pi pi-whatsapp"></i> WhatsApp</button>
                    </div>
                    <div>
                        <label class="text-[11px] text-ink-500">{{ __('Plantilla rápida') }}</label>
                        <select name="template" class="crm-input pl-3 mt-1 text-[12px]" onchange="this.form.message.value = this.options[this.selectedIndex].dataset.body || ''">
                            <option value="">{{ __('Seleccionar plantilla') }}</option>
                            <option data-body="Bienvenido a Makai Residences. Te confirmamos la reserva.">{{ __('Bienvenida') }}</option>
                            <option data-body="Recordatorio: tu cuota está próxima a vencer.">{{ __('Recordatorio de cuota') }}</option>
                            <option data-body="Tu documento KYC está pendiente. Por favor completa los datos.">{{ __('KYC pendiente') }}</option>
                        </select>
                    </div>
                    <textarea name="message" rows="3" required placeholder="Mensaje…" class="crm-input pl-3 pt-2 h-auto resize-none mt-2"></textarea>
                    <button type="submit" class="crm-btn crm-btn-primary w-full justify-center"><i class="pi pi-send text-[11px]"></i> {{ __('Enviar') }}</button>
                </form>
                @else
                    <div class="text-[12px] text-ink-500">{{ __('Selecciona una conversación.') }}</div>
                @endif
            </div>
            @if($active)
            <div class="p-4">
                <div class="text-[11px] uppercase font-semibold text-ink-400 mb-2">{{ __('Actividad reciente') }}</div>
                <div class="space-y-2 text-[12px] text-ink-700">
                    <div>• {{ $active->documents->count() }} documentos</div>
                    <div>• {{ $active->payments->count() ?? 0 }} pagos</div>
                    <div>• Registrado {{ optional($active->created_at)->format('Y-m-d') }}</div>
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>

@push('scripts')
<script>
    const adminCommScroll = document.getElementById('admin-comm-scroll');
    if (adminCommScroll) adminCommScroll.scrollTop = adminCommScroll.scrollHeight;

    function toggleRightRail() {
        const rightRail = document.getElementById('right-rail');
        const conversationSection = document.getElementById('conversation-section');
        
        if (rightRail && conversationSection) {
            rightRail.classList.toggle('hidden');
            
            if (rightRail.classList.contains('hidden')) {
                // Menú oculto: conversación ocupa más espacio
                conversationSection.classList.remove('col-span-6');
                conversationSection.classList.add('col-span-9');
            } else {
                // Menú visible: conversación ocupa espacio normal
                conversationSection.classList.remove('col-span-9');
                conversationSection.classList.add('col-span-6');
            }
        }
    }

    // Ajustar col-span al cargar la página si el menú está oculto por defecto
    document.addEventListener('DOMContentLoaded', function() {
        const rightRail = document.getElementById('right-rail');
        const conversationSection = document.getElementById('conversation-section');
        
        if (rightRail && conversationSection && rightRail.classList.contains('hidden')) {
            conversationSection.classList.remove('col-span-6');
            conversationSection.classList.add('col-span-9');
        }
    });
</script>
@endpush
@endsection
