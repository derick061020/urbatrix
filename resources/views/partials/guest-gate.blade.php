{{--
    Escaparate para visitantes sin cuenta.

    La home dejó de exigir sesión: el visitante ve el catálogo y toda la
    interfaz, pero la zona de unidades va ligeramente desenfocada y cualquier
    intento de USARLA (abrir una unidad, filtrar, guardar, reservar) abre este
    popup, que lleva al registro o al login.

    El desenfoque es a propósito suave —lo justo para que se lea «hay algo aquí
    detrás» sin impedir ver las propiedades, que es lo que se quiere enseñar—.
    Se ajusta con --guest-blur.

    El hero queda nítido: es la carta de presentación del proyecto.
--}}
<style>
  body[data-guest] {
    --guest-blur: 2px;
  }

  /* Sólo la zona de unidades. `filter` crea contexto de apilamiento, así que
     se aplica al contenedor y no a cada tarjeta: más barato de componer. */
  body[data-guest] #main-unit-reserve-list {
    filter: blur(var(--guest-blur)) saturate(0.92);
    transition: filter 0.4s ease;
    user-select: none;
  }

  /* Capa que se come los clics de toda la zona desenfocada. Va por encima del
     contenido pero por debajo del popup. */
  body[data-guest] .guest-catch {
    position: absolute;
    inset: 0;
    z-index: 40;
    cursor: pointer;
  }
  body[data-guest] #main-unit-reserve-list { position: relative; }

  /* Cinta fija que explica por qué no se puede tocar nada. */
  .guest-ribbon {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    z-index: 60;
    display: flex;
    align-items: center;
    gap: 14px;
    max-width: calc(100vw - 32px);
    padding: 12px 14px 12px 20px;
    border-radius: 9999px;
    background: rgba(30, 28, 26, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 18px 44px rgba(30, 28, 26, 0.34);
    color: #f1ede9;
    font-family: 'Poppins', sans-serif;
  }
  .guest-ribbon p {
    margin: 0;
    font-size: 13px;
    line-height: 1.35;
  }
  .guest-ribbon strong { font-weight: 700; }
  .guest-ribbon small {
    display: block;
    font-size: 11px;
    opacity: 0.72;
  }
  .guest-ribbon a {
    flex-shrink: 0;
    padding: 9px 18px;
    border-radius: 9999px;
    background: #f1ede9;
    color: #2b2b1a;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-decoration: none;
    white-space: nowrap;
  }
  .guest-ribbon a:hover { background: #ffffff; }

  /* ── Popup ─────────────────────────────────────────────────────────────── */
  .guest-modal {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(30, 28, 26, 0.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
  }
  .guest-modal[open] { display: flex; }
  .guest-modal-card {
    width: 100%;
    max-width: 420px;
    border-radius: 24px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 30px 80px rgba(30, 28, 26, 0.40);
    animation: guest-pop 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  @keyframes guest-pop {
    from { opacity: 0; transform: translateY(14px) scale(0.97); }
    to   { opacity: 1; transform: none; }
  }
  .guest-modal-img {
    height: 150px;
    background: url('/images/siebe/comunes/01-fachada-atardecer.jpg') center/cover;
  }
  .guest-modal-body {
    padding: 22px 24px 24px;
    font-family: 'Poppins', sans-serif;
    text-align: center;
  }
  .guest-modal-body h3 {
    margin: 0 0 8px;
    font-family: 'Lora', Georgia, serif;
    font-size: 21px;
    font-weight: 700;
    color: #1e1c1a;
  }
  .guest-modal-body p {
    margin: 0 0 20px;
    font-size: 13px;
    line-height: 1.5;
    color: #6b6660;
  }
  .guest-modal-actions { display: flex; flex-direction: column; gap: 9px; }
  .guest-btn {
    display: block;
    padding: 12px 18px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
  }
  .guest-btn-primary { background: #2b2b1a; color: #f1ede9; }
  .guest-btn-primary:hover { background: #3f3f28; }
  .guest-btn-ghost { background: transparent; color: #2b2b1a; border: 1px solid #ddd8cf; }
  .guest-btn-ghost:hover { background: #f1ede9; }
  .guest-modal-close {
    margin-top: 14px;
    background: none;
    border: none;
    color: #9a958a;
    font-size: 12px;
    cursor: pointer;
    font-family: inherit;
  }
  .guest-modal-close:hover { color: #2b2b1a; }

  @media (max-width: 640px) {
    .guest-ribbon { flex-direction: column; gap: 10px; text-align: center; padding: 14px 18px; bottom: 14px; }
    .guest-ribbon a { width: 100%; }
  }
</style>

<div class="guest-ribbon" role="status">
  <p>
    <strong>{{ __('Estás viendo una vista previa') }}</strong>
    <small>{{ __('Crea tu cuenta para explorar las unidades, ver planos y reservar.') }}</small>
  </p>
  <a href="{{ route('register') }}">{{ __('Crear cuenta') }}</a>
</div>

<div class="guest-modal" id="guestModal" role="dialog" aria-modal="true" aria-labelledby="guestModalTitle">
  <div class="guest-modal-card">
    <div class="guest-modal-img" aria-hidden="true"></div>
    <div class="guest-modal-body">
      <h3 id="guestModalTitle">{{ __('Creá tu cuenta para continuar') }}</h3>
      <p>{{ __('El catálogo completo, los planos de cada unidad y la reserva en línea están disponibles al registrarte. Toma menos de un minuto.') }}</p>
      <div class="guest-modal-actions">
        <a class="guest-btn guest-btn-primary" href="{{ route('register') }}">{{ __('Crear cuenta') }}</a>
        <a class="guest-btn guest-btn-ghost" href="{{ route('login') }}">{{ __('Ya tengo cuenta') }}</a>
      </div>
      <button type="button" class="guest-modal-close" onclick="closeGuestModal()">{{ __('Seguir mirando') }}</button>
    </div>
  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('guestModal');
  var opened = false;

  window.openGuestModal = function () {
    if (!modal) return;
    modal.setAttribute('open', '');
    document.body.style.overflow = 'hidden';
    opened = true;
  };
  window.closeGuestModal = function () {
    if (!modal) return;
    modal.removeAttribute('open');
    document.body.style.overflow = '';
  };

  // Cerrar al hacer clic fuera de la tarjeta o con Escape.
  modal && modal.addEventListener('click', function (e) {
    if (e.target === modal) closeGuestModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeGuestModal();
  });

  // La capa que intercepta los clics se monta sobre la zona de unidades. Se
  // crea por JS para no tocar el marcado de la lista, que se re-renderiza al
  // filtrar y al cargar más páginas.
  function mountCatcher() {
    var zone = document.getElementById('main-unit-reserve-list');
    if (!zone || zone.querySelector('.guest-catch')) return;
    var catcher = document.createElement('div');
    catcher.className = 'guest-catch';
    catcher.setAttribute('aria-hidden', 'true');
    catcher.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      openGuestModal();
    });
    zone.appendChild(catcher);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountCatcher);
  } else {
    mountCatcher();
  }
  // El scroll infinito reemplaza el contenido de la zona: re-montar si hiciera falta.
  setInterval(mountCatcher, 1500);

  // Primer aviso: si el visitante llega hasta las unidades, se le muestra el
  // popup una vez. Después puede seguir mirando sin que reaparezca solo.
  var zone = document.getElementById('main-unit-reserve-list');
  if (zone && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting && !opened) {
          openGuestModal();
          io.disconnect();
        }
      });
    }, { threshold: 0.15 });
    io.observe(zone);
  }
})();
</script>
