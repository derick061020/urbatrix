{{-- ====== Modal: Registrar pago ====== --}}
<dialog id="modal-registrar-pago" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ $action ?? route('admin.crm.payment.create') }}" enctype="multipart/form-data" class="w-[824px] max-w-[95vw] bg-white rounded-2xl overflow-hidden">
        @csrf
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

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Monto recibido') }}</label>
                    <div class="flex gap-2 mt-1">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                            <input type="number" step="0.01" name="amount" required value="0.00" class="crm-input pl-7">
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
                    <input type="text" name="label" required value="Cuota 1/24 - Plan de Pagos" class="crm-input pl-3 mt-1">
                </div>
            </div>

            <div class="border-2 border-dashed border-ink-200 rounded-xl py-7 px-4 text-center cursor-pointer hover:border-brand transition-colors"
                 onclick="this.querySelector('input').click()">
                <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                <div class="text-[13px] font-semibold text-ink-700 mt-2">{{ __('Arrastra aquí o haz clic para seleccionar') }}</div>
                <div class="text-[11px] text-ink-500 mt-1">{{ __('PDF, JPG o PNG · máx. 4 MB') }}</div>
                <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); this.previousElementSibling.click()">{{ __('Buscar archivo') }}</button>
                <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.parentNode.querySelector('button[type=button]').textContent = this.files[0]?.name || 'Buscar archivo'">
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
