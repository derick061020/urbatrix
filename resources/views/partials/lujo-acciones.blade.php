{{--
    Devuelve a quien tiene sesión las acciones que la capa «lujo» secuestra.

    La capa sustituye openReservePage, openAdvisorVideoCall y
    toggleModalWishlist por su «Acceso privado», que es lo correcto para un
    visitante anónimo: en el original esas tres lo mandaban a /login. Pero las
    reasigna sin mirar si hay sesión, así que a un cliente logueado también le
    salía el muro: no podía agendar la videollamada, ni reservar, ni guardar.
    Lo reportó Miguel el 15 de octubre («estando logueado, al agendar sale
    esto»).

    No se toca lujo.js: viene del repo de Samuel y queremos poder actualizarlo
    tal cual. Aquí se guardan las funciones de verdad antes de que la capa las
    pise y se devuelven después, sólo si hay sesión. El visitante anónimo
    conserva el muro de la capa, que es como debe ser.

    El orden importa y no es casual: la captura es un script normal, así que
    corre mientras se parsea la página, antes que lujo.js, que va con `defer`.
    La devolución va con `defer` y detrás de la capa, así que corre después.
--}}
@auth
<script>
  // Las de verdad, antes de que la capa las sustituya.
  window.__accionesReales = {
    openReservePage:     window.openReservePage,
    openAdvisorVideoCall: window.openAdvisorVideoCall,
    toggleModalWishlist: window.toggleModalWishlist,
  };
</script>
@endauth

{{-- Y la devolución, después de la capa. --}}
@auth
<script>
  // En DOMContentLoaded y no con `defer`: un <script defer> en línea no difiere
  // nada, corre al parsear, y entonces aún no ha pasado la capa. Los scripts
  // externos con defer —lujo.js lo es— corren antes de este evento.
  document.addEventListener('DOMContentLoaded', function () {
    var reales = window.__accionesReales || {};
    Object.keys(reales).forEach(function (nombre) {
      if (typeof reales[nombre] === 'function') window[nombre] = reales[nombre];
    });


    // La capa también atrapa los clics en los enlaces a /login y /register y
    // en el corazón de guardar. Con sesión eso no tiene sentido: se deja pasar
    // el clic antes de que ella lo vea (captura, y el suyo también lo es, pero
    // este listener se registra después y basta con parar la propagación de
    // los que son nuestros).
    document.addEventListener('click', function (e) {
      var g = e.target.closest('[data-wishlist-toggle]');
      if (g && typeof reales.toggleModalWishlist === 'function') {
        e.stopImmediatePropagation();
        reales.toggleModalWishlist(g.dataset.unitId);
      }
    }, true);
  });
</script>
@endauth
