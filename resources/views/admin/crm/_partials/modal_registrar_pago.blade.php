{{-- ====== Modal: Registrar pago ====== --}}
<dialog id="modal-registrar-pago" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ $action ?? route('admin.crm.payment.create') }}" enctype="multipart/form-data" class="w-[824px] max-w-[95vw] bg-white rounded-2xl overflow-hidden" onsubmit="return rpBeforeSubmit(this);">
        @csrf
        {{-- El comprobante se sube por chunks (evita 413); aquí sólo viaja su ruta. --}}
        <input type="hidden" name="receipt_path" id="rp-receipt-path" value="">
        <input type="hidden" name="_uploading" id="rp-uploading" value="">

        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-credit-card"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Registrar pago') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="flex items-stretch">
        <div class="flex-1 min-w-0 p-6 space-y-4">
            @if(!isset($reservationId))
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Expediente / Cliente') }}</label>
                    <select name="reservation_id" required class="crm-input pl-3 mt-1">
                        <option value="">Seleccionar…</option>
                        @foreach(\App\Models\Reservation::with('unit')->orderBy('first_name')->get() as $r)
                            <option value="{{ $r->id }}">{{ $r->first_name }} {{ $r->last_name }} — {{ $r->unit->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
            @endif

            {{-- Cuota objetivo: el reparto del monto recibido arranca por ella. --}}
            <input type="hidden" name="target_payment_id" id="rp-target" value="">




            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto recibido') }}</label>
                    <div class="flex gap-2 mt-1">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                            <input type="number" step="0.01" name="amount" id="rp-amount" required value="0.00" class="crm-input pl-7">
                        </div>
                        <select name="currency" class="crm-input pl-3 w-24 mt-0">
                            <option value="USD">🇺🇸 USD</option>
                            <option value="DOP">🇩🇴 DOP</option>
                            <option value="EUR">🇪🇺 EUR</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha') }}</label>
                    <input type="date" name="paid_at" required value="{{ now()->toDateString() }}" class="crm-input pl-3 mt-1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Método de pago') }}</label>
                    <select name="payment_method" required class="crm-input pl-3 mt-1">
                        <option value="wire">{{ __('Wire Transfer') }}</option>
                        <option value="ach">ACH</option>
                        <option value="card">{{ __('Tarjeta') }}</option>
                        <option value="cash">{{ __('Efectivo') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Concepto') }}</label>
                    <input type="text" name="label" id="rp-label" required value="Cuota 1/24 - Plan de Pagos" class="crm-input pl-3 mt-1">
                </div>
            </div>

            <div class="border-2 border-dashed border-ink-200 rounded-xl py-7 px-4 text-center cursor-pointer hover:border-brand transition-colors"
                 onclick="this.querySelector('input[type=file]').click()">
                <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                <div class="text-[13px] font-semibold text-ink-700 mt-2">{{ __('Arrastra aquí o haz clic para seleccionar') }}</div>
                <div class="text-[11px] text-ink-500 mt-1">{{ __('PDF, JPG o PNG · máx. 50 MB') }}</div>
                <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); this.parentNode.querySelector('input[type=file]').click()"><span id="rp-receipt-name">{{ __('Buscar archivo') }}</span></button>
                {{-- Sin name: el archivo NO se postea entero; se sube por chunks. --}}
                <input type="file" id="rp-receipt-file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="rpReceiptSelected(this)">
            </div>

            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Notas') }} <span class="text-ink-400 font-normal">(Opcional)</span></label>
                <textarea name="notes" rows="3" maxlength="200" placeholder="{{ __('Referencia bancaria, número de comprobante') }}" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea>
            </div>
        </div>
        @include('_partials.bank_panel')
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Confirmar pago') }}</button>
        </div>
    </form>
</dialog>

<script>
    // Abre el modal de pago precargado con el saldo de una cuota concreta.
    // El reparto del monto recibido arrancará por esa cuota (target_payment_id).
    function abrirModalPago(paymentId, remaining, label) {
        var target = document.getElementById('rp-target');
        var amount = document.getElementById('rp-amount');
        var lbl    = document.getElementById('rp-label');
        if (target) target.value = paymentId || '';
        if (amount) amount.value = remaining || '0.00';
        if (lbl && label) lbl.value = label;
        // Limpia el comprobante de una apertura anterior.
        var rp = document.getElementById('rp-receipt-path');
        var rn = document.getElementById('rp-receipt-name');
        if (rp) rp.value = '';
        if (rn) rn.textContent = 'Buscar archivo';
        document.getElementById('modal-registrar-pago').showModal();
    }

    // Sube el comprobante en trozos de ~512 KB para evitar el 413 ("Too Large").
    async function rpReceiptSelected(input) {
        var file = input.files && input.files[0];
        if (!file) return;

        var nameEl   = document.getElementById('rp-receipt-name');
        var pathEl   = document.getElementById('rp-receipt-path');
        var upEl     = document.getElementById('rp-uploading');
        var csrf     = document.querySelector('#modal-registrar-pago input[name=_token]')?.value || '';
        var chunkSize = 512 * 1024;
        var total    = Math.ceil(file.size / chunkSize) || 1;
        var uploadId = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);

        if (pathEl) pathEl.value = '';
        if (upEl)   upEl.value = '1';

        try {
            for (var i = 0; i < total; i++) {
                var chunk = file.slice(i * chunkSize, (i + 1) * chunkSize);
                var fd = new FormData();
                fd.append('chunk', chunk);
                fd.append('upload_id', uploadId);
                fd.append('index', i);
                fd.append('total', total);
                fd.append('name', file.name);
                fd.append('_token', csrf);

                var res = await fetch('{{ route('admin.crm.payment.receipt') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                    credentials: 'same-origin',
                });
                if (res.status === 413) throw new Error('El servidor rechazó el envío por tamaño. Subí client_max_body_size en nginx.');
                var d = await res.json().catch(function () { return {}; });
                if (!res.ok || d.success === false) throw new Error(d.message || 'No se pudo subir el comprobante.');

                if (nameEl) nameEl.textContent = file.name + ' — ' + Math.round(((i + 1) / total) * 100) + '%';

                if (d.done) {
                    if (pathEl) pathEl.value = d.path || '';
                    if (nameEl) nameEl.textContent = file.name;
                }
            }
        } catch (e) {
            if (pathEl) pathEl.value = '';
            if (nameEl) nameEl.textContent = 'Buscar archivo';
            alert(e.message || 'No se pudo subir el comprobante.');
        } finally {
            if (upEl) upEl.value = '';
        }
    }

    // Evita enviar el pago mientras el comprobante aún se está subiendo.
    function rpBeforeSubmit(form) {
        var upEl = document.getElementById('rp-uploading');
        if (upEl && upEl.value === '1') {
            alert('Esperá a que termine la subida del comprobante.');
            return false;
        }
        return true;
    }
</script>
