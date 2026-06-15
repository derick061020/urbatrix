{{--
  Notifications bell + dropdown.
  Required props:
    $endpoint   — route name string, GET endpoint that returns {items, unread}
    $readRoute  — route name string, POST endpoint that marks all as read
--}}
<div class="topbar-notif-wrap relative shrink-0">
    <button type="button" id="notif-bell" class="topbar-icon-btn" title="{{ __('Notificaciones') }}" aria-haspopup="true" aria-expanded="false">
        <i class="pi pi-bell"></i>
        <span id="notif-dot" class="dot-indicator" style="display:none;"></span>
        <span id="notif-count" class="notif-count" style="display:none;">0</span>
    </button>

    <div id="notif-panel" class="notif-panel" role="dialog" aria-label="{{ __('Notificaciones') }}">
        <div class="notif-head">
            <div class="notif-head-title">{{ __('Notificaciones') }}</div>
            <button type="button" id="notif-mark-read" class="notif-mark-read">{{ __('Marcar todas como leídas') }}</button>
        </div>
        <div id="notif-list" class="notif-list">
            <div class="notif-loading"><i class="pi pi-spin pi-spinner"></i> Cargando…</div>
        </div>
        <div class="notif-foot">
            <span class="text-[11px] text-ink-400">{{ __('Actualizado automáticamente') }}</span>
        </div>
    </div>
</div>

