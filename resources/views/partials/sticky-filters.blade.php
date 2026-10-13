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
     proyecto (crema en Bahía Mar, gris azulado en los otros). */
  .fg-catalog-head,
  .fg-filter-bar {
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

  /* En pantallas estrechas sólo se fija la barra de filtros: la cinta de
     «volver» ocupa demasiado alto de un viewport de móvil. */
  @media (max-width: 760px) {
    body.has-sticky-filters .fg-catalog-head { position: static; }
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
          if (h > 0 && h <= 200) return h;
          break;
        }
        el = el.parentElement;
      }
    }
    return 0;
  }

  function measure() {
    var top = topBarHeight();
    root.style.setProperty('--fg-sticky-top', Math.round(top) + 'px');

    // La cinta de «volver» sólo existe en los proyectos con tipos de villa, y
    // sólo se ve dentro del catálogo.
    var h = (head && head.offsetParent !== null) ? head.getBoundingClientRect().height : 0;
    root.style.setProperty('--fg-sticky-head', Math.round(h) + 'px');

    if (zone) {
      root.style.setProperty('--fg-sticky-bg', getComputedStyle(zone).backgroundColor);
    }
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
    }, { rootMargin: '-' + (Math.round(topBarHeight()) + 1) + 'px 0px 0px 0px', threshold: 0 })
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
