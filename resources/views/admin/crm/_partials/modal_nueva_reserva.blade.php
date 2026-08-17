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
                    <i class="pi pi-user-plus text-[11px] mr-1"></i> {{ __('Cliente nuevo') }}
                </button>
                <button type="button" data-mode-btn="existing"
                        class="py-2 rounded-lg transition-colors text-ink-500 hover:text-ink-800">
                    <i class="pi pi-users text-[11px] mr-1"></i> Cliente existente
                </button>
            </div>

            {{-- ── Modo: cliente existente ── --}}
            <div data-pane="existing" class="hidden">
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Buscar cliente') }}</label>
                <div class="relative mt-1" data-combobox data-existing-input>
                    <input type="hidden" name="user_id" data-combobox-value disabled>
                    <input type="text" data-combobox-search autocomplete="off" placeholder="{{ __('Escribe nombre o email…') }}" class="crm-input pl-3" disabled>
                    <ul data-combobox-list class="hidden absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-auto bg-white border border-ink-200 rounded-lg shadow-lg">
                        @foreach($clients ?? [] as $c)
                            @php $clabel = ($c->name ?: trim(($c->first_name ?? '').' '.($c->last_name ?? ''))).' — '.$c->email; @endphp
                            <li data-value="{{ $c->id }}" data-label="{{ $clabel }}" class="px-3 py-2 text-[13px] text-ink-700 hover:bg-ink-50 cursor-pointer">{{ $clabel }}</li>
                        @endforeach
                    </ul>
                </div>
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

                    {{-- Correos adicionales del cliente (co-compradores): sólo dato de contacto. --}}
                    <div data-extra-emails class="space-y-2 mt-2"></div>
                    <button type="button" data-add-email class="inline-flex items-center gap-1 text-[12px] font-semibold text-brand hover:underline mt-2">
                        <i class="pi pi-plus text-[10px]"></i> {{ __('Agregar otro correo') }}
                    </button>

                    <p class="text-[11px] text-ink-500 mt-1.5"><i class="pi pi-envelope text-[10px] mr-1"></i> {{ __('Se enviará una invitación al correo principal para que active su cuenta y cree su contraseña.') }}</p>
                </div>
            </div>

            {{-- ── Datos de la reserva ── --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Unidad') }}</label>
                    <div class="relative mt-1" data-combobox>
                        <input type="hidden" name="unit_id" data-combobox-value>
                        <input type="text" data-combobox-search autocomplete="off" placeholder="{{ __('Buscar unidad…') }}" class="crm-input pl-3">
                        <ul data-combobox-list class="hidden absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-auto bg-white border border-ink-200 rounded-lg shadow-lg">
                            @foreach($units ?? [] as $u)
                                @php $ulabel = ($u->custom_id ?? $u->name).' — $'.number_format($u->price ?? 0); @endphp
                                <li data-value="{{ $u->id }}" data-label="{{ $ulabel }}" class="px-3 py-2 text-[13px] text-ink-700 hover:bg-ink-50 cursor-pointer">{{ $ulabel }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha del primer pago (reserva)') }}</label>
                    <input type="date" name="fecha" required value="{{ now()->toDateString() }}" class="crm-input pl-3 mt-1">
                    <span class="text-[10px] text-ink-400">{{ __('Define la fecha de la seña y el arranque del plan de pagos.') }}</span>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto recibido') }}</label>
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                    <input type="number" step="0.01" name="monto" required value="0.00" class="crm-input pl-7">
                </div>
            </div>

            {{-- ── Comprobante de la seña (opcional) ── --}}
            {{-- Se sube por chunks (evita 413); aquí sólo viaja su ruta. --}}
            <input type="hidden" name="receipt_path" id="nr-receipt-path" value="">
            <input type="hidden" id="nr-uploading" value="">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Comprobante de pago') }} <span class="text-ink-400 font-normal">({{ __('opcional') }})</span></label>
                <div class="mt-1.5" id="nr-morph"></div>
                <input type="file" id="nr-receipt-file" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
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
    // Se consulta en vivo: los correos adicionales se agregan de forma dinámica.
    const newInputs = () => form.querySelectorAll('[data-new-input]');
    const existing  = form.querySelector('[data-existing-input]');
    const existingValue  = existing.querySelector('[data-combobox-value]');
    const existingSearch = existing.querySelector('[data-combobox-search]');

    // ── Combobox buscable (cliente existente + unidad) ──
    form.querySelectorAll('[data-combobox]').forEach(box => {
        const value  = box.querySelector('[data-combobox-value]');
        const search = box.querySelector('[data-combobox-search]');
        const list   = box.querySelector('[data-combobox-list]');
        const items  = Array.from(list.querySelectorAll('li'));

        const open  = () => list.classList.remove('hidden');
        const close = () => list.classList.add('hidden');

        function filter() {
            const q = search.value.trim().toLowerCase();
            let shown = 0;
            items.forEach(li => {
                const match = li.dataset.label.toLowerCase().includes(q);
                li.classList.toggle('hidden', !match);
                if (match) shown++;
            });
            if (shown) open(); else close();
        }

        search.addEventListener('focus', () => { items.forEach(li => li.classList.remove('hidden')); open(); });
        search.addEventListener('input', () => { value.value = ''; filter(); });
        items.forEach(li => li.addEventListener('mousedown', e => {
            e.preventDefault();
            value.value = li.dataset.value;
            search.value = li.dataset.label;
            close();
        }));
        box.addEventListener('focusout', () => setTimeout(() => { if (!box.contains(document.activeElement)) close(); }, 0));
    });

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

        // Requeridos + enabled según el modo (evita enviar campos del modo oculto).
        // Los marcados como opcionales (correos adicionales) nunca son obligatorios.
        newInputs().forEach(i => {
            i.required = (mode === 'new') && !i.hasAttribute('data-optional');
            i.disabled = (mode !== 'new');
        });
        existingValue.disabled  = (mode !== 'existing');
        existingSearch.disabled = (mode !== 'existing');
    }

    const unitBox    = form.querySelector('[name="unit_id"]');
    const unitSearch = unitBox.closest('[data-combobox]').querySelector('[data-combobox-search]');

    // Validación: unidad obligatoria + cliente seleccionado en modo existente
    form.addEventListener('submit', e => {
        if (!unitBox.value) {
            e.preventDefault();
            unitSearch.focus();
            alert('{{ __('Selecciona una unidad del listado.') }}');
            return;
        }
        if (hidden.value === 'existing' && !existingValue.value) {
            e.preventDefault();
            existingSearch.focus();
            alert('{{ __('Selecciona un cliente del listado.') }}');
            return;
        }
        // No enviar mientras el comprobante aún se sube.
        if (document.getElementById('nr-uploading')?.value === '1') {
            e.preventDefault();
            alert('{{ __('Esperá a que termine la subida del comprobante.') }}');
        }
    });

    // ── Correos adicionales del cliente (sólo dato de contacto) ──
    const extraWrap = form.querySelector('[data-extra-emails]');
    form.querySelector('[data-add-email]').addEventListener('click', () => {
        if (extraWrap.querySelectorAll('input').length >= 10) return;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML =
            '<input type="email" name="cliente_emails_extra[]" data-new-input data-optional class="crm-input pl-3 flex-1" placeholder="otro@email.com">' +
            '<button type="button" class="w-9 h-9 shrink-0 rounded-lg border border-ink-200 text-ink-400 hover:text-err hover:border-err transition-colors" title="Quitar" aria-label="Quitar"><i class="pi pi-trash text-[12px]"></i></button>';
        row.querySelector('button').addEventListener('click', () => row.remove());
        extraWrap.appendChild(row);
        setMode(hidden.value);
        row.querySelector('input').focus();
    });

    btns.forEach(b => b.addEventListener('click', () => setMode(b.dataset.modeBtn)));
    setMode('new');
})();

