{{-- ====== Modal: Subir documento ====== --}}
<dialog id="modal-subir-documento" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="{{ $action ?? route('admin.crm.document.upload') }}" enctype="multipart/form-data" class="w-[520px] bg-white rounded-2xl overflow-hidden" onsubmit="return sdBeforeSubmit(this)">
        @csrf
        {{-- El archivo se sube por chunks; aquí guardamos su ruta final y estado. --}}
        <input type="hidden" name="file_path" id="sd-file-path" value="">
        <input type="hidden" name="file_name" id="sd-file-name-input" value="">
        <input type="hidden" id="sd-uploading" value="">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-file"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Subir documento') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Tipo') }}</label>
                    <select name="document_type" required class="crm-input pl-3 mt-1">
                        <option value="">Seleccionar…</option>
                        <option value="kyc">KYC</option>
                        <option value="reservation">{{ __('Reserva') }}</option>
                        <option value="payment_plan">{{ __('Plan de pagos') }}</option>
                        <option value="promise">{{ __('Promesa de compraventa') }}</option>
                        <option value="contract">{{ __('Contrato') }}</option>
                        <option value="passport">{{ __('Identificación / Pasaporte') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Cliente') }}</label>
                    <select name="reservation_id" {{ isset($reservationId) ? 'readonly' : 'required' }} class="crm-input pl-3 mt-1">
                        @if(isset($reservationId))
                            <option value="{{ $reservationId }}" selected>{{ __('Expediente actual') }}</option>
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
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre del documento') }}</label>
                <input type="text" name="title" required placeholder="{{ __('KYC — Carlos Méndez') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <div class="border-2 border-dashed border-ink-200 rounded-xl py-8 px-4 text-center cursor-pointer hover:border-brand transition-colors"
                     onclick="this.querySelector('input').click()">
                    <i class="pi pi-cloud-upload text-ink-400 text-[22px]"></i>
                    <div class="text-[13px] font-semibold text-ink-700 mt-2">{{ __('Arrastra aquí o haz clic para seleccionar') }}</div>
                    <div class="text-[11px] text-ink-500 mt-1">{{ __('PDF, JPG o PNG · máx. 50 MB') }}</div>
                    <button type="button" id="sd-file-name" class="crm-btn crm-btn-ghost text-[11px] py-1 px-3 mt-3" onclick="event.stopPropagation(); this.previousElementSibling.click()">{{ __('Buscar archivo') }}</button>
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="sdFileSelected(this)">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha') }}</label>
                    <input type="date" name="generated_at" class="crm-input pl-3 mt-1" value="{{ now()->toDateString() }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Estado inicial') }}</label>
                    <select name="status" class="crm-input pl-3 mt-1">
                        <option value="pending">{{ __('Pendiente revisión') }}</option>
                        <option value="generated">{{ __('Generado') }}</option>
                        <option value="signed">{{ __('Firmado') }}</option>
                        <option value="approved">{{ __('Aprobado') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-upload"></i> {{ __('Subir documento') }}</button>
        </div>
    </form>
</dialog>

<script>
    // Sube el documento en trozos de ~512 KB para evitar el 413 ("Too Large").
    async function sdFileSelected(input) {
        var file = input.files && input.files[0];
        if (!file) return;

        var nameEl = document.getElementById('sd-file-name');
        var pathEl = document.getElementById('sd-file-path');
        var fnEl   = document.getElementById('sd-file-name-input');
        var upEl   = document.getElementById('sd-uploading');
        var csrf   = document.querySelector('#modal-subir-documento input[name=_token]')?.value || '';
        var chunkSize = 512 * 1024;
        var total     = Math.ceil(file.size / chunkSize) || 1;
        var uploadId  = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);

        if (pathEl) pathEl.value = '';
        if (fnEl)   fnEl.value = '';
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

                var res = await fetch('{{ route('admin.crm.document.upload-chunk') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                    credentials: 'same-origin',
                });
                if (res.status === 413) throw new Error('El servidor rechazó el envío por tamaño. Subí client_max_body_size en nginx.');
                var d = await res.json().catch(function () { return {}; });
                if (!res.ok || d.success === false) throw new Error(d.message || 'No se pudo subir el documento.');

                if (nameEl) nameEl.textContent = file.name + ' — ' + Math.round(((i + 1) / total) * 100) + '%';

                if (d.done) {
                    if (pathEl) pathEl.value = d.path || '';
                    if (fnEl)   fnEl.value = d.name || file.name;
                    if (nameEl) nameEl.textContent = file.name;
                }
            }
        } catch (e) {
            if (pathEl) pathEl.value = '';
            if (fnEl)   fnEl.value = '';
            if (nameEl) nameEl.textContent = 'Buscar archivo';
            alert(e.message || 'No se pudo subir el documento.');
        } finally {
            if (upEl) upEl.value = '';
        }
    }

    // Evita enviar el formulario si falta el archivo o aún se está subiendo.
    function sdBeforeSubmit(form) {
        var upEl   = document.getElementById('sd-uploading');
        var pathEl = document.getElementById('sd-file-path');
        if (upEl && upEl.value === '1') {
            alert('Esperá a que termine la subida del documento.');
            return false;
        }
        if (!pathEl || !pathEl.value) {
            alert('Seleccioná un archivo para subir.');
            return false;
        }
        return true;
    }
</script>
