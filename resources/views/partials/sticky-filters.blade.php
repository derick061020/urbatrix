{{--
    La barra de filtros y el contador se quedan fijos al hacer scroll.

    Antes se iban con la página: para cambiar un filtro —o para leer cuántas
    unidades estaba viendo— había que volver arriba del todo. En un catálogo de
    varios cientos eso es un viaje largo.

    Las medidas no se escriben a mano: el alto de la barra superior y el de la
    cinta de «volver» se miden en vivo y se publican como variables CSS, porque
    cambian con el ancho, con el idioma y según el proyecto.
--}}
<style>
  /* La zona pegajosa necesita fondo propio: si no, las tarjetas se ven por
     debajo al pasar. Se toma el del contenedor del listado, que cambia por
     proyecto (papel en Bahía Mar, gris azulado en los otros).

     Pintado a sangre y no sobre el elemento: la cinta de «volver» está limitada
     a 1440 px y la barra a 1760, así que un fondo propio dejaba las tarjetas
     asomando por los lados en pantallas anchas. El pseudo se estira a 100vw y
     va detrás del contenido, dentro del mismo contexto de apilamiento. */
  body.has-sticky-filters .fg-catalog-head::before,
  body.has-sticky-filters .fg-filter-bar::before {
    content: "";
    position: absolute;
    top: 0; bottom: 0;
    left: 50%;
    /* El ancho lo pone el JS: 100vw incluye la barra de scroll y sacaría una
       barra horizontal de unos pocos píxeles. */
    width: var(--fg-sticky-bleed, 100vw);
    transform: translateX(-50%);
    z-index: -1;
    background: var(--fg-sticky-bg, #f2f5f8);
  }

  body.has-sticky-filters .fg-catalog-head {
    position: sticky;
    top: var(--fg-sticky-top, 88px);
    z-index: 31;
  }
  body.has-sticky-filters .fg-filter-bar {
    position: sticky;
    top: calc(var(--fg-sticky-top, 88px) + var(--fg-sticky-head, 0px));
    z-index: 30;
  }

  /* Pegada ocupa alto de pantalla: en cuanto se despega, la cinta respira
     menos arriba y la barra menos abajo. Suma unos 20 px de catálogo visible. */
     Sin transición a propósito: el alto de la cinta se vuelve a medir al
     pegarse (es lo que separa la barra de abajo), y animarlo haría que la
     medida cayera a mitad de camino. */
  body.has-sticky-filters.is-filters-stuck .fg-catalog-head { padding-top: 14px; }
  body.has-sticky-filters.is-filters-stuck .fg-filter-bar { padding-bottom: 12px; }

  /* Al despegarse del flujo, una sombra corta para que se lea como una barra
     por encima del listado y no como parte de él. */
  body.has-sticky-filters .fg-filter-bar::after {
    content: "";
    position: absolute;
    left: 0; right: 0; top: 100%;
    height: 14px;
    opacity: 0;
    pointer-events: none;
    background: linear-gradient(to bottom, rgba(16, 24, 40, 0.07), rgba(16, 24, 40, 0));
    transition: opacity 0.2s ease;
  }
  body.has-sticky-filters.is-filters-stuck .fg-filter-bar::after { opacity: 1; }

  /* Pegada, la banda necesita leerse como una capa por encima del listado.
     Miguel: «cuando haces scroll se mezcla todo del mismo color». No se mete
     un color nuevo —rompería la paleta del proyecto—: se usa la tinta de la
     marca a muy baja opacidad sobre el mismo papel, más un filo de 1px. Así
     la frontera existe en los tres proyectos sin desentonar en ninguno. */
  body.has-sticky-filters.is-filters-stuck .fg-catalog-head::before,
  body.has-sticky-filters.is-filters-stuck .fg-filter-bar::before {
    background:
      linear-gradient(var(--brand-soft-2, rgba(16,24,40,.04)), var(--brand-soft-2, rgba(16,24,40,.04))),
      var(--fg-sticky-bg, #f2f5f8);
  }
  body.has-sticky-filters.is-filters-stuck .fg-filter-bar::before {
    box-shadow: inset 0 -1px 0 var(--brand-soft-3, rgba(16,24,40,.12));
  }

  /* La barra de arriba es una píldora que flota con los lados y el hueco
     superior transparentes, así que las fotos de las tarjetas se veían pasar
     por detrás de ella. Pegada, el papel de la banda sube hasta el borde de la
     pantalla y la píldora queda apoyada encima, que es lo que pidió Miguel:
     «que el sticky de los filtros incluya al header». El JS marca cuál es la
     primera pieza pegada, porque en Makai y Siebe no hay cinta de tipos y la
     primera es la barra de filtros. */
  body.has-sticky-filters.is-filters-stuck .is-sticky-first::before {
    top: calc(-1 * var(--fg-sticky-top, 88px));
  }

  /* Lo mismo, pintado en la barra de arriba (el <nav> fijo a todo lo ancho,
     transparente, con la píldora dentro). El pseudo de arriba no basta en el
     iPhone: en móvil la barra lleva overflow-x: clip y Safari recorta también
     lo que sobresale por arriba, así que entre la píldora y los filtros
     quedaba una franja por la que pasaban las fotos (Derick, 23 de octubre:
     «se ve un poco sucio»). Con el fondo en el propio <nav>, la píldora y los
     filtros quedan sobre una sola banda, y no depende del recorte. */
  /* !important porque el <nav> trae background:transparent en línea. */
  body.has-sticky-filters.is-filters-stuck .fg-sticky-topbar {
    background:
      linear-gradient(var(--brand-soft-2, rgba(16,24,40,.04)), var(--brand-soft-2, rgba(16,24,40,.04))),
      var(--fg-sticky-bg, #f2f5f8) !important;
  }

  /* En pantallas estrechas sólo se fija la barra de filtros: la cinta de
     «volver» ocupa demasiado alto de un viewport de móvil. */
  @media (max-width: 760px) {
    /* relative y no static: el fondo a sangre es un pseudo absoluto y tiene
       que seguir anclado a la cinta. */
    body.has-sticky-filters .fg-catalog-head { position: relative; }
    body.has-sticky-filters .fg-filter-bar { top: var(--fg-sticky-top, 72px); }
  }
</style>

<script>
(function () {
  var bar = document.querySelector('.fg-filter-bar');
  if (!bar || !('CSS' in window) || !CSS.supports || !CSS.supports('position', 'sticky')) return;

  var head = document.querySelector('.fg-catalog-head');
  var zone = document.getElementById('main-unit-reserve-list');
  var root = document.documentElement;

  // Alto de la barra superior fija. Se parte de un elemento que sólo existe
  // dentro del navbar y se sube hasta el primer ancestro fijo; sondear por
  // coordenadas no sirve, porque ahí arriba también hay piezas fijas del hero
  // que miden casi la pantalla entera.
  var NAV_HINTS = ['.nav-center', '[onclick*="toggleProfileMenu"]', '.lang-toggle', '[onclick*="toggleMenu"]'];

  function topBarHeight() {
    for (var i = 0; i < NAV_HINTS.length; i++) {
      var el = document.querySelector(NAV_HINTS[i]);
      while (el && el !== document.body) {
        var cs = getComputedStyle(el);
        if (cs.position === 'fixed') {
          var h = el.getBoundingClientRect().height;
          // Una barra superior razonable; cualquier cosa más alta es otra pieza.
          if (h > 0 && h <= 200) {
            // Marcada para pintarle el fondo de la banda al pegarse (ver CSS).
            el.classList.add('fg-sticky-topbar');
            return h;
          }
          break;
        }
        el = el.parentElement;
      }
    }
    return 0;
  }

  // En Bahía Mar la capa «lujo» escala la interfaz entera con `zoom` en
  // pantallas anchas (1,15 desde 1800px; 1,3 desde 2200; 1,5 desde 2800).
  // Con zoom, getBoundingClientRect() devuelve píxeles de PANTALLA, pero una
  // longitud en CSS se interpreta sin escalar: escribir top:101px acaba
  // empujando 101×1,15 = 116px reales. Esa diferencia es exactamente el hueco
  // por el que se veían pasar las tarjetas entre la cinta y la barra, y por eso
  // crecía con el ancho. Se divide por el zoom acumulado.
  function zoomAcumulado() {
    var z = 1, e = bar;
    while (e && e.nodeType === 1) {
      var v = parseFloat(getComputedStyle(e).zoom);
      if (v && v > 0 && v !== 1) z *= v;
      e = e.parentElement;
    }
    return z || 1;
  }

  function measure() {
    var z = zoomAcumulado();
    var top = topBarHeight() / z;
    root.style.setProperty('--fg-sticky-top', Math.round(top) + 'px');

    // La cinta de «volver» sólo existe en los proyectos con tipos de villa, y
    // sólo se ve dentro del catálogo.
    var h = (head && head.offsetParent !== null) ? head.getBoundingClientRect().height / z : 0;
    root.style.setProperty('--fg-sticky-head', Math.round(h) + 'px');

    // Cuál es la primera pieza pegada: su fondo es el que sube a tapar el
    // hueco de la barra superior.
    var primera = (head && head.offsetParent !== null) ? head : bar;
    [head, bar].forEach(function (el) {
      if (el) el.classList.toggle('is-sticky-first', el === primera);
    });

    if (zone) {
      // Si la zona es transparente (puede pasar si otra capa la repinta), se
      // usa la del body: un fondo a sangre transparente no tapa nada.
      var bg = getComputedStyle(zone).backgroundColor;
      if (!bg || bg === 'transparent' || /,\s*0\)$/.test(bg)) {
        bg = getComputedStyle(document.body).backgroundColor;
      }
      root.style.setProperty('--fg-sticky-bg', bg);
    }

    // clientWidth excluye la barra de scroll (100vw no) y, a diferencia de
    // getBoundingClientRect, YA viene en unidades sin escalar: aquí no hay que
    // dividir por el zoom o la banda se queda corta por los lados.
    root.style.setProperty('--fg-sticky-bleed', Math.ceil(root.clientWidth) + 'px');
  }

  // El observador de abajo mira las clases del body, y medir también las tocaba:
  // se realimentaba y colgaba la pestaña. Ahora la clase se pone una sola vez,
  // al arrancar, y cada remedida espera al siguiente fotograma.
  var pending = false;
  function remeasure() {
    if (pending) return;
    pending = true;
    requestAnimationFrame(function () { pending = false; measure(); });
  }

  // La sombra sólo cuando la barra está realmente pegada, no cuando va en su
  // sitio: se detecta con un centinela justo encima.
  function watchStuck() {
    if (!('IntersectionObserver' in window)) return;
    var sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    sentinel.style.cssText = 'height:1px;margin:0;padding:0;';
    bar.parentNode.insertBefore(sentinel, head || bar);
    new IntersectionObserver(function (entries) {
      document.body.classList.toggle('is-filters-stuck', !entries[0].isIntersecting);
    }, { rootMargin: '-' + (Math.round(topBarHeight() / zoomAcumulado()) + 1) + 'px 0px 0px 0px', threshold: 0 })
      .observe(sentinel);
  }

  measure();
  document.body.classList.add('has-sticky-filters');
  watchStuck();

  window.addEventListener('resize', remeasure);
  // El alto de la cinta cambia al entrar y salir de un modelo, y al cambiar de
  // vista: se vuelve a medir cuando cambian las clases que la gobiernan.
  if ('MutationObserver' in window) {
    new MutationObserver(remeasure).observe(document.body, {
      attributes: true, attributeFilter: ['class', 'data-view']
    });
  }
  window.addEventListener('load', remeasure);
})();
</script>