<style>
    .topbar-notif-wrap .notif-count {
        position:absolute; top:-4px; right:-4px;
        min-width:18px; height:18px; padding:0 5px;
        border-radius:999px; background:#fb3748; color:#fff;
        font-size:10px; font-weight:700; letter-spacing:.02em;
        display:flex; align-items:center; justify-content:center;
        border:2px solid #fff; line-height:1;
    }
    .notif-panel {
        position:absolute; top: calc(100% + 8px); right:0;
        width: 360px; max-height: 480px;
        background:#fff; border:1px solid #ebebeb; border-radius:14px;
        box-shadow: 0 24px 48px -16px rgba(10,13,20,.22);
        z-index: 60; display:none; overflow:hidden;
        flex-direction:column;
    }
    .notif-panel.open { display:flex; }
    .notif-head {
        padding: 14px 16px;
        display:flex; align-items:center; justify-content:space-between;
        border-bottom: 1px solid #f2f5f8;
    }
    .notif-head-title { font-size:14px; font-weight:700; color:#171717; }
    .notif-mark-read {
        background:transparent; border:none; cursor:pointer;
        font-size:11px; color:#5c7c68; font-weight:600;
        padding: 4px 6px; border-radius:6px;
    }
    .notif-mark-read:hover { background:#eef2ef; }
    .notif-list { flex:1; overflow-y:auto; }
    .notif-list::-webkit-scrollbar { width:6px; }
    .notif-list::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:6px; }

    .notif-item {
        display:flex; gap:11px; align-items:flex-start;
        padding: 12px 16px; cursor:pointer;
        text-decoration:none; color:#171717;
        border-bottom: 1px solid #f7f9fa;
        transition: background-color .12s;
        position:relative;
    }
    .notif-item:hover { background:#f9fafb; }
    .notif-item.is-unread { background: #f5f9f6; }
    .notif-item.is-unread::before {
        content:""; position:absolute; left:6px; top:50%; transform:translateY(-50%);
        width:6px; height:6px; border-radius:999px; background:#5c7c68;
    }
    .notif-icon {
        width:34px; height:34px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; font-size:14px;
    }
    .notif-icon.blue  { background:#e7f0fb; color:#3b82f6; }
    .notif-icon.amber { background:#fff5e1; color:#d97706; }
    .notif-icon.green { background:#e3f7ec; color:#1daf61; }
    .notif-icon.red   { background:#ffebec; color:#fb3748; }
    .notif-icon.gray  { background:#f2f5f8; color:#5c5c5c; }
    .notif-body { flex:1; min-width:0; }
    .notif-title {
        font-size:13px; font-weight:600; color:#171717; line-height:1.3;
        margin-bottom:2px;
    }
    .notif-text {
        font-size:12px; color:#5c5c5c; line-height:1.35;
        overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
    }
    .notif-when {
        font-size:10px; color:#a3a3a3; margin-top:3px; font-weight:500;
    }
    .notif-foot {
        padding: 10px 16px; text-align:center;
        border-top: 1px solid #f2f5f8; background:#fafbfc;
    }
    .notif-loading, .notif-empty {
        padding: 36px 16px; text-align:center;
        color:#a3a3a3; font-size:12px;
    }
    .notif-empty i { display:block; font-size:28px; margin-bottom:8px; color:#cacfd8; }

    @media (max-width: 640px) {
        .notif-panel { width: calc(100vw - 24px); right: -12px; }
    }
</style>

<script>
(function () {
    const bell      = document.getElementById('notif-bell');
    const panel     = document.getElementById('notif-panel');
    const list      = document.getElementById('notif-list');
    const dot       = document.getElementById('notif-dot');
    const counter   = document.getElementById('notif-count');
    const markBtn   = document.getElementById('notif-mark-read');
    if (!bell || !panel) return;

    const endpoint  = @json(route($endpoint));
    const readUrl   = @json(route($readRoute));
    const csrf      = document.querySelector('meta[name=csrf-token]')?.content || '';

    let cache = { items: [], unread: 0 };
    let pollTimer = null;

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c =>
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
        );
    }

    function applyBadge(n) {
        if (n > 0) {
            counter.textContent = n > 9 ? '9+' : String(n);
            counter.style.display = 'flex';
            dot.style.display = 'none';
        } else {
            counter.style.display = 'none';
            dot.style.display = 'none';
        }
    }

    function render() {
        if (!cache.items || cache.items.length === 0) {
            list.innerHTML = '<div class="notif-empty"><i class="pi pi-inbox"></i>No tienes notificaciones</div>';
            return;
        }
        let html = '';
        cache.items.forEach(it => {
            html += '<a href="'+escapeHtml(it.url)+'" class="notif-item'+(it.unread?' is-unread':'')+'">';
            html += '<span class="notif-icon '+escapeHtml(it.color || 'gray')+'"><i class="pi '+escapeHtml(it.icon || 'pi-bell')+'"></i></span>';
            html += '<span class="notif-body">';
            html += '<div class="notif-title">'+escapeHtml(it.title)+'</div>';
            if (it.body) html += '<div class="notif-text">'+escapeHtml(it.body)+'</div>';
            if (it.when) html += '<div class="notif-when">'+escapeHtml(it.when)+'</div>';
            html += '</span></a>';
        });
        list.innerHTML = html;
    }

    async function load(initial = false) {
        try {
            const res = await fetch(endpoint, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('HTTP '+res.status);
            cache = await res.json();
            applyBadge(cache.unread || 0);
            if (panel.classList.contains('open')) render();
            else if (initial) render();
        } catch (e) {
            if (initial) list.innerHTML = '<div class="notif-empty"><i class="pi pi-exclamation-triangle"></i>No se pudieron cargar</div>';
        }
    }

    async function markAllRead() {
        try {
            await fetch(readUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With':'XMLHttpRequest' },
            });
            cache.items = cache.items.map(i => ({ ...i, unread: false }));
            cache.unread = 0;
            applyBadge(0);
            render();
        } catch (e) { /* ignore */ }
    }

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        const opened = panel.classList.toggle('open');
        bell.setAttribute('aria-expanded', opened ? 'true' : 'false');
        if (opened) {
            render();
            load();
        }
    });

    markBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        markAllRead();
    });

    document.addEventListener('click', (e) => {
        if (!panel.contains(e.target) && e.target !== bell && !bell.contains(e.target)) {
            panel.classList.remove('open');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            panel.classList.remove('open');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    // Initial load + poll every 60s
    load(true);
    pollTimer = setInterval(load, 60000);
    window.addEventListener('beforeunload', () => clearInterval(pollTimer));
})();
</script>
