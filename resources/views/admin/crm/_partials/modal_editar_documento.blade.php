{{-- ====== Modal: Editar documento ======
     Un solo diálogo para toda la tabla: openEditDocument({...}) lo rellena con
     los datos de la fila y apunta el form a la ruta de ese documento. El archivo
     es opcional: si no se elige otro, se conserva el actual. --}}
<dialog id="modal-editar-documento" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="POST" action="" enctype="multipart/form-data" class="w-[520px] max-w-[calc(100vw-2rem)] bg-white rounded-2xl overflow-hidden" onsubmit="return edBeforeSubmit(this)">
        @csrf
        <input type="hidden" name="file_path" id="ed-file-path" value="">
        <input type="hidden" name="file_name" id="ed-file-name-input" value="">
        <input type="hidden" id="ed-uploading" value="">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-pencil"></i></div>
            <div class="text-[15px] font-bold text-ink-900 flex-1">{{ __('Editar documento') }}</div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre del documento') }}</label>
                <input type="text" name="title" id="ed-title" required class="crm-input pl-3 mt-1">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Tipo') }}</label>
                    <select name="document_type" id="ed-type" required class="crm-input pl-3 mt-1">
                        <option value="kyc">KYC</option>
                        <option value="reservation">{{ __('Reserva') }}</option>
                        <option value="payment_plan">{{ __('Plan de pagos') }}</option>
                        <option value="promise">{{ __('Promesa de compraventa') }}</option>
                        <option value="purchase_promise">{{ __('Promesa de compraventa (generada)') }}</option>
                        <option value="contract">{{ __('Contrato') }}</option>
                        <option value="passport">{{ __('Identificación / Pasaporte') }}</option>
                        <option value="other">{{ __('Otro') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Estado') }}</label>
                    <select name="status" id="ed-status" class="crm-input pl-3 mt-1">
                        <option value="pending">{{ __('Pendiente revisión') }}</option>
                        <option value="generated">{{ __('Generado') }}</option>
                        <option value="signed">{{ __('Firmado') }}</option>
                        <option value="approved">{{ __('Aprobado') }}</option>
                        <option value="rejected">{{ __('Rechazado') }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Archivo') }} <span class="font-normal text-ink-400">{{ __('(opcional: dejá el actual o elegí otro)') }}</span></label>
                <div class="text-[11px] text-ink-500 mt-0.5 flex items-center gap-1"><i class="pi pi-paperclip text-[10px]"></i> <span id="ed-current-file">—</span></div>
                <div class="mt-1.5" id="ed-morph"></div>
                <input type="file" id="ed-file" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Fecha') }}</label>
                <input type="date" name="generated_at" id="ed-date" class="crm-input pl-3 mt-1">
            </div>
            <div id="ed-plan-warning" class="hidden rounded-lg border border-warn/30 bg-warn-soft/40 px-3 py-2 text-[11px] text-warn-dark">
                <i class="pi pi-info-circle"></i> {{ __('Un plan de pagos en estado firmado o aprobado bloquea la tarjeta del plan en la pestaña Pagos. Si lo pasás a otro estado, el plan vuelve a quedar editable.') }}
            </div>
        </div>
        <div class="px-6 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cancelar') }}</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-save"></i> {{ __('Guardar cambios') }}</button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        var dlg    = document.getElementById('modal-editar-documento');
        var input  = document.getElementById('ed-file');
        var pathEl = document.getElementById('ed-file-path');
        var fnEl   = document.getElementById('ed-file-name-input');
        var upEl   = document.getElementById('ed-uploading');
        if (!dlg || !input) return;

        var morph = null;
        if (window.UploadMorph) {
            morph = UploadMorph.mount(input, {
                into: document.getElementById('ed-morph'),
                emptyName: @json(__('Reemplazar archivo')),
                emptySub: @json(__('o arrástralo aquí · PDF, JPG o PNG · máx. 50 MB')),
                onSelect: function (file) { send(file); }
            });
        }

        // Misma subida por chunks que "Subir documento"; el form sólo recibe la ruta final.
        function send(file) {
            var csrf = dlg.querySelector('input[name=_token]');
            pathEl.value = '';
            fnEl.value = '';
            upEl.value = '1';
            UploadMorph.chunked(morph, file, '{{ route('admin.crm.document.upload-chunk') }}', {}, csrf ? csrf.value : '')
                .then(function (d) {
                    pathEl.value = d.path || '';
                    fnEl.value = d.name || file.name;
                    upEl.value = '';
                    morph.done(@json(__('Archivo nuevo listo')), {
                        label: @json(__('Cambiar')),
                        onClick: function () { morph.clear(); pathEl.value = ''; fnEl.value = ''; }
                    });
                })
                .catch(function (e) {
                    pathEl.value = ''; fnEl.value = ''; upEl.value = '';
                    morph.fail(e.message || @json(__('No se pudo subir el archivo.')), function () { send(file); });
                });
        }

        // Rellena el modal con la fila y lo abre.
        window.openEditDocument = function (d) {
            dlg.querySelector('form').action = d.url;
            document.getElementById('ed-title').value = d.title || '';
            var type = document.getElementById('ed-type');
            if (![].some.call(type.options, function (o) { return o.value === d.type; })) {
                var o = document.createElement('option'); o.value = d.type; o.textContent = d.type; type.appendChild(o);
            }
            type.value = d.type || 'other';
            document.getElementById('ed-status').value = d.status || 'pending';
            document.getElementById('ed-date').value = d.date || '';
            document.getElementById('ed-current-file').textContent = d.filename || @json(__('Sin archivo'));
            document.getElementById('ed-plan-warning').classList.toggle('hidden', d.type !== 'payment_plan');
            pathEl.value = ''; fnEl.value = ''; upEl.value = '';
            if (morph) morph.clear();
            dlg.showModal();
        };
    })();

    function edBeforeSubmit(form) {
        var upEl = document.getElementById('ed-uploading');
        if (upEl && upEl.value === '1') {
            alert(@json(__('Esperá a que termine la subida del archivo.')));
            return false;
        }
        return true;
    }
</script>
