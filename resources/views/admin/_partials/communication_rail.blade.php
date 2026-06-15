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
