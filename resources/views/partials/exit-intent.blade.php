{{--
    Popup de retención: salta cuando el visitante intenta irse de la plataforma.

    El motivo concreto: desde la home, el botón «atrás» del navegador te sacaba
    del sitio de una. Ahora se deja un estado trampa en el historial, de modo
    que ese primer «atrás» cae aquí en vez de llevarte afuera. Si insiste, se
    va: el popup aparece una sola vez por pestaña y el segundo «atrás» se
    respeta. No se usa `beforeunload` —el navegador sólo deja mostrar su propio
    diálogo, que no se puede redactar y queda peor que no poner nada—.

    En escritorio se añade el clásico «exit intent»: el cursor sale por el borde
    de arriba, camino de la barra de direcciones o de la X.

    Los dos argumentos que se muestran salen de la base, no son adorno: cuántas
    unidades quedan sin vender y desde cuánto arranca la más barata disponible.
--}}
@php
    // Se calcula sobre la colección que la home ya tiene cargada; si por lo que
    // sea no estuviera, se pregunta a la base.
    $exitPool = collect($units ?? []);
    if ($exitPool->isEmpty()) {
        $exitPool = \App\Models\Unit::where('public', true)->get(['status', 'price']);
    }

    $exitAvailable = $exitPool->reject(fn ($u) => in_array(
        strtolower((string) ($u->status ?? '')), ['sold', 'reserved', 'pending'], true
    ));

    $exitCount = $exitAvailable->count();
    $exitFrom  = (float) $exitAvailable->pluck('price')->filter(fn ($p) => (float) $p > 0)->min();
@endphp

