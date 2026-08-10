{{--
    Modal: Editar / corregir una cuota del plan de pagos.

    Sirve tanto para cuotas pendientes como para pagos YA realizados: el asesor
    puede ajustar el monto cobrado si alguien se equivocó al cargarlo. El estado
    (pagado / parcial / pendiente) no se elige, se deriva de los montos.
--}}
<dialog id="modal-editar-pago" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" id="ep-form" action="" class="w-[560px] max-w-[95vw] bg-white rounded-2xl overflow-hidden">
        @csrf

        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-pencil"></i></div>
            <div class="flex-1 min-w-0">
                <div class="text-[15px] font-bold text-ink-900">{{ __('Editar pago') }}</div>
                <div class="text-[11px] text-ink-500 truncate" id="ep-subtitle">—</div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Concepto') }}</label>
                <input type="text" name="label" id="ep-label" required maxlength="255" class="crm-input pl-3 mt-1">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto programado') }}</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                        <input type="number" step="0.01" min="0" name="amount" id="ep-amount" required class="crm-input pl-7" oninput="epRecalc()">
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto pagado') }}</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                        <input type="number" step="0.01" min="0" name="paid_amount" id="ep-paid" required class="crm-input pl-7" oninput="epRecalc()">
                    </div>
                    <button type="button" onclick="epFillFull()" class="text-[11px] text-brand font-semibold mt-1 hover:underline">{{ __('Igualar al monto programado') }}</button>
                </div>
            </div>

            {{-- Resumen en vivo: cómo quedará la cuota al guardar. --}}
            <div id="ep-summary" class="rounded-lg border border-ink-200 bg-ink-50/60 px-4 py-3 flex items-center justify-between gap-3">
                <div class="text-[12px] text-ink-600">{{ __('Quedará como') }} <b id="ep-state" class="text-ink-900">—</b></div>
                <div class="text-[12px] text-ink-600">{{ __('Saldo') }}: <b id="ep-remaining" class="text-ink-900">$0</b></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha de vencimiento') }}</label>
                    <input type="date" name="due_date" id="ep-due" required class="crm-input pl-3 mt-1">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha de pago') }}</label>
                    <input type="date" name="paid_at" id="ep-paid-at" class="crm-input pl-3 mt-1">
                    <span class="text-[10px] text-ink-400">{{ __('Se ignora si la cuota no tiene abonos.') }}</span>
                </div>
            </div>

            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Método de pago') }}</label>
                <select name="payment_method" id="ep-method" class="crm-input pl-3 mt-1">
                    <option value="">{{ __('Sin especificar') }}</option>
                    <option value="wire">{{ __('Wire Transfer') }}</option>
                    <option value="transfer">{{ __('Transferencia') }}</option>
                    <option value="ach">ACH</option>
                    <option value="card">{{ __('Tarjeta') }}</option>
                    <option value="cash">{{ __('Efectivo') }}</option>
                    <option value="check">{{ __('Cheque') }}</option>
                    <option value="other">{{ __('Otro') }}</option>
                </select>
            </div>

            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Notas') }} <span class="text-ink-400 font-normal">({{ __('Opcional') }})</span></label>
                <textarea name="notes" id="ep-notes" rows="2" maxlength="1000" placeholder="{{ __('Motivo de la corrección, referencia bancaria…') }}" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"></textarea>
            </div>

            <div class="text-[11px] text-ink-500 flex items-start gap-2">
                <i class="pi pi-info-circle text-[11px] mt-0.5"></i>
                <span>{{ __('La corrección queda registrada en la actividad del asesor. El estado de la cuota se recalcula con los montos que dejes acá.') }}</span>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> {{ __('Guardar cambios') }}</button>
        </div>
    </form>
</dialog>

<script>
    // Plantilla de la URL de guardado: el id real se inyecta al abrir el modal.
    window.epUrlTemplate = @json(route('admin.crm.payment.update', ['payment' => '__ID__']));

    function abrirModalEditarPago(p) {
        var form = document.getElementById('ep-form');
        form.action = window.epUrlTemplate.replace('__ID__', p.id);

        document.getElementById('ep-subtitle').textContent = p.subtitle || '';
        document.getElementById('ep-label').value    = p.label || '';
        document.getElementById('ep-amount').value   = p.amount || '0.00';
        document.getElementById('ep-paid').value     = p.paid_amount || '0.00';
        document.getElementById('ep-due').value      = p.due_date || '';
        document.getElementById('ep-paid-at').value  = p.paid_at || '';
        document.getElementById('ep-notes').value    = p.notes || '';

        var method = document.getElementById('ep-method');
        // Métodos heredados que no están en la lista: se agregan al vuelo para
        // no perder el valor original al guardar.
        if (p.payment_method && ! Array.from(method.options).some(function (o) { return o.value === p.payment_method; })) {
            method.add(new Option(p.payment_method, p.payment_method));
        }
        method.value = p.payment_method || '';

        epRecalc();
        document.getElementById('modal-editar-pago').showModal();
    }

    function epFillFull() {
        document.getElementById('ep-paid').value = document.getElementById('ep-amount').value || '0.00';
        epRecalc();
    }

    // Refleja en vivo el estado y el saldo resultantes, y bloquea el guardado
    // si el abono supera lo programado (el excedente se registra como saldo a
    // favor desde "Registrar pago", no inflando la cuota).
    function epRecalc() {
        var amount = parseFloat(document.getElementById('ep-amount').value || '0');
        var paid   = parseFloat(document.getElementById('ep-paid').value || '0');
        if (isNaN(amount)) amount = 0;
        if (isNaN(paid))   paid = 0;

        var box  = document.getElementById('ep-summary');
        var st   = document.getElementById('ep-state');
        var rem  = document.getElementById('ep-remaining');
        var save = document.querySelector('#ep-form button[type=submit]');
        var fmt  = function (n) { return '$' + n.toLocaleString('en-US', { maximumFractionDigits: 2 }); };

        box.className = 'rounded-lg border px-4 py-3 flex items-center justify-between gap-3 ';

        if (paid > amount + 0.001) {
            box.className += 'border-err/30 bg-err-soft/40';
            st.textContent  = @json(__('Monto pagado mayor al programado'));
            rem.textContent = fmt(0);
            save.disabled = true;
            save.classList.add('opacity-50', 'pointer-events-none');
            return;
        }

        save.disabled = false;
        save.classList.remove('opacity-50', 'pointer-events-none');

        if (amount > 0 && paid + 0.001 >= amount) {
            box.className += 'border-ok/30 bg-ok-soft/40';
            st.textContent = @json(__('Pagada'));
        } else if (paid > 0) {
            box.className += 'border-warn/30 bg-warn-soft/40';
            st.textContent = @json(__('Parcial'));
        } else {
            box.className += 'border-ink-200 bg-ink-50/60';
            st.textContent = @json(__('Pendiente'));
        }
        rem.textContent = fmt(Math.max(0, amount - paid));
    }
</script>
