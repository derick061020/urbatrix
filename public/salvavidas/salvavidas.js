/*
 * Salvavidas: botón flotante, abajo a la izquierda, en todas las páginas.
 *
 * Al pulsarlo captura lo que el usuario está viendo en ese momento y, encima
 * del botón, se despliega otro redondo para ver «Mis tickets». Tras la captura
 * se abre el formulario: comentario y archivos adjuntos. La captura la hace
 * html2canvas-pro (copia local, se carga solo al usarla).
 *
 * Lo mete en cada página el middleware InjectSalvavidas, que deja la
 * configuración en window.SALVAVIDAS. El backend es SupportTicketController.
 */
(function () {
  'use strict';
  const CFG = window.SALVAVIDAS;
  if (!CFG || document.getElementById('salvavidas')) return;

  const EN = CFG.lang === 'en';
  const T = EN ? {
    main: 'Report a problem', cancel: 'Cancel', mine: 'My tickets',
    capturing: 'Capturing…',
    shotFail: 'The screenshot could not be taken. You can still send the ticket and attach files.',
    title: 'New ticket', retake: 'Retake', remove: 'Remove',
    comment: 'What is happening?', commentPh: 'Describe the problem or what you need. The more detail, the better.',
    name: 'Name', email: 'Email (so we can reply)', files: 'Attach files',
    filesHint: 'Up to 5 files, 10 MB each', send: 'Send ticket', sending: 'Sending…',
    sent: 'Ticket sent', sentText: 'We have received it. You will see our reply in «My tickets».',
    close: 'Close', seeMine: 'See my tickets', empty: 'You have not sent any tickets yet.',
    reply: 'Team reply', loading: 'Loading…', err: 'It could not be sent. Please try again.',
    tooBig: 'is larger than 10 MB', tooMany: 'Maximum 5 files', newTicket: 'New ticket',
    needComment: 'Write a comment', needEmail: 'Enter a valid email', newReply: 'New reply',
    page: 'Page', shot: 'Screenshot', addShot: '+ Add a screenshot',
  } : {
    main: 'Reportar un problema', cancel: 'Cancelar', mine: 'Mis tickets',
    capturing: 'Capturando…',
    shotFail: 'No se pudo hacer la captura. Puedes enviar el ticket igualmente y adjuntar archivos.',
    title: 'Nuevo ticket', retake: 'Repetir', remove: 'Quitar',
    comment: '¿Qué está pasando?', commentPh: 'Describe el problema o lo que necesitas. Cuanto más detalle, mejor.',
    name: 'Nombre', email: 'Correo (para poder responderte)', files: 'Adjuntar archivos',
    filesHint: 'Hasta 5 archivos de 10 MB cada uno', send: 'Enviar ticket', sending: 'Enviando…',
    sent: 'Ticket enviado', sentText: 'Lo hemos recibido. Verás nuestra respuesta en «Mis tickets».',
    close: 'Cerrar', seeMine: 'Ver mis tickets', empty: 'Todavía no has enviado ningún ticket.',
    reply: 'Respuesta del equipo', loading: 'Cargando…', err: 'No se pudo enviar. Inténtalo de nuevo.',
    tooBig: 'pesa más de 10 MB', tooMany: 'Máximo 5 archivos', newTicket: 'Nuevo ticket',
    needComment: 'Escribe un comentario', needEmail: 'Escribe un correo válido', newReply: 'Respuesta nueva',
    page: 'Página', shot: 'Captura', addShot: '+ Añadir una captura',
  };

  const ICON = {
    buoy: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M5.6 5.6l3.6 3.6M14.8 14.8l3.6 3.6M18.4 5.6l-3.6 3.6M9.2 14.8l-3.6 3.6"/></svg>',
    close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>',
    list: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/><path d="M14 5v12" stroke-dasharray="2 2"/></svg>',
    clip: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.5l-7.8 7.8a5 5 0 0 1-7.1-7.1l8.5-8.5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4l7.8-7.8"/></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
  };

  const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const h = (tag, cls, html) => { const e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; };
  const MAX_FILES = 5, MAX_BYTES = 10 * 1024 * 1024;

  // ── Raíz ──────────────────────────────────────────────────────────────────
  const root = h('div', 'sv-root');
  root.id = 'salvavidas';
  root.style.setProperty('--sv-accent', CFG.accent || '#2b2b1a');
  root.innerHTML = `
    <div class="sv-fab-wrap">
      <button type="button" class="sv-fab sv-fab--mine" data-sv="mine" aria-label="${T.mine}" title="${T.mine}" tabindex="-1">${ICON.list}<span class="sv-dot" hidden></span></button>
      <button type="button" class="sv-fab sv-fab--main" data-sv="main" aria-label="${T.main}" title="${T.main}" aria-expanded="false">
        <span class="sv-ico sv-ico--buoy">${ICON.buoy}</span><span class="sv-ico sv-ico--close">${ICON.close}</span><span class="sv-ico sv-ico--spin"><span class="sv-spin"></span></span><span class="sv-dot" hidden></span>
      </button>
    </div>`;
  document.body.appendChild(root);
  const btnMain = root.querySelector('[data-sv="main"]');
  const btnMine = root.querySelector('[data-sv="mine"]');

  let state = 'idle';          // idle · select · capturing · form · mine
  let shot = null;             // Blob de la captura
  let shotUrl = null;
  let files = [];
  let panel = null;

  function setOpen(open) {
    root.classList.toggle('is-open', open);
    btnMain.setAttribute('aria-expanded', open ? 'true' : 'false');
    btnMain.setAttribute('aria-label', open ? T.cancel : T.main);
    btnMain.title = open ? T.cancel : T.main;
    btnMine.tabIndex = open ? 0 : -1;
  }

  function reset() {
    state = 'idle';
    if (panel) { panel.remove(); panel = null; }
    if (shotUrl) URL.revokeObjectURL(shotUrl);
    shot = shotUrl = null;
    files = [];
    setOpen(false);
  }

  btnMain.addEventListener('click', () => {
    if (state === 'idle') capture();
    else if (state !== 'capturing') reset();
  });
  btnMine.addEventListener('click', () => openMine());
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && state !== 'idle' && state !== 'capturing') { e.stopPropagation(); reset(); }
  }, true);

  // ── 1. Captura de lo que se ve ahora mismo ────────────────────────────────
  let libPromise = null;
  function loadLib() {
    if (window.html2canvas) return Promise.resolve(window.html2canvas);
    if (!libPromise) {
      libPromise = new Promise((ok, ko) => {
        const s = document.createElement('script');
        s.src = CFG.urls.lib;
        s.onload = () => (window.html2canvas ? ok(window.html2canvas) : ko(new Error('html2canvas')));
        s.onerror = ko;
        document.head.appendChild(s);
      }).catch(e => { libPromise = null; throw e; });
    }
    return libPromise;
  }

  // Lo que queda fuera de la pantalla no se clona ni se pinta. La librería copia
  // los estilos de cada SVG, y con cientos de tarjetas (Makai) la captura de la
  // página entera tardaba 40 s; así son unos pocos.
  function fuera(el) {
    if (el === document.body || el === document.documentElement || el.tagName === 'HEAD' || el.closest?.('head')) return false;
    const b = el.getBoundingClientRect();
    if (!b.width && !b.height) return false;   // display:contents y similares: sus hijos sí se ven
    return b.bottom < 0 || b.top > window.innerHeight || b.right < 0 || b.left > window.innerWidth;
  }

  async function capture() {
    if (panel) { panel.remove(); panel = null; }
    state = 'capturing';
    setOpen(true);
    root.classList.add('is-capturing');
    btnMain.setAttribute('aria-label', T.capturing);
    let ok = false;
    try {
      const html2canvas = await loadLib();
      const w = document.documentElement.clientWidth, hh = window.innerHeight;
      const scale = Math.min(window.devicePixelRatio || 1, 2);
      const canvas = await Promise.race([
        html2canvas(document.body, {
          x: window.scrollX, y: window.scrollY, width: w, height: hh,
          windowWidth: w, windowHeight: hh, scrollX: window.scrollX, scrollY: window.scrollY,
          scale, useCORS: true, logging: false, imageTimeout: 6000,
          backgroundColor: getComputedStyle(document.body).backgroundColor || '#ffffff',
          // El propio salvavidas no sale en la foto.
          ignoreElements: el => el.id === 'salvavidas' || fuera(el),
        }),
        new Promise((_, ko) => setTimeout(() => ko(new Error('timeout')), 25000)),
      ]);
      shot = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.9));
      ok = !!shot;
    } catch (e) {
      console.warn('[salvavidas] captura', e);
      shot = null;
    }
    root.classList.remove('is-capturing');
    if (state !== 'capturing') return;   // lo cerró mientras tanto
    if (shotUrl) URL.revokeObjectURL(shotUrl);
    shotUrl = shot ? URL.createObjectURL(shot) : null;
    openForm(ok ? null : T.shotFail);
  }

  // ── 3. Formulario ─────────────────────────────────────────────────────────
  function openPanel(cls) {
    if (panel) panel.remove();
    panel = h('div', 'sv-panel ' + (cls || ''));
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'false');
    root.appendChild(panel);
    return panel;
  }

  function openForm(notice) {
    state = 'form';
    setOpen(true);
    const p = openPanel('sv-panel--form');
    p.setAttribute('aria-label', T.title);
    p.innerHTML = `
      <div class="sv-head"><b>${T.title}</b><button type="button" class="sv-x" data-sv="close" aria-label="${T.close}">${ICON.close}</button></div>
      <form class="sv-body" novalidate>
        ${notice ? `<div class="sv-note">${esc(notice)}</div>` : ''}
        ${shotUrl ? `<figure class="sv-shot"><img src="${shotUrl}" alt="${T.shot}"><figcaption><button type="button" class="sv-link" data-sv="retake">${T.retake}</button><button type="button" class="sv-link" data-sv="dropshot">${T.remove}</button></figcaption></figure>`
                  : `<button type="button" class="sv-chip sv-chip--ghost sv-chip--block" data-sv="retake">${T.addShot}</button>`}
        <label class="sv-field"><span>${T.comment}</span><textarea name="comment" rows="4" maxlength="5000" placeholder="${T.commentPh}" required></textarea></label>
        ${CFG.auth ? '' : `
          <div class="sv-row">
            <label class="sv-field"><span>${T.name}</span><input name="name" autocomplete="name" maxlength="255"></label>
            <label class="sv-field"><span>${T.email}</span><input name="email" type="email" autocomplete="email" maxlength="255" required></label>
          </div>`}
        <div class="sv-field">
          <span>${T.files}</span>
          <label class="sv-drop">${ICON.clip}<span>${T.filesHint}</span><input type="file" multiple hidden></label>
          <ul class="sv-files"></ul>
        </div>
        <div class="sv-err" role="alert" hidden></div>
        <button type="submit" class="sv-send">${T.send}</button>
      </form>`;

    const form = p.querySelector('form');
    const err = p.querySelector('.sv-err');
    const list = p.querySelector('.sv-files');
    const input = p.querySelector('input[type=file]');
    const drop = p.querySelector('.sv-drop');

    p.querySelector('[data-sv="close"]').addEventListener('click', reset);
    p.querySelectorAll('[data-sv="retake"]').forEach(b => b.addEventListener('click', () => { keepForm(form); capture(); }));
    const dropShot = p.querySelector('[data-sv="dropshot"]');
    if (dropShot) dropShot.addEventListener('click', () => {
      keepForm(form);
      if (shotUrl) URL.revokeObjectURL(shotUrl);
      shot = shotUrl = null;
      openForm();
    });
    restoreForm(form);

    function addFiles(list_) {
      showErr('');
      for (const f of list_) {
        if (files.length >= MAX_FILES) { showErr(T.tooMany); break; }
        if (f.size > MAX_BYTES) { showErr(`${f.name} ${T.tooBig}`); continue; }
        files.push(f);
      }
      paintFiles();
    }
    function paintFiles() {
      list.innerHTML = files.map((f, i) => `<li><span>${esc(f.name)}</span><small>${(f.size / 1024 / 1024).toFixed(1)} MB</small><button type="button" data-i="${i}" aria-label="${T.remove}">${ICON.close}</button></li>`).join('');
    }
    paintFiles();
    input.addEventListener('change', () => { addFiles(input.files); input.value = ''; });
    list.addEventListener('click', e => { const b = e.target.closest('button[data-i]'); if (b) { files.splice(+b.dataset.i, 1); paintFiles(); } });
    ['dragover', 'dragenter'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('is-over'); }));
    drop.addEventListener('drop', e => addFiles(e.dataTransfer.files));
    // Pegar una imagen del portapapeles en el comentario la adjunta.
    form.comment.addEventListener('paste', e => {
      const imgs = [...(e.clipboardData?.files || [])];
      if (imgs.length) { e.preventDefault(); addFiles(imgs); }
    });

    function showErr(m) { err.textContent = m; err.hidden = !m; }

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const comment = form.comment.value.trim();
      if (!comment) { showErr(T.needComment); form.comment.focus(); return; }
      if (!CFG.auth && !/^\S+@\S+\.\S+$/.test(form.email.value.trim())) { showErr(T.needEmail); form.email.focus(); return; }
      showErr('');
      const fd = new FormData();
      fd.append('comment', comment);
      if (!CFG.auth) { fd.append('email', form.email.value.trim()); if (form.name.value.trim()) fd.append('name', form.name.value.trim()); }
      if (shot) fd.append('screenshot', shot, 'captura.' + (shot.type === 'image/jpeg' ? 'jpg' : 'png'));
      files.forEach(f => fd.append('attachments[]', f, f.name));
      fd.append('page_url', location.href.slice(0, 2048));
      fd.append('page_title', (document.title || '').slice(0, 255));
      fd.append('viewport', window.innerWidth + 'x' + window.innerHeight);
      const send = form.querySelector('.sv-send');
      send.disabled = true;
      send.textContent = T.sending;
      try {
        const r = await fetch(CFG.urls.store, {
          method: 'POST', body: fd, credentials: 'same-origin',
          headers: { 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const d = await r.json().catch(() => ({}));
        if (!r.ok || !d.success) {
          const first = d.errors ? Object.values(d.errors)[0] : null;
          throw new Error((first && first[0]) || d.message || T.err);
        }
        saved = null;
        done(d.code);
      } catch (ex) {
        showErr(ex.message && ex.message !== 'Failed to fetch' ? ex.message : T.err);
        send.disabled = false;
        send.textContent = T.send;
      }
    });
    setTimeout(() => form.comment.focus({ preventScroll: true }), 30);
  }

  // Lo escrito sobrevive a «Repetir» la captura.
  let saved = null;
  function keepForm(form) {
    saved = { comment: form.comment.value, name: form.name?.value, email: form.email?.value };
  }
  function restoreForm(form) {
    if (!saved) return;
    form.comment.value = saved.comment || '';
    if (form.name && saved.name) form.name.value = saved.name;
    if (form.email && saved.email) form.email.value = saved.email;
  }

  function done(code) {
    if (shotUrl) URL.revokeObjectURL(shotUrl);
    shot = shotUrl = null;
    files = [];
    const p = openPanel('sv-panel--done');
    p.innerHTML = `
      <div class="sv-head"><b>${T.sent}</b><button type="button" class="sv-x" data-sv="close" aria-label="${T.close}">${ICON.close}</button></div>
      <div class="sv-body sv-done">
        <span class="sv-ok">${ICON.check}</span>
        <div class="sv-code">${esc(code)}</div>
        <p>${T.sentText}</p>
        <button type="button" class="sv-send" data-sv="seemine">${T.seeMine}</button>
      </div>`;
    p.querySelector('[data-sv="close"]').addEventListener('click', reset);
    p.querySelector('[data-sv="seemine"]').addEventListener('click', openMine);
  }

  // ── 4. Mis tickets ────────────────────────────────────────────────────────
  async function openMine() {
    state = 'mine';
    setOpen(true);
    const p = openPanel('sv-panel--mine');
    p.setAttribute('aria-label', T.mine);
    p.innerHTML = `
      <div class="sv-head"><b>${T.mine}</b><button type="button" class="sv-x" data-sv="close" aria-label="${T.close}">${ICON.close}</button></div>
      <div class="sv-body"><div class="sv-muted">${T.loading}</div></div>
      <div class="sv-foot"><button type="button" class="sv-send" data-sv="new">${ICON.buoy}${T.newTicket}</button></div>`;
    p.querySelector('[data-sv="close"]').addEventListener('click', reset);
    p.querySelector('[data-sv="new"]').addEventListener('click', () => { p.remove(); panel = null; capture(); });
    const body = p.querySelector('.sv-body');
    try {
      const r = await fetch(CFG.urls.mine, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const d = await r.json();
      if (panel !== p) return;
      paintDots(0);
      if (!d.tickets.length) { body.innerHTML = `<div class="sv-empty">${ICON.list}<p>${T.empty}</p></div>`; return; }
      body.innerHTML = '<ul class="sv-tickets">' + d.tickets.map(t => `
        <li class="sv-ticket">
          <div class="sv-ticket-top">
            <b>${esc(t.code)}</b>
            <span class="sv-pill sv-pill--${esc(t.status)}">${esc(t.status_label)}</span>
            ${t.new_reply ? `<span class="sv-pill sv-pill--new">${T.newReply}</span>` : ''}
            <small>${esc(t.created_at)}</small>
          </div>
          <p class="sv-ticket-txt">${esc(t.comment)}</p>
          ${t.screenshot || t.attachments.length ? `<div class="sv-ticket-files">
            ${t.screenshot ? `<a href="${esc(t.screenshot)}" target="_blank" rel="noopener" class="sv-thumb"><img src="${esc(t.screenshot)}" alt="${T.shot}" loading="lazy"></a>` : ''}
            ${t.attachments.map(a => `<a href="${esc(a.url)}" target="_blank" rel="noopener" class="sv-att">${ICON.clip}<span>${esc(a.name)}</span></a>`).join('')}
          </div>` : ''}
          ${t.reply ? `<div class="sv-reply"><small>${T.reply} · ${esc(t.replied_at)}</small><p>${esc(t.reply)}</p></div>` : ''}
        </li>`).join('') + '</ul>';
    } catch (e) {
      if (panel === p) body.innerHTML = `<div class="sv-err">${T.err}</div>`;
    }
  }

  // ── No tapar el menú lateral ──────────────────────────────────────────────
  // En los paneles (cliente, broker, admin) abajo a la izquierda está el pie del
  // sidebar: la tarjeta del usuario, el idioma, salir. Si debajo del botón hay
  // un sidebar fijo, el botón pasa a la esquina inferior izquierda del
  // contenido, justo a su derecha. Si es una barra fija inferior, sube encima.
  function esquivar() {
    root.style.removeProperty('--sv-left');
    root.style.removeProperty('--sv-bottom');
    const r = btnMain.getBoundingClientRect();
    const x = r.left + r.width / 2, y = r.top + r.height / 2;
    const hit = document.elementsFromPoint(x, y).find(el => !root.contains(el) && el !== document.body && el !== document.documentElement);
    if (!hit) return;
    let fijo = null;
    for (let el = hit; el && el !== document.body; el = el.parentElement) {
      const pos = getComputedStyle(el).position;
      if (pos === 'fixed' || pos === 'sticky') { fijo = el; break; }
    }
    if (!fijo) return;
    const f = fijo.getBoundingClientRect();
    if (f.height > window.innerHeight * 0.6 && f.width < 420 && f.left < 40) {
      root.style.setProperty('--sv-left', Math.round(f.right + 16) + 'px');
    } else if (f.top > window.innerHeight * 0.55) {
      root.style.setProperty('--sv-bottom', Math.round(window.innerHeight - f.top + 12) + 'px');
    }
  }
  let esqT;
  const reEsquivar = () => { clearTimeout(esqT); esqT = setTimeout(esquivar, 150); };
  window.addEventListener('resize', reEsquivar);
  window.addEventListener('load', reEsquivar);
  esquivar();

  // ── Punto de «respuesta nueva» ────────────────────────────────────────────
  function paintDots(n) {
    root.querySelectorAll('.sv-dot').forEach(d => { d.hidden = !n; });
  }
  const idle = window.requestIdleCallback || (fn => setTimeout(fn, 1500));
  idle(() => {
    fetch(CFG.urls.unread, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(r => (r.ok ? r.json() : null)).then(d => d && paintDots(d.unread)).catch(() => {});
  });
})();