<style>
  .exit-modal {
    position: fixed;
    inset: 0;
    z-index: 2100; /* por encima del popup de bienvenida */
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 18, 27, 0.66);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
  }
  .exit-modal[open] { display: flex; animation: exit-fade 0.35s ease both; }
  @keyframes exit-fade { from { opacity: 0; } to { opacity: 1; } }

  .exit-card {
    position: relative;
    width: 100%;
    max-width: 560px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    background: #fcfbf8;
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(10, 13, 20, 0.5);
    animation: exit-pop 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  @keyframes exit-pop {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to   { opacity: 1; transform: none; }
  }
  .exit-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-soft-3) 50%, var(--brand) 100%);
  }

  .exit-close {
    position: absolute;
    top: 14px; right: 16px;
    z-index: 2;
    padding: 4px 8px;
    background: none;
    border: none;
    font: inherit;
    font-size: 22px;
    line-height: 1;
    color: #6e7787;
    cursor: pointer;
    transition: color 0.2s;
  }
  .exit-close:hover { color: #1c2530; }

  .exit-body { padding: 40px 44px 20px; text-align: center; }
  .exit-ic {
    width: 64px; height: 64px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: var(--brand-soft);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .exit-ic svg { width: 32px; height: 32px; stroke: var(--brand); stroke-width: 1.5; fill: none; }
  .exit-eyebrow {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--brand);
  }
  .exit-body h3 {
    margin: 10px 0 0;
    font-size: 28px;
    font-weight: 600;
    line-height: 1.12;
    letter-spacing: -0.5px;
    color: #1c2530;
  }
  .exit-lead {
    margin: 12px auto 0;
    max-width: 42ch;
    font-size: 14px;
    line-height: 1.6;
    color: #6e7787;
  }

  .exit-hooks {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin: 22px 0 4px;
  }
  .exit-hook {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border: 1px solid #e8e4da;
    border-radius: 30px;
    background: #ffffff;
    font-size: 12px;
    font-weight: 500;
    color: #1c2530;
  }
  .exit-hook svg { width: 15px; height: 15px; flex-shrink: 0; stroke: var(--brand); stroke-width: 2; fill: none; }
  .exit-hook b { font-weight: 700; color: var(--brand); }

  .exit-actions { display: flex; flex-direction: column; gap: 12px; padding: 8px 44px 16px; }
  .exit-stay {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 17px;
    border: none;
    border-radius: 11px;
    background: var(--brand);
    color: #ffffff;
    font: inherit;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.3px;
    cursor: pointer;
    transition: filter 0.2s, transform 0.2s;
  }
  .exit-stay:hover { filter: brightness(1.15); transform: translateY(-2px); }
  .exit-stay svg { width: 18px; height: 18px; stroke: #ffffff; stroke-width: 2; fill: none; }
  .exit-leave {
    padding: 8px;
    border: none;
    border-radius: 7px;
    background: none;
    font: inherit;
    font-size: 13px;
    font-weight: 500;
    color: #6e7787;
    cursor: pointer;
    transition: color 0.2s, background 0.2s;
  }
  .exit-leave:hover { color: #1c2530; background: #f4f1ea; }

  .exit-foot { padding: 0 44px 28px; text-align: center; }
  .exit-foot p { margin: 0; font-size: 11px; line-height: 1.5; color: #6e7787; }
  .exit-foot b { color: #1c2530; }

  @media (max-width: 520px) {
    .exit-modal { padding: 16px; }
    .exit-body { padding: 32px 24px 16px; }
    .exit-body h3 { font-size: 23px; }
    .exit-actions, .exit-foot { padding-left: 24px; padding-right: 24px; }
    .exit-hooks { flex-direction: column; align-items: stretch; }
  }
</style>

<div class="exit-modal" id="exitModal" role="dialog" aria-modal="true" aria-labelledby="exitModalTitle">
  <div class="exit-card">
    <button type="button" class="exit-close" aria-label="{{ __('Cerrar y quedarme') }}" onclick="exitStay()">&times;</button>

    <div class="exit-body">
      <span class="exit-ic">
        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9h14v-9"/><path d="M9 21v-6h6v6"/></svg>
      </span>
      <span class="exit-eyebrow">{{ __('Espera un momento') }}</span>
      <h3 id="exitModalTitle">{{ __('¿Seguro que quieres irte?') }}</h3>
      <p class="exit-lead">{{ __('Estás a un paso de encontrar tu lugar en :project. Lo que has visto se perderá si sales ahora.', ['project' => config('company.project')]) }}</p>

      @if($exitCount > 0 || $exitFrom > 0)
        <div class="exit-hooks">
          @if($exitCount > 0)
            <span class="exit-hook">
              <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              {!! __('Quedan :count disponibles', ['count' => '<b>'.trans_choice('{1}:n unidad|[2,*]:n unidades', $exitCount, ['n' => number_format($exitCount)]).'</b>']) !!}
            </span>
          @endif
          @if($exitFrom > 0)
            <span class="exit-hook">
              <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
              {!! __('Desde :price', ['price' => '<b>$'.number_format($exitFrom, 0, ',', ',').'</b>']) !!}
            </span>
          @endif
        </div>
      @endif
    </div>

    <div class="exit-actions">
      <button type="button" class="exit-stay" onclick="exitStay()">
        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        {{ __('Seguir explorando') }}
      </button>
      <button type="button" class="exit-leave" onclick="exitLeave()">{{ __('No, gracias. Quiero salir') }}</button>
    </div>

    <div class="exit-foot">
      @guest
        <p>{!! __('¿Prefieres que te avisemos de nuevas unidades? Guarda tu selección creando tu cuenta — :free.', ['free' => '<b>'.__('es gratis').'</b>']) !!}</p>
      @else
        <p>{!! __('Tu lista de guardados y tus reservas te esperan en :dashboard.', ['dashboard' => '<b>'.__('tu escritorio').'</b>']) !!}</p>
      @endguest
    </div>
  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('exitModal');
  if (!modal) return;

  var SEEN = 'exit_shown';
  var leaving = false;   // el visitante ya dijo que se va: no estorbar más

  function alreadySeen() {
    try { return sessionStorage.getItem(SEEN) === '1'; } catch (e) { return false; }
  }
  function canShow() {
    if (leaving || modal.hasAttribute('open')) return false;
    return !alreadySeen();
  }

  // ¿Hay un diálogo abierto que el «atrás» deba cerrar primero? Es lo que se
  // espera de ese botón, y además evita apilar un popup sobre otro.
  function closeOpenDialog() {
    // En Bahía Mar, la capa «lujo» sustituye nuestro popup de bienvenida por su
    // «Acceso privado»: hay que mirar el suyo, o el atrás se lo saltaría.
    var reg = document.querySelector('.lx-reg.is-open');
    if (reg) {
      var cerrar = reg.querySelector('.lx-reg-cerrar, [data-cerrar]');
      if (cerrar) cerrar.click(); else reg.classList.remove('is-open');
      return true;
    }
    var guest = document.getElementById('guestModal');
    if (guest && guest.hasAttribute('open')) {
      if (typeof window.closeGuestModal === 'function') window.closeGuestModal();
      else { guest.removeAttribute('open'); document.body.style.overflow = ''; }
      return true;
    }
    return false;
  }
  function show() {
    if (!canShow()) return;
    modal.setAttribute('open', '');
    document.body.style.overflow = 'hidden';
    try { sessionStorage.setItem(SEEN, '1'); } catch (e) {}
  }

  window.exitStay = function () {
    modal.removeAttribute('open');
    document.body.style.overflow = '';
  };
  window.exitLeave = function () {
    modal.removeAttribute('open');
    document.body.style.overflow = '';
    leaving = true;
    // Dos atrás, no uno: el estado trampa que metimos al cargar ocupa una
    // entrada del historial y apunta a esta misma URL, así que un solo back
    // dejaría al visitante donde estaba.
    history.go(-2);
  };

  // Escape = quedarse, que es lo menos destructivo.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.hasAttribute('open')) exitStay();
  });
  modal.addEventListener('click', function (e) {
    if (e.target === modal) exitStay();
  });

  // ── Disparador 1 · el botón «atrás» ─────────────────────────────────────
  // Se mete un estado trampa: el primer «atrás» cae en este popstate en vez de
  // sacarte del sitio. La home sólo usa replaceState para sus filtros y vistas,
  // así que esto no le pisa nada.
  try {
    history.pushState({ exitGuard: true }, '', location.href);
    window.addEventListener('popstate', function () {
      if (leaving) return;

      // Con un diálogo abierto, el «atrás» lo cierra y se queda ahí. Sin esto
      // el popup de bienvenida se tragaba la pulsación sin que pasara nada.
      if (closeOpenDialog()) {
        history.pushState({ exitGuard: true }, '', location.href);
        return;
      }

      // La página puede reclamar el «atrás» para navegar dentro de sí misma:
      // en Bahía Mar, estando dentro de un modelo de villa, ese atrás devuelve
      // a la portada de tipos en vez de sacar del sitio. Si lo consume, se
      // vuelve a armar la trampa y aquí no se muestra nada.
      if (typeof window.__handleBackNavigation === 'function' && window.__handleBackNavigation()) {
        history.pushState({ exitGuard: true }, '', location.href);
        return;
      }

      if (alreadySeen()) {
        // Ya lo vio una vez: su «atrás» manda. El navegador acaba de retroceder
        // a la entrada trampa, que es esta misma página, así que hay que dar un
        // paso más para que salga de verdad.
        leaving = true;
        history.back();
        return;
      }
      history.pushState({ exitGuard: true }, '', location.href);  // re-armar
      show();
    });
  } catch (e) {}

  // ── Disparador 2 · el cursor se va por arriba (sólo escritorio) ─────────
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    document.addEventListener('mouseout', function (e) {
      if (!e.relatedTarget && e.clientY <= 0) show();
    });
  }
})();
</script>
