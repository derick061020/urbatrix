/* ============================================================================
   BAHÍA MAR — Capa "eco" v7 · JS de apoyo
   Acompaña a bahia-mar-eco.css. Solo inserta markup y decide qué sombra va en
   cada villa —y, desde la sección 12, en cada tarjeta del catálogo—; toda la
   geometría y la animación viven en el CSS.

   Lo ideal es que esto salga del template. Existe para no tocar el motor de
   render en la primera iteración — ver docs/capa-eco.md.
   ============================================================================ */

(() => {
  'use strict';

  /* Una sombra por villa, en orden. Cada entrada tiene su regla
     [data-eco-shade="..."] en el CSS: con dos assets y cinco tipologías, lo
     que cambia entre variantes es escala, corte y fuerza, nunca el asset.

     El reparto es fronda / monstera / fronda / monstera / fronda a propósito.
     Con 3 frondas y 2 monsteras en 5 huecos NO existe orden que evite a la vez
     el asset repetido en bandas seguidas y la alternancia estricta, y de las
     dos cosas la que salta a la vista es la repetición en contiguas. Si el
     patrón llega a leerse, la solución no es reordenar: es un TERCER asset
     (cómo generarlo, en CLAUDE.md → Assets).

     Con más de cinco tipologías el ciclo se repite; para no repetir, añade
     entrada aquí y su regla en el CSS. */
  const SOMBRAS = ['fronda', 'monstera', 'fronda-alt', 'monstera-alt', 'fronda-corta'];

  /* Ritmos del balanceo, en segundos. NINGUNO puede ser múltiplo de otro ni
     del hero (47s): si se sincronizan, el ojo detecta el loop y el efecto
     pasa de "hay un árbol ahí fuera" a "hay un gif de fondo". Por eso primos:
     entre primos distintos el múltiplo es imposible, así que la lista crece
     sin tener que comprobar nada — solo hay que no repetir y no usar el 47.

     ⚠️ Estos son la MITAD del ciclo, no el ciclo. La animación va con
     `alternate`, así que un 29 son 58 segundos de ida y vuelta. Los valores
     viejos (29-43) daban entre 58 y 86 segundos para recorrer 5 píxeles: el
     movimiento estaba escrito pero no ocurría a una velocidad que un ojo
     humano pueda registrar.

     Los de ahora (11-23) dejan el ciclo completo entre 22 y 46 segundos. Con
     la amplitud del CSS eso son ~2,5px por segundo, que es donde el ojo lo
     registra sin buscarlo. Sigue siendo lento — una fronda mecida no es un
     abanico.

     ⚠️ Los de la banda verde NO están aquí, están en el CSS (29 y 31), porque
     no son por-villa. Si tocas estos, míralos: los siete números tienen que
     ser primos DISTINTOS entre sí, y ninguno puede ser 47, que es el hero.  */
  const RITMOS = [13, 17, 11, 19, 23];

  /* ⚠️ MÉTRICAS PLACEHOLDER — Bahía Mar tiene que confirmarlas antes de
     publicar. Publicar datos ambientales inventados es exactamente el
     problema que esta capa pretende evitar.

     El copy NO se escribe aquí: el sitio tiene selector ES/EN y este texto
     tiene que pasar por el sistema de traducción, así que lo inyecta el Blade
     en `window.ECO_COPY` con `__()` (ver home.blade.php). Lo de abajo es solo
     el respaldo por si el bloque no llegara a pintarse. */
  const COPY = window.ECO_COPY || {};

  const COMPROMISO = COPY.compromiso || {
    eyebrow: 'Compromiso',
    titulo:  'El proyecto se adapta al terreno. No al revés.',
    stats: [
      { valor: '68%',   label: 'Del terreno preservado' },
      { valor: '1.240', label: 'Árboles nativos conservados' },
      { valor: '100%',  label: 'Agua de lluvia captada' }
    ]
  };

  const CONTADOR = COPY.contador ||
    '<b>68%</b> del terreno preservado · <b>1.240</b> árboles nativos';

  /* Tras qué banda se inserta la sección de compromiso (0 = tras la 1ª). Va en
     el MEDIO: es donde rompe el ritmo sin partir la lista en un bloque corto y
     otro largo.

     ⚠️ No es un número fijo, y no puede serlo. El clon tiene 5 tipologías, pero
     el demo de consola se pega sobre el SITIO REAL, que hoy tiene 3 — y un
     índice escrito a mano deja de significar "el medio" en cuanto cambia el
     número: un 2 pensado para 5 manda la banda al final si solo hay 3.
     Calculado sale solo, y con cualquier cantidad: 3 → 1, 4 → 1, 5 → 2. */
  const posicionBanda = total => Math.floor((total - 1) / 2);


  /* ── 1. Sombra vegetal por tipología ────────────────────────────────────
     El LADO no se puede asumir: el layout alterna foto/texto, y el orden en
     que llegan las tipologías no lo decide esta capa.

     Tampoco vale comparar getBoundingClientRect(): en el primer render las
     imágenes son lazy y aún no ocupan su sitio, así que la comparación sale
     mal. (Pasó: Villa C acababa con la planta en el lado contrario.)

     La señal fiable es la columna del grid, que viene del CSS y no depende
     de que haya cargado nada. Devuelve 'left' o 'right', y el CSS lo traduce
     a `grid-column: 1 | 2` — la sombra cae en la columna del texto y por
     construcción no puede tocar la foto.                                   */
  function ladoDelTexto(band) {
    const body  = band.querySelector('.fg-card-body');
    const inner = band.querySelector('.fg-card-inner');
    if (!body || !inner) return 'left';

    const columnas = getComputedStyle(inner).gridTemplateColumns
                       .split(/\s+/).filter(Boolean).length;
    if (columnas < 2) return 'left';        // apilado en móvil: no hay lados

    const col = parseInt(getComputedStyle(body).gridColumnStart, 10);
    return Number.isFinite(col) && col >= 2 ? 'right' : 'left';
  }

  function pintarSombras() {
    document.querySelectorAll('.fg-type-band').forEach((band, i) => {
      if (band.querySelector('.eco-shade')) return;

      const inner = band.querySelector('.fg-card-inner');
      if (!inner) return;

      band.dataset.ecoSide  = ladoDelTexto(band);
      band.dataset.ecoShade = SOMBRAS[i % SOMBRAS.length];
      band.style.setProperty('--eco-ritmo', RITMOS[i % RITMOS.length] + 's');

      /* Dos capas: la exterior es el grid item y recorta; la interior lleva
         la imagen y la deriva. Sin el recorte, el scale del balanceo saca la
         sombra de su celda y se mete en la columna de la foto — ver el
         comentario de .eco-shade en el CSS. */
      const sh = document.createElement('div');
      sh.className = 'eco-shade';
      sh.setAttribute('aria-hidden', 'true');
      sh.appendChild(document.createElement('i'));
      inner.appendChild(sh);
    });
  }

  /* ── 1b. Sombra vegetal en las tarjetas del catálogo ────────────────────
     Sección 12 del CSS.

     ⚠️ NO TODAS LAS TARJETAS LLEVAN HOJA — una de cada tres, y el reparto es
     lo único delicado de esta función.

     Con hoja en todas, veinte tarjetas iguales convierten la sombra en un
     patrón de papel pintado: deja de leerse como "hay una planta ahí fuera" y
     pasa a leerse como una textura del componente. Salteándolas, cada hoja
     vuelve a ser un accidente de luz — y como el fondo salvia ya es el que
     sostiene el verde (12.1), no hace falta que la vegetación esté en todas
     para que la sección se lea verde.

     El PATRÓN va dentro de un ciclo de 17. Diecisiete es primo, así que no
     comparte factor con ninguna de las anchuras del grid (5, 4, 3, 2 y 1
     columnas) y el reparto no puede alinearse en columnas ni en filas con
     ninguna de ellas. Un `i % 3`, que es lo primero que uno escribe, dibuja
     franjas verticales limpias en cuanto el grid baja a 3 columnas.

     Los huecos dentro del ciclo tampoco son regulares (3, 4, 2, 3, 2, 3): con
     un paso constante el ojo encuentra el ritmo aunque el ciclo sea primo.

     Y las ESPECIES: seis hojas por ciclo contra cinco variantes hace que la
     rotación se desplace una posición en cada vuelta, así que la combinación
     de qué hoja cae en qué sitio no se repite nunca. De ahí que se sienta que
     hay varias y no dos alternándose.

     ⚠️ EL ÍNDICE ES EL DEL DOM, no un contador propio. El grid se re-renderiza
     al filtrar y las tarjetas nuevas llegan mezcladas con las que ya tenían
     sombra; un contador que sólo avanzara con las nuevas repartiría distinto
     en cada filtrado. Con el índice del DOM, una tarjeta cae siempre en la
     misma variante esté quien esté a su lado.

     Va dentro de `.fg-card-body` a propósito: es la caja que recorta la
     sombra y la que garantiza que no toque la foto — el equivalente a pedir
     la columna del texto en las bandas. */
  const CICLO  = 17;
  const PATRON = [0, 3, 7, 9, 12, 14];   // posiciones con hoja dentro del ciclo

  function hojaDe(i) {
    const pos = PATRON.indexOf(i % CICLO);
    if (pos === -1) return null;         // esta tarjeta va limpia
    const n = Math.floor(i / CICLO) * PATRON.length + pos;
    return SOMBRAS[n % SOMBRAS.length];
  }

  function pintarSombrasCatalogo() {
    document.querySelectorAll('.fg-units-grid > .fg-card').forEach((card, i) => {
      const especie = hojaDe(i);
      const actual  = card.querySelector('.eco-shade-card');

      /* Al filtrar, una tarjeta puede caer en otro índice y quedarse con una
         hoja que ya no le toca. Se retira antes de decidir nada más. */
      if (!especie) {
        if (actual) actual.remove();
        delete card.dataset.ecoShade;
        return;
      }

      card.dataset.ecoShade = especie;
      if (actual) return;

      const body = card.querySelector('.fg-card-body');
      if (!body) return;

      const sh = document.createElement('div');
      sh.className = 'eco-shade-card';
      sh.setAttribute('aria-hidden', 'true');
      sh.appendChild(document.createElement('i'));
      body.insertBefore(sh, body.firstChild);
    });
  }

  /* El grid colapsa a una columna en móvil, así que el lado cambia con el
     ancho. Sin esto la sombra se queda donde estaba al cargar. */
  let resizeTimer;
  function recalcularLados() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      document.querySelectorAll('.fg-type-band').forEach(band => {
        band.dataset.ecoSide = ladoDelTexto(band);
      });
    }, 150);
  }


  /* ── 2. Banda de compromiso ───────────────────────────────────────────
     ⚠️ SE AÑADE AL FINAL DEL DOM Y SE COLOCA CON `order`.

     El sitio alterna foto/texto con `.fg-type-band:nth-child(2n+1)`. Meter
     la banda EN MEDIO del grid cambia la paridad de todas las que van
     después: con 3 tipologías, Villa C pasaba de impar a par y acababa con
     la foto en el mismo lado que Villa B. Se perdía el zigzag del sitio.

     `.fg-types-grid` es flex column, así que `order` la coloca donde
     queramos sin moverla en el DOM: el sitio ve exactamente la misma
     secuencia de hijos que sin la capa, y nth-child sigue cuadrando.      */
  function pintarBandaCompromiso() {
    if (document.querySelector('.eco-band')) return;

    const bandas = [...document.querySelectorAll('.fg-type-band')];
    if (!bandas.length) return;

    const grid = bandas[0].parentElement;
    if (!grid) return;

    const sec = document.createElement('section');
    sec.className = 'eco-band';
    sec.innerHTML = `
      <div class="eco-band-foliage" aria-hidden="true"></div>
      <p class="eco-band-eyebrow">${COMPROMISO.eyebrow}</p>
      <h2 class="eco-band-title">${COMPROMISO.titulo}</h2>
      <div class="eco-band-stats">
        ${COMPROMISO.stats.map(s =>
          `<div class="eco-band-stat"><b>${s.valor}</b><span>${s.label}</span></div>`
        ).join('')}
      </div>`;

    grid.appendChild(sec);          // al final: no toca la paridad de nadie
    colocarBanda();
  }

  /* Reparte `order` para que la banda caiga en el medio. Hay que numerar
     también las villas: con order:0 por defecto, la banda se quedaría al
     final. Se rellama tras cada re-render del grid — y ahí está la gracia de
     recalcular la posición cada vez: si el re-render deja otro número de
     tipologías, la banda se recoloca sola. */
  function colocarBanda() {
    const sec = document.querySelector('.eco-band');
    if (!sec) return;

    const bandas = [...document.querySelectorAll('.fg-type-band')];
    const pos = posicionBanda(bandas.length);
    let n = 1;
    bandas.forEach((band, i) => {
      band.style.order = n++;
      if (i === pos) sec.style.order = n++;
    });
    if (pos >= bandas.length) sec.style.order = n;   // fuera de rango: al final
  }


  /* ── 3. Contador eco del header ─────────────────────────────────────── */
  function pintarContador() {
    const nav = document.querySelector('.nav-center');
    if (!nav || nav.querySelector('.eco-counter')) return;
    const el = document.createElement('span');
    el.className = 'eco-counter';
    el.innerHTML = CONTADOR;
    nav.appendChild(el);
  }


  /* ── 4. Entradas al entrar en pantalla ──────────────────────────────────
     ⚠️ EL ESTADO OCULTO SE ESCRIBE DESDE AQUÍ, nunca desde el CSS.

     Si `.eco-reveal` viniera puesto en la hoja de estilos y este archivo no
     llegara a ejecutarse —un 404, un error más arriba, un navegador sin
     IntersectionObserver— la home entera se quedaría en blanco: el contenido
     seguiría en el DOM, invisible, sin nadie que lo encienda. Poniéndolo el
     JS, el peor caso es que las bandas aparezcan ya montadas, que es
     exactamente como estaban antes de esta sección.

     Por eso también el guard de IntersectionObserver va ANTES de marcar
     nada. */
  const observador = 'IntersectionObserver' in window
    ? new IntersectionObserver((entradas, obs) => {
        entradas.forEach(e => {
          if (!e.isIntersecting) return;
          e.target.classList.add('eco-in');
          obs.unobserve(e.target);      // se entra una vez; no es un efecto de scroll
        });
      }, {
        /* Un poco antes del borde inferior: la entrada tiene que estar
           terminando cuando la banda llega al centro de la pantalla, no
           empezando. Con `0px` se ve arrancar y parece que va con retraso. */
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.08
      })
    : null;

  function revelar() {
    if (!observador) return;
    document.querySelectorAll('.fg-type-band, .eco-band').forEach(el => {
      if (el.classList.contains('eco-reveal')) return;

      /* Lo que ya está en pantalla al cargar NO se anima: se marca como
         entrado y punto. Animar lo visible obliga al usuario a esperar a que
         aparezca algo que ya debería estar ahí, y encima compite con la
         entrada del hero. */
      const r = el.getBoundingClientRect();
      const visible = r.top < innerHeight * 0.9 && r.bottom > 0;

      el.classList.add('eco-reveal');
      if (visible) el.classList.add('eco-in');
      else observador.observe(el);
    });
  }


  /* ⚠️ Aquí vivía una entrada para la píldora del header, enganchada a
     `makai:hero-revealed`. Está retirada, y el porqué está escrito entero en
     el CSS (sección 11, "LA PÍLDORA DEL HEADER NO ENTRA"). Resumen: no
     conseguí demostrar que la transición terminara siempre, y su fallo dejaba
     la cabecera invisible. No lo reintentes sin leer aquello. */


  /* La banda no altera la paridad (va al final del DOM, colocada con
     `order`), así que el orden de estas llamadas es indiferente. Se mantiene
     por legibilidad.

     `revelar()` sí va la última: necesita que la banda de compromiso exista
     ya para poder observarla. */
  function init() {
    pintarBandaCompromiso();
    pintarSombras();
    pintarSombrasCatalogo();
    pintarContador();
    revelar();
  }

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', init)
    : init();

  addEventListener('resize', recalcularLados);

  /* El grid se re-renderiza al filtrar/ordenar. Si en el código real existe
     un hook de render, llamar a init() desde ahí y borrar este observer.

     Solo escucha childList, así que los `style.order` que escribe
     colocarBanda() no lo vuelven a disparar. */
  const host = document.querySelector('.fg-types-section');
  if (host) {
    new MutationObserver(() => {
      pintarBandaCompromiso();
      colocarBanda();            // las villas nuevas nacen sin order
      pintarSombras();
      recalcularLados();         // por si el re-render cambió el orden
      revelar();                 // y las nuevas nacen sin entrada
    }).observe(host, { childList: true, subtree: true });
  }

  /* El catálogo tiene su propio ciclo de vida: se filtra, se ordena y carga
     por tandas al bajar, y cada tanda son tarjetas nuevas sin sombra.

     ⚠️ Sólo `childList` y SIN `subtree`. La sombra se inserta DENTRO de una
     tarjeta, así que un observer con subtree se dispararía a sí mismo en
     bucle. Escuchando sólo a los hijos directos del grid, lo único que lo
     despierta es que entren o salgan tarjetas — que es justo el caso. */
  const gridCatalogo = document.querySelector('.fg-units-grid');
  if (gridCatalogo) {
    new MutationObserver(pintarSombrasCatalogo).observe(gridCatalogo, { childList: true });
  }
})();
