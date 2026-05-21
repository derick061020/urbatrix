{{-- ====== Modal: Exportar ====== --}}
<dialog id="{{ $id ?? 'modal-exportar' }}" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="GET" action="{{ $action ?? route('admin.crm.export') }}" class="w-[400px] bg-white rounded-2xl overflow-hidden">
        <input type="hidden" name="resource" value="{{ strtolower($name ?? 'expedientes') }}">
        <div class="px-5 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-upload"></i></div>
            <div class="flex-1">
                <div class="text-[15px] font-bold text-ink-900">Exportar {{ $name ?? 'Expedientes' }}</div>
                <div class="text-[11px] text-ink-500">Puedes exportar sin código adicional.</div>
            </div>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Formato de exportación</label>
                <select name="format" class="crm-input pl-3 mt-1">
                    <option value="csv">CSV — valores separados por coma</option>
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Rango de datos</label>
                <select name="range" class="crm-input pl-3 mt-1">
                    <option value="3m">Últimos 3 meses</option>
                    <option value="6m">Últimos 6 meses</option>
                    <option value="1y">Último año</option>
                    <option value="all">Todo</option>
                </select>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-ink-100 flex items-center gap-2 justify-end bg-ink-50">
            <button type="button" onclick="this.closest('dialog').close()" class="crm-btn crm-btn-ghost">Cancelar</button>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-download"></i> Descargar</button>
        </div>
    </form>
</dialog>
