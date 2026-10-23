/* ============================================================================
   CAPA DE MARCA — Siebe Residences: la parte de comportamiento (ver lujo.css)
   ============================================================================

   Adaptación de la capa «lujo» de Bahía Mar (urbatrix-bahia-mar/clon-lujo,
   en producción en landmass.urbatrix.com). Siebe es un solo desarrollo, sin
   tipologías: de aquella capa se quedan el hero (slider + Hoja), las
   tarjetas, la ficha y el registro; fuera las bandas, las portadas, las
   plantas y el paso entre tipologías.

   Solo añade o reordena lo que ya pinta el sitio; no pide nada nuevo al
   backend. Todo lo que muestra sale de:
     · el HTML del propio sitio (tarjetas),
     · /api/units/{id} (la ficha), que el sitio ya pide,
     · TEXTOS de interfaz y el copy de sieberesidences.com.
   No inventa cifras: lo que no tiene dato, no se pinta.

   No toca el header ni el footer (son los de Urbatrix).

   Se resuscribe a los re-renders del grid con un MutationObserver. Si el
   motor de render tiene un hook propio, se puede llamar a
   window.lujo.refrescar() desde ahí.
   ========================================================================== */
(function () {
  'use strict';

  const ES = (document.documentElement.lang || 'en').toLowerCase().startsWith('es');
  const t = (es, en) => (ES ? es : en);

  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const el = (tag, cls, html) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  };
  const reducido = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── ICONOS ─────────────────────────────────────────────────────────────
     La iconografía de la capa: trazo fino de 1,3, esquinas redondeadas, en
     el gris de la landing (el color lo pone el CSS). Un dibujo por dato. */
  const ICONO = {
    hab:      '<path d="M3 18v-5.5A1.5 1.5 0 0 1 4.5 11h15a1.5 1.5 0 0 1 1.5 1.5V18M3 15.5h18M3 18v2M21 18v2M5.5 11V7.8a.8.8 0 0 1 .8-.8h4.4a.8.8 0 0 1 .8.8V11M12.5 11V7.8a.8.8 0 0 1 .8-.8h4.4a.8.8 0 0 1 .8.8V11"/>',
    banos:    '<path d="M4 12h16v2.5a4.5 4.5 0 0 1-4.5 4.5h-7A4.5 4.5 0 0 1 4 14.5V12zM6.5 12V6a2 2 0 0 1 3.7-1.1M7.5 19l-1 2M16.5 19l1 2"/>',
    interior: '<path d="M4 4h16v16H4zM4 11h7v9M15 4v4"/>',
    terraza:  '<path d="M12 3.5v1.6M6.6 5.7l1.1 1.1M17.4 5.7l-1.1 1.1M8.5 11.5a3.5 3.5 0 0 1 7 0M3 14.5h18M4.5 14.5V20M9.5 14.5V20M14.5 14.5V20M19.5 14.5V20M3 20h18"/>',
    total:    '<path d="M4 4h16v16H4zM4 4l5 5M20 4l-5 5M4 20l5-5M20 20l-5-5"/>',
    planta:   '<path d="M3 20h18M5 20V8l7-4 7 4v12M9 20v-5h6v5M9 11h.01M15 11h.01"/>',
    tipo:     '<path d="M4 5h16v14H4zM4 10h9M13 5v14M13 14h7"/>',
    rasgo:    '<path d="M12 3l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.4l-5.2 2.7 1-5.8L3.5 9.2l5.9-.9z"/>',
  };
  const svg = n => `<svg viewBox="0 0 24 24" aria-hidden="true">${ICONO[n]}</svg>`;

  /* ── 1. HERO ────────────────────────────────────────────────────────────
     El de Bahía Mar: slider propio a sangre dentro del .fg-stage del sitio,
     con el telón diagonal y 8 s por imagen. Los renders son los de la
     landing de Siebe (lujo/hero/, webp a 2048 y 1024): la fachada al
     atardecer, el lobby, un dormitorio, la azotea y la fachada de dia.
     La marca, pequeña y abajo a la izquierda: el logo, la ubicación y el
     tagline de sieberesidences.com. Índice «01 — 05» con una línea fina de
     progreso; cada número salta a su imagen. Con prefers-reduced-motion:
     sin telón ni autoplay. */
  // Las cinco que mando Miguel («Imagene slide», 16 oct). Las dos fachadas son
  // casi el mismo encuadre a distinta hora, asi que van la primera y la ultima:
  // separadas, no se leen como repetida. Las fachadas bajan el encuadre para no
  // comerse la entrada ni el monolito con el logotipo, que es lo que identifica
  // el proyecto.
  const HERO = [
    { img: '01-fachada-atardecer', foco: '50% 58%', alt: t('La fachada de Siebe Residences al atardecer', 'The Siebe Residences facade at sunset') },
    { img: '02-lobby',             foco: '50% 50%', alt: t('El lobby acristalado al anochecer', 'The glass lobby at dusk') },
    { img: '03-interior',          foco: '50% 55%', alt: t('Dormitorio principal de una residencia', 'Main bedroom of a residence') },
    { img: '04-azotea',            foco: '50% 60%', alt: t('La piscina de la azotea con las tumbonas', 'The rooftop pool and loungers') },
    { img: '05-fachada-dia',       foco: '50% 58%', alt: t('La fachada de Siebe Residences de día', 'The Siebe Residences facade by day') },
  ];
  const HERO_MS = 8000;
  const MARCA = {
    sub: t('Pontón · Aruba', 'Pontón · Aruba'),
    titulo: t('Cerca de todo, lejos del ruido', 'Close to everything, never in the noise'),
    ir: t('Ver las residencias', 'View the residences'),
  };
  function hero() {
    const stage = $('.fg-stage');
    if (!stage || $('.lx-hero', stage)) return;
    const raiz = el('div', 'lx-hero');
    raiz.innerHTML = `
      <div class="lx-hero-capas">${HERO.map((c, i) => `
        <figure class="lx-telon-capa${i === 0 ? ' is-activa' : ''}" data-i="${i}" style="--lx-foco:${c.foco}">
          <img ${i === 0 ? 'src' : 'data-src'}="/lujo/hero/${c.img}.webp"
               ${i === 0 ? 'srcset' : 'data-srcset'}="/lujo/hero/${c.img}-1024.webp 1024w, /lujo/hero/${c.img}.webp 2048w"
               sizes="100vw" alt="${c.alt}" ${i === 0 ? 'fetchpriority="high"' : ''} decoding="async">
        </figure>`).join('')}
      </div>
      <div class="lx-hero-pie">
        <img class="lx-hero-logo" src="/lujo/img/sr-logo-white.svg" alt="Siebe Residences" width="131" height="61">
        <p class="lx-hero-sub">${MARCA.sub}</p>
        <p class="lx-hero-titulo">${MARCA.titulo}</p>
        <button type="button" class="lx-hero-ir">${MARCA.ir}</button>
      </div>
      <div class="lx-hero-indice" role="group" aria-label="${t('Imágenes', 'Images')}">
        ${HERO.map((c, i) => `<button type="button" data-i="${i}" aria-label="${c.alt}" aria-pressed="${i === 0}">${String(i + 1).padStart(2, '0')}</button>`).join('')}
        <span class="lx-hero-linea" aria-hidden="true"><i></i></span>
      </div>`;
    raiz.appendChild(el('div', 'lx-hero-sombra'));
    stage.appendChild(raiz);
    stage.classList.add('lx-hero-on');

    const figs = $$('.lx-telon-capa', raiz);
    const botones = $$('.lx-hero-indice button', raiz);
    const linea = $('.lx-hero-linea i', raiz);
    const cargar = i => {
      const img = $('img', figs[i]);
      if (img && img.dataset.src) { img.srcset = img.dataset.srcset; img.src = img.dataset.src; delete img.dataset.src; }
    };
    let actual = 0, temporizador = null, visible = true;
    const ir = i => {
      if (i === actual) return;
      cargar(i); cargar((i + 1) % figs.length);
      figs.forEach(f => f.classList.remove('is-entrando', 'is-saliendo'));
      figs[actual].classList.remove('is-activa');
      if (!reducido()) figs[actual].classList.add('is-saliendo');
      figs[i].classList.add('is-activa');
      if (!reducido()) figs[i].classList.add('is-entrando');
      botones.forEach((b, k) => b.setAttribute('aria-pressed', String(k === i)));
      actual = i;
      progreso();
    };
    // Al terminar el telón, la imagen pasa al acercamiento lento.
    figs.forEach(f => f.addEventListener('animationend', e => {
      if (e.target === f && f.classList.contains('is-entrando')) f.classList.remove('is-entrando');
    }));
    const progreso = () => {
      linea.classList.remove('is-corriendo'); void linea.offsetWidth;
      if (temporizador) linea.classList.add('is-corriendo');
    };
    const arrancar = () => {
      clearInterval(temporizador); temporizador = null;
      if (reducido() || !visible || document.hidden) return progreso();
      temporizador = setInterval(() => ir((actual + 1) % HERO.length), HERO_MS);
      progreso();
    };
    botones.forEach(b => b.addEventListener('click', () => { ir(+b.dataset.i); arrancar(); }));
    $('.lx-hero-ir', raiz).addEventListener('click', () => {
      const destino = $('.fg-filter-bar') || $('#main-unit-reserve-list');
      if (destino) window.scrollTo({ top: destino.getBoundingClientRect().top + scrollY - 100, behavior: reducido() ? 'auto' : 'smooth' });
    });
    new IntersectionObserver(([e]) => { visible = e.isIntersecting; arrancar(); }, { threshold: .2 }).observe(raiz);
    document.addEventListener('visibilitychange', arrancar);
    cargar(1);
    arrancar();
  }

  /* ── 2. LA HOJA ─────────────────────────────────────────────────────────
     La transición 1 de Bahía Mar: el hero queda fijo y la lista sube encima.
     El JS solo escribe --lx-p (0→1 según lo bajado sobre el alto del hero) y
     la altura del header (--lx-cabecera), para no colocar nada debajo de él;
     toda la forma vive en el CSS. */
  function hoja() {
    document.documentElement.classList.add('lx-hoja');
    const stage = $('.fg-stage');
    const cab = $('nav');
    const medirCab = () => {
      const r = cab && cab.getBoundingClientRect();
      if (r && r.height) document.documentElement.style.setProperty('--lx-cabecera', Math.round(r.bottom) + 'px');
    };
    medirCab();
    addEventListener('resize', medirCab);
    if (!stage || reducido()) return;
    let pendiente = false;
    const medir = () => {
      pendiente = false;
      const p = Math.min(1, Math.max(0, scrollY / Math.max(1, stage.offsetHeight)));
      stage.style.setProperty('--lx-p', p.toFixed(3));
    };
    addEventListener('scroll', () => { if (!pendiente) { pendiente = true; requestAnimationFrame(medir); } }, { passive: true });
    addEventListener('resize', medir);
    medir();
  }

  /* El pie no se queda debajo de lo fijo (la barra Grid/List y el WhatsApp):
     reserva abajo lo que ocupen (--lx-pie-hueco). Igual que en Bahía Mar. */
  function hueco() {
    const raiz = document.documentElement;
    let medir = null;
    const calc = () => {
      medir = null;
      let alto = 0;
      for (const s of ['.fg-toggle-bar', '.fg-wa-float']) {
        const e = $(s);
        if (!e || getComputedStyle(e).position !== 'fixed') continue;
        const r = e.getBoundingClientRect();
        if (r.height && r.bottom > innerHeight * .6) alto = Math.max(alto, innerHeight - r.top);
      }
      const v = alto ? Math.round(alto + 16) + 'px' : '0px';
      if (raiz.style.getPropertyValue('--lx-pie-hueco') !== v) raiz.style.setProperty('--lx-pie-hueco', v);
    };
    const pedir = () => { if (!medir) medir = requestAnimationFrame(calc); };
    addEventListener('resize', pedir);
    addEventListener('scroll', pedir, { passive: true });
    calc();
  }

  /* ── 3. TARJETAS DE LAS UNIDADES ─────────────────────────────────────────
     La estructura de Bahía Mar (las maquetas de Miguel), sin sus datos de
     ejemplo y con lo que Siebe sí tiene:
       foto con estado · «Residence» + número · «N bedroom(s) · planta» ·
       precio y $/m² · reserva · medidas con icono · dos acciones.
     Siebe tiene terraza en todas: va en las medidas en vez de los parqueos
     (hoy 0 en las 18). El área total, en la ficha. */
  const ESTADO = {
    available: t('Disponible', 'Available'),
    reserved:  t('Reservada', 'Reserved'),
    sold:      t('Vendida', 'Sold'),
    pending:   t('Pendiente', 'Pending'),
  };
  const UNIDAD = t('Residencia', 'Residence');
  const PLANTA = { '1st': t('1.ª planta', '1st floor'), '2nd': t('2.ª planta', '2nd floor'), '3rd': t('3.ª planta', '3rd floor'), '4th': t('4.ª planta', '4th floor') };
  const planta = p => PLANTA[p] || (p ? t(`Planta ${p}`, `${p} floor`) : '');
  const habitaciones = n => (Number(n) === 1 ? t('1 habitación', '1 bedroom') : t(`${n} habitaciones`, `${n} bedrooms`));

  function valorStat(card, titulo) {
    const s = $(`.fg-stat[title="${titulo}"] .v`, card);
    return s ? s.textContent.replace(/m2$|m²$/, '').trim() : '';
  }

  function boton(texto, clase, accion) {
    const b = el('button', clase, texto);
    b.type = 'button';
    b.addEventListener('click', e => { e.stopPropagation(); accion(); });
    return b;
  }

  function cards() {
    $$('.fg-units-grid > .fg-card').forEach(card => {
      if (card.dataset.lx) return;
      card.dataset.lx = '1';
      const d = card.dataset;
      const id = d.filterUnit;
      const estado = d.filterStatus || 'available';
      card.dataset.lxEstado = estado;

      // Foto: el estado en un chip discreto.
      const img = $('.fg-card-img', card);
      if (img) {
        const chips = el('div', 'lx-chips');
        chips.appendChild(el('span', `lx-chip lx-chip--${estado}`, ESTADO[estado] || estado));
        img.appendChild(chips);
        if (estado === 'sold') img.appendChild(el('span', 'lx-sello', ESTADO.sold));
      }

      // Nombre: «Residence» pequeño y el número grande.
      const nombre = $('.fg-card-title-row .name', card);
      if (nombre) {
        const num = nombre.textContent.trim();
        nombre.innerHTML = `<span class="lx-unidad">${UNIDAD}</span> <span class="lx-num">${num}</span>`;
      }
      const sub = $('.fg-card-subtitle', card);
      if (sub && d.filterBedrooms) sub.textContent = [habitaciones(d.filterBedrooms), planta(d.filterFloor)].filter(Boolean).join(' · ');

      // Reserva desde $5,000: la condición real del banner, bajo el precio.
      const banner = $('.fg-reserve-banner', card);
      const precio = $('.fg-card-price', card);
      if (banner && precio && estado === 'available') precio.after(el('div', 'lx-reserva', banner.textContent.trim()));

      // Medidas con icono y etiqueta, en vez de seis iconos sin nombre.
      const head = $('.fg-card-head', card);
      if (head) {
        const m2 = v => v && `${v}<small>m²</small>`;
        const filas = [
          ['hab', t('Hab.', 'Beds'), valorStat(card, 'Bedrooms')],
          ['banos', t('Baños', 'Baths'), valorStat(card, 'Bathrooms').replace(/\.0$/, '')],
          ['interior', t('Interior', 'Interior'), m2(valorStat(card, 'Internal area'))],
          ['terraza', t('Terraza', 'Terrace'), m2(valorStat(card, 'External area'))],
        ].filter(([, , v]) => v && v !== '0' && v !== '—');
        const dl = el('dl', 'lx-specs');
        filas.forEach(([i, k, v]) => dl.appendChild(el('div', null, `${svg(i)}<dt>${k}</dt><dd>${v}</dd>`)));
        head.after(dl);
      }

      // Acciones por estado (las de Bahía Mar).
      const acciones = $('.fg-card-buttons', card);
      if (acciones) {
        acciones.replaceChildren();
        if (estado === 'available') {
          acciones.append(
            boton(t('Agendar videollamada', 'Book a video call'), 'lx-btn lx-btn--lleno', () => openAdvisorVideoCall(id)),
            boton(t('Ver propiedad', 'View property'), 'lx-btn', () => openMoreInfo(id)));
        } else if (estado === 'reserved') {
          acciones.append(
            boton(t('Avísame si se libera', 'Notify me if released'), 'lx-btn lx-btn--lleno', () => registro.abrir('aviso', id)),
            boton(t('Ver propiedad', 'View property'), 'lx-btn', () => openMoreInfo(id)));
        } else {
          acciones.append(
            boton(t('Avísame de reventas', 'Resale alerts'), 'lx-btn lx-btn--lleno', () => registro.abrir('aviso', id)),
            boton(t('Ver propiedad', 'View property'), 'lx-btn', () => openMoreInfo(id)));
        }
      }
    });
  }

  // Fotos de las tarjetas: hueco reservado (aspect-ratio + fondo) y fundido
  // al cargar, en vez de aparecer de golpe.
  function fotosSuaves() {
    $$('.fg-units-grid > .fg-card .fg-card-img > img:not([data-lx-img])').forEach(img => {
      img.dataset.lxImg = '1';
      if (img.complete && img.naturalWidth) return;
      img.classList.add('lx-img-espera');
      const listo = () => img.classList.remove('lx-img-espera');
      img.addEventListener('load', listo, { once: true });
      img.addEventListener('error', listo, { once: true });
    });
  }

  /* ── 4. REGISTRO: ¿COMPRADOR O BROKER? ──────────────────────────────────
     El de Bahía Mar. Sustituye al «Welcome to Siebe Residences» y a las
     redirecciones a /login del anónimo (reservar, agendar, guardar).
     Pregunta primero quién es, porque cada uno va a un área distinta, y lleva
     a su formulario. En el clon no hay backend: el flujo es visual y termina
     en /register con ?perfil=. */
  const registro = (function () {
    const TX = {
      titulo: t('Acceso privado', 'Private access'),
      intro: t('La disponibilidad completa, los planos de cada residencia y la reserva en línea están disponibles con una cuenta. ¿Cómo quiere continuar?',
               'Full availability, each residence’s floor plans and online booking are available with an account. How would you like to continue?'),
      cliente: [t('Soy comprador', 'I am a buyer'), t('Busco una residencia para mí o mi familia.', 'I am looking for a residence for myself or my family.')],
      broker: [t('Soy broker', 'I am a broker'), t('Represento a clientes e inversionistas.', 'I represent clients and investors.')],
      ya: t('Ya tengo cuenta', 'I already have an account'),
      volver: t('Volver', 'Back'),
      aviso: t('Le avisaremos por correo en cuanto haya novedades de esta residencia.', 'We will email you as soon as there is news about this residence.'),
      campos: {
        cliente: [[t('Nombre y apellidos', 'Full name'), 'text', 'name'], [t('Correo electrónico', 'Email'), 'email', 'email'], [t('Teléfono', 'Phone'), 'tel', 'tel']],
        broker: [[t('Nombre y apellidos', 'Full name'), 'text', 'name'], [t('Agencia o empresa', 'Agency or company'), 'text', 'organization'], [t('Correo profesional', 'Work email'), 'email', 'email'], [t('Teléfono', 'Phone'), 'tel', 'tel']],
      },
      tituloForm: { cliente: t('Cuenta de comprador', 'Buyer account'), broker: t('Cuenta de broker', 'Broker account') },
      notaForm: {
        cliente: t('Accederá a los planos, la disponibilidad en tiempo real y la reserva en línea.', 'You will get floor plans, live availability and online booking.'),
        broker: t('Accederá al área de brokers: disponibilidad, fichas para compartir con sus clientes y seguimiento de sus reservas.', 'You will get the broker area: availability, sheets to share with your clients and tracking of your reservations.'),
      },
      crear: t('Crear cuenta', 'Create account'),
    };
    let modal, intent = null, unidad = null;

    function construir() {
      modal = el('div', 'lx-reg');
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-labelledby', 'lxRegTitulo');
      modal.innerHTML = `
        <div class="lx-reg-fondo" data-cerrar></div>
        <div class="lx-reg-caja">
          <button type="button" class="lx-reg-cerrar" data-cerrar aria-label="${t('Cerrar', 'Close')}">×</button>
          <div class="lx-reg-paso" data-paso="elegir">
            <img class="lx-reg-marca" src="/lujo/img/sr-logo.svg" alt="Siebe Residences" width="131" height="61">
            <h2 id="lxRegTitulo" class="lx-reg-titulo">${TX.titulo}</h2>
            <p class="lx-reg-intro" data-intro>${TX.intro}</p>
            <div class="lx-reg-opciones">
              <button type="button" class="lx-reg-opcion" data-perfil="cliente"><b>${TX.cliente[0]}</b><span>${TX.cliente[1]}</span></button>
              <button type="button" class="lx-reg-opcion" data-perfil="broker"><b>${TX.broker[0]}</b><span>${TX.broker[1]}</span></button>
            </div>
            <a class="lx-enlace lx-reg-ya" href="/login">${TX.ya}</a>
          </div>
          <form class="lx-reg-paso" data-paso="form" hidden novalidate>
            <button type="button" class="lx-reg-volver" data-volver>← ${TX.volver}</button>
            <h2 class="lx-reg-titulo" data-titulo-form></h2>
            <p class="lx-reg-intro" data-nota-form></p>
            <div class="lx-reg-campos"></div>
            <button type="submit" class="lx-btn lx-btn--lleno lx-reg-enviar">${TX.crear}</button>
          </form>
        </div>`;
      document.body.appendChild(modal);
      modal.addEventListener('click', e => {
        if (e.target.closest('[data-cerrar]')) cerrar();
        const op = e.target.closest('[data-perfil]');
        // Directo al registro con el rol ya elegido. Antes abria un formulario
        // aqui dentro que acababa en /register igualmente, o sea que pedia los
        // datos dos veces (Kary, 21 oct).
        if (op) location.href = '/register?perfil=' + encodeURIComponent(op.dataset.perfil);
        if (e.target.closest('[data-volver]')) paso('elegir');
      });
      $('form', modal).addEventListener('submit', e => {
        e.preventDefault();
        const q = new URLSearchParams({ perfil: $('form', modal).dataset.perfil });
        if (intent) q.set('intent', intent);
        if (unidad) q.set('unit', unidad);
        location.href = '/register?' + q;
      });
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('is-open')) cerrar(); });
    }

    function paso(nombre) {
      $$('.lx-reg-paso', modal).forEach(p => { p.hidden = p.dataset.paso !== nombre; });
      const foco = $(`[data-paso="${nombre}"] button, [data-paso="${nombre}"] input`, modal);
      if (foco) foco.focus();
    }

    function formulario(perfil) {
      const f = $('form', modal);
      f.dataset.perfil = perfil;
      $('[data-titulo-form]', f).textContent = TX.tituloForm[perfil];
      $('[data-nota-form]', f).textContent = TX.notaForm[perfil];
      $('.lx-reg-campos', f).innerHTML = TX.campos[perfil].map(([label, tipo, auto], n) =>
        `<label class="lx-campo"><span>${label}</span><input type="${tipo}" name="c${n}" autocomplete="${auto}"></label>`).join('');
      paso('form');
    }

    function abrir(motivo, id) {
      if (!modal) construir();
      intent = motivo || null;
      unidad = id || null;
      $('[data-intro]', modal).textContent = motivo === 'aviso' ? TX.aviso + ' ' + TX.intro : TX.intro;
      paso('elegir');
      modal.classList.add('is-open');
      document.documentElement.classList.add('lx-sin-scroll');
      if (typeof window.closeGuestModal === 'function') window.closeGuestModal();
    }
    function cerrar() {
      modal.classList.remove('is-open');
      document.documentElement.classList.remove('lx-sin-scroll');
    }
    return { abrir, cerrar };
  })();

  /* Todo lo que en el original manda al anónimo a /login pasa por aquí. */
  // `currentOpenUnit` es un `let` global del sitio: existe, pero no en window.
  const abierta = () => (typeof currentOpenUnit !== 'undefined' ? currentOpenUnit : null);
  function interceptarAcceso() {
    // Con muro (?clon-con-muro, el recorrido del anónimo) el popup del sitio
    // pasa a ser este. Sin muro se queda mudo, como lo deja servir.py.
    if (document.body.hasAttribute('data-guest')) window.openGuestModal = () => registro.abrir();
    window.openReservePage = id => registro.abrir('reserve', id || abierta());
    window.openAdvisorVideoCall = id => registro.abrir('videocall', id || abierta());
    window.toggleModalWishlist = () => registro.abrir('wishlist', abierta());
    document.addEventListener('click', e => {
      const a = e.target.closest('a[href^="/login"], a[href^="/register"], [data-wishlist-toggle]');
      if (!a || a.closest('.lx-reg') || a.closest('nav')) return;   // el header es de Urbatrix
      e.preventDefault();
      e.stopImmediatePropagation();
      registro.abrir(a.matches('[data-wishlist-toggle]') ? 'wishlist' : null, a.dataset.unitId);
    }, true);
  }

  /* ── 5. FICHA DE LA UNIDAD (popup #moreInfoModal) ──────────────────────
     La última versión de la de Bahía Mar. La columna izquierda del original
     mezcla datos de la unidad con bloques que no salen de ella: «Unlock
     $20,000 Discount», «12% below Aruba avg.» y el «$450/m²» (texto fijo,
     no corresponde a ninguna superficie). Se queda lo que es de la unidad:
     nombre, precio (con el mismo $/m² que la tarjeta, sobre el interior),
     condiciones de reserva, el simulador de pagos de Urbatrix (sale del
     precio real) y los datos con etiqueta e icono. La descripción solo si
     es un texto (hoy el admin tiene «T1», el código del plano). */
  const FICHA = {
    eyebrow: u => [UNIDAD, planta(u.floor)].filter(Boolean).join(' · '),
    m2int: t('m² interiores', 'interior m²'),
    reserva: t('Reserva', 'Reservation'),
    datos: t('La residencia', 'The residence'),
    plan: t('Plan de pagos', 'Payment plan'),
    filas: u => [
      ['hab', t('Habitaciones', 'Bedrooms'), u.bedrooms],
      ['banos', t('Baños', 'Bathrooms'), u.bathrooms && Number(u.bathrooms)],
      ['planta', t('Planta', 'Floor'), planta(u.floor)],
      ['tipo', t('Plano', 'Layout'), u.layout],
      ['rasgo', t('Tipo', 'Type'), u.custom_1],
      ['interior', t('Interior', 'Interior'), m2(u.internal_area)],
      ['terraza', t('Terraza', 'Terrace'), m2(u.external_area)],
      ['total', t('Total', 'Total'), m2(u.total_area)],
    ].filter(([, , v]) => v && v !== '0' && Number(v) !== 0),
  };
  const num = (v, dec = 0) => Number(v).toLocaleString(ES ? 'es-ES' : 'en-US', { maximumFractionDigits: dec });
  const m2 = v => (Number(v) > 0 ? `${num(v, 2)} m²` : null);

  function pintarFicha(u) {
    const modal = $('#moreInfoModal');
    const izq = $('.mt-left-inner', modal);
    if (!izq) return;
    modal.dataset.lxEstado = (u.status || 'available').toLowerCase();
    let caja = $('.lx-ficha', izq);
    if (!caja) {
      caja = el('div', 'lx-ficha');
      izq.prepend(caja);
    }
    const ppm = Number(u.internal_area) > 0 ? Math.round(Number(u.price) / Number(u.internal_area)) : 0;
    // Las condiciones de reserva salen del propio sitio (ya traducidas).
    const condRes = $('#moreInfoModal .mt-price-meta .warning');
    const condNo = condRes && condRes.parentElement.querySelector('.muted');
    const estado = (u.status || 'available').toLowerCase();
    // Antes de reescribir la caja, los nodos del sitio que viven dentro
    // (precio, monedas, simulador) vuelven a su sitio; si no, se irían con el
    // innerHTML de la unidad anterior.
    const fila = $('.mt-price-row', modal);
    [$('#modalPrice'), $('#moreInfoModal .mt-currency-toggle')].forEach(n => { if (n && fila && caja.contains(n)) fila.appendChild(n); });
    $$('.ps-teaser, .ps-acc', caja).forEach(n => izq.appendChild(n));
    const desc = (u.description || '').trim();
    caja.innerHTML = `
      <p class="lx-eyebrow">${FICHA.eyebrow(u)}</p>
      <h2 class="lx-ficha-nombre">${UNIDAD} ${u.name || u.id}</h2>
      <div class="lx-ficha-precio" data-slot-precio></div>
      ${ppm && estado !== 'sold' ? `<p class="lx-ficha-ppm">$${num(ppm)} / ${FICHA.m2int}</p>` : ''}
      ${condRes && estado === 'available' ? `<div class="lx-ficha-reserva">
          <span class="lx-eyebrow">${FICHA.reserva}</span>
          <p><b>${condRes.textContent.trim()}</b>${condNo ? `<span>${condNo.textContent.trim()}</span>` : ''}</p>
        </div>` : ''}
      <div class="lx-ficha-plan" data-slot-plan></div>
      ${desc.length > 24 ? `<p class="lx-ficha-desc">${desc}</p>` : ''}
      <p class="lx-eyebrow lx-ficha-sub">${FICHA.datos}</p>
      <dl class="lx-ficha-datos">${FICHA.filas(u).map(([i, k, v]) => `<div><dt>${svg(i)}${k}</dt><dd>${v}</dd></div>`).join('')}</dl>`;
    // El precio, el selector de moneda y el simulador son los nodos del
    // sitio, movidos: así siguen respondiendo sin duplicar lógica.
    const slot = $('[data-slot-precio]', caja);
    const precio = $('#modalPrice');
    const monedas = $('#moreInfoModal .mt-currency-toggle');
    if (estado === 'sold') slot.innerHTML = `<span class="lx-ficha-vendida">${ESTADO.sold}</span>`;
    else { if (precio) slot.appendChild(precio); if (monedas) slot.appendChild(monedas); }
    const plan = $('[data-slot-plan]', caja);
    const teaser = $('.ps-teaser', izq), acc = $('.ps-acc', izq);
    if (estado === 'available' && (teaser || acc)) { if (teaser) plan.appendChild(teaser); if (acc) plan.appendChild(acc); }
    else plan.remove();
    accionAlterna(modal, estado, u);
  }

  /* Reservada o vendida: en vez de «Reserved»/«Sold» desactivado, una salida
     (avísame). El botón del sitio se oculta por CSS. */
  function accionAlterna(modal, estado, u) {
    const fila = $('.mt-cta-row', modal);
    if (!fila) return;
    $$('.lx-cta-alt', fila).forEach(n => n.remove());
    if (estado === 'available') return;
    const texto = estado === 'reserved' ? t('Avísame si se libera', 'Notify me if released') : t('Avísame de reventas', 'Resale alerts');
    fila.prepend(boton(texto, 'lx-cta-alt mt-btn-secondary', () => registro.abrir('aviso', u.id)));
  }

  /* Galería: autoplay cada 6 s y fundido entre fotos. Se para al pasar el
     ratón, al tocar flechas o miniaturas y con prefers-reduced-motion. El
     cambio de foto lo sigue haciendo el sitio (nextModalImg); aquí solo se
     pone la foto anterior encima y se desvanece. */
  const AUTOPLAY_MS = 6000;
  let autoplay = null, pausada = false;
  function galeria() {
    const g = $('#moreInfoModal .mt-gallery');
    const img = $('#modalMainImg');
    if (!g || !img || g.dataset.lx) return;
    g.dataset.lx = '1';
    const velo = el('img', 'lx-velo');
    velo.alt = '';
    velo.setAttribute('aria-hidden', 'true');
    img.after(velo);
    g.appendChild(el('span', 'lx-progreso'));
    new MutationObserver(ms => {
      const antes = ms[0].oldValue;
      if (!antes || antes === img.getAttribute('src') || reducido()) return;
      velo.src = antes;
      velo.classList.toggle('is-plan', /\/planos\/|Floorplan|Layout/i.test(antes));
      velo.classList.remove('is-fuera');
      void velo.offsetWidth;
      const fundir = () => velo.classList.add('is-fuera');
      if (img.complete) requestAnimationFrame(fundir); else img.addEventListener('load', fundir, { once: true });
    }).observe(img, { attributes: true, attributeFilter: ['src'], attributeOldValue: true });
    g.addEventListener('mouseenter', () => { pausada = true; g.classList.add('is-pausada'); });
    g.addEventListener('mouseleave', () => { pausada = false; g.classList.remove('is-pausada'); reiniciarBarra(); });
    $$('.mt-arrow, .mt-zoom-hint', g).forEach(b => b.addEventListener('click', parar));
    $('#mtThumbs') && $('#mtThumbs').addEventListener('click', parar);
    // Property / Amenities / Plans encima de la foto, como un switcher. Se
    // mueve el mismo nodo (#mtCatToggle): el sitio lo busca por id y sus
    // onclick siguen igual. El activo se anuncia con aria-pressed.
    const cat = $('#mtCatToggle');
    if (cat) {
      g.appendChild(cat);
      const pill = $('.mt-cat-pill', cat);
      if (pill) {
        pill.setAttribute('role', 'group');
        pill.setAttribute('aria-label', t('Galería', 'Gallery'));
        const marcar = () => $$('.mt-cat-btn', cat).forEach(b => b.setAttribute('aria-pressed', String(b.classList.contains('active'))));
        marcar();
        new MutationObserver(marcar).observe(pill, { subtree: true, attributes: true, attributeFilter: ['class'] });
      }
      $$('.mt-cat-btn', cat).forEach(b => b.addEventListener('click', reiniciarBarra));
    }
  }
  function reiniciarBarra() {
    const b = $('#moreInfoModal .lx-progreso');
    if (!b) return;
    b.classList.remove('is-corriendo'); void b.offsetWidth;
    if (autoplay && !pausada) b.classList.add('is-corriendo');
  }
  function arrancarGaleria() {
    parar();
    if (reducido()) return;
    autoplay = setInterval(() => {
      const abierto = $('#moreInfoModal') && $('#moreInfoModal').style.display !== 'none';
      if (!abierto) return parar();
      if (pausada || document.hidden || $('#imgZoomOverlay.open')) return;
      if (typeof nextModalImg === 'function') { nextModalImg(); reiniciarBarra(); }
    }, AUTOPLAY_MS);
    reiniciarBarra();
  }
  function parar() {
    clearInterval(autoplay); autoplay = null;
    const b = $('#moreInfoModal .lx-progreso');
    if (b) b.classList.remove('is-corriendo');
  }

  function envolverFicha() {
    const original = window.openMoreInfo;
    if (typeof original !== 'function' || original.lx) return;
    const envuelta = function (id) {
      const r = original.apply(this, arguments);
      galeria();
      fetch(`/api/units/${encodeURIComponent(id)}`, { headers: { Accept: 'application/json' } })
        .then(res => (res.ok ? res.json() : null))
        .then(u => { if (u) { pintarFicha(u); arrancarGaleria(); } })
        .catch(() => {});
      return r;
    };
    envuelta.lx = true;
    window.openMoreInfo = envuelta;
    const cerrar = window.closeMoreInfo;
    if (typeof cerrar === 'function') window.closeMoreInfo = function () { parar(); return cerrar.apply(this, arguments); };
  }

  /* Error del original: Escape llama a closeReservePage(), que da por hecho
     que existe #reservePage; en el home de Siebe no está, así que lanza un
     TypeError y corta el resto de cierres del mismo atajo (menú, proyectos,
     perfil). Se hace tolerante. En el Blade bastaría un `if (!el) return;`. */
  function escapeSeguro() {
    const original = window.closeReservePage;
    if (typeof original !== 'function' || original.lx) return;
    const segura = function () { if (document.getElementById('reservePage')) return original.apply(this, arguments); };
    segura.lx = true;
    window.closeReservePage = segura;
  }

  /* El contador de la barra de filtros está escrito en español en el Blade
     («Mostrando X de Y unidades»), también con la página en inglés. Se
     traduce aquí; en el Blade iría con __(). Idempotente. */
  function contador() {
    if (ES) return;
    $$('.fg-pill-matches').forEach(p => {
      const n = [...p.childNodes].find(x => x.nodeType === 3 && /Mostrando\s+\d+\s+de\s+\d+/.test(x.textContent));
      if (n) n.textContent = n.textContent.replace(/Mostrando\s+(\d+)\s+de\s+(\d+)\s+unidades/, 'Showing $1 of $2 residences');
    });
  }

  /* ── ARRANQUE ───────────────────────────────────────────────────────────── */
  function refrescar() {
    cards();
    fotosSuaves();
    contador();
  }
  window.lujo = { refrescar, registro };

  function iniciar() {
    document.documentElement.classList.add('lx');
    interceptarAcceso();
    escapeSeguro();
    envolverFicha();
    hero();
    hoja();
    refrescar();
    hueco();
    const obs = new MutationObserver(() => {
      clearTimeout(iniciar.t);
      iniciar.t = setTimeout(refrescar, 60);
    });
    const zona = $('#main-unit-reserve-list');
    if (zona) obs.observe(zona, { childList: true, subtree: true });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
