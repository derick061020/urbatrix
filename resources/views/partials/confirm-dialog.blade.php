{{-- ============================================================
     Diálogo de confirmación reutilizable (línea gráfica de la web,
     misma base que partials/logout-modal).

     Uso desde JS:
       confirmDialog({
         title:        '¿Quitar de guardados?',
         text:         'Esta unidad dejará de aparecer en tu lista.',
         confirmLabel: 'Quitar',
         tone:         'danger' | 'brand' (default danger),
         icon:         'pi pi-heart-fill',
         onConfirm:    () => { ... }
       });
     ============================================================ --}}
<style>
    .confirm-dlg-overlay {
        position: fixed; inset: 0; z-index: 3000;
        display: none; align-items: center; justify-content: center;
        padding: 24px;
        background: rgba(15, 17, 24, 0.48);
        animation: confirmDlgFade .16s ease-out;
    }
    .confirm-dlg-overlay.open { display: flex; }
    @keyframes confirmDlgFade { from { opacity: 0; } to { opacity: 1; } }

    .confirm-dlg-card {
        width: 100%; max-width: 420px;
        border-radius: 18px; background: #fff;
        box-shadow: 0 30px 80px -20px rgba(10, 13, 20, .4);
        padding: 24px;
        font-family: 'Poppins', sans-serif;
        animation: confirmDlgIn .18s ease-out;
    }
    @keyframes confirmDlgIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .confirm-dlg-icon {
        width: 44px; height: 44px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff1f2; color: #dc2626; margin-bottom: 14px;
    }
    .confirm-dlg-card.tone-brand .confirm-dlg-icon { background: rgba(92,124,104,.12); color: #5c7c68; }
    .confirm-dlg-title {
        margin: 0; color: #171717; font-size: 18px; font-weight: 700;
        line-height: 1.25; letter-spacing: -0.02em;
    }
    .confirm-dlg-text {
        margin: 8px 0 22px; color: #667085; font-size: 13px; line-height: 1.55;
    }
    .confirm-dlg-actions { display: flex; gap: 10px; }
    .confirm-dlg-actions > * { flex: 1 1 0; min-width: 0; }
    .confirm-dlg-btn {
        width: 100%; height: 40px; border-radius: 10px;
        border: 1px solid #e5e7eb; padding: 0 16px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: background .15s, border-color .15s, transform .15s;
    }
    .confirm-dlg-btn:hover { transform: translateY(-1px); }
    .confirm-dlg-cancel { background: #fff; color: #344054; }
    .confirm-dlg-cancel:hover { background: #f9fafb; }
    .confirm-dlg-submit { border-color: #dc2626; background: #dc2626; color: #fff; }
    .confirm-dlg-submit:hover { border-color: #b91c1c; background: #b91c1c; }
    .confirm-dlg-card.tone-brand .confirm-dlg-submit { border-color: #5c7c68; background: #5c7c68; }
    .confirm-dlg-card.tone-brand .confirm-dlg-submit:hover { border-color: #4a6354; background: #4a6354; }
    @media (max-width: 520px) {
        .confirm-dlg-card { padding: 20px; }
        .confirm-dlg-actions { flex-direction: column-reverse; }
    }
</style>

<div id="confirmDialog" class="confirm-dlg-overlay" role="dialog" aria-modal="true" aria-labelledby="confirmDlgTitle" aria-describedby="confirmDlgText">
    <div class="confirm-dlg-card" id="confirmDlgCard">
        <div class="confirm-dlg-icon" id="confirmDlgIconWrap" aria-hidden="true">
            <i class="pi pi-exclamation-triangle" id="confirmDlgIcon"></i>
        </div>
        <h2 id="confirmDlgTitle" class="confirm-dlg-title">¿Estás seguro?</h2>
        <p id="confirmDlgText" class="confirm-dlg-text">Esta acción no se puede deshacer.</p>
        <div class="confirm-dlg-actions">
            <button type="button" class="confirm-dlg-btn confirm-dlg-cancel" id="confirmDlgCancel">Cancelar</button>
            <button type="button" class="confirm-dlg-btn confirm-dlg-submit" id="confirmDlgConfirm">Confirmar</button>
        </div>
    </div>
</div>

<script>
(function(){
    const modal   = document.getElementById('confirmDialog');
    if (!modal) return;
    const card    = document.getElementById('confirmDlgCard');
    const titleEl = document.getElementById('confirmDlgTitle');
    const textEl  = document.getElementById('confirmDlgText');
    const iconEl  = document.getElementById('confirmDlgIcon');
    const okBtn   = document.getElementById('confirmDlgConfirm');
    const noBtn   = document.getElementById('confirmDlgCancel');

    let pendingConfirm = null;
    let previousOverflow = '';

    function close() {
        modal.classList.remove('open');
        document.body.style.overflow = previousOverflow;
        pendingConfirm = null;
    }

    window.confirmDialog = function(opts) {
        opts = opts || {};
        titleEl.textContent = opts.title || '¿Estás seguro?';
        textEl.textContent  = opts.text  || 'Esta acción no se puede deshacer.';
        okBtn.textContent   = opts.confirmLabel || 'Confirmar';
        noBtn.textContent   = opts.cancelLabel  || 'Cancelar';
        iconEl.className     = opts.icon || 'pi pi-exclamation-triangle';
        card.classList.toggle('tone-brand', opts.tone === 'brand');
        pendingConfirm = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;

        previousOverflow = document.body.style.overflow;
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        okBtn.focus();
    };
    window.closeConfirmDialog = close;

    okBtn.addEventListener('click', function(){
        const cb = pendingConfirm;
        close();
        if (cb) cb();
    });
    noBtn.addEventListener('click', close);
    modal.addEventListener('click', function(e){ if (e.target === modal) close(); });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && modal.classList.contains('open')) close();
    });
})();
</script>
