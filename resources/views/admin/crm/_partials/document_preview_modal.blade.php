@once
<dialog id="document-preview-modal" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <div class="w-[min(1040px,95vw)] bg-white rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600">
                <i class="pi pi-file"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div id="document-preview-title" class="text-[15px] font-bold text-ink-900 truncate">{{ __('Vista previa') }}</div>
                <div id="document-preview-filename" class="text-[11px] text-ink-500 truncate"></div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1">
                <i class="pi pi-times text-[12px]"></i>
            </button>
        </div>

        <div class="bg-ink-50 p-4">
            <div class="bg-white border border-ink-100 rounded-xl overflow-hidden min-h-[70vh] flex items-center justify-center">
                <iframe id="document-preview-frame" class="hidden w-full h-[70vh] bg-white" title="{{ __('Vista previa del documento') }}"></iframe>
                <img id="document-preview-image" class="hidden max-h-[70vh] max-w-full object-contain" alt="{{ __('Vista previa del documento') }}">
                <div id="document-preview-empty" class="hidden text-center px-6 py-12">
                    <div class="w-12 h-12 rounded-xl bg-ink-100 text-ink-500 flex items-center justify-center mx-auto mb-3">
                        <i class="pi pi-file text-[18px]"></i>
                    </div>
                    <div class="text-[14px] font-semibold text-ink-900">{{ __('Vista previa no disponible') }}</div>
                    <div class="text-[12px] text-ink-500 mt-1">{{ __('Este tipo de archivo no se puede mostrar directamente en el navegador.') }}</div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-ink-100 flex items-center justify-end bg-white">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">{{ __('Cerrar') }}</button>
        </div>
    </div>
</dialog>

<script>
(function () {
    if (window.openDocumentPreview) return;

    window.openDocumentPreview = function (payload) {
        const modal = document.getElementById('document-preview-modal');
        const title = document.getElementById('document-preview-title');
        const filename = document.getElementById('document-preview-filename');
        const frame = document.getElementById('document-preview-frame');
        const image = document.getElementById('document-preview-image');
        const empty = document.getElementById('document-preview-empty');

        if (!modal || !payload?.url) return;

        const cleanFilename = payload.filename || payload.title || 'archivo';
        const extension = cleanFilename.split('.').pop().toLowerCase();
        const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        title.textContent = payload.title || 'Vista previa';
        filename.textContent = cleanFilename;

        frame.classList.add('hidden');
        image.classList.add('hidden');
        empty.classList.add('hidden');
        frame.removeAttribute('src');
        image.removeAttribute('src');

        if (['pdf', 'doc', 'docx', 'html'].includes(extension)) {
            frame.src = payload.url;
            frame.classList.remove('hidden');
        } else if (imageTypes.includes(extension)) {
            image.src = payload.url;
            image.classList.remove('hidden');
        } else {
            empty.classList.remove('hidden');
        }

        modal.showModal();
    };

    document.addEventListener('close', function (event) {
        if (event.target?.id !== 'document-preview-modal') return;
        document.getElementById('document-preview-frame')?.removeAttribute('src');
        document.getElementById('document-preview-image')?.removeAttribute('src');
    }, true);
})();
</script>
@endonce
