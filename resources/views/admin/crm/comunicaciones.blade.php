@extends('layouts.admin_crm')
@section('title', 'Control de comunicaciones — CRM Duna Makai')
@section('page_title', 'Control de comunicaciones')
@section('page_breadcrumb', 'Comunicación · Por proyecto y canal')
@php $activeRoute = 'crm.comunicaciones'; @endphp

@push('styles')
<style>
    /* Switch accesible (línea gráfica CRM) */
    .cc-sw {
        --w:40px; --h:22px;
        width:var(--w); height:var(--h); border-radius:999px; border:1px solid #cacfd8;
        background:#eaecf0; position:relative; cursor:pointer; padding:0;
        transition:background .15s, border-color .15s; flex-shrink:0;
    }
    .cc-sw::after {
        content:""; position:absolute; top:2px; left:2px; width:16px; height:16px;
        border-radius:50%; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.18); transition:left .15s;
    }
    .cc-sw[aria-checked="true"]{ background:#5c7c68; border-color:#5c7c68; }
    .cc-sw[aria-checked="true"]::after{ left:20px; }
    .cc-sw[aria-checked="mixed"]{ background:#5c7c68; border-color:#5c7c68; opacity:.5; }
    .cc-sw[aria-checked="mixed"]::after{ left:11px; }
    .cc-sw:disabled{ opacity:.4; cursor:not-allowed; }
    .cc-sw.fam{ --w:46px; --h:24px; }
    .cc-sw.fam::after{ width:18px; height:18px; }
    .cc-sw.fam[aria-checked="true"]::after{ left:24px; }

    .cc-grid { display:grid; grid-template-columns: 1fr 92px 92px 92px 116px 120px; gap:8px; align-items:center; }
    .cc-dash { color:#cacfd8; }
    @media (max-width: 767px){
        .cc-head { display:none; }
        .cc-grid { grid-template-columns: 1fr 60px 60px 60px; row-gap:6px; }
        .cc-tpl-cell, .cc-since-cell { grid-column: 1 / -1; padding-left:0; }
    }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-4" id="cc-app">

    <p class="text-[13px] text-ink-600 max-w-3xl">
        Activa o desactiva cada comunicación que el sistema envía al comprador, por proyecto y por canal.
        Todo proyecto nuevo entra en <strong class="text-ink-900">silencio total</strong>: importar datos nunca
        dispara comunicaciones hasta que tú las enciendas.
    </p>

    {{-- Tabs de proyecto --}}
    <div class="flex flex-wrap gap-2" id="ccProjTabs"></div>

    @if($projects->isEmpty())
        <div class="crm-card p-8 text-center text-ink-500 text-[13px]">
            No hay proyectos todavía. Crea un proyecto para configurar sus comunicaciones.
        </div>
    @else

    {{-- Estado del proyecto --}}
    <div class="crm-card p-5">
        <div class="flex flex-wrap gap-5 items-start justify-between">
            <div>
                <h2 class="font-display text-[18px] font-semibold text-ink-950" id="ccProjName">—</h2>
                <div class="text-[12px] text-ink-500" id="ccProjSub">Duna Development Group · Cap Cana</div>
                <div class="flex items-center gap-3 mt-3">
                    <button class="cc-sw fam" id="ccMasterSw" role="switch" aria-checked="false" aria-label="Estado de comunicaciones del proyecto"></button>
                    <span class="text-[13px] font-semibold text-ink-900">Comunicaciones del proyecto</span>
                    <span class="crm-pill" id="ccMasterPill"></span>
                </div>
            </div>
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] uppercase tracking-wider font-semibold text-ink-400" for="ccArranque">Fecha de arranque</label>
                    <input type="date" id="ccArranque" class="h-9 px-3 border border-ink-200 rounded-lg text-[13px] text-ink-900 outline-none focus:border-brand">
                </div>
                <button class="crm-btn crm-btn-ghost" id="ccCopyBtn"><i class="pi pi-copy"></i> Copiar de otro proyecto…</button>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-ink-100">
            <p class="text-[12px] text-ink-500 m-0">
                Ningún evento anterior a la fecha de arranque genera comunicación, aunque el tipo esté encendido.
                Los registros importados (origen migrado) nunca notifican.
            </p>
            <div class="hidden mt-4 rounded-lg border border-warn/30 border-l-4 border-l-warn bg-warn-soft px-4 py-3 text-[13px] text-ink-700" id="ccSilenceBanner">
                <strong>Proyecto en silencio.</strong> Ninguna comunicación sale al comprador. Los interruptores están
                bloqueados hasta que cambies el estado del proyecto a “Activo”.
            </div>
        </div>
    </div>

    {{-- Catálogo --}}
    <div class="crm-card overflow-hidden">
        <div class="cc-grid cc-head px-4 py-2.5 border-b border-ink-100 text-[11px] uppercase tracking-wider font-semibold text-ink-400">
            <div>Comunicación</div>
            <div class="text-center">Email</div>
            <div class="text-center">WhatsApp</div>
            <div class="text-center">In-app</div>
            <div>Plantilla</div>
            <div>Activa desde</div>
        </div>
        <div id="ccCatalog"></div>
    </div>

    {{-- Marketing (informativo) --}}
    <div class="crm-card p-5 flex gap-3 items-start">
        <span class="text-[18px]"><i class="pi pi-megaphone text-brand"></i></span>
        <div>
            <h3 class="text-[14px] font-semibold text-ink-900 m-0">Comunicaciones de marketing</h3>
            <p class="text-[12px] text-ink-500 m-0 mt-1">
                Novedades, lanzamientos y promociones. No se controlan aquí: dependen del
                <strong>consentimiento del comprador</strong> (casilla opcional, revocable), conforme a CAN-SPAM, GDPR y Ley 172-13.
            </p>
        </div>
    </div>

    @endif
</div>

@if(!$projects->isEmpty())
<script>
(function(){
    const CATALOG  = @json($catalog['families']);
    const CHANNELS = @json($catalog['channels']);
    const PROJECTS = @json(
        $projects->mapWithKeys(fn($p) => [$p->id => [
            'name'     => $p->name,
            'sub'      => trim(($p->location ? $p->location : '')),
            'active'   => (bool) $p->comms_active,
            'arranque' => optional($p->comms_start_date)->format('Y-m-d'),
        ]])
    );
    const CONFIG = @json($config);

    const ROUTES = {
        toggle:   @json(route('admin.crm.comunicaciones.toggle')),
        master:   @json(route('admin.crm.comunicaciones.master')),
        arranque: @json(route('admin.crm.comunicaciones.arranque')),
        copy:     @json(route('admin.crm.comunicaciones.copy')),
    };
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const ids = Object.keys(PROJECTS);
    let current = ids[0];
    const $ = s => document.querySelector(s);

    async function post(url, body){
        const r = await fetch(url, {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify(body),
        });
        if(!r.ok) throw new Error('request failed');
        return r.json();
    }

    /* ---------- Tabs ---------- */
    function renderTabs(){
        const wrap = $('#ccProjTabs'); wrap.innerHTML = '';
        ids.forEach(id => {
            const p = PROJECTS[id];
            const b = document.createElement('button');
            const active = id === current;
            b.className = 'crm-btn ' + (active ? 'crm-btn-primary' : 'crm-btn-ghost');
            const dotColor = p.active ? '#1fc16b' : '#fa7319';
            b.innerHTML = `<span class="dot" style="background:${dotColor}"></span> ${p.name}`;
            b.onclick = () => { current = id; renderAll(); };
            wrap.appendChild(b);
        });
    }

    /* ---------- Encabezado ---------- */
    function renderHead(){
        const p = PROJECTS[current];
        $('#ccProjName').textContent = p.name;
        $('#ccProjSub').textContent = p.sub || 'Sin ubicación';
        $('#ccArranque').value = p.arranque || '';
        const sw = $('#ccMasterSw');
        sw.setAttribute('aria-checked', p.active);
        const pill = $('#ccMasterPill');
        pill.textContent = p.active ? 'Activo' : 'Silencio';
        pill.className = 'crm-pill ' + (p.active ? 'bg-ok-soft text-ok' : 'bg-warn-soft text-warn');
        $('#ccSilenceBanner').classList.toggle('hidden', p.active);
    }

    /* ---------- Catálogo ---------- */
    function fmt(d){
        if(!d) return '—';
        const [y,m,day] = d.split('-');
        const mes = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'][parseInt(m,10)-1];
        return `${parseInt(day,10)} ${mes} ${y}`;
    }

    function renderCatalog(){
        const p = PROJECTS[current];
        const cfg = CONFIG[current] || {};
        const activo = p.active;
        const root = $('#ccCatalog'); root.innerHTML = '';

        CATALOG.forEach(fam => {
            const locked = !!fam.locked;
            const famEl = document.createElement('div');
            famEl.className = 'border-b border-ink-100 last:border-b-0';

            /* fila de familia */
            const fr = document.createElement('div');
            fr.className = 'cc-grid px-4 py-3 ' + (locked ? 'bg-ink-100' : 'bg-ink-50');
            if(locked){
                fr.innerHTML = `
                    <div class="flex items-center gap-2 font-semibold text-[13px] text-ink-900">${fam.name}
                        <span class="text-[11px] font-normal text-ink-500 inline-flex items-center gap-1"><i class="pi pi-lock text-[10px]"></i> requerida por ley</span>
                    </div>
                    <div></div><div></div><div></div><div></div>
                    <div><span class="crm-pill bg-ink-200 text-ink-500">Siempre activa</span></div>`;
            } else {
                const count = fam.types.length;
                fr.innerHTML = `
                    <div class="font-semibold text-[13px] text-ink-900">${fam.name} <span class="font-normal text-ink-400 text-[12px]">· ${count} ${count>1?'tipos':'tipo'}</span></div>
                    <div class="flex justify-center" data-fam="${fam.key}" data-chan="email"></div>
                    <div class="flex justify-center" data-fam="${fam.key}" data-chan="whatsapp"></div>
                    <div class="flex justify-center" data-fam="${fam.key}" data-chan="inapp"></div>
                    <div></div><div></div>`;
            }
            famEl.appendChild(fr);

            /* filas de tipo */
            fam.types.forEach(t => {
                const tr = document.createElement('div');
                tr.className = 'cc-grid px-4 py-2.5 pl-7 border-t border-ink-100 ' + (locked ? 'bg-ink-100/60' : '');
                const cells = CHANNELS.map(c => {
                    if(!t.ch.includes(c)) return `<div class="flex justify-center"><span class="cc-dash">—</span></div>`;
                    const on = !!(cfg[t.code] && cfg[t.code][c]);
                    const dis = (!activo || locked) ? 'disabled' : '';
                    const checked = locked ? true : on;
                    return `<div class="flex justify-center"><button class="cc-sw" role="switch"
                             aria-checked="${checked}" ${dis}
                             data-code="${t.code}" data-chan="${c}"
                             aria-label="${t.name} · ${c}"></button></div>`;
                }).join('');
                const anyOn = activo && CHANNELS.some(c => cfg[t.code] && cfg[t.code][c]);
                const since = locked ? '' : (anyOn ? fmt(p.arranque) : '—');
                const tplCls = t.tpl === 'lista' ? 'bg-ok-soft text-ok' : 'bg-ink-100 text-ink-500';
                tr.innerHTML = `
                    <div>
                        <div class="text-[13px] text-ink-800">${t.name}</div>
                        <div class="text-[11px] text-ink-400 font-mono">${t.code}</div>
                    </div>
                    ${cells}
                    <div class="cc-tpl-cell"><span class="crm-pill ${tplCls}">${t.tpl==='lista'?'Lista':'Pendiente'}</span></div>
                    <div class="cc-since-cell text-[12px] text-ink-500">${since}</div>`;
                famEl.appendChild(tr);
            });

            root.appendChild(famEl);
        });

        bindSwitches();
        refreshFamilySwitches();
    }

    /* ---------- Interacción ---------- */
    function bindSwitches(){
        const p = PROJECTS[current];
        const cfg = CONFIG[current];

        document.querySelectorAll('#ccCatalog .cc-sw[data-code]').forEach(sw => {
            sw.onclick = async () => {
                if(sw.disabled) return;
                const { code, chan } = sw.dataset;
                const next = !(cfg[code] && cfg[code][chan]);
                cfg[code] = cfg[code] || {};
                cfg[code][chan] = next;
                renderCatalog();
                try {
                    await post(ROUTES.toggle, { project_id: current, code, channel: chan, enabled: next });
                } catch(e) {
                    cfg[code][chan] = !next; renderCatalog();
                    toast('No se pudo guardar el cambio');
                }
            };
        });

        /* switch de familia: enciende/apaga todos los canales soportados */
        document.querySelectorAll('#ccCatalog [data-fam]').forEach(cell => {
            const fam = CATALOG.find(f => f.key === cell.dataset.fam);
            const chan = cell.dataset.chan;
            const supported = fam.types.some(t => t.ch.includes(chan));
            if(!supported){ cell.innerHTML = '<span class="cc-dash">—</span>'; return; }
            const sw = document.createElement('button');
            sw.className = 'cc-sw fam'; sw.setAttribute('role','switch');
            if(!p.active) sw.disabled = true;
            sw.onclick = async () => {
                if(sw.disabled) return;
                const target = sw.getAttribute('aria-checked') !== 'true';
                const affected = fam.types.filter(t => t.ch.includes(chan));
                affected.forEach(t => { cfg[t.code] = cfg[t.code]||{}; cfg[t.code][chan] = target; });
                renderCatalog();
                try {
                    await Promise.all(affected.map(t =>
                        post(ROUTES.toggle, { project_id: current, code: t.code, channel: chan, enabled: target })
                    ));
                } catch(e) { toast('No se pudieron guardar todos los cambios'); }
            };
            cell.innerHTML = ''; cell.appendChild(sw);
        });
    }

    function refreshFamilySwitches(){
        const cfg = CONFIG[current];
        document.querySelectorAll('#ccCatalog [data-fam] .cc-sw.fam').forEach(sw => {
            const cell = sw.parentElement;
            const fam = CATALOG.find(f => f.key === cell.dataset.fam);
            const chan = cell.dataset.chan;
            const rel = fam.types.filter(t => t.ch.includes(chan));
            const onCount = rel.filter(t => cfg[t.code] && cfg[t.code][chan]).length;
            let state = 'false';
            if(onCount === rel.length && rel.length) state = 'true';
            else if(onCount > 0) state = 'mixed';
            sw.setAttribute('aria-checked', state);
        });
    }

    /* ---------- Estado maestro ---------- */
    $('#ccMasterSw').onclick = async () => {
        const p = PROJECTS[current];
        const next = !p.active;
        p.active = next;
        renderAll();
        try { await post(ROUTES.master, { project_id: current, active: next }); }
        catch(e){ p.active = !next; renderAll(); toast('No se pudo cambiar el estado'); }
    };

    $('#ccArranque').onchange = async (e) => {
        const val = e.target.value;
        PROJECTS[current].arranque = val;
        renderCatalog();
        try { await post(ROUTES.arranque, { project_id: current, date: val || null }); }
        catch(err){ toast('No se pudo guardar la fecha'); }
    };

    /* ---------- Copiar configuración ---------- */
    $('#ccCopyBtn').onclick = async () => {
        const others = ids.filter(id => id !== current);
        if(!others.length){ toast('No hay otro proyecto para copiar'); return; }
        const labels = others.map((id,i) => `${i+1}. ${PROJECTS[id].name}`).join('\n');
        const pick = prompt('Copiar configuración desde:\n' + labels + '\n\nEscribe el número:');
        if(!pick) return;
        const src = others[parseInt(pick,10)-1];
        if(!src){ toast('Opción no válida'); return; }
        try {
            await post(ROUTES.copy, { project_id: current, source_id: src });
            CONFIG[current] = JSON.parse(JSON.stringify(CONFIG[src]));
            PROJECTS[current].active = true;
            toast('Configuración copiada de ' + PROJECTS[src].name);
            renderAll();
        } catch(e){ toast('No se pudo copiar la configuración'); }
    };

    /* ---------- Toast ---------- */
    let toastEl, toastT;
    function toast(msg){
        if(!toastEl){
            toastEl = document.createElement('div');
            toastEl.style.cssText = 'position:fixed;bottom:22px;left:50%;transform:translateX(-50%) translateY(20px);background:#222530;color:#fff;padding:11px 18px;border-radius:8px;font-size:13px;opacity:0;pointer-events:none;transition:opacity .2s, transform .2s;z-index:80;';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = msg;
        toastEl.style.opacity = '1'; toastEl.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(toastT);
        toastT = setTimeout(() => { toastEl.style.opacity = '0'; toastEl.style.transform = 'translateX(-50%) translateY(20px)'; }, 2400);
    }

    function renderAll(){ renderTabs(); renderHead(); renderCatalog(); }
    renderAll();
})();
</script>
@endif
@endsection
