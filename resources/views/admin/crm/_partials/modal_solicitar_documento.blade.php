{{-- ====== Modal: Solicitar documento al cliente ====== --}}
<dialog id="modal-solicitar-documento" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.document.request', $reservation->id) }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-inbox"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Solicitar documento al cliente') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-[12px] text-ink-500">{{ __('El cliente verá este documento como requerido en su panel y podrá subir el archivo desde ahí.') }}</p>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre del documento') }}</label>
                <input type="text" name="title" required placeholder="{{ __('Comprobante de ingresos') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Descripción / instrucciones') }} <span class="text-ink-400 font-normal">(opcional)</span></label>
                <input type="text" name="description" placeholder="{{ __('Últimos 3 estados de cuenta o carta laboral') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha límite') }} <span class="text-ink-400 font-normal">(opcional)</span></label>
                <input type="date" name="due_date" class="crm-input pl-3 mt-1">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-send"></i> {{ __('Solicitar al cliente') }}</button>
        </div>
    </form>
</dialog>
