{{-- ====== Modal: Nueva reserva ====== --}}
<dialog id="modal-nueva-reserva" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ route('admin.crm.reservation.create') }}" class="w-[520px] bg-white rounded-2xl overflow-hidden" data-nueva-reserva>
        @csrf
        {{-- client_mode controla si se vincula un cliente existente o se crea uno nuevo con invitación --}}
        <input type="hidden" name="client_mode" value="new" data-client-mode>
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-id-card"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Nueva reserva') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">

            {{-- ── Selector de modo de cliente ── --}}
            <div class="grid grid-cols-2 gap-1 p-1 bg-ink-100 rounded-xl text-[12px] font-semibold">
                <button type="button" data-mode-btn="new"
                        class="py-2 rounded-lg transition-colors bg-white text-ink-900 shadow-sm">
                    <i class="pi pi-user-plus text-[11px] mr-1"></i> Cliente nuevo
                </button>
                <button type="button" data-mode-btn="existing"
                        class="py-2 rounded-lg transition-colors text-ink-500 hover:text-ink-800">
                    <i class="pi pi-users text-[11px] mr-1"></i> Cliente existente
                </button>
            </div>

            {{-- ── Modo: cliente existente ── --}}
            <div data-pane="existing" class="hidden">
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Buscar cliente') }}</label>
                <select name="user_id" data-existing-input class="crm-input pl-3 mt-1" disabled>
                    <option value="">{{ __('Seleccionar cliente…') }}</option>
                    @foreach($clients ?? [] as $c)
                        <option value="{{ $c->id }}">{{ $c->name ?: trim(($c->first_name ?? '').' '.($c->last_name ?? '')) }} — {{ $c->email }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-ink-500 mt-1.5">{{ __('La reserva se vinculará a la cuenta seleccionada.') }}</p>
            </div>

            {{-- ── Modo: cliente nuevo ── --}}
            <div data-pane="new" class="space-y-4">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre del cliente') }}</label>
                    <input type="text" name="cliente_nombre" data-new-input placeholder="{{ __('Nombre completo') }}" class="crm-input pl-3 mt-1">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Email del cliente') }}</label>
                    <input type="email" name="cliente_email" data-new-input placeholder="cliente@email.com" class="crm-input pl-3 mt-1">
                    <p class="text-[11px] text-ink-500 mt-1.5"><i class="pi pi-envelope text-[10px] mr-1"></i> {{ __('Se enviará una invitación para que active su cuenta y cree su contraseña.') }}</p>
                </div>
            </div>

            {{-- ── Datos de la reserva ── --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Unidad') }}</label>
                    <select name="unit_id" required class="crm-input pl-3 mt-1">
                        <option value="">Seleccionar…</option>
                        @foreach($units ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->custom_id ?? $u->name }} — ${{ number_format($u->price ?? 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha') }}</label>
                    <input type="date" name="fecha" required value="{{ now()->toDateString() }}" class="crm-input pl-3 mt-1">
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto recibido') }}</label>
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                    <input type="number" step="0.01" name="monto" required value="0.00" class="crm-input pl-7">
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary">{{ __('Crear reserva') }}</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    const form = document.querySelector('form[data-nueva-reserva]');
    if (!form || form.dataset.bound) return;
    form.dataset.bound = '1';

    const hidden    = form.querySelector('[data-client-mode]');
    const btns      = form.querySelectorAll('[data-mode-btn]');
    const panes     = { new: form.querySelector('[data-pane="new"]'), existing: form.querySelector('[data-pane="existing"]') };
    const newInputs = form.querySelectorAll('[data-new-input]');
    const existing  = form.querySelector('[data-existing-input]');

    function setMode(mode) {
        hidden.value = mode;

        // Paneles
        panes.new.classList.toggle('hidden', mode !== 'new');
        panes.existing.classList.toggle('hidden', mode !== 'existing');

        // Botones
        btns.forEach(b => {
            const on = b.dataset.modeBtn === mode;
            b.classList.toggle('bg-white', on);
            b.classList.toggle('text-ink-900', on);
            b.classList.toggle('shadow-sm', on);
            b.classList.toggle('text-ink-500', !on);
        });

        // Requeridos + enabled según el modo (evita enviar campos del modo oculto)
        newInputs.forEach(i => { i.required = (mode === 'new'); i.disabled = (mode !== 'new'); });
        existing.required = (mode === 'existing');
        existing.disabled = (mode !== 'existing');
    }

    btns.forEach(b => b.addEventListener('click', () => setMode(b.dataset.modeBtn)));
    setMode('new');
})();
</script>
