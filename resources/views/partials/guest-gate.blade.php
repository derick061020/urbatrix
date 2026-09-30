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
@php
    // Foto de cabecera del popup: la primera imagen de amenidades del catálogo
    // (en los tres proyectos es una vista del edificio o del entorno). Así el
    // partial sirve igual en Siebe, Makai y Bahía Mar sin tocar rutas.
    // Orden de preferencia: una vista del conjunto (fachada, piscina, entorno)
    // entre las del repo —los renders curados del estudio—, luego cualquier
    // otra del repo, y sólo al final las subidas a mano desde el panel
    // (/storage/…), que pueden ser fotos de obra o de móvil.
    $guestShot = \App\Models\UnitImage::where('path', 'like', '/images/%')
            ->where(function ($q) {
                foreach (['fachada', 'exterior', 'entorno', 'pool', 'beach', 'piscina'] as $w) {
                    $q->orWhere('path', 'like', '%' . $w . '%');
                }
            })->orderBy('id')->value('path')
        ?: \App\Models\UnitImage::where('category', 'amenities')
            ->where('path', 'like', '/images/%')->orderBy('id')->value('path')
        ?: \App\Models\UnitImage::where('path', 'like', '/images/%')->orderBy('id')->value('path')
        ?: \App\Models\UnitImage::where('category', 'amenities')->orderBy('id')->value('path')
        ?: \App\Models\UnitImage::orderBy('id')->value('path');

    // Isotipo del proyecto. Cada marca lo guarda distinto en images/brand/, y
    // en algún checkout quedó suelto el de otro proyecto, así que se busca por
    // orden de preferencia y se comprueba que exista: primero el nombre
    // genérico (Bahía Mar) y luego el que lleva el nombre de la marca.
    // El blanco primero: el sello del popup va en el color de la marca.
    $guestMark = collect(['images/brand/logo-mark-white.png', 'images/brand/logo-mark.png'])
        ->merge(collect(glob(public_path('images/brand/*-logo-mark.svg')))
            ->map(fn ($f) => 'images/brand/' . basename($f)))
        ->first(fn ($rel) => is_file(public_path($rel)));
@endphp
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

  /* Cinta fija que explica por qué no se puede tocar nada. Sin font-family
     propia: hereda la Open Sans global de la home, como el resto de la UI. */
  .guest-ribbon {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    z-index: 60;
    display: flex;
    align-items: center;
    gap: 16px;
    max-width: calc(100vw - 32px);
    padding: 10px 10px 10px 20px;
    border-radius: 9999px;
    background: rgba(15, 18, 27, 0.94);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 18px 44px rgba(10, 13, 20, 0.34);
    color: #ffffff;
  }
  .guest-ribbon p { margin: 0; font-size: 13px; line-height: 1.35; }
  .guest-ribbon strong { font-weight: 600; }
  .guest-ribbon small { display: block; font-size: 11px; opacity: 0.7; margin-top: 2px; }
  .guest-ribbon a {
    flex-shrink: 0;
    padding: 10px 20px;
    border-radius: 9999px;
    background: #ffffff;
    color: #111111;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s ease;
  }
  .guest-ribbon a:hover { background: #eaecf0; }

  /* ── Popup ─────────────────────────────────────────────────────────────
     Mismo lenguaje que el modal de unidad de la home (.mt-shell): velo
     oscuro con blur, borde claro y sombra profunda. Ninguna regla de
     font-family: todo hereda la Open Sans global. */
  .guest-modal {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 18, 27, 0.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
  }
  .guest-modal[open] { display: flex; }
  .guest-modal-card {
    position: relative;
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border: 1px solid #eaecf0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(10, 13, 20, 0.35);
    text-align: center;
    animation: guest-pop 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  @keyframes guest-pop {
    from { opacity: 0; transform: translateY(14px) scale(0.97); }
    to   { opacity: 1; transform: none; }
  }
  /* Cabecera: render del proyecto fundido a blanco, para que el sello y el
     texto arranquen sin un corte duro. */
  .guest-modal-img {
    position: relative;
    height: 150px;
    background-position: center 60%;
    background-size: cover;
  }
  .guest-modal-img::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(30,28,26,0.10) 0%, rgba(255,255,255,0) 30%, rgba(255,255,255,0.72) 82%, #ffffff 100%);
  }
  /* Sello de marca centrado, montado sobre el borde de la foto. */
  .guest-modal-mark {
    position: absolute;
    left: 50%;
    bottom: -30px;
    transform: translateX(-50%);
    z-index: 2;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--brand);
    border: 4px solid #ffffff;
    box-shadow: 0 6px 18px rgba(10, 13, 20, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .guest-modal-mark img {
    width: 30px;
    height: 30px;
    object-fit: contain;
  }
  .guest-modal-body { padding: 46px 32px 30px; }
  .guest-modal-eyebrow {
    display: block;
    margin-bottom: 10px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--brand);
    opacity: 0.85;
  }
  .guest-modal-body h3 {
    margin: 0 0 10px;
    font-size: 21px;
    font-weight: 700;
    line-height: 1.25;
    letter-spacing: -0.3px;
    color: #1e1c1a;
  }
  .guest-modal-body p {
    margin: 0 auto 24px;
    max-width: 34ch;
    font-size: 13.5px;
    line-height: 1.6;
    color: #6b6660;
  }
  .guest-modal-actions { display: flex; flex-direction: column; gap: 10px; }
  /* Mismos botones que el login, para que el salto a /register no sorprenda. */
  .guest-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease, border-color 0.2s ease, filter 0.2s ease;
  }
  .guest-btn-primary {
    background: var(--brand);
    color: #ffffff;
    border: 1px solid var(--brand);
    box-shadow: 0 1px 2px 0 rgba(10,13,20,.06);
  }
  .guest-btn-primary:hover { filter: brightness(1.18); }
  .guest-btn-ghost {
    background: #ffffff;
    color: #171717;
    border: 1px solid #ebebeb;
  }
  .guest-btn-ghost:hover { background: #f8f8f8; }

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
    <div class="guest-modal-img" aria-hidden="true"@if($guestShot) style="background-image:url('{{ $guestShot }}')"@endif>
      @if($guestMark)
        <span class="guest-modal-mark"><img src="{{ asset($guestMark) }}" alt=""></span>
      @endif
    </div>
    <div class="guest-modal-body">
      <span class="guest-modal-eyebrow">{{ config('company.project') }}</span>
      <h3 id="guestModalTitle">{{ __('Creá tu cuenta para continuar') }}</h3>
      <p>{{ __('El catálogo completo, los planos de cada unidad y la reserva en línea están disponibles al registrarte. Toma menos de un minuto.') }}</p>
      <div class="guest-modal-actions">
        <a class="guest-btn guest-btn-primary" href="{{ route('register') }}">{{ __('Crear cuenta') }}</a>
        <a class="guest-btn guest-btn-ghost" href="{{ route('login') }}">{{ __('Ya tengo cuenta') }}</a>
      </div>
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
