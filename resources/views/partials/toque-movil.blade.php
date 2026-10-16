{{--
    Zonas de toque en móvil.

    Un barrido a 390 px encontró entre 27 y 34 elementos pulsables de menos de
    36 px de alto en los tres proyectos: las pestañas del listado a 30–32, el
    conmutador ES/EN a 28 y, en Bahía Mar, los enlaces finos de la capa
    («Ver las villas disponibles» a 17, «Agendar videollamada» a 25).

    Dos tratamientos distintos según lo que sea la pieza:

    · Los controles con caja propia —pestañas, idioma, volver a tipos— crecen a
      44 px de alto, que es lo que recomienda Apple y lo que agradece el pulgar.
      Son píldoras: ganar alto les sienta bien.

    · Los enlaces finos NO se tocan por fuera. Su delgadez es deliberada y
      engordarlos rompería la estética que eligió Samuel. Lo que crece es el
      área sensible, con un pseudo centrado que no se ve.

    Se filtra sólo por ancho y no por `pointer: coarse`: esa consulta no se
    puede comprobar en un navegador sin táctil, y prefiero enviar algo medido.
    En una ventana estrecha de escritorio lo único que pasa es que los
    objetivos son más cómodos.
--}}
<style>
  @media (max-width: 900px) {
    /* Controles con caja: crecen de verdad. */
    .fg-list-tab,
    .lang-btn,
    .fg-catalog-back,
    .fg-plan-piso-option,
    .mt-cat-btn {
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    /* Enlaces finos: se agranda el área, no el enlace. */
    .lx-enlace,
    .lx-hero-cta,
    .fg-catalog-head .fg-catalog-back,
    [data-wishlist-toggle] {
      position: relative;
    }
    .lx-enlace::after,
    .lx-hero-cta::after,
    [data-wishlist-toggle]::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100%;
      min-width: 44px;
      height: 44px;
      transform: translate(-50%, -50%);
      background: transparent;
    }
  }
</style>
