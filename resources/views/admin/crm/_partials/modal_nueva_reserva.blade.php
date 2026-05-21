{{-- ====== Modal: Nueva reserva ====== --}}
<dialog id="modal-nueva-reserva" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.reservation.create') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-id-card"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">Nueva reserva</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Cliente</label>
                    <div class="relative mt-1">
                        <input type="text" name="cliente_nombre" required placeholder="Nombre completo" class="crm-input pl-3">
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Unidad</label>
                    <select name="unit_id" required class="crm-input pl-3 mt-1">
                        <option value="">Seleccionar…</option>
                        @foreach($units ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->custom_id ?? $u->name }} — ${{ number_format($u->price ?? 0) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Email del cliente</label>
                <input type="email" name="cliente_email" required placeholder="cliente@email.com" class="crm-input pl-3 mt-1">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Fecha</label>
                    <input type="date" name="fecha" required value="{{ now()->toDateString() }}" class="crm-input pl-3 mt-1">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Monto recibido</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                        <input type="number" step="0.01" name="monto" required value="0.00" class="crm-input pl-7">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary">Crear reserva</button>
        </div>
    </form>
</dialog>
