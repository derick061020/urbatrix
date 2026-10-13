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

    El popup aparece solo a los 5 segundos de llegar, una vez por sesión. Antes
    había además una cinta fija abajo repitiendo el aviso; se quitó porque tapaba
    el hero y decía lo mismo que el popup.
--}}
@php
    // Isotipo del proyecto. Cada marca lo guarda distinto en images/brand/, y
    // en algún checkout quedó suelto el de otro proyecto, así que se busca por
    // orden de preferencia y se comprueba que exista: primero el nombre
    // genérico (Bahía Mar) y luego el que lleva el nombre de la marca.
    // El blanco primero: el sello del popup va sobre el color de la marca.
    $guestMark = collect(['images/brand/logo-mark-white.png', 'images/brand/logo-mark.png'])
        ->merge(collect(glob(public_path('images/brand/*-logo-mark.svg')))
            ->map(fn ($f) => 'images/brand/' . basename($f)))
        ->first(fn ($rel) => is_file(public_path($rel)));

    $guestProject = config('company.project');

    // Segundos que se deja mirar antes de abrir el popup. Si alguna vez hay que
    // moverlo, es este número y nada más.
    $guestDelay = 5;
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

  /* ── Popup ─────────────────────────────────────────────────────────────
     Ninguna regla de font-family: todo hereda la tipografía global de la
     home. El carácter editorial se consigue con pesos, tamaños y espaciado. */
  .guest-modal {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 18, 27, 0.60);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    animation: guest-fade 0.4s ease both;
  }
  .guest-modal[open] { display: flex; }
  @keyframes guest-fade { from { opacity: 0; } to { opacity: 1; } }

  .guest-modal-card {
    position: relative;
    width: 100%;
    max-width: 560px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    background: #fcfbf8;
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(10, 13, 20, 0.45);
    text-align: center;
    animation: guest-pop 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  @keyframes guest-pop {
    from { opacity: 0; transform: translateY(22px) scale(0.97); }
    to   { opacity: 1; transform: none; }
  }
  /* Filo de color de la marca, como en el diseño. */
  .guest-modal-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-soft-3) 50%, var(--brand) 100%);
  }

  .guest-modal-head { padding: 38px 44px 22px; }
  .guest-modal-mark {
    width: 56px;
    height: 56px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 18px rgba(10, 13, 20, 0.18);
  }
  .guest-modal-mark img { width: 28px; height: 28px; object-fit: contain; }
  .guest-modal-eyebrow {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--brand);
  }
  .guest-modal-head h3 {
    margin: 10px 0 0;
    font-size: 28px;
    font-weight: 600;
    line-height: 1.15;
    letter-spacing: -0.5px;
    color: #1c2530;
  }
  .guest-modal-head p {
    margin: 12px auto 0;
    max-width: 40ch;
    font-size: 14px;
    line-height: 1.55;
    color: #6e7787;
  }

  /* ── Opciones ───────────────────────────────────────────────────────── */
  .guest-options {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 6px 44px 18px;
    text-align: left;
  }
  .guest-opt {
    position: relative;
    display: block;
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #e8e4da;
    border-radius: 14px;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s, box-shadow 0.25s;
  }
  .guest-opt:hover {
    transform: translateY(-4px);
    border-color: var(--brand);
    box-shadow: 0 16px 34px var(--brand-soft-2);
  }
  .guest-opt:focus-visible { outline: 3px solid var(--brand-ring); outline-offset: 2px; }
  .guest-opt .guest-ic {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: var(--brand-soft);
    transition: background 0.25s;
  }
  .guest-opt:hover .guest-ic { background: var(--brand); }
  .guest-opt .guest-ic svg { stroke: var(--brand); stroke-width: 1.6; fill: none; transition: stroke 0.25s; }
  .guest-opt:hover .guest-ic svg { stroke: #ffffff; }
  .guest-opt h4 { margin: 0 0 6px; font-size: 17px; font-weight: 700; color: #1c2530; }
  .guest-opt .guest-desc { font-size: 12.5px; line-height: 1.5; color: #6e7787; }
  .guest-opt .guest-go {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--brand);
  }
  .guest-opt .guest-go svg { width: 15px; height: 15px; stroke: var(--brand); stroke-width: 2; fill: none; transition: transform 0.25s; }
  .guest-opt:hover .guest-go svg { transform: translateX(4px); }
  .guest-opt .guest-badge {
    position: absolute;
    top: 16px; right: 16px;
    padding: 3px 8px;
    border-radius: 20px;
    background: var(--brand-soft);
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--brand);
  }

  /* Principal — grande, con descripción y llamada */
  .guest-opt--primary {
    padding: 28px 26px;
    border-color: var(--brand-soft-3);
    box-shadow: 0 10px 26px var(--brand-soft-2);
  }
  .guest-opt--primary .guest-ic { width: 56px; height: 56px; margin-bottom: 16px; }
  .guest-opt--primary .guest-ic svg { width: 28px; height: 28px; }
  .guest-opt--primary h4 { font-size: 20px; }
  .guest-opt--primary .guest-desc { max-width: 88%; }
  .guest-opt--primary .guest-go { margin-top: 18px; font-size: 13px; }

  /* Secundaria — compacta y horizontal */
  .guest-opt--secondary { display: flex; align-items: center; gap: 18px; padding: 18px 22px; }
  .guest-opt--secondary .guest-ic { width: 44px; height: 44px; flex-shrink: 0; }
  .guest-opt--secondary .guest-ic svg { width: 22px; height: 22px; }
  .guest-opt--secondary .guest-txt { flex: 1; min-width: 0; }
  .guest-opt--secondary h4 { font-size: 16px; margin-bottom: 3px; }
  .guest-opt--secondary .guest-go { flex-shrink: 0; }

  /* ── Pie ────────────────────────────────────────────────────────────── */
  .guest-modal-foot { padding: 2px 44px 28px; }
  .guest-skip {
    margin-bottom: 8px;
    padding: 8px 10px;
    background: none;
    border: none;
    border-radius: 7px;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    color: #1c2530;
    cursor: pointer;
    transition: color 0.2s, background 0.2s;
  }
  .guest-skip:hover { color: var(--brand); background: var(--brand-soft); }
  .guest-skip:focus-visible { outline: 2px solid var(--brand-ring); outline-offset: 2px; }
  .guest-modal-foot .guest-hint { margin: 0; font-size: 11px; color: #6e7787; }

  @media (max-width: 560px) {
    .guest-modal { padding: 16px; }
    .guest-modal-head { padding: 30px 24px 18px; }
    .guest-modal-head h3 { font-size: 23px; }
    .guest-options { padding: 6px 24px 16px; }
    .guest-modal-foot { padding: 2px 24px 24px; }
    .guest-opt--primary .guest-desc { max-width: 100%; }
  }
</style>

<div class="guest-modal" id="guestModal" role="dialog" aria-modal="true" aria-labelledby="guestModalTitle">
  <div class="guest-modal-card">

    <div class="guest-modal-head">
      @if($guestMark)
        <span class="guest-modal-mark"><img src="{{ asset($guestMark) }}" alt=""></span>
      @endif
      <span class="guest-modal-eyebrow">{{ __('Bienvenido a bordo') }}</span>
      <h3 id="guestModalTitle">{{ __('Bienvenido a :project', ['project' => $guestProject]) }}</h3>
      <p>{{ __('Estás viendo una vista previa. Creá tu cuenta para explorar las unidades, ver los planos de cada una y reservar en línea.') }}</p>
    </div>

    <div class="guest-options">
      <a class="guest-opt guest-opt--primary" href="{{ route('register') }}">
        <span class="guest-badge">{{ __('Recomendado') }}</span>
        <span class="guest-ic">
          <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9h14v-9"/><path d="M10 19v-5h4v5"/></svg>
        </span>
        <h4>{{ __('Crear cuenta') }}</h4>
        <div class="guest-desc">{{ __('Quiero explorar las unidades disponibles y encontrar mi propiedad.') }}</div>
        <span class="guest-go">{{ __('Ver unidades') }}
          <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
        </span>
      </a>

      <a class="guest-opt guest-opt--secondary" href="{{ route('login') }}">
        <span class="guest-ic">
          <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        </span>
        <span class="guest-txt">
          <h4>{{ __('Ya tengo cuenta') }}</h4>
          <div class="guest-desc">{{ __('Entrá para retomar donde lo dejaste.') }}</div>
        </span>
        <span class="guest-go">
          <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
        </span>
      </a>
    </div>

    <div class="guest-modal-foot">
      <button type="button" class="guest-skip" onclick="closeGuestModal()">{{ __('Lo decido luego') }} →</button>
      <p class="guest-hint">{{ __('Podés seguir mirando el proyecto; las unidades se abren al crear la cuenta.') }}</p>
    </div>

  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('guestModal');

  // Una vez por pestaña: si ya se mostró, el visitante sigue navegando sin que
  // le vuelva a saltar solo (al tocar una unidad sí se abre).
  var SEEN = 'guest_modal_seen';

  window.openGuestModal = function () {
    if (!modal) return;
    modal.setAttribute('open', '');
    document.body.style.overflow = 'hidden';
    try { sessionStorage.setItem(SEEN, '1'); } catch (e) {}
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

  // El visitante se queda mirando y a los {{ $guestDelay }} segundos le salta el
  // popup. Antes dependía de que llegase scrolleando hasta las unidades, así que
  // a quien se quedaba en el hero no le aparecía nunca.
  var ya = false;
  try { ya = sessionStorage.getItem(SEEN) === '1'; } catch (e) {}
  if (!ya) {
    setTimeout(function () {
      if (!modal.hasAttribute('open')) openGuestModal();
    }, {{ (int) $guestDelay * 1000 }});
  }
})();
</script>