// Sube el comprobante de la seña por chunks (~512 KB) para evitar el 413.
// UploadMorph.chunked sube y dibuja el progreso; acá sólo guardamos la ruta.
(function () {
    var input  = document.getElementById('nr-receipt-file');
    var pathEl = document.getElementById('nr-receipt-path');
    var upEl   = document.getElementById('nr-uploading');
    if (!input || !window.UploadMorph) return;

    var morph = UploadMorph.mount(input, {
        into: document.getElementById('nr-morph'),
        emptyName: @json(__('Adjuntar recibo firmado y sellado')),
        emptySub: @json(__('o arrástralo aquí · PDF, JPG o PNG · máx. 50 MB')),
        onSelect: function (file) { send(file); }
    });

    function send(file) {
        var csrf = document.querySelector('form[data-nueva-reserva] input[name=_token]')
                   || document.querySelector('meta[name=csrf-token]');
        var token = csrf ? (csrf.value || csrf.content || '') : '';
        pathEl.value = '';
        upEl.value = '1';

        UploadMorph.chunked(morph, file, '{{ route('admin.crm.payment.receipt') }}', {}, token)
            .then(function (d) {
                pathEl.value = d.path || '';
                upEl.value = '';
                morph.done(@json(__('Comprobante subido')), {
                    label: @json(__('Cambiar')),
                    onClick: function () { pathEl.value = ''; morph.clear(); }
                });
            })
            .catch(function (e) {
                pathEl.value = '';
                upEl.value = '';
                morph.fail(e.message || @json(__('No se pudo subir el comprobante.')),
                           function () { send(file); });
            });
    }
})();
</script>
