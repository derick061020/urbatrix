{{-- ====== Modal: Subir documento ====== --}}
<dialog id="modal-subir-documento" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ $action ?? route('admin.crm.document.upload') }}" enctype="multipart/form-data" class="w-[520px] bg-white rounded-2xl overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-file"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">Subir documento</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Tipo</label>
                    <select name="document_type" required class="crm-input pl-3 mt-1">
                        <option value="">Seleccionar…</option>
                        <option value="kyc">KYC</option>
                        <option value="reservation">Reserva</option>
                        <option value="payment_plan">Plan de pagos</option>
                        <option value="promise">Promesa de compraventa</option>
                        <option value="contract">Contrato</option>
                        <option value="passport">Identificación / Pasaporte</option>
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Cliente</label>
                    <select name="reservation_id" {{ isset($reservationId) ? 'readonly' : 'required' }} class="crm-input pl-3 mt-1">
                        @if(isset($reservationId))
                            <option value="{{ $reservationId }}" selected>Expediente actual</option>
                        @else
                            <option value="">Seleccionar…</option>
                            @foreach(\App\Models\Reservation::orderBy('first_name')->get() as $r)
                                <option value="{{ $r->id }}">{{ $r->first_name }} {{ $r->last_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Nombre del documento</label>
                <input type="text" name="title" required placeholder="KYC — Carlos Méndez" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <div class="border-2 border-dashed border-ink-200 rounded-xl py-8 px-4 text-center cursor-pointer hover:border-brand transition-colors"
                     onclick="this.querySelector('input').click()">
                    <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                    <div class="text-[13px] font-semibold text-ink-700 mt-2">Arrastra aquí o haz clic para seleccionar</div>
                    <div class="text-[11px] text-ink-500 mt-1">PDF, JPG o PNG · máx. 4 MB</div>
                    <button type="button" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); this.previousElementSibling.click()">Buscar archivo</button>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.parentNode.querySelector('button[type=button]').textContent = this.files[0]?.name || 'Buscar archivo'">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Fecha</label>
                    <input type="date" name="generated_at" class="crm-input pl-3 mt-1" value="{{ now()->toDateString() }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Estado inicial</label>
                    <select name="status" class="crm-input pl-3 mt-1">
                        <option value="pending">Pendiente revisión</option>
                        <option value="generated">Generado</option>
                        <option value="signed">Firmado</option>
                        <option value="approved">Aprobado</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-upload"></i> Subir documento</button>
        </div>
    </form>
</dialog>
