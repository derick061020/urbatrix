{{-- ====== Modal: Exportar (admin = directo · broker = solicita código) ====== --}}
@php
    $modalId  = $id ?? 'modal-exportar';
    $resource = strtolower($name ?? 'expedientes');
    $label    = $name ?? 'Expedientes';
    $isAdminExport = auth()->check() && auth()->user()->is_admin;
@endphp

@if($isAdminExport)
{{-- ========== ADMIN: exporta directamente ========== --}}
<dialog id="{{ $modalId }}" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto">
    <form method="GET" action="{{ route('admin.crm.export') }}" class="w-[400px] bg-white rounded-2xl overflow-hidden">
        <input type="hidden" name="resource" value="{{ $resource }}">
        <div class="px-5 py-4 border-b border-ink-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg border border-ink-200 flex items-center justify-center text-ink-600"><i class="pi pi-upload"></i></div>
            <div class="flex-1">
                <div class="text-[15px] font-bold text-ink-900">Exportar {{ $label }}</div>
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
@else
{{-- ========== BROKER / USER: requiere código de autorización ========== --}}
<dialog id="{{ $modalId }}" class="rounded-2xl p-0 backdrop:bg-black/40 m-auto export-auth-dialog"
        data-resource="{{ $resource }}"
        data-label="{{ $label }}"
        data-request-url="{{ route('admin.crm.export.request') }}"
        data-resend-url="{{ route('admin.crm.export.resend') }}"
        data-verify-url="{{ route('admin.crm.export.verify') }}">

    {{-- Paso 1: bloqueado · solicitar código --}}
    <div data-step="locked" class="w-[420px] bg-white rounded-2xl overflow-hidden">
        <div class="flex justify-end px-3 pt-3">
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="px-6 pb-6 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-ink-100 flex items-center justify-center text-ink-600 mb-4">
                <i class="pi pi-lock text-[18px]"></i>
            </div>
            <div class="text-[16px] font-bold text-ink-900">Exportar {{ $label }}</div>
            <p class="text-[12px] text-ink-500 mt-1 px-2">
                Esta exportación contiene datos sensibles y requiere autorización del administrador del sistema.
            </p>

            <div class="mt-5 border border-ink-100 rounded-xl p-4 text-left bg-ink-50/40">
                <div class="text-[10px] font-bold tracking-wider text-ink-500 uppercase mb-2">Cómo funciona</div>
                <ol class="space-y-1.5 text-[12px] text-ink-700">
                    <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-white border border-ink-200 text-[10px] font-bold flex items-center justify-center text-ink-700 flex-shrink-0">1</span> Solicitas el código de autorización</li>
                    <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-white border border-ink-200 text-[10px] font-bold flex items-center justify-center text-ink-700 flex-shrink-0">2</span> El admin recibe un código de 6 dígitos en su correo</li>
                    <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-white border border-ink-200 text-[10px] font-bold flex items-center justify-center text-ink-700 flex-shrink-0">3</span> El admin te comparte el código</li>
                    <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-white border border-ink-200 text-[10px] font-bold flex items-center justify-center text-ink-700 flex-shrink-0">4</span> Ingresas el código y se descarga la exportación</li>
                </ol>
            </div>

            <button type="button" data-action="request-code" class="mt-5 w-full crm-btn crm-btn-primary justify-center">
                <span data-text>Solicitar código de autorización</span>
                <span data-loading class="hidden"><i class="pi pi-spin pi-spinner"></i></span>
            </button>
            <div class="mt-3 text-[11px] text-ink-500 flex items-center justify-center gap-1.5">
                <i class="pi pi-info-circle"></i> El código caduca en 10 minutos. Solo puede ser usado una vez.
            </div>
            <div data-error class="hidden mt-3 text-[12px] text-danger-dark bg-danger-soft rounded-lg px-3 py-2"></div>
        </div>
    </div>

    {{-- Paso 2: ingresar código --}}
    <div data-step="code" class="hidden w-[420px] bg-white rounded-2xl overflow-hidden">
        <div class="flex justify-end px-3 pt-3">
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-700 p-1"><i class="pi pi-times text-[12px]"></i></button>
        </div>
        <div class="px-6 pb-6 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-ink-100 flex items-center justify-center text-ink-600 mb-4">
                <i class="pi pi-lock text-[18px]"></i>
            </div>
            <div class="text-[16px] font-bold text-ink-900">Código enviado</div>
            <p class="text-[12px] text-ink-500 mt-1">
                Se envió un código de 6 dígitos al correo del administrador:<br>
                <span class="font-semibold text-ink-700" data-admin-email>—</span>
            </p>

            <div class="mt-5 flex items-center justify-center gap-2" data-code-inputs>
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                        class="w-12 h-14 text-center text-[22px] font-bold border border-ink-200 rounded-lg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20" />
                    @if ($i === 2)
                        <span class="text-ink-400 text-[20px]">-</span>
                    @endif
                @endfor
            </div>

            <button type="button" data-action="verify-code" class="mt-5 w-full crm-btn crm-btn-primary justify-center" disabled>
                <span data-text>Enviar código</span>
                <span data-loading class="hidden"><i class="pi pi-spin pi-spinner"></i></span>
            </button>

            <div data-error class="hidden mt-3 text-[12px] text-danger-dark bg-danger-soft rounded-lg px-3 py-2"></div>

            <div class="mt-4 text-[12px] text-ink-500">¿Tienes problemas para recibir el código?</div>
            <button type="button" data-action="resend-code" class="text-[12px] font-semibold text-ink-900 underline mt-1">Reenviar código</button>
            <div data-expires class="mt-2 text-[11px] text-ink-500"></div>
        </div>
    </div>
