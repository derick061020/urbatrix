/* ============================================================================
   CAPA DE MARCA «LUJO» — la parte de comportamiento (ver lujo.css)
   ============================================================================

   Solo añade o reordena lo que ya pinta el sitio; no pide nada nuevo al
   backend. Todo lo que muestra sale de:
     · el HTML del propio sitio (cards, bandas, cabecera),
     · /api/units/{id} (la ficha), que el sitio ya pide,
     · TEXTOS (abajo), que son copy de interfaz o copy real del cliente.

   No inventa cifras. Lo que no tiene dato, no se pinta.

   Se resuscribe a los re-renders del grid con un MutationObserver, igual que
   la capa eco. Si el motor de render tiene un hook propio, llama a
   window.lujo.refrescar() desde ahí y quita el observer del final.
   ========================================================================== */
(function () {
  'use strict';

  const ES = (document.documentElement.lang || 'en').toLowerCase().startsWith('es');
  const t = (es, en) => (ES ? es : en);

  /* ── MEDIOS PENDIENTES ──────────────────────────────────────────────────
     Miguel va a cambiar la galería y hay un vídeo nuevo en camino. No se
     inventan: cuando lleguen, se rellena aquí y aparecen solos.
       heroVideo   → vídeo de la primera imagen del hero (mp4/webm), p. ej.
                     el del Mercedes clásico. Va encima de esa imagen, que
                     queda de póster.
       villaVideo  → por modelo (T1…T5): añade «Vídeo» a la galería de la
                     ficha, como primer elemento.
     Las fotos de la galería no van aquí: salen de /api/units/{id}.images, o
     sea del admin de Urbatrix. */
  const MEDIOS = Object.assign({
    heroVideo: null,          // p. ej. '/lujo/video/bahia-mar.mp4'
    heroPoster: null,
    villaVideo: { T1: null, T2: null, T3: null, T4: null, T5: null },
  }, window.LUJO_MEDIOS || {});

  /* ── TEXTOS ─────────────────────────────────────────────────────────────
     Las frases por tipología son las de las fichas «Tipos de villas» que el
     cliente pasó por WhatsApp, las mismas que usa la landing
     (clon-era-residence/data/villas.ts y villas.es.ts). No son nuevas. */
  const FRASES = {
    T1: t('Arquitectura de gran presencia, para una vida sin límites.', 'Architecture of great presence, for a life without limits.'),
    T2: t('La elegancia en su forma más esencial.', 'Elegance in its most essential form.'),
    T3: t('Todo el privilegio de Bahía Mar, en un solo nivel.', 'All the privilege of Bahía Mar, on a single level.'),
    T4: t('La serenidad del Caribe, hecha arquitectura.', 'The serenity of the Caribbean, made architecture.'),
    T5: t('Dos niveles abiertos a un horizonte extraordinario.', 'Two levels open to an extraordinary horizon.'),
  };

  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const el = (tag, cls, html) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  };
  const reducido = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* La planta llega como el valor crudo de la base ('1st', '2nd', 'ground'):
     en estos proyectos se cargó en inglés y se colaba sin traducir en la
     página en español. Se arma a partir del número, así sale igual en los dos
     idiomas y no depende de cómo esté puesta la etiqueta en el admin. */
  const planta = v => {
    const x = String(v == null ? '' : v).trim();
    if (!x) return '';
    if (/^(ground|pb)$/i.test(x)) return t('Planta baja', 'Ground Floor');
    const n = (x.match(/\d+/) || [])[0];
    return n ? t(`Piso ${n}`, `Floor ${n}`) : x;
  };

  /* ── 1. CABECERA: disponibilidad en vez de «0 of 233 units sold» ─────────
     Mismo dato que el sitio (vendidas y total), contado al revés: a un broker
     le interesa cuántas quedan. */
  function cabecera() {
    const span = $('.nav-center > span:first-child');
    if (!span || span.dataset.lx) return;
    const m = span.textContent.match(/(\d[\d.,]*)\D+(\d[\d.,]*)/);
    if (!m) return;
    const vendidas = parseInt(m[1].replace(/\D/g, ''), 10);
    const total = parseInt(m[2].replace(/\D/g, ''), 10);
    const libres = total - vendidas;
    span.dataset.lx = '1';
    span.innerHTML = t(`<b>${libres}</b> de ${total} residencias disponibles`, `<b>${libres}</b> of ${total} residences available`);
  }

  /* ── 2. BANDAS DE TIPOLOGÍA ─────────────────────────────────────────────── */
  function bandas() {
    $$('.fg-type-band').forEach(band => {
      if (band.dataset.lx) return;
      band.dataset.lx = '1';
      const body = $('.fg-card-body', band);
      const nombre = $('.fg-card-title-row', band);
      const frase = FRASES[band.dataset.model];
      if (body && nombre && frase) nombre.after(el('p', 'lx-tagline', frase));
      const inner = $('.fg-card-inner', band);
      if (inner) inner.appendChild(el('div', 'lx-mar')).setAttribute('aria-hidden', 'true');
    });
  }

  /* ── 2b. PLANTAS EN VÍDEO (las de la landing) ───────────────────────────
     Los tres clips de hojas de bahiamarterrenas.com (los únicos que usa la
     landing; los de buganvilla del repo son restos de ERA), recodificados a
     440–480 px y 20 fps: 184, 209 y 387 KB (los originales, 0,7 a 2,2 MB).
     Una por banda, alternando clip y esquina —arriba o abajo a la derecha
     del panel claro, el lado contrario al texto—, para que ninguna se lea
     como la anterior repetida. Ni texto, ni precio ni botones debajo.
     · No se descargan hasta que la banda está cerca (preload="none" + IO) y
       se pausan al salir de pantalla.
     · Con prefers-reduced-motion, en Safari (no pinta el alfa del WebM) y
       en móvil se pone la imagen fija del mismo clip (40–53 KB). */
  const PLANTAS = {
    T1: ['01', 'arriba'], T2: ['02', 'abajo'], T3: ['03', 'arriba'],
    T4: ['01', 'abajo'],  T5: ['02', 'arriba'],
  };
  const alfaWebm = () => !!document.createElement('video').canPlayType('video/webm; codecs="vp9"')
    && !/^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent);
  let ioPlantas;
  function plantas() {
    $$('.fg-type-band').forEach(band => {
      const [clip, esquina] = PLANTAS[band.dataset.model] || [];
      const inner = $('.fg-card-inner', band);
      if (!clip || !inner || $('.lx-planta', inner)) return;
      plantaEn(inner, clip, esquina);
    });
  }
  function plantaEn(destino, clip, esquina = 'arriba') {
    {
      const inner = destino;
      const caja = el('div', `lx-planta lx-planta--${clip} lx-planta--${esquina}`);
      caja.setAttribute('aria-hidden', 'true');
      const base = `/lujo/plantas/hojas-${clip}`;
      if (reducido() || !alfaWebm() || window.innerWidth <= 900) {
        const img = el('img');
        Object.assign(img, { src: base + '.webp', alt: '', loading: 'lazy', decoding: 'async' });
        caja.appendChild(img);
      } else {
        const v = el('video');
        Object.assign(v, { muted: true, loop: true, playsInline: true, preload: 'none', poster: base + '.webp' });
        v.setAttribute('muted', '');
        v.dataset.src = base + '.webm';
        caja.appendChild(v);
        ioPlantas = ioPlantas || new IntersectionObserver(entradas => entradas.forEach(e => {
          const vid = e.target;
          if (e.isIntersecting) {
            if (!vid.src) vid.src = vid.dataset.src;
            vid.play().catch(() => {});
          } else vid.pause();
        }), { rootMargin: '200px 0px' });
        ioPlantas.observe(v);
      }
      inner.appendChild(caja);
    }
  }

  /* ── 2c. VARIANTE B DE LAS TIPOLOGÍAS: PORTADAS ────────────────────────
     La A (foto y panel mitad y mitad, alternando) se queda tal cual y es la
     de por defecto. La B se ve con ?variante=b o en el panel «Propuestas».
     (La primera B, un índice de las cinco en una sola sección, se descartó
     en la ronda 3: cada tipología tiene que tener su propia fila.)
     Composición: una fila por tipología, la foto a sangre ocupando la
     pantalla, y el texto, el precio y los botones en una ficha de papel
     posada sobre una esquina de abajo —izquierda y derecha alternando—, con
     la planta asomando por encima de la ficha. Como una portada: la villa
     manda y la ficha es el pie de foto.
     No crea nada: mete en .lx-portada los mismos nodos que pinta el sitio
     (cuerpo, precio, botones) y la planta. Idempotente, porque el
     MutationObserver vuelve a llamar a refrescar() tras cada cambio. */
  function portadas() {
    if (!document.documentElement.classList.contains('lx-var-b')) return;
    $$('.fg-type-band').forEach(band => {
      const inner = $('.fg-card-inner', band);
      if (!inner || $('.lx-portada', inner)) return;
      const ficha = el('div', 'lx-portada');
      ['.fg-card-body', '.fg-card-price', '.fg-card-actions', '.lx-planta']
        .forEach(s => { const n = $(s, inner); if (n) ficha.appendChild(n); });
      inner.appendChild(ficha);
      fotoPortada(band);
    });
  }
  /* Fotos de las portadas: outpainting de cada render con la villa intacta
     (herramientas/portadas.py, comprobación en lujo/portadas/codex/). En
     escritorio la casa queda en el lado contrario a la ficha; en móvil, en
     el 65 % de arriba. Si falta la de una tipología, se queda la del sitio. */
  const FOTOS = { T1: 'horizonte', T2: 'esencia', T3: 'armonia', T4: 'ambar', T5: 'remanso' };
  function fotoPortada(band) {
    const nombre = FOTOS[band.dataset.model];
    const img = $('.fg-card-img img', band);
    if (!nombre || !img || img.closest('picture')) return;
    const base = `/lujo/portadas/${nombre}`;
    if (img.dataset.lxPortada) return;
    img.dataset.lxPortada = '1';
    // En el acto, antes de que el <img> lazy empiece a pedir la foto del
    // sitio. Un <source> que da error no cae solo al <img>: si pasa, se
    // quitan las fuentes y vuelve la foto del sitio.
    // Si la foto del sitio ya se está descargando, mover el <img> la
    // cancelaría (una petición abortada en la consola de red): se espera a
    // que termine.
    if (!img.complete && img.currentSrc) {
      const luego = () => ponerPicture(img, base);
      img.addEventListener('load', luego, { once: true });
      img.addEventListener('error', luego, { once: true });
    } else ponerPicture(img, base);
    img.addEventListener('error', () => {
      if (!/\/lujo\/portadas\//.test(img.currentSrc || '')) return;
      $$('source', img.parentNode).forEach(x => x.remove());
      img.style.display = '';
    }, { once: true });
  }
  function ponerPicture(img, base) {
    if (!img.isConnected || img.closest('picture')) return;
    const pic = el('picture');
    pic.innerHTML = `
      <source media="(max-width: 900px)" srcset="${base}-movil-mitad.webp 540w, ${base}-movil.webp 1080w" sizes="100vw">
      <source srcset="${base}-escritorio-mitad.webp 1200w, ${base}-escritorio-1600.webp 1600w, ${base}-escritorio.webp 2400w" sizes="calc(100vw - 32px)">`;
    img.replaceWith(pic);
    pic.appendChild(img);
  }
  /* Opciones de comparación (solo del clon): ?variante=a|b y
     ?transicion=1|2|3. La URL manda y se recuerda en localStorage. */
  // Versión publicada (clon-lujo/publicar.py → Vercel): sin panel ni
  // herramientas del clon y con las opciones que eligió Samuel, fijas.
  const PUBLICADO = !!window.LUJO_PUBLICADO;
  function opcion(nombre, validos, defecto) {
    if (PUBLICADO) return defecto;
    const q = new URLSearchParams(location.search).get(nombre);
    // La clave lleva «r4»: en la ronda 4 cambiaron los valores por defecto
    // y no queremos que una elección vieja guardada los tape.
    const k = 'lx-r4-' + nombre;
    if (validos.includes(q)) { try { localStorage.setItem(k, q); } catch (e) {} return q; }
    try { const g = localStorage.getItem(k); return validos.includes(g) ? g : defecto; } catch (e) { return defecto; }
  }
  // Ronda 4: Samuel eligió B (Portadas) y la transición 1 (Hoja).
  const varianteActual = () => opcion('variante', ['a', 'b'], 'b');
  const transicionActual = () => opcion('transicion', ['1', '2', '3'], '1');
  // Entrar en una tipología (ver 5b): 1 Foto · 2 Fundido · 3 Telón · 4 Viaje.
  const PASO_DEFECTO = '4';
  const pasoActual = () => opcion('paso', ['1', '2', '3', '4'], PASO_DEFECTO);

  /* Panel «Propuestas» (ronda 3: Samuel no encontró el conmutador A·B).
     Solo existe en el clon, para comparar; NO va a producción. Abajo a la
     izquierda, en tinta, con nombre y etiquetas legibles; se pliega a una
     pastilla y recuerda si estaba plegado (en móvil empieza plegado). */
  function propuestas(variante, transicion) {
    if (PUBLICADO || $('.lx-propuestas')) return;
    const url = (k, v) => { const u = new URLSearchParams(location.search); u.set(k, v); return '?' + u; };
    const grupo = (titulo, k, actual, ops) => `
      <div class="lx-prop-grupo" role="group" aria-label="${titulo}">
        <span class="lx-prop-titulo">${titulo}</span>
        <div class="lx-prop-ops">${ops.map(([v, nombre]) =>
          `<a href="${url(k, v)}" aria-current="${v === actual ? 'true' : 'false'}"><b>${v.toUpperCase()}</b>${nombre}</a>`).join('')}</div>
      </div>`;
    const c = el('aside', 'lx-propuestas');
    c.setAttribute('aria-label', t('Propuestas para comparar', 'Proposals to compare'));
    c.innerHTML = `
      <button type="button" class="lx-prop-cab" aria-expanded="true">
        <span>${t('Propuestas', 'Proposals')}</span><small>${t('solo en el clon', 'clone only')}</small><i aria-hidden="true"></i>
      </button>
      <div class="lx-prop-cuerpo">
        ${grupo(t('Tipologías', 'Villa types'), 'variante', variante, [['a', t('Bandas', 'Bands')], ['b', t('Portadas', 'Covers')]])}
        ${grupo(t('Del hero a las villas', 'Hero to villas'), 'transicion', transicion, [['1', t('Hoja', 'Sheet')], ['2', t('Tarjeta', 'Card')], ['3', t('Telón', 'Curtain')]])}
        ${grupo(t('Entrar en una tipología', 'Opening a villa type'), 'paso', pasoActual(), [['1', t('Foto', 'Photo')], ['2', t('Fundido', 'Fade')], ['3', t('Telón', 'Curtain')], ['4', t('Viaje', 'Morph')]])}
      </div>`;
    document.body.appendChild(c);
    const cab = $('.lx-prop-cab', c);
    let plegado;
    try { plegado = localStorage.getItem('lx-prop-plegado'); } catch (e) {}
    const poner = v => { c.classList.toggle('is-plegado', v); cab.setAttribute('aria-expanded', String(!v)); };
    poner(plegado === null || plegado === undefined ? window.innerWidth <= 900 : plegado === '1');
    let tocado = false;
    cab.addEventListener('click', () => {
      tocado = true;
      const v = !c.classList.contains('is-plegado'); poner(v);
      try { localStorage.setItem('lx-prop-plegado', v ? '1' : '0'); } catch (e) {}
    });
    // Abierto tapa cards y botones al bajar: se pliega solo al pasar el hero
    // (sin guardarlo), salvo que se haya abierto a mano en esta visita.
    addEventListener('scroll', () => {
      if (!tocado && scrollY > innerHeight * .6 && !c.classList.contains('is-plegado')) poner(true);
    }, { passive: true });
  }

  /* ── 2d-bis. EL PIE NO SE QUEDA DEBAJO DE LO FIJO ────────────────────────
     Samuel (13 oct): el footer tapaba el selector Grid/List. Dos causas:
     · el footer y la lista son position:relative; z-index:2 (transiciones,
       2e) y la barra fija vive DENTRO de la lista, así que el footer, que va
       después, la pintaba encima → la lista sube a z-index 3 (CSS 3c);
     · al final de la página la barra, el WhatsApp y el panel tapaban el
       pie → el pie reserva abajo lo que ocupen (--lx-pie-hueco). */
  function hueco() {
    const raiz = document.documentElement;
    let medir = null;
    const calc = () => {
      medir = null;
      let alto = 0;
      for (const s of ['.fg-toggle-bar', '.fg-wa-float', '.lx-propuestas.is-plegado']) {
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

  /* ── 2e. DEL HERO A LAS VILLAS: TRES TRANSICIONES (ronda 3) ─────────────
     Samuel no quiere el corte seco entre el hero y lo de abajo, ni el
     fundido de antes. Tres propuestas, seleccionables en el panel:
       1 · Hoja    — el hero se queda fijo y la primera sección sube encima
                     como una hoja de papel con el borde superior curvo; el
                     hero se oscurece un poco al quedar debajo.
       2 · Tarjeta — el hero es ya una tarjeta a sangre con las esquinas de
                     abajo redondeadas y un filo de papel alrededor; al
                     bajar se recoge un poco más, como una foto sobre la mesa.
       3 · Telón   — el borde inferior del hero es la diagonal del telón, y
                     una planta de la landing crece desde el papel y cruza a
                     la foto: hace de puente.
     Las tres valen igual en la vista de una tipología (es el mismo hero).
     Con prefers-reduced-motion no hay nada ligado al scroll, pero ninguna
     queda en corte seco: 1 se queda con la hoja curva solapando el hero,
     2 con la tarjeta, 3 con la diagonal y la planta fija.
     El JS solo escribe --lx-p (0→1 según lo bajado sobre el alto del hero)
     y pone la planta del 3; toda la forma vive en el CSS. */
  function transiciones(n) {
    document.documentElement.classList.add('lx-tr-' + n);
    const stage = $('.fg-stage');
    const lista = $('#main-unit-reserve-list');
    if (!stage) return;
    if (n === '3' && lista && !$('.lx-puente', lista)) {
      const puente = el('div', 'lx-puente');
      lista.prepend(puente);
      plantaEn(puente, '03', 'puente');
    }
    if (reducido()) return;
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

  /* ── 2d. ENTRADA AL HACER SCROLL (propuesta) ────────────────────────────
     El «ctn» de la landing: el texto de cada tipología sube 24 px y aparece
     en 1,2 s, una sola vez. Solo texto, nunca fotos ni precios en
     movimiento. La clase lx-revela la pone este JS: sin JS, todo visible.
     Con prefers-reduced-motion no se activa. */
  let ioRevela;
  function revelar() {
    if (reducido() || !('IntersectionObserver' in window)) return;
    document.documentElement.classList.add('lx-revela');
    ioRevela = ioRevela || new IntersectionObserver(es => es.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('is-visto'); ioRevela.unobserve(e.target); }
    }), { rootMargin: '0px 0px -12% 0px' });
    $$('.fg-type-band .fg-card-body, .fg-type-band .fg-card-price, .fg-type-band .fg-card-actions')
      .forEach(n => { if (!n.dataset.lxRev) { n.dataset.lxRev = '1'; ioRevela.observe(n); } });
  }

  /* ── 3. VÍDEO DEL HERO (solo si existe) ─────────────────────────────────── */
  /* ── 3b. HERO ───────────────────────────────────────────────────────────
     Mismo criterio que en Bahía Mar: el nombre del sitio en grande sobre el
     render «queda de proyecto barato» (Miguel, 12 oct), así que fuera la
     escena del sitio y en su lugar un slider a sangre con la marca pequeña
     abajo a la izquierda.
     · Las cinco imágenes son los renders oficiales que mandó Miguel al grupo
       de Siebe el 16 de octubre, sin retocar: fachada al atardecer, azotea
       con piscina, dormitorio, lobby de noche y fachada de día. Se sirven en
       webp a 2048 y 1024; las dos fachadas llevan además recorte vertical
       para móvil, porque el edificio es alto y un 16:9 en vertical lo parte.
     · Las dos fachadas son casi el mismo encuadre a distinta hora, así que
       van la primera y la última: separadas, no se leen como repetida.
     · Transición: el mismo telón diagonal, 8 s por imagen. Índice «01 — 05»
       con una línea fina de progreso; cada número salta a su imagen.
     · El vídeo (MEDIOS.heroVideo), cuando llegue, entra como primera capa.
     · Con prefers-reduced-motion: sin telón ni autoplay; la primera imagen,
       y los números siguen cambiando de imagen en seco. */
  const HERO = [
    // Abre la fachada a la hora dorada: es la imagen con el logotipo del
    // proyecto en el monolito, o sea la que dice dónde estás.
    { img: '01-fachada-atardecer', movil: true, alt: t('La fachada de Siebe Residences al atardecer', 'The Siebe Residences facade at sunset') },
    { img: '02-azotea-piscina',    alt: t('La piscina de la azotea con las tumbonas', 'The rooftop pool and loungers') },
    { img: '03-dormitorio',        alt: t('Dormitorio principal de una residencia', 'Main bedroom of a residence') },
    { img: '04-lobby-noche',       alt: t('El lobby acristalado al anochecer', 'The glass lobby at dusk') },
    { img: '05-fachada-dia',       movil: true, alt: t('La fachada de Siebe Residences de día', 'The Siebe Residences facade by day') },
  ];
  const HERO_MS = 8000;
  function hero() {
    const stage = $('.fg-stage.has-slider') || $('.fg-stage');
    if (!stage || $('.lx-hero', stage)) return;
    const capas = HERO.map(h => ({ ...h, src: `/lujo/hero/${h.img}.webp`, src1024: `/lujo/hero/${h.img}-1024.webp` }));
    const raiz = el('div', 'lx-hero');
    raiz.innerHTML = `
      <div class="lx-hero-capas">${capas.map((c, i) => `
        <figure class="lx-telon-capa${i === 0 ? ' is-activa' : ''}" data-i="${i}" data-img="${c.img}">
          ${c.movil ? `<picture><source media="(max-width: 600px)" ${i === 0 ? 'srcset' : 'data-srcset'}="/lujo/hero/${c.img}-movil-540.webp 540w, /lujo/hero/${c.img}-movil.webp 1080w" sizes="100vw">` : ''}
          <img ${i === 0 ? 'src' : 'data-src'}="${c.src}" ${i === 0 ? 'srcset' : 'data-srcset'}="${c.src1024} 1024w, ${c.src} 2048w"
               sizes="100vw" alt="${c.alt}" ${i === 0 ? 'fetchpriority="high"' : ''} decoding="async">${c.movil ? '</picture>' : ''}
        </figure>`).join('')}
      </div>
      <div class="lx-hero-pie">
        <span class="lx-hero-ola" aria-hidden="true"></span>
        <p class="lx-hero-marca">Siebe</p>
        <p class="lx-hero-sub">Residences · Aruba</p>
        <button type="button" class="lx-hero-ir">${t('Ver las residencias disponibles', 'View available residences')}</button>
      </div>
      <div class="lx-hero-indice" role="group" aria-label="${t('Imágenes', 'Images')}">
        ${capas.map((c, i) => `<button type="button" data-i="${i}" aria-label="${c.alt}" aria-pressed="${i === 0}">${String(i + 1).padStart(2, '0')}</button>`).join('')}
        <span class="lx-hero-linea" aria-hidden="true"><i></i></span>
      </div>`;
    raiz.appendChild(el('div', 'lx-hero-sombra'));
    stage.appendChild(raiz);
    stage.classList.add('lx-hero-on');

    if (MEDIOS.heroVideo) {
      const v = el('video', 'lx-hero-video');
      Object.assign(v, { muted: true, loop: true, playsInline: true, autoplay: !reducido(), preload: 'metadata', src: MEDIOS.heroVideo });
      if (MEDIOS.heroPoster) v.poster = MEDIOS.heroPoster;
      v.setAttribute('aria-hidden', 'true');
      $('.lx-telon-capa', raiz).prepend(v);
    }

    const figs = $$('.lx-telon-capa', raiz);
    const botones = $$('.lx-hero-indice button', raiz);
    const linea = $('.lx-hero-linea i', raiz);
    const cargar = i => {
      const img = $('img', figs[i]);
      const fuente = $('source[data-srcset]', figs[i]);
      if (fuente) { fuente.srcset = fuente.dataset.srcset; delete fuente.dataset.srcset; }
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
    const progreso = () => {
      linea.classList.remove('is-corriendo'); void linea.offsetWidth;
      if (temporizador) linea.classList.add('is-corriendo');
    };
    const arrancar = () => {
      clearInterval(temporizador); temporizador = null;
      if (reducido() || !visible || document.hidden) return progreso();
      if (raiz.classList.contains('is-tipo')) return progreso();
      temporizador = setInterval(() => ir((actual + 1) % HERO.length), HERO_MS);
      progreso();
    };
    botones.forEach(b => b.addEventListener('click', () => { ir(+b.dataset.i); arrancar(); }));
    $('.lx-hero-ir', raiz).addEventListener('click', () => {
      const destino = document.body.classList.contains('is-catalog')
        ? ($('.fg-catalog-head') || $('#main-unit-reserve-list'))
        : ($('.fg-types-section') || $('#main-unit-reserve-list'));
      if (destino) window.scrollTo({ top: destino.getBoundingClientRect().top + scrollY - 90, behavior: reducido() ? 'auto' : 'smooth' });
    });
    new IntersectionObserver(([e]) => { visible = e.isIntersecting; arrancar(); }, { threshold: .2 }).observe(raiz);
    document.addEventListener('visibilitychange', arrancar);
    cargar(1);
    arrancar();

    /* El hero de cada tipología (la vista que abre «Ver villas»): el mismo
       encuadre, con la foto de esa tipología entrando con el telón y su
       nombre en el pie. Al volver a «Tipos de villa», vuelve el slider.
       Así la transición elegida (1/2/3) vale igual en las dos vistas. */
    const capaTipo = el('figure', 'lx-telon-capa lx-capa-tipo');
    capaTipo.dataset.i = figs.length;
    capaTipo.appendChild(el('img'));
    $('.lx-hero-capas', raiz).appendChild(capaTipo);
    figs.push(capaTipo);
    const pie = { marca: $('.lx-hero-marca', raiz), sub: $('.lx-hero-sub', raiz), ir: $('.lx-hero-ir', raiz) };
    const original = { marca: pie.marca.textContent, sub: pie.sub.textContent, ir: pie.ir.textContent };
    modoTipo = modelo => {
      const band = modelo && $(`.fg-type-band[data-model="${modelo}"]`);
      if (band) {
        // currentSrc: la misma foto que se ve en la portada (y la que usa el
        // paso de 5b), así el relevo entre las dos no se nota.
        const fb = $('.fg-card-img img', band);
        const img = $('img', capaTipo), src = fb && (fb.currentSrc || fb.getAttribute('src'));
        if (src) img.src = src;
        img.alt = band.dataset.modelName || '';
        const fase = ($('.fg-card-subtitle', band)?.textContent || '').trim();
        pie.marca.textContent = band.dataset.modelName || '';
        pie.sub.textContent = [t('Villa', 'Villa') + ' ' + (band.dataset.modelName || ''), fase].filter(Boolean).join(' · ');
        pie.ir.textContent = t('Ver las villas', 'View the villas');
        raiz.classList.add('is-tipo');
        ir(figs.length - 1);   // si ya estaba en una tipología, solo cambia la foto
      } else if (raiz.classList.contains('is-tipo')) {
        Object.assign(pie.marca, { textContent: original.marca });
        Object.assign(pie.sub, { textContent: original.sub });
        Object.assign(pie.ir, { textContent: original.ir });
        raiz.classList.remove('is-tipo');
        ir(0);
      }
      arrancar();
    };
    const abrir = window.openVillaType, volver = window.backToVillaTypes;
    if (typeof abrir === 'function') window.openVillaType = function (m) { const r = abrir.apply(this, arguments); modoTipo(m); return r; };
    if (typeof volver === 'function') window.backToVillaTypes = function () { const r = volver.apply(this, arguments); modoTipo(null); return r; };
    if (typeof currentFilters !== 'undefined' && currentFilters.model) modoTipo(currentFilters.model);
  }
  let modoTipo = () => {};

  /* ── 4. CARDS DEL CATÁLOGO ──────────────────────────────────────────────
     La estructura de la propuesta de Miguel (referencias/miguel-2026-10-12),
     sin sus datos de ejemplo:
       foto con estado + vista · nombre · «Villa de N hab · Fase» ·
       precio y $/m² · specs con etiqueta · acción principal + «Ver detalle».
     Fuera de su maqueta, porque no hay dato: fecha de entrega, «12 fotos ·
     tour 360°», el asesor con nombre y tiempo de respuesta, «solo quedan…»,
     la lista de espera con cifras y «1 de cada 5 reservas no se confirma».
     Parqueos solo si hay (hoy 0 en las 233). */
  const ESTADO = {
    available: t('Disponible', 'Available'),
    reserved:  t('Reservada', 'Reserved'),
    sold:      t('Vendida', 'Sold'),
    pending:   t('Pendiente', 'Pending'),
  };
  const VISTA = { ocean: t('Vista al mar', 'Ocean view'), mountain: t('Vista a la montaña', 'Mountain view') };

  // Nombre de tipología → clave de modelo, leído de las bandas de la portada.
  function modeloDe(nombre) {
    const base = (nombre || '').trim().split(/\s+/)[0].toLowerCase();
    const band = $$('.fg-type-band').find(b => (b.dataset.modelName || '').toLowerCase() === base);
    return band ? band.dataset.model : null;
  }

  function valorStat(card, titulo) {
    const s = $(`.fg-stat[title="${titulo}"] .v`, card);
    if (!s) return '';
    const v = s.textContent.replace(/m2$|m²$/, '').trim();
    // Los baños vienen de un decimal en la base y el sitio los escribe «1.0».
    // En una ficha de lujo eso canta: si no hay media parte, se quita la coma.
    // Se deja «1.5», que sí quiere decir algo (un baño y un aseo).
    return /^\d+\.0+$/.test(v) ? String(parseInt(v, 10)) : v;
  }

  function boton(texto, clase, accion) {
    const b = el('button', clase, texto);
    b.type = 'button';
    b.addEventListener('click', e => { e.stopPropagation(); accion(); });
    return b;
  }

  function cards() {
    $$('.fg-units-grid > .fg-card').forEach((card, i) => {
      if (card.dataset.lx) return;
      card.dataset.lx = '1';
      const d = card.dataset;
      const id = d.filterUnit;
      const estado = d.filterStatus || 'available';
      card.dataset.lxEstado = estado;

      // Foto: estado y vista como dos chips discretos.
      const img = $('.fg-card-img', card);
      if (img) {
        const chips = el('div', 'lx-chips');
        chips.appendChild(el('span', `lx-chip lx-chip--${estado}`, ESTADO[estado] || estado));
        if (VISTA[d.filterOutlook]) chips.appendChild(el('span', 'lx-chip', VISTA[d.filterOutlook]));
        img.appendChild(chips);
        if (estado === 'sold') img.appendChild(el('span', 'lx-sello', ESTADO.sold));
      }

      // Nombre: la tipología en Italiana y el número aparte, en Archivo.
      const nombre = $('.fg-card-title-row .name', card);
      if (nombre) {
        const m = nombre.textContent.trim().match(/^(.*?)\s+(\d+)$/);
        if (m) nombre.innerHTML = `<span class="lx-nombre">${m[1]}</span> <span class="lx-num">${m[2]}</span>`;
      }
      const sub = $('.fg-card-subtitle', card);
      if (sub && d.filterBedrooms) {
        const hab = d.filterBedrooms;
        // La planta NO se saca de data-filter-floor: ahí está el valor crudo de
        // la base, que en estos proyectos se cargó en inglés ('1st', '2nd') y
        // se colaba sin traducir en la página en español. El subtítulo que ya
        // pintó el servidor la trae traducida («Piso 1»), así que se reutiliza
        // su primer segmento y sólo se vuelve a componer la frase.
        const planta = (sub.dataset.lxPlanta ??= sub.textContent.split('·')[0].trim());
        sub.textContent = t(`Residencia de ${hab} hab · ${planta}`, `${hab}-bedroom residence · ${planta}`);
      }

      // Reserva desde $5,000: la condición real del banner, bajo el precio.
      const banner = $('.fg-reserve-banner', card);
      const precio = $('.fg-card-price', card);
      if (banner && precio && estado === 'available') {
        precio.after(el('div', 'lx-reserva', banner.textContent.trim()));
      }

      // Specs con etiqueta, en vez de seis iconos sin nombre.
      const head = $('.fg-card-head', card);
      if (head) {
        const filas = [
          [t('Hab.', 'Beds'), valorStat(card, 'Bedrooms')],
          [t('Baños', 'Baths'), valorStat(card, 'Bathrooms')],
          [t('Parqueos', 'Parking'), valorStat(card, 'Parking')],
          [t('Interior', 'Interior'), valorStat(card, 'Internal area') && valorStat(card, 'Internal area') + ' m²'],
          [t('Total', 'Total'), valorStat(card, 'Total area') && valorStat(card, 'Total area') + ' m²'],
        ].filter(([, v]) => v && v !== '0' && v !== '—');
        const dl = el('dl', 'lx-specs');
        filas.forEach(([k, v]) => dl.appendChild(el('div', null, `<dt>${k}</dt><dd>${v}</dd>`)));
        head.after(dl);
      }

      // La hoja, en todas (Miguel) y siempre a la derecha, donde hay aire.
      const body = $('.fg-card-body', card);
      if (body) body.prepend(el('div', `lx-hoja lx-hoja--${i % 2 ? 'monstera' : 'fronda'}`));

      // Acciones por estado.
      const acciones = $('.fg-card-buttons', card);
      if (acciones) {
        acciones.replaceChildren();
        const modelo = modeloDe(nombre && nombre.textContent);
        // «Ver similares» abría la vista de tipologías. En Siebe no la hay, así
        // que ese botón no llevaría a ninguna parte: en su lugar va «Ver
        // detalle», que es lo único que tiene sentido en una unidad ocupada.
        const hayTipologias = typeof window.openVillaType === 'function' && modelo;
        const segundo = hayTipologias
          ? boton(t('Ver similares', 'Similar residences'), 'lx-btn', () => openVillaType(modelo))
          : boton(t('Ver detalle', 'View details'), 'lx-btn', () => openMoreInfo(id));
        if (estado === 'available') {
          acciones.append(
            boton(t('Agendar videollamada', 'Book a video call'), 'lx-btn lx-btn--lleno', () => openAdvisorVideoCall(id)),
            boton(t('Ver detalle', 'View details'), 'lx-btn', () => openMoreInfo(id)));
        } else if (estado === 'reserved') {
          acciones.append(
            boton(t('Avísame si se libera', 'Notify me if released'), 'lx-btn lx-btn--lleno', () => registro.abrir('aviso', id)),
            segundo);
        } else {
          acciones.append(
            boton(t('Avísame de reventas', 'Resale alerts'), 'lx-btn lx-btn--lleno', () => registro.abrir('aviso', id)),
            segundo);
        }
      }
    });
  }

  /* ── 5. REGISTRO: ¿cliente o broker? ────────────────────────────────────
     Sustituye al «Create your account to continue» y a las redirecciones a
     /login del anónimo (reservar, agendar, guardar). Pregunta primero quién
     es, porque cada uno va a un área distinta, y lleva a su formulario.
     En el clon no hay backend: el flujo es visual y termina en /register
     con ?perfil=, que en el clon es una página «fuera del clon». */
  const registro = (function () {
    const TX = {
      eyebrow: 'Siebe Residences',
      titulo: t('Acceso privado', 'Private access'),
      intro: t('El catálogo completo, los planos de cada residencia y la reserva en línea están disponibles con una cuenta. ¿Cómo quiere continuar?',
               'The full catalogue, each residence’s floor plans and online booking are available with an account. How would you like to continue?'),
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
            <p class="lx-eyebrow">${TX.eyebrow}</p>
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
        if (op) formulario(op.dataset.perfil);
        if (e.target.closest('[data-volver]')) paso('elegir');
      });
      $('form', modal).addEventListener('submit', e => {
        e.preventDefault();
        const perfil = $('form', modal).dataset.perfil;
        const q = new URLSearchParams({ perfil });
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
    // pasa a ser este. Sin muro se queda mudo, como lo dejó servir.py.
    if (document.body.hasAttribute('data-guest')) window.openGuestModal = () => registro.abrir();
    window.openReservePage = id => registro.abrir('reserve', id || abierta());
    window.openAdvisorVideoCall = id => registro.abrir('videocall', id || abierta());
    window.toggleModalWishlist = () => registro.abrir('wishlist', abierta());
    document.addEventListener('click', e => {
      const a = e.target.closest('a[href^="/login"], a[href^="/register"], [data-wishlist-toggle]');
      if (!a || a.closest('.lx-reg')) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      registro.abrir(a.matches('[data-wishlist-toggle]') ? 'wishlist' : null, a.dataset.unitId);
    }, true);
  }

  /* ── 6. FICHA DE LA VILLA (popup #moreInfoModal) ───────────────────────
     Miguel: «tiene información que flipas». La columna izquierda del original
     mezcla datos de la villa con bloques que no salen de ella:
       · «Unlock $20,000 Discount», «12% below the area average» y
         «Last inquiry 2 hours ago · Shortlisted by 7 others»: texto fijo;
       · revalorización (+8 %/año) y renta estimada: calculadas con un % fijo
         sobre el precio, no son datos de la villa;
       · «3 min beach · 5 min golf · 15 min airport», «24/7 security»,
         «A day here»: genéricos, iguales en todas las villas;
       · «$450/m²»: no corresponde a ninguna superficie (el $/m² real sale
         ~$1,588 sobre el total o ~$2,200 sobre el interior);
       · el asesor «Admin User» y dos botones de planos que no hacen nada.
     Se queda lo que es de la villa, en este orden: nombre, precio (con el
     mismo $/m² que la card, sobre el interior), condiciones de reserva bien
     a la vista, la descripción y los datos con etiqueta.
     A la derecha no se toca nada de lo que pidió conservar (fotos, descargar,
     aviso legal, compartir, guardar, broker por WhatsApp); la galería gana
     autoplay y fundido. */
  const FICHA = {
    eyebrow: (modelo, fase) => [modelo && t(`Tipo ${modelo}`, `Type ${modelo}`), fase].filter(Boolean).join(' · '),
    m2int: t('m² interiores', 'interior m²'),
    reserva: t('Reserva', 'Reservation'),
    datos: t('La residencia', 'The residence'),
    filas: u => [
      [t('Habitaciones', 'Bedrooms'), u.bedrooms],
      [t('Baños', 'Bathrooms'), u.bathrooms && Number(u.bathrooms)],
      [t('Niveles', 'Levels'), u.stories],
      [t('Parqueos', 'Parking'), u.parking_bays],
      [t('Vista', 'View'), VISTA[u.outlook]],
      [t('Interior', 'Interior'), m2(u.internal_area)],
      [t('Terraza y exterior', 'Terrace & outdoor'), m2(u.external_area)],
      [t('Total construido', 'Total built'), m2(u.total_area)],
      [t('Parcela', 'Plot'), m2(u.plot_area)],
    ].filter(([, v]) => v && v !== '0' && Number(v) !== 0),
    piscina: t('Piscina y exteriores', 'Pool & outdoor'),
  };
  const num = (v, dec = 0) => Number(v).toLocaleString(ES ? 'es-DO' : 'en-US', { maximumFractionDigits: dec });
  const m2 = v => (Number(v) > 0 ? `${num(v, 2)} m²` : null);

  // «Horizonte 001» → la tipología en Italiana y el número en Archivo.
  const nombreFicha = n => {
    const m = String(n).trim().match(/^(.*?)\s+(\d+)$/);
    return m ? `${m[1]} <span class="lx-num">${m[2]}</span>` : n;
  };
  let fichaActual = null;
  function pintarFicha(u) {
    const modal = $('#moreInfoModal');
    const izq = $('.mt-left-inner', modal);
    if (!izq) return;
    fichaActual = u;
    modal.dataset.lxEstado = (u.status || 'available').toLowerCase();
    let caja = $('.lx-ficha', izq);
    if (!caja) {
      caja = el('div', 'lx-ficha');
      izq.prepend(caja);
    }
    // En Siebe las unidades se llaman por su número («101»), así que la
    // primera palabra del nombre no es el modelo: la tipología está en
    // `layout` («T1»). Si falta, no se inventa un epígrafe.
    const modeloNombre = u.layout || '';
    const ppm = Number(u.internal_area) > 0 ? Math.round(Number(u.price) / Number(u.internal_area)) : 0;
    // Las condiciones de reserva salen del propio sitio (ya traducidas).
    const condRes = $('#moreInfoModal .mt-price-meta .warning');
    const condNo = condRes && condRes.parentElement.querySelector('.muted');
    const estado = (u.status || 'available').toLowerCase();
    // Antes de reescribir la caja, el precio y las monedas vuelven a su sitio
    // (si no, se irían con el innerHTML de la villa anterior).
    const fila = $('.mt-price-row', modal);
    [$('#modalPrice'), $('#moreInfoModal .mt-currency-toggle')].forEach(n => { if (n && fila && caja.contains(n)) fila.appendChild(n); });
    caja.innerHTML = `
      <p class="lx-eyebrow">${FICHA.eyebrow(modeloNombre, planta(u.floor))}</p>
      <h2 class="lx-ficha-nombre">${nombreFicha(u.name || u.custom_id)}</h2>
      <div class="lx-ficha-precio" data-slot-precio></div>
      ${ppm && estado !== 'sold' ? `<p class="lx-ficha-ppm">$${num(ppm)} / ${FICHA.m2int}</p>` : ''}
      ${condRes && estado === 'available' ? `<div class="lx-ficha-reserva">
          <span class="lx-eyebrow">${FICHA.reserva}</span>
          <p><b>${condRes.textContent.trim()}</b>${condNo ? `<span>${condNo.textContent.trim()}</span>` : ''}</p>
        </div>` : ''}
      ${u.description ? `<p class="lx-ficha-desc">${u.description}</p>` : ''}
      <p class="lx-eyebrow lx-ficha-sub">${FICHA.datos}</p>
      <dl class="lx-ficha-datos">${FICHA.filas(u).map(([k, v]) => `<div><dt>${k}</dt><dd>${v}</dd></div>`).join('')}</dl>
      ${u.custom_3 ? `<p class="lx-eyebrow lx-ficha-sub">${FICHA.piscina}</p><p class="lx-ficha-extra">${u.custom_3}</p>` : ''}`;
    // El precio y el selector de moneda son los nodos del sitio, movidos: así
    // siguen respondiendo al cambio de moneda sin duplicar lógica.
    const slot = $('[data-slot-precio]', caja);
    const precio = $('#modalPrice');
    const monedas = $('#moreInfoModal .mt-currency-toggle');
    if (estado === 'sold') slot.innerHTML = `<span class="lx-ficha-vendida">${ESTADO.sold}</span>`;
    else { if (precio) slot.appendChild(precio); if (monedas) slot.appendChild(monedas); }
    accionAlterna(modal, estado, u);
    galeriaVideo(u);
  }

  /* Reservada o vendida: en vez de «Reserved»/«Sold» desactivado, la misma
     salida que en la card. El botón del sitio se oculta por CSS. */
  function accionAlterna(modal, estado, u) {
    const fila = $('.mt-cta-row', modal);
    if (!fila) return;
    $$('.lx-cta-alt', fila).forEach(n => n.remove());
    if (estado === 'available') return;
    const modelo = u.layout;
    // En Bahía Mar la segunda opción llevaba a la vista de tipologías. Aquí no
    // hay tipologías —Siebe es un edificio con unidades— y openVillaType no
    // existe, así que sin esta comprobación el clic reventaba con un
    // ReferenceError. Cuando no la hay, se ofrece el aviso de reventa, que es
    // lo que el catálogo ya daba para las vendidas.
    const verSimilares = typeof window.openVillaType === 'function' && modelo;
    const b = estado === 'reserved'
      ? boton(t('Avísame si se libera', 'Notify me if released'), 'lx-cta-alt mt-btn-secondary', () => registro.abrir('aviso', u.custom_id))
      : verSimilares
        ? boton(t('Ver residencias similares', 'Similar residences'), 'lx-cta-alt mt-btn-secondary', () => { closeMoreInfo(); openVillaType(modelo); })
        : boton(t('Avísame de reventas', 'Resale alerts'), 'lx-cta-alt mt-btn-secondary', () => registro.abrir('aviso', u.custom_id));
    fila.prepend(b);
  }

  // Vídeo de la tipología (MEDIOS.villaVideo) como primer elemento de la galería.
  function galeriaVideo(u) {
    const g = $('#moreInfoModal .mt-gallery');
    $$('.lx-ficha-video', g).forEach(n => n.remove());
    const src = MEDIOS.villaVideo[u.layout];
    if (!src || !g) return;
    const v = el('video', 'lx-ficha-video');
    Object.assign(v, { src, controls: true, playsInline: true, muted: true, preload: 'metadata' });
    g.prepend(v);
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
    const barra = el('span', 'lx-progreso');
    g.appendChild(barra);
    new MutationObserver(ms => {
      const antes = ms[0].oldValue;
      if (!antes || antes === img.getAttribute('src') || reducido()) return;
      velo.src = antes;
      velo.classList.toggle('is-plan', /\/planos\//.test(antes));
      velo.classList.remove('is-fuera');
      void velo.offsetWidth;
      const fundir = () => velo.classList.add('is-fuera');
      if (img.complete) requestAnimationFrame(fundir); else img.addEventListener('load', fundir, { once: true });
    }).observe(img, { attributes: true, attributeFilter: ['src'], attributeOldValue: true });
    g.addEventListener('mouseenter', () => { pausada = true; g.classList.add('is-pausada'); });
    g.addEventListener('mouseleave', () => { pausada = false; g.classList.remove('is-pausada'); reiniciarBarra(); });
    $$('.mt-arrow, .mt-zoom-hint', g).forEach(b => b.addEventListener('click', parar));
    $('#mtThumbs') && $('#mtThumbs').addEventListener('click', parar);
    // Samuel (13 oct): Property / Amenities encima de la foto, como un
    // switcher, para que la imagen llegue hasta la barra de acciones. Se
    // mueve el mismo nodo (#mtCatToggle): el sitio lo busca por id y sus
    // onclick → setModalCategory siguen igual. El activo se anuncia con
    // aria-pressed, sincronizado con la clase .active que pone el sitio.
    const cat = $('#mtCatToggle');
    if (cat) {
      g.appendChild(cat);
      const pill = $('.mt-cat-pill', cat);
      pill.setAttribute('role', 'group');
      pill.setAttribute('aria-label', t('Galería', 'Gallery'));
      const marcar = () => $$('.mt-cat-btn', cat).forEach(b => b.setAttribute('aria-pressed', String(b.classList.contains('active'))));
      marcar();
      new MutationObserver(marcar).observe(pill, { subtree: true, attributes: true, attributeFilter: ['class'] });
      $$('.mt-cat-btn', cat).forEach(b => b.addEventListener('click', reiniciarBarra));
    }
  }
  function reiniciarBarra() {
    const b = $('#moreInfoModal .lx-progreso');
    if (!b) return;
    b.classList.remove('is-corriendo'); void b.offsetWidth;
    if (autoplay && !pausada) b.classList.add('is-corriendo');
  }
  function arrancar() {
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
        .then(u => { if (u) { pintarFicha(u); arrancar(); } })
        .catch(() => {});
      return r;
    };
    envuelta.lx = true;
    window.openMoreInfo = envuelta;
    const cerrar = window.closeMoreInfo;
    if (typeof cerrar === 'function') window.closeMoreInfo = function () { parar(); return cerrar.apply(this, arguments); };
  }

  /* ── 5b. ENTRAR EN UNA TIPOLOGÍA Y VOLVER ────────────────────────────────
     Samuel (13 oct): «el cambio es abrupto y los elementos cargan a
     tropezones». Lo que hacía el sitio al pulsar «Ver villas»:
       1. syncCatalogMode() en el acto: las portadas desaparecen y se ve el
          catálogo ANTERIOR (cards de otra tipología) hasta que llega el fetch;
       2. llegan las cards nuevas con su animación de entrada y las fotos sin
          cargar (lazy): huecos grises que se rellenan de una en una;
       3. scroll suave a la cabecera del catálogo desde una posición que ya ha
          saltado (al desaparecer las portadas el documento encoge);
       4. al volver, corte seco y a la PRIMERA portada, no a la de origen.
     Ahora, como la Hoja / el telón: la foto de la portada se abre a toda la
     pantalla; debajo, con los datos ya pedidos (se precargan al pasar el
     ratón o tocar), se cambia de vista y se coloca el scroll arriba; cuando
     las fotos de la primera fila están decodificadas, la foto se recoge en el
     hero de la tipología (la misma imagen, así que el relevo no se ve) y las
     cards entran escalonadas. Al volver, lo mismo al revés, hasta la portada
     de la que se vino. Con reducir movimiento: un solo corte, sin catálogo
     viejo a la vista ni scroll que salte. */
  const PASO = { abre: 520, cierra: 600, maxEspera: 2200 };
  function pasoTipologias() {
    const abrir = window.openVillaType, volver = window.backToVillaTypes, recargar = window.reloadUnits;
    if (typeof abrir !== 'function' || typeof recargar !== 'function' || abrir.lxPaso) return;
    const raiz = document.documentElement;
    const espera = ms => new Promise(r => setTimeout(r, ms));
    const marco = () => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

    // Precarga: la primera página de la tipología (y sus primeras fotos) se
    // pide al pasar por la portada; el fetch del sitio la recibe de aquí.
    const fetchReal = window.fetch.bind(window);
    const precargas = new Map();
    const enVuelo = new Set();
    const urlDe = modelo => {
      if (typeof buildUnitParams !== 'function' || typeof currentFilters === 'undefined') return null;
      const antes = currentFilters.model;
      currentFilters.model = modelo;
      try { return '/api/home-units?' + buildUnitParams(0).toString(); } finally { currentFilters.model = antes; }
    };
    const fotosDe = html => [...html.matchAll(/<img[^>]+src="([^"]+)"/g)].slice(0, 8).map(m => m[1]);
    const precargar = modelo => {
      const url = urlDe(modelo);
      if (!url) return Promise.resolve();
      let p = precargas.get(url);
      if (!p) {
        p = fetchReal(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
          .then(r => (r.ok ? r.text() : Promise.reject(r.status)))
          .then(txt => {
            // Se guardan hasta que terminan: un Image() sin referencia puede
            // recogerse antes de tiempo y su descarga se cancela.
            try { fotosDe(JSON.parse(txt).cards || '').forEach(s => {
              const i = new Image(); i.decoding = 'async';
              enVuelo.add(i);
              i.onload = i.onerror = () => enVuelo.delete(i);
              i.src = s;
            }); } catch (e) {}
            return txt;
          });
        p.catch(() => precargas.delete(url));
        precargas.set(url, p);
        setTimeout(() => precargas.delete(url), 60000);
      }
      return p.catch(() => {});
    };
    window.fetch = function (input, init) {
      const url = typeof input === 'string' ? input : input && input.url;
      const p = url && precargas.get(url.replace(location.origin, ''));
      if (p) return p.then(txt => new Response(txt, { status: 200, headers: { 'Content-Type': 'application/json' } }), () => fetchReal(input, init));
      return fetchReal(input, init);
    };
    const avisar = e => {
      const band = e.target.closest && e.target.closest('.fg-type-band');
      if (band && band.dataset.model) precargar(band.dataset.model);
    };
    document.addEventListener('pointerover', avisar, { passive: true });
    document.addEventListener('touchstart', avisar, { passive: true });
    document.addEventListener('focusin', avisar);

    // Cuándo llegan los datos: se envuelve reloadUnits para enterarse de su
    // onDone. Mientras dura el paso, el scroll suave del sitio se anula (el
    // scroll lo coloca el paso, una sola vez y bajo la foto).
    let alLlegar = null;
    window.reloadUnits = function (o) {
      o = o || {};
      const d = o.onDone;
      o.onDone = data => {
        const r = d ? d(data) : undefined;
        if (alLlegar) { const f = alLlegar; alLlegar = null; f(data); }
        return r;
      };
      return recargar.call(this, o);
    };
    const scrollSitio = Element.prototype.scrollIntoView;
    const callar = si => { Element.prototype.scrollIntoView = si ? function () {} : scrollSitio; };
    const datos = () => new Promise(r => { alLlegar = r; setTimeout(r, PASO.maxEspera + 2000); });

    // Fotos de la primera fila: decodificadas antes de enseñarlas.
    const fotosVisibles = (sel, n) => $$(sel).filter(i => { const r = i.getBoundingClientRect(); return r.width && r.top < innerHeight && r.bottom > 0; }).slice(0, n);
    const listas = imgs => Promise.race([
      Promise.all(imgs.map(i => { i.loading = 'eager'; return i.decode ? i.decode().catch(() => {}) : Promise.resolve(); })),
      espera(PASO.maxEspera),
    ]);
    // Cards: fuera la animación del sitio (corría bajo la foto); la nuestra
    // empieza cuando se ven.
    const prepararCards = () => {
      $$('.fg-units-grid > .fg-card').forEach(c => c.classList.remove('is-lazy-in'));
    };
    const entrarCards = (retraso = 60) => {
      if (reducido()) return;
      const vis = $$('.fg-units-grid > .fg-card').filter(c => c.getBoundingClientRect().top < innerHeight);
      vis.forEach((c, i) => { c.style.setProperty('--lx-i', i); c.style.setProperty('--lx-entra-d', retraso + 'ms'); c.classList.add('lx-entra'); });
      setTimeout(() => vis.forEach(c => c.classList.remove('lx-entra')), 700 + retraso + vis.length * 55);
    };

    // La capa del paso: la foto a pantalla completa, fija, que se recorta
    // (clip-path) y se escala (transform) para coincidir con una caja: la de
    // la portada, la pantalla o el hero. Ni una propiedad de layout: no hay
    // saltos que medir y va por el compositor.
    const caja = e => { const r = e.getBoundingClientRect(); return { top: r.top, left: r.left, width: r.width, height: r.height }; };
    const radio = e => parseFloat(getComputedStyle(e).borderTopLeftRadius) || 0;
    const fraccion = pos => (pos || '50% 50%').split(' ').map(v => (v.endsWith('%') ? parseFloat(v) / 100 : .5));
    const estado = (capa, c, pos, rad) => {
      const img = $('img', capa), W = img.naturalWidth || 1600, H = img.naturalHeight || 900;
      const vw = innerWidth, vh = innerHeight;
      const [px, py] = fraccion(pos);
      const sb = Math.max(c.width / W, c.height / H), sf = Math.max(vw / W, vh / H);
      const ox = c.left + (c.width - W * sb) * px, oy = c.top + (c.height - H * sb) * py;
      const fx = (vw - W * sf) / 2, fy = (vh - H * sf) / 2;
      const k = sb / sf;
      return {
        clipPath: `inset(${c.top}px ${vw - c.left - c.width}px ${vh - c.top - c.height}px ${c.left}px round ${rad}px)`,
        transform: `translate(${ox - k * fx}px, ${oy - k * fy}px) scale(${k})`,
      };
    };
    const crearCapa = (src, desde, pos, rad) => {
      const capa = el('div', 'lx-paso');
      const img = el('img');
      img.alt = ''; img.src = src;
      capa.appendChild(img);
      document.body.appendChild(capa);
      const poner = () => { const e = estado(capa, desde, pos, rad); capa.style.clipPath = e.clipPath; img.style.transform = e.transform; capa._e = e; };
      poner();
      capa.listo = (img.decode ? img.decode().catch(() => {}) : Promise.resolve()).then(poner);
      return capa;
    };
    const mover = (capa, a, pos, rad, ms) => capa.listo.then(() => {
      const img = $('img', capa), de = capa._e, e = estado(capa, a, pos, rad);
      const op = { duration: ms, easing: 'cubic-bezier(.76,0,.24,1)', fill: 'forwards' };
      capa._e = e;
      return Promise.all([
        capa.animate([{ clipPath: de.clipPath }, { clipPath: e.clipPath }], op).finished,
        img.animate([{ transform: de.transform }, { transform: e.transform }], op).finished,
      ]);
    });
    const pantalla = () => ({ top: 0, left: 0, width: innerWidth, height: innerHeight });
    const quitar = capa => { capa.classList.add('is-fuera'); setTimeout(() => capa.remove(), 280); };
    const heroTipo = () => $('.lx-capa-tipo img');
    const cortarTelon = () => $$('.lx-telon-capa').forEach(f => f.classList.remove('is-entrando', 'is-saliendo'));

    /* Cuatro maneras de hacer el cambio, elegibles en el panel «Propuestas»
       (?paso=1|2|3|4). Todas comparten lo de debajo (precarga, catálogo viejo
       oculto, scroll colocado una vez, vuelta a la portada de origen); solo
       cambia cómo se tapa la vista vieja y cómo se descubre la nueva.
         1 Foto     la foto de la portada se abre a pantalla y se recoge en el
                    hero (la primera versión, ~2 s).
         2 Fundido  fundido corto a papel (120 ms) y la vista nueva sube 14 px
                    mientras el papel se va (220–260 ms).
         3 Telón    la diagonal del hero barre la pantalla en tinta (300 ms)
                    y sale por la izquierda descubriendo la vista (340 ms).
         4 Viaje    View Transitions: la foto de la portada viaja hasta el hero
                    sin pasar por pantalla completa. Sin soporte en el
                    navegador, cae al fundido. */
    const estilo = pasoActual();
    let enCurso = false;
    const EASE = 'cubic-bezier(.76,0,.24,1)';
    const cortina = clase => { const c = el('div', 'lx-cortina ' + clase); document.body.appendChild(c); return c; };
    const anim = (e, frames, ms, easing = EASE) => e.animate(frames, { duration: ms, easing, fill: 'forwards' }).finished;
    const TELON_ENTRA = [{ clipPath: 'polygon(100% 0%, 100% 0%, 101% 100%, 125% 100%)' }, { clipPath: 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)' }];
    const TELON_SALE = [{ clipPath: 'polygon(0% 0%, 100% 0%, 125% 100%, 0% 100%)' }, { clipPath: 'polygon(0% 0%, 0% 0%, 0% 100%, 0% 100%)' }];
    // Lo que sube 14 px al descubrir la vista nueva (nunca la lista entera:
    // dentro va la barra fija Grid/List, que con transform dejaría de ser fija).
    const subir = (sel, ms) => Promise.all($$(sel).filter(e => e.offsetParent).map(e =>
      e.animate([{ transform: 'translateY(14px)', opacity: .001 }, { transform: 'none', opacity: 1 }], { duration: ms, easing: 'cubic-bezier(.25,1,.5,1)' }).finished));
    const VISTA_TIPO = '.lx-hero-pie, .fg-catalog-head, .fg-filter-bar, .fg-units-grid';
    const VISTA_PORTADAS = '.fg-types-section';
    const usaViaje = () => estilo === '4' && typeof document.startViewTransition === 'function';
    // Nombres de View Transitions: solo mientras dura el viaje, y uno por
    // elemento (dos iguales a la vez abortan la transición).
    let nombrados = [];
    const nombrar = (e, n) => { if (e) { e.style.viewTransitionName = n; nombrados.push(e); } };
    const desnombrar = () => { nombrados.forEach(e => { e.style.viewTransitionName = ''; }); nombrados = []; };

    const entrarYa = function (modelo) {
      const band = modelo && $(`.fg-type-band[data-model="${modelo}"]`);
      const foto = band && $('.fg-card-img img', band);
      if (enCurso || !band || !foto || !$('.lx-hero')) return abrir.apply(this, arguments);
      enCurso = true;
      const args = arguments, yo = this;
      const pedido = precargar(modelo);
      raiz.classList.add('lx-cambiando');
      callar(true);
      const terminar = () => { raiz.classList.remove('lx-cambiando'); callar(false); enCurso = false; };
      // Debajo: cambia la vista, llegan los datos, scroll arriba, fotos listas.
      const cambiar = (maxFotos = PASO.maxEspera) => {
        const llegan = datos();
        abrir.apply(yo, args);           // → modoTipo: el hero pasa a esta tipología
        cortarTelon();                   // sin telón del slider: el relevo es del paso
        return llegan.then(() => {
          prepararCards(); refrescar();
          raiz.classList.remove('lx-cambiando');
          scrollTo(0, 0);
          return Promise.race([listas([heroTipo(), ...fotosVisibles('.fg-units-grid > .fg-card .fg-card-img > img', 8)].filter(Boolean)), espera(maxFotos)]);
        });
      };

      if (reducido()) {
        // Un solo corte: se espera a los datos con la vista de portadas intacta.
        return pedido.then(() => cambiar(600)).then(terminar);
      }
      if (estilo === '2' || (estilo === '4' && !usaViaje())) {
        const velo = cortina('is-papel');
        return anim(velo, [{ opacity: 0 }, { opacity: 1 }], 120, 'ease-out')
          .then(() => pedido).then(() => cambiar(120)).then(marco)
          .then(() => Promise.all([anim(velo, [{ opacity: 1 }, { opacity: 0 }], 220, 'ease-out'), subir(VISTA_TIPO, 260)]))
          .then(() => { velo.remove(); terminar(); });
      }
      if (estilo === '3') {
        const telon = cortina('is-tinta');
        return Promise.all([anim(telon, TELON_ENTRA, 300), pedido]).then(() => cambiar(150)).then(marco)
          .then(() => Promise.all([anim(telon, TELON_SALE, 340), subir(VISTA_TIPO, 340)]))
          .then(() => { telon.remove(); terminar(); });
      }
      if (estilo === '4') {
        return pedido.then(() => {
          nombrar(foto, 'lx-foto');
          raiz.classList.add('lx-viaje');
          const vt = document.startViewTransition(() => {
            desnombrar();
            // En el estado nuevo: la foto del hero recibe el nombre (la
            // portada se coloca en ella) y el pie del hero entra después,
            // cuando la foto ya ha llegado (lx-texto, ver CSS 14).
            return cambiar(250).then(() => { nombrar(heroTipo(), 'lx-foto'); nombrar($('.lx-hero-pie'), 'lx-texto'); entrarCards(320); });
          });
          return vt.finished.catch(() => {});
        }).then(() => { desnombrar(); raiz.classList.remove('lx-viaje'); terminar(); });
      }
      // Desde la portada (visible) la foto se abre desde su sitio; desde otra
      // tipología o la ficha («Villas similares») la portada está oculta y la
      // foto entra a pantalla completa con un fundido.
      const r = foto.getBoundingClientRect();
      const aLaVista = r.width > 0 && r.bottom > 0 && r.top < innerHeight;
      const capa = crearCapa(foto.currentSrc || foto.src, aLaVista ? caja(foto) : pantalla(),
        aLaVista ? getComputedStyle(foto).objectPosition : '50% 50%', aLaVista ? radio($('.fg-card-img', band)) : 0);
      if (!aLaVista) { capa.classList.add('is-fuera'); void capa.offsetWidth; capa.classList.remove('is-fuera'); }
      const cubierta = aLaVista ? mover(capa, pantalla(), '50% 50%', 0, PASO.abre) : capa.listo.then(() => espera(360));
      return Promise.all([cubierta, pedido]).then(() => {
        const llegan = datos();
        abrir.apply(yo, args);           // → modoTipo: el hero pasa a esta tipología
        cortarTelon();                   // sin telón: el relevo lo hace la capa
        return llegan;
      }).then(() => {
        prepararCards(); refrescar();
        raiz.classList.remove('lx-cambiando');
        scrollTo(0, 0);
        return listas([heroTipo(), ...fotosVisibles('.fg-units-grid > .fg-card .fg-card-img > img', 8)].filter(Boolean));
      }).then(marco).then(() => {
        const destino = heroTipo();
        entrarCards();
        return mover(capa, caja(destino.closest('.lx-hero')), getComputedStyle(destino).objectPosition, 0, PASO.cierra);
      }).then(() => { quitar(capa); terminar(); });
    
    };

    const salirYa = function () {
      const modelo = typeof currentFilters !== 'undefined' && currentFilters.model;
      const yo = this, args = arguments;
      if (enCurso || !modelo) return volver.apply(this, arguments);
      enCurso = true;
      raiz.classList.add('lx-cambiando');
      callar(true);
      const terminar = () => { raiz.classList.remove('lx-cambiando'); callar(false); enCurso = false; };
      const aSuPortada = () => {
        const band = $(`.fg-type-band[data-model="${modelo}"]`);
        if (!band) return null;
        scrollTo(0, Math.max(0, band.getBoundingClientRect().top + scrollY - (innerWidth > 900 ? 110 : 80)));
        return band;
      };
      // Debajo: vuelven las portadas, scroll a la de origen, su foto lista.
      const cambiar = (maxFoto = PASO.maxEspera) => {
        volver.apply(yo, args);
        cortarTelon();
        const band = aSuPortada();
        revelar();
        $$('.fg-type-band [data-lx-rev]').forEach(n => n.classList.add('is-visto'));
        const foto = band && $('.fg-card-img img', band);
        return Promise.race([listas(foto ? [foto] : []), espera(maxFoto)]).then(() => ({ band, foto }));
      };
      if (reducido()) {
        return cambiar(0).then(terminar);
      }
      if (estilo === '2' || (estilo === '4' && !usaViaje())) {
        const velo = cortina('is-papel');
        return anim(velo, [{ opacity: 0 }, { opacity: 1 }], 120, 'ease-out')
          .then(() => cambiar(120)).then(marco)
          .then(() => Promise.all([anim(velo, [{ opacity: 1 }, { opacity: 0 }], 220, 'ease-out'), subir(VISTA_PORTADAS, 260)]))
          .then(() => { velo.remove(); terminar(); });
      }
      if (estilo === '3') {
        const telon = cortina('is-tinta');
        return anim(telon, TELON_ENTRA, 300).then(() => cambiar(150)).then(marco)
          .then(() => Promise.all([anim(telon, TELON_SALE, 340), subir(VISTA_PORTADAS, 340)]))
          .then(() => { telon.remove(); terminar(); });
      }
      if (estilo === '4') {
        const ht = heroTipo();
        const hero = ht && ht.closest('.lx-hero');
        if (ht && hero.getBoundingClientRect().bottom > 80) nombrar(ht, 'lx-foto');
        raiz.classList.add('lx-viaje');
        const vt = document.startViewTransition(() => {
          desnombrar();
          // Al revés: la foto del hero vuelve a su portada y la ficha de la
          // portada (texto, precio, botones) entra cuando la foto ha llegado.
          return cambiar(250).then(({ band, foto }) => {
            nombrar(foto, 'lx-foto');
            nombrar(band && ($('.lx-portada', band) || $('.fg-card-body', band)), 'lx-texto');
          });
        });
        return vt.finished.catch(() => {}).then(() => { desnombrar(); raiz.classList.remove('lx-viaje'); terminar(); });
      }
      const ht = heroTipo();
      const hero = ht && ht.closest('.lx-hero');
      const visible = hero && hero.getBoundingClientRect().bottom > 80;
      const capa = crearCapa(ht ? (ht.currentSrc || ht.src) : '', visible ? caja(hero) : pantalla(), ht ? getComputedStyle(ht).objectPosition : '50% 50%', 0);
      if (!visible) capa.classList.add('is-fuera');
      void capa.offsetWidth;
      capa.classList.remove('is-fuera');
      return mover(capa, pantalla(), '50% 50%', 0, PASO.abre).then(() => {
        volver.apply(yo, args);
        cortarTelon();
        const band = aSuPortada();
        const foto = band && $('.fg-card-img img', band);
        revelar();
        $$('.fg-type-band [data-lx-rev]').forEach(n => n.classList.add('is-visto'));
        return listas(foto ? [foto] : []).then(marco).then(() => foto
          ? mover(capa, caja(foto), getComputedStyle(foto).objectPosition, radio($('.fg-card-img', band)), PASO.cierra)
          : null);
      }).then(() => { quitar(capa); terminar(); });
    
    };
    // En cola: si se pide entrar o volver mientras otro cambio sigue en
    // marcha (p. ej. «Tipos de villa» antes de que acabe la entrada, con la
    // primera petición lenta), espera a que termine en vez de caer al
    // comportamiento del sitio, que no vuelve a la portada de origen.
    let enMarcha = Promise.resolve();
    const enCola = fn => function () {
      const yo = this, args = arguments;
      const p = enMarcha.then(() => fn.apply(yo, args));
      enMarcha = Promise.resolve(p).catch(() => {});
      return p;
    };
    const entrar = enCola(entrarYa);
    const salir = enCola(salirYa);
    entrar.lxPaso = true;
    window.openVillaType = entrar;
    window.backToVillaTypes = salir;
  }

  // Fotos de las cards: hueco reservado (aspect-ratio + color de fondo) y
  // fundido al cargar, en vez de aparecer de golpe.
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

  /* ── ARRANQUE ───────────────────────────────────────────────────────────── */
  function refrescar() {
    cabecera();
    bandas();
    plantas();
    portadas();
    revelar();
    cards();
    fotosSuaves();
  }
  window.lujo = { refrescar, MEDIOS, registro };

  function iniciar() {
    document.documentElement.classList.add('lx');
    const variante = varianteActual();
    const transicion = transicionActual();
    propuestas(variante, transicion);
    if (variante === 'b') document.documentElement.classList.add('lx-var-b');
    interceptarAcceso();
    envolverFicha();
    hero();
    pasoTipologias();
    transiciones(transicion);
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
