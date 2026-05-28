<script>
(function () {
    const input    = document.getElementById('global-search-input');
    const dropdown = document.getElementById('global-search-dropdown');
    if (!input || !dropdown) return;

    const endpoint = @json($endpoint);
    let debounceId   = null;
    let activeFetch  = null;
    let currentItems = [];
    let activeIdx    = -1;
    let lastQuery    = '';

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c =>
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
        );
    }
    function highlight(text, q) {
        const safe = escapeHtml(text);
        if (!q) return safe;
        const escQ = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp('('+escQ+')', 'ig'), '<mark>$1</mark>');
    }

    function render(groups, q) {
        currentItems = [];
        activeIdx = -1;

        if (!groups || groups.length === 0) {
            dropdown.innerHTML = '<div class="search-dropdown__empty">Sin resultados para “'+escapeHtml(q)+'”</div>';
            return;
        }

        let html = '';
        groups.forEach(group => {
            html += '<div class="search-dropdown__group">';
            html += '<div class="search-dropdown__title">'+escapeHtml(group.title)+'</div>';
            group.items.forEach(item => {
                const idx = currentItems.length;
                currentItems.push(item);
                html += '<a href="'+escapeHtml(item.url)+'" class="search-dropdown__item" data-idx="'+idx+'">';
                html += '<span class="search-dropdown__icon"><i class="pi '+escapeHtml(item.icon || 'pi-search')+'"></i></span>';
                html += '<span class="min-w-0 flex-1">';
                html += '<span class="search-dropdown__label">'+highlight(item.label, q)+'</span>';
                if (item.sub) html += '<span class="search-dropdown__sub block">'+highlight(item.sub, q)+'</span>';
                html += '</span></a>';
            });
            html += '</div>';
        });
        dropdown.innerHTML = html;
    }

    function updateActive() {
        dropdown.querySelectorAll('.search-dropdown__item').forEach((el, i) => {
            el.classList.toggle('is-active', i === activeIdx);
            if (i === activeIdx) el.scrollIntoView({ block: 'nearest' });
        });
    }

    function open()  { dropdown.classList.add('open'); }
    function close() { dropdown.classList.remove('open'); activeIdx = -1; }

    async function runSearch(q) {
        if (q.length < 2) {
            dropdown.innerHTML = '<div class="search-dropdown__empty">Escribe al menos 2 caracteres…</div>';
            open();
            return;
        }
        dropdown.innerHTML = '<div class="search-dropdown__spinner"><i class="pi pi-spin pi-spinner mr-1"></i> Buscando…</div>';
        open();

        if (activeFetch) activeFetch.abort();
        const ctrl = new AbortController();
        activeFetch = ctrl;

        try {
            const res = await fetch(endpoint + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: ctrl.signal,
            });
            if (!res.ok) throw new Error('HTTP '+res.status);
            const json = await res.json();
            if (q !== lastQuery) return;
            render(json.groups || [], q);
        } catch (err) {
            if (err.name === 'AbortError') return;
            dropdown.innerHTML = '<div class="search-dropdown__empty">Error al buscar. Intenta de nuevo.</div>';
        }
    }

    input.addEventListener('input', () => {
        const q = input.value.trim();
        lastQuery = q;
        clearTimeout(debounceId);
        if (!q) { close(); return; }
        debounceId = setTimeout(() => runSearch(q), 220);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') open();
    });

    input.addEventListener('keydown', (e) => {
        const isOpen = dropdown.classList.contains('open');
        if (e.key === 'ArrowDown') {
            if (!isOpen || currentItems.length === 0) return;
            e.preventDefault();
            activeIdx = (activeIdx + 1) % currentItems.length;
            updateActive();
        } else if (e.key === 'ArrowUp') {
            if (!isOpen || currentItems.length === 0) return;
            e.preventDefault();
            activeIdx = activeIdx <= 0 ? currentItems.length - 1 : activeIdx - 1;
            updateActive();
        } else if (e.key === 'Enter') {
            if (isOpen && activeIdx >= 0 && currentItems[activeIdx]) {
                e.preventDefault();
                window.location.href = currentItems[activeIdx].url;
            }
        } else if (e.key === 'Escape') {
            close();
            input.blur();
        }
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && e.target !== input) close();
    });
})();
</script>