</dialog>
@endif

@once
@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function setError(root, msg) {
        const box = root.querySelector('[data-error]');
        if (!box) return;
        if (!msg) { box.classList.add('hidden'); box.textContent = ''; return; }
        box.textContent = msg;
        box.classList.remove('hidden');
    }
    function setLoading(btn, loading) {
        if (!btn) return;
        btn.disabled = !!loading;
        btn.querySelector('[data-text]')?.classList.toggle('hidden', !!loading);
        btn.querySelector('[data-loading]')?.classList.toggle('hidden', !loading);
    }
    function showStep(dialog, step) {
        dialog.querySelectorAll('[data-step]').forEach(el => {
            el.classList.toggle('hidden', el.dataset.step !== step);
        });
    }
    function getCode(dialog) {
        return [...dialog.querySelectorAll('[data-code-inputs] input')].map(i => i.value.trim()).join('');
    }
    function updateVerifyEnabled(dialog) {
        const btn = dialog.querySelector('[data-action="verify-code"]');
        if (!btn) return;
        btn.disabled = getCode(dialog).length !== 6;
    }
    function startExpiryCountdown(dialog, expiresAt) {
        const el = dialog.querySelector('[data-expires]');
        if (!el) return;
        if (dialog._expiryTimer) clearInterval(dialog._expiryTimer);
        function tick() {
            const remaining = Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000));
            const mm = String(Math.floor(remaining / 60)).padStart(2, '0');
            const ss = String(remaining % 60).padStart(2, '0');
            el.textContent = remaining > 0 ? `Caduca en ${mm}:${ss}` : 'Código caducado, solicita uno nuevo.';
            if (remaining <= 0) clearInterval(dialog._expiryTimer);
        }
        tick();
        dialog._expiryTimer = setInterval(tick, 1000);
    }

    async function requestCode(dialog, isResend = false) {
        const btn = dialog.querySelector(isResend ? '[data-action="resend-code"]' : '[data-action="request-code"]');
        setError(dialog, '');
        setLoading(btn, true);
        try {
            const res = await fetch(dialog.dataset.requestUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ resource: dialog.dataset.resource }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) throw new Error(data.message || 'No se pudo solicitar el código.');
            dialog.querySelectorAll('[data-admin-email]').forEach(el => el.textContent = data.admin_email || 'el administrador');
            dialog.querySelectorAll('[data-code-inputs] input').forEach(i => i.value = '');
            updateVerifyEnabled(dialog);
            showStep(dialog, 'code');
            startExpiryCountdown(dialog, data.expires_at);
            dialog.querySelector('[data-code-inputs] input')?.focus();
        } catch (e) {
            setError(dialog, e.message || 'Error de conexión.');
        } finally {
            setLoading(btn, false);
        }
    }

    async function verifyCode(dialog) {
        const btn = dialog.querySelector('[data-action="verify-code"]');
        const code = getCode(dialog);
        if (code.length !== 6) return;
        setError(dialog, '');
        setLoading(btn, true);
        try {
            const res = await fetch(dialog.dataset.verifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json,text/csv,*/*', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ resource: dialog.dataset.resource, code: code }),
            });
            if (!res.ok) {
                const data = await res.json().catch(() => ({ message: 'El código es inválido o ya caducó.' }));
                throw new Error(data.message || 'El código es inválido o ya caducó.');
            }
            // Forzar descarga del CSV devuelto
            const blob = await res.blob();
            const disposition = res.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/i);
            const filename = match ? match[1] : (dialog.dataset.resource + '.csv');
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); a.remove();
            URL.revokeObjectURL(url);
            dialog.close();
            // Reinicia al estado inicial para próximos usos
            showStep(dialog, 'locked');
            if (dialog._expiryTimer) clearInterval(dialog._expiryTimer);
        } catch (e) {
            setError(dialog, e.message);
        } finally {
            setLoading(btn, false);
        }
    }

    function bindDialog(dialog) {
        if (dialog._bound) return;
        dialog._bound = true;

        dialog.querySelector('[data-action="request-code"]')?.addEventListener('click', () => requestCode(dialog, false));
        dialog.querySelector('[data-action="resend-code"]')?.addEventListener('click', () => requestCode(dialog, true));
        dialog.querySelector('[data-action="verify-code"]')?.addEventListener('click', () => verifyCode(dialog));

        const inputs = [...dialog.querySelectorAll('[data-code-inputs] input')];
        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                const v = input.value.replace(/\D/g, '').slice(-1);
                input.value = v;
                if (v && idx < inputs.length - 1) inputs[idx + 1].focus();
                updateVerifyEnabled(dialog);
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) inputs[idx - 1].focus();
                if (e.key === 'Enter' && getCode(dialog).length === 6) verifyCode(dialog);
            });
            input.addEventListener('paste', (e) => {
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                if (!text) return;
                e.preventDefault();
                [...text].forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
                inputs[Math.min(text.length, inputs.length - 1)].focus();
                updateVerifyEnabled(dialog);
            });
        });

        dialog.addEventListener('close', () => {
            showStep(dialog, 'locked');
            setError(dialog, '');
            dialog.querySelectorAll('[data-code-inputs] input').forEach(i => i.value = '');
            if (dialog._expiryTimer) clearInterval(dialog._expiryTimer);
        });
    }

    document.querySelectorAll('dialog.export-auth-dialog').forEach(bindDialog);

    // Para futuras inclusiones dinámicas del partial
    window.__bindExportAuthDialog = bindDialog;
})();
</script>
@endpush
@endonce
