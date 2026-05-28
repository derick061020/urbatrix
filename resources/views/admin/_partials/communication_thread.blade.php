@php
    $avBg = $avBg ?? ['#7cb8e7','#f3b04f','#a5b0c5','#d6a3c6','#d56a6a','#cdd6df','#a6c5b3'];
@endphp
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
        <a href="{{ route('admin.crm.expediente.detalle', $active->id) }}" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3">Ir a expediente</a>
        <button type="button" data-comm-rail-toggle class="crm-btn crm-btn-ghost text-[11px] py-1 px-3" title="Mostrar panel de canales">
            <i class="pi pi-sliders-h"></i>
            <span data-comm-rail-toggle-label>Canales</span>
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
            <div class="text-center text-[12px] text-ink-500 mt-12">Sin mensajes aún. Envía el primero abajo.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.crm.message.send') }}" class="p-3 border-t border-ink-100 bg-white flex items-center gap-2 m-0">@csrf
        <input type="hidden" name="reservation_id" value="{{ $active->id }}">
        <input type="hidden" name="channel" value="chat">
        <input type="text" name="message" required maxlength="5000" autocomplete="off" placeholder="Escribe un mensaje…" class="flex-1 h-9 border border-ink-200 rounded-lg px-3 text-[13px] focus:outline-none focus:border-brand">
        <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-send text-[11px]"></i> Enviar</button>
    </form>
@else
    <div class="flex-1 flex items-center justify-center text-[12px] text-ink-500">Selecciona una conversación</div>
@endif
