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
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Archivo') }}</label>
                <div class="mt-1.5" id="sd-morph"></div>
                <input type="file" id="sd-file" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
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
    // UploadMorph.chunked hace la subida y dibuja el progreso; acá sólo
    // guardamos la ruta que devuelve el último chunk.
    (function () {
        var input  = document.getElementById('sd-file');
        var pathEl = document.getElementById('sd-file-path');
        var fnEl   = document.getElementById('sd-file-name-input');
        var upEl   = document.getElementById('sd-uploading');
        if (!input || !window.UploadMorph) return;

        var morph = UploadMorph.mount(input, {
            into: document.getElementById('sd-morph'),
            emptyName: @json(__('Seleccionar archivo')),
            emptySub: @json(__('o arrástralo aquí · PDF, JPG o PNG · máx. 50 MB')),
            onSelect: function (file) { send(file); }
        });

        function send(file) {
            var csrf = document.querySelector('#modal-subir-documento input[name=_token]');
            pathEl.value = '';
            fnEl.value = '';
            upEl.value = '1';

            UploadMorph.chunked(morph, file, '{{ route('admin.crm.document.upload-chunk') }}',
                                {}, csrf ? csrf.value : '')
                .then(function (d) {
                    pathEl.value = d.path || '';
                    fnEl.value = d.name || file.name;
                    upEl.value = '';
                    morph.done(@json(__('Documento subido')), {
                        label: @json(__('Cambiar')),
                        onClick: function () { morph.clear(); }
                    });
                })
                .catch(function (e) {
                    pathEl.value = '';
                    fnEl.value = '';
                    upEl.value = '';
                    morph.fail(e.message || @json(__('No se pudo subir el documento.')),
                               function () { send(file); });
                });
        }
    })();

    // Evita enviar el formulario si falta el archivo o aún se está subiendo.
    function sdBeforeSubmit(form) {
        var upEl   = document.getElementById('sd-uploading');
        var pathEl = document.getElementById('sd-file-path');
        if (upEl && upEl.value === '1') {
            alert(@json(__('Esperá a que termine la subida del documento.')));
            return false;
        }
        if (!pathEl || !pathEl.value) {
            alert(@json(__('Seleccioná un archivo para subir.')));
            return false;
        }
        return true;
    }
</script>
