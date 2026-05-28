@extends('layouts.client')
@section('title', 'Mensajes — MAKAI')
@section('page_title', 'Mensajes')
@section('page_breadcrumb', 'Mi portal · Mensajes')
@php $activeRoute = 'messages'; @endphp

@section('content')
@php
    $advisor = \App\Models\Agent::where('active', true)->orderBy('id')->first();
    $myName  = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? '')) ?: (Auth::user()->name ?? 'Yo');
    $myInit  = strtoupper(substr($myName, 0, 1) . (str_contains($myName, ' ') ? substr(explode(' ', $myName)[1] ?? '', 0, 1) : ''));
    $advInit = strtoupper(substr($advisor->name ?? 'AR', 0, 2));
    $messages = $messages ?? collect();

    // Group messages by date for "AYER / HOY / 2026-05-20" headers
    $today    = \Carbon\Carbon::today();
    $yesterday= $today->copy()->subDay();
@endphp

<div class="p-7 h-[calc(100vh-72px-56px)]">
    <div class="cli-card flex flex-col h-full overflow-hidden">

        {{-- Conversation header --}}
        <div class="px-5 py-3 border-b border-ink-100 flex items-center gap-3">
            <div class="relative">
                <div class="cli-avatar" style="background:#7cb8e7">{{ $advInit }}</div>
                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-ok border-2 border-white"></span>
            </div>
            <div class="flex-1">
                <div class="text-[15px] font-bold text-ink-950">{{ $advisor->name ?? 'Tu asesor' }}</div>
                <div class="text-[12px] text-ink-500">Tu asesor · Makai Residences</div>
            </div>
            @if($advisor?->phone)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $advisor->phone) }}" target="_blank" class="cli-btn cli-btn-ghost"><i class="pi pi-whatsapp text-[12px]"></i> WhatsApp</a>
                <a href="tel:{{ $advisor->phone }}" class="cli-btn cli-btn-ghost"><i class="pi pi-phone text-[12px]"></i> Llamar</a>
            @endif
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-5 py-6 space-y-4 bg-white" id="msg-scroll">
            @if($messages->isEmpty())
                <div class="text-center text-[13px] text-ink-500 mt-10">
                    <i class="pi pi-comments text-[24px] text-ink-300 mb-3 block"></i>
                    Aún no hay mensajes. Escribí abajo para iniciar la conversación con tu asesor.
                </div>
            @endif

            @php $lastDay = null; @endphp
            @foreach($messages as $m)
                @php
                    $created = $m->created_at;
                    $day = $created->isSameDay($today) ? 'HOY' : ($created->isSameDay($yesterday) ? 'AYER' : $created->locale('es')->isoFormat('D MMM YYYY'));
                    $showHeader = $day !== $lastDay;
                    $lastDay = $day;
                    $isClient = $m->sender_role === 'client';
                    $senderName = $m->sender?->name ?? ($isClient ? $myName : ($advisor->name ?? 'Asesor'));
                @endphp
                @if($showHeader)
                    <div class="text-center text-[10px] uppercase tracking-[0.18em] font-semibold text-ink-400 my-3">{{ $day }}</div>
                @endif

                @if($isClient)
                    <div class="flex items-end gap-2.5 justify-end">
                        <div class="text-right">
                            <div class="max-w-[520px] bg-brand text-white rounded-2xl rounded-br-md px-4 py-2.5 text-[13px] whitespace-pre-line">{{ $m->body }}</div>
                            <div class="text-[10px] text-ink-400 mt-1 pr-1">{{ $senderName }} · {{ $created->format('H:i') }}</div>
                        </div>
                        <div class="cli-avatar cli-avatar-sm" style="background:#cdd6df">{{ $myInit ?: 'YO' }}</div>
                    </div>
                @else
                    <div class="flex items-end gap-2.5 justify-start">
                        <div class="cli-avatar cli-avatar-sm" style="background:#7cb8e7">{{ $advInit }}</div>
                        <div>
                            <div class="max-w-[520px] bg-ink-200 text-ink-900 rounded-2xl rounded-bl-md px-4 py-2.5 text-[13px] whitespace-pre-line">{{ $m->body }}</div>
                            <div class="text-[10px] text-ink-400 mt-1 pl-1">{{ $senderName }} · {{ $created->format('H:i') }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Composer --}}
        @if($reservation)
            <form method="POST" action="{{ route('dashboard.messages.send') }}" class="px-4 py-3 border-t border-ink-100 flex items-center gap-2 m-0">
                @csrf
                <input type="text" name="body" required maxlength="5000" autocomplete="off"
                       placeholder="Escribe un mensaje…"
                       class="flex-1 h-9 border border-ink-200 rounded-lg px-3 text-[13px] focus:outline-none focus:border-brand">
                <button type="submit" class="cli-btn cli-btn-primary text-[12px] py-2 px-4">Enviar <i class="pi pi-send text-[11px]"></i></button>
            </form>
        @else
            <div class="px-4 py-3 border-t border-ink-100 text-center text-[12px] text-ink-500">
                Necesitás una reserva activa para chatear con tu asesor.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
  const s = document.getElementById('msg-scroll'); if (s) s.scrollTop = s.scrollHeight;
</script>
@endpush
@endsection
