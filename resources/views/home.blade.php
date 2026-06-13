<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('home.title') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  <link rel="icon" href="{{ asset('images/favicon-urbatrix.png') }}" type="image/png">
  <link href="{{ asset('vendor/primeicons/primeicons.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=10">
</head>

<body data-view="grid">

  <!-- ░░░ MAKAI LOADING SCREEN ░░░ -->
  <div id="makaiLoader" aria-hidden="true">
    <div class="ml-inner">
      <div class="ml-rings">
        <span class="ml-ring"></span>
        <span class="ml-ring"></span>
        <span class="ml-ring"></span>
        <span class="ml-core"></span>
      </div>
      <img src="/images/makai-logo.png" alt="Makai" class="ml-logo">
      <div class="ml-bar"><span></span></div>
    </div>
  </div>
  <style>
    #makaiLoader{
      position:fixed; inset:0; z-index:99999;
      display:flex; align-items:center; justify-content:center;
      background:radial-gradient(120% 120% at 50% 30%, #fbfcfa 0%, #f1f4ee 55%, #e8ede5 100%);
      transition:opacity .6s ease, visibility .6s ease;
    }
    #makaiLoader.is-hidden{ opacity:0; visibility:hidden; pointer-events:none; }
    .ml-inner{
      display:flex; flex-direction:column; align-items:center; gap:30px;
      animation:ml-fade-in .7s ease both;
    }
    /* Anillos topográficos / ondas que se expanden ("Makai" = hacia el mar) */
    .ml-rings{ position:relative; width:120px; height:120px; }
    .ml-ring{
      position:absolute; inset:0; margin:auto;
      width:36px; height:36px; border-radius:50%;
      border:1.5px solid #5c7c68;
      transform:scale(.3); opacity:0;
      animation:ml-ripple 2.4s cubic-bezier(.22,.61,.36,1) infinite;
    }
    .ml-ring:nth-child(2){ animation-delay:.6s; }
    .ml-ring:nth-child(3){ animation-delay:1.2s; }
    .ml-core{
      position:absolute; inset:0; margin:auto;
      width:14px; height:14px; border-radius:50%;
      background:#5c7c68;
      animation:ml-pulse 2.4s ease-in-out infinite;
    }
    @keyframes ml-ripple{
      0%   { transform:scale(.3);  opacity:0; }
      15%  { opacity:.55; }
      100% { transform:scale(3.2); opacity:0; }
    }
    @keyframes ml-pulse{
      0%,100%{ transform:scale(1);   opacity:1; }
      50%    { transform:scale(.7);  opacity:.65; }
    }
    .ml-logo{
      height:34px; width:auto; max-width:200px; object-fit:contain;
      opacity:.92;
      animation:ml-breathe 3s ease-in-out infinite;
    }
    @keyframes ml-breathe{
      0%,100%{ opacity:.92; }
      50%    { opacity:.6; }
    }
    .ml-bar{
      width:160px; height:3px; border-radius:99px;
      background:rgba(92,124,104,.15); overflow:hidden;
    }
    .ml-bar span{
      display:block; height:100%; width:40%; border-radius:99px;
      background:linear-gradient(90deg, transparent, #5c7c68, transparent);
      animation:ml-slide 1.3s ease-in-out infinite;
    }
    @keyframes ml-slide{
      0%  { transform:translateX(-120%); }
      100%{ transform:translateX(330%); }
    }
    @keyframes ml-fade-in{ from{ opacity:0; transform:translateY(8px);} to{ opacity:1; transform:none;} }
    /* Con "reducir movimiento" mantenemos solo latidos de opacidad (sin desplazamientos). */
    @media (prefers-reduced-motion:reduce){
      .ml-ring{ animation:none; }
      .ml-ring:first-child{ opacity:.35; transform:scale(2.2); }
      .ml-bar span{ animation:ml-breathe 1.3s ease-in-out infinite; transform:none; width:100%; }
    }
  </style>
  <script>
    (function(){
      var loader = document.getElementById('makaiLoader');
      if(!loader) return;
      var start = Date.now();
      var MIN_SHOW = 1500; // ms mínimos visibles para que la animación se aprecie aunque cargue rápido
      var hidden = false;
      function replayHero(){
        // La animación de entrada del hero ya corrió oculta bajo el loader;
        // la reiniciamos justo al levantar el telón para que sí se vea.
        var hero = document.getElementById('hero');
        if(!hero || !hero.dataset.active) return;
        var active = hero.dataset.active;
        hero.removeAttribute('data-active');
        void hero.offsetWidth; // fuerza reflow para reiniciar la animación
        hero.dataset.active = active;
      }
      function hide(){
        if(hidden) return; hidden = true;
        replayHero();
        loader.classList.add('is-hidden');
        setTimeout(function(){ if(loader && loader.parentNode){ loader.parentNode.removeChild(loader); } }, 700);
      }
      function requestHide(){
        var remaining = MIN_SHOW - (Date.now() - start);
        setTimeout(hide, remaining > 0 ? remaining : 0);
      }
      // Oculta cuando toda la página (incluidas imágenes) terminó de cargar,
      // respetando el tiempo mínimo de exhibición.
      if(document.readyState === 'complete'){ requestHide(); }
      else { window.addEventListener('load', requestHide); }
      // Fallback: nunca dejar la pantalla bloqueada más de 8s.
      setTimeout(hide, 8000);
    })();
  </script>
  <!-- ░░░ /MAKAI LOADING SCREEN ░░░ -->

  @php
    // Listas globales editables desde el admin (Unidades → Configuraciones).
    // Las claves COINCIDEN con los valores que guarda el formulario de unidad,
    // de modo que los filtros de la home matcheen y muestren una etiqueta legible.
    $outlookLabels = \App\Support\UnitOptions::map('outlooks');
    $floorLabels   = \App\Support\UnitOptions::map('floors');
    $typeLabels    = \App\Support\UnitOptions::map('types');
  @endphp

  <!-- MORE INFO MODAL — Figma 220:20041 (modal-tipologia) -->
  <div id="moreInfoModal" class="mt-overlay" style="display:none;">
    <div class="mt-backdrop" onclick="closeMoreInfo()"></div>
    <div class="mt-shell" role="dialog" aria-modal="true" aria-label="{{ __('Unit details') }}">

      <!-- HEADER -->
      <div class="mt-header">
        <div class="mt-header-left">
          <img src="/images/makai-logo.png" alt="Makai" class="mt-header-logo">
          <span class="mt-header-dot"></span>
          <span class="mt-header-unit">{{ __('Unit') }} <span id="modalUnitNum">A-101</span></span>
          <span id="modalStatusBadge" class="mt-badge-available">
            <span class="dot"></span><span id="modalStatusText">{{ __('AVAILABLE') }}</span>
          </span>
        </div>
        <div class="mt-header-right">
          <div class="mt-pill mt-pill-soft">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>{{ __('Last inquiry') }} <b>2 {{ __('hours ago') }}</b> · {{ __('Shortlisted by') }} <b>7 {{ __('others') }}</b></span>
          </div>
          <div class="mt-pill mt-pill-wa">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
            <span>{{ __('Contact Broker on WhatsApp') }}</span>
          </div>
          <button class="mt-close" onclick="closeMoreInfo()" aria-label="{{ __('Cerrar') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>

      <div class="mt-body">

        <!-- LEFT — content panel -->
        <aside class="mt-left">
          <div class="mt-peopleview">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
            <span><b id="modalShortlistedCount">0</b> {{ __('people shortlisted this unit') }}</span>
          </div>

          <div class="mt-left-inner">

            <!-- Discount chip -->
            <span class="mt-discount-chip">{{ __('Unlock :amount Discount', ['amount' => '$20,000']) }}</span>

            <!-- Price block -->
            <div class="mt-price-block">
              <div class="mt-price-row">
                <span class="mt-price" id="modalPrice">$450 000</span>
                <div class="mt-currency-toggle">
                  <button type="button" class="active" data-cur="USD">USD</button>
                  <button type="button" data-cur="EUR">EUR</button>
                  <button type="button" data-cur="CAD">CAD</button>
                  <button type="button" data-cur="MXN">MXN</button>
                </div>
              </div>
              <div class="mt-price-meta">
                <span class="muted">$450/m²</span>
                <span class="sep"></span>
                <span class="success">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M7 14l5-5 5 5z" transform="rotate(180 12 12)"/></svg>
                  {{ __('12% below Cap Cana avg.') }}
                </span>
              </div>
              <div class="mt-price-meta">
                <span class="warning">{{ __('Reserve from $5,000') }}</span>
                <span class="sep"></span>
                <span class="muted">{{ __('Not refundable') }}</span>
              </div>
            </div>

            <!-- Description -->
            <p class="mt-desc" id="modalDesc">1st Floor &nbsp;·&nbsp; 1 Bed &amp; Family Room &nbsp;·&nbsp; SE &nbsp;·&nbsp; Lake Facing</p>

            <!-- Stats — top row (4 boxes) + bottom row (3 boxes) -->
            <div class="mt-stats-rows">
              <div class="mt-stats-row">
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatBed">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg></div>
                  <div class="label">{{ __('BED') }}</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatBath">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg></div>
                  <div class="label">{{ __('BATH') }}</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatPark">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg></div>
                  <div class="label">{{ __('PARK') }}</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatPool">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg></div>
                  <div class="label">{{ __('POOL') }}</div>
                </div>
              </div>
              <div class="mt-stats-row">
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatInt">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg></div>
                  <div class="label">{{ __('INT M²') }}</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatExt">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg></div>
                  <div class="label">{{ __('TERRACE M²') }}</div>
                </div>
                <div class="mt-stat-box">
                  <div class="value"><span id="modalStatTotal">—</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg></div>
                  <div class="label">{{ __('TOTAL M²') }}</div>
                </div>
              </div>
            </div>

            <div class="mt-divider"></div>

            <!-- For Investment / For Living toggle -->
            <div class="mt-buyer-toggle" role="tablist">
              <button type="button" class="active" data-buyer="investment">{{ __('For Investment') }}</button>
              <button type="button" data-buyer="living">{{ __('For Living') }}</button>
            </div>

            

            {{-- ══════════════ PARA INVERSIÓN ══════════════ --}}

            <!-- A · Precio inteligente -->
            <div class="mt-block mt-investment-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
                <span class="mt-eyebrow">{{ __('Smart entry price') }}</span>
              </div>
              <div class="mt-b-value"><span id="modalSmartPpm">—</span> · <span class="accent">{{ __('12% below the area average') }}</span></div>
              <p class="mt-b-micro">{{ __('Average based on comparable transactions in the zone.') }}</p>
            </div>

            <!-- B · Valorización (histórica → escenario) -->
            <div class="mt-block mt-block--green mt-investment-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg></span>
                <span class="mt-eyebrow">{{ __('Appreciation') }}</span>
              </div>
              <div class="mt-vrow">
                <span class="k">{{ __('Historical') }}</span>
                <span class="v">+8% {{ __('avg./yr') }} · 2018–2024</span>
              </div>
              <div class="mt-vrow" id="modalProjected" style="display:none;">
                <span class="k">{{ __('Scenario') }}</span>
                <span class="v"><span id="modalProjectedNow">$0</span> <span class="arrow">→</span> <span id="modalProjectedFuture">—</span> <span class="hint" id="modalProjectedHint">—</span></span>
              </div>
              <p class="mt-b-micro">{{ __('Reference scenario built on the area\'s historical appreciation. Not a guarantee or offer of return.') }}</p>
            </div>

            <!-- C · Renta estimada -->
            <div class="mt-block mt-investment-only" id="modalRentBlock" style="display:none;">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                <span class="mt-eyebrow">{{ __('Estimated rental income') }}</span>
              </div>
              <div class="mt-b-value"><span id="modalRentVal">—</span><span class="unit">/{{ __('mo') }}</span> <span id="modalRentYield"></span></div>
              <p class="mt-b-body">{{ __('Based on comparable-unit rates and average occupancy in the area.') }}</p>
              <p class="mt-b-micro">{{ __('Reference estimate, not guaranteed.') }}</p>
            </div>

            <!-- D · CONFOTUR (factual) -->
            <div class="mt-block mt-block--gold mt-investment-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></span>
                <span class="mt-eyebrow">{{ __('CONFOTUR benefits') }}</span>
              </div>
              <div class="mt-b-value">{{ __('15-year property-tax exemption + 0% transfer tax') }}</div>
              <p class="mt-b-body">{{ __('CONFOTUR-approved project: save the 3% transfer tax') }} (<span id="modalConfoturSavings">—</span>) {{ __('plus the annual property tax for 15 years.') }}</p>
              <p class="mt-b-micro">{{ __('Benefit subject to Law 158-01 (CONFOTUR) and the project\'s current qualification.') }}</p>
            </div>

            <!-- Investment commentary (optional, from DB) -->
            <div class="mt-compare mt-investment-only" id="modalCompare" style="display:none;">
              <span class="bullet"></span>
              <span id="modalCompareText">—</span>
            </div>

            <!-- For-investment longform description (optional, from DB) -->
            <p class="mt-section-text mt-investment-only" id="modalInvestmentText" style="display:none;"></p>

            {{-- ══════════════ PARA VIVIR ══════════════ --}}

            <!-- 1 · Tu espacio -->
            <div class="mt-block mt-living-only" id="modalSpaceBlock" style="display:none;">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg></span>
                <span class="mt-eyebrow">{{ __('Your space') }}</span>
              </div>
              <div class="mt-b-value" id="modalSpaceVal">—</div>
              <p class="mt-b-body">{{ __('A terrace that extends your living room outdoors: open-air breakfasts and sunset views.') }}</p>
            </div>

            <!-- 2 · Un día aquí -->
            <div class="mt-block mt-living-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg></span>
                <span class="mt-eyebrow">{{ __('A day here') }}</span>
              </div>
              <p class="mt-b-body" id="modalLivingText">{{ __('Mornings at the beach club, afternoons on the tennis court, dinners steps from the restaurant. All within the community.') }}</p>
            </div>

            <!-- 3 · Amenidades -->
            <div class="mt-block mt-living-only" id="modalAmenBlock" style="display:none;">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
                <span class="mt-eyebrow" id="modalLifestyleLabel">{{ __('Amenities & Lifestyle') }}</span>
              </div>
              <div id="modalRowAmen"><span id="modalAmenities">—</span></div>
            </div>

            <!-- 4 · Dónde estás -->
            <div class="mt-block mt-living-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                <span class="mt-eyebrow">{{ __('Where you are') }}</span>
              </div>
              <div class="mt-chips">
                <span class="mt-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18c2 0 2-1.5 4-1.5S8 18 10 18s2-1.5 4-1.5S16 18 18 18s2-1.5 4-1.5"/></svg>3 min {{ __('beach') }}</span>
                <span class="mt-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 18V5l7-2v4"/><circle cx="6" cy="18" r="3"/></svg>5 min {{ __('golf') }}</span>
                <span class="mt-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5a1.8 1.8 0 0 0-2.5-2.5L13.5 8 5.3 6.2a1 1 0 0 0-.9 1.7L9 11l-2 2-2-.5-1 1 3 2 2 3 1-1L11 15l2-2 3.4 4.6a1 1 0 0 0 1.7-.9z"/></svg>15 min {{ __('airport') }}</span>
              </div>
            </div>

            <!-- 5 · Sin preocupaciones -->
            <div class="mt-block mt-living-only">
              <div class="mt-b-head">
                <span class="mt-b-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                <span class="mt-eyebrow">{{ __('Peace of mind') }}</span>
              </div>
              <div class="mt-calm">
                <span>{{ __('24/7 security') }}</span>
                <span>{{ __('Managed & maintained') }}</span>
                <span>{{ __('Private community') }}</span>
              </div>
            </div>

            <div class="mt-divider"></div>

            <!-- Floor plan downloads -->
            <p class="mt-section-label">{{ __('Floor Plan Downloads') }}</p>
            <div class="mt-fancy-row">
              <button type="button" class="mt-fancy-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                {{ __('With Measurements') }}
              </button>
              <button type="button" class="mt-fancy-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                {{ __('Without Measurements') }}
              </button>
            </div>

            <!-- Advisor card -->
            @php
              $advisorName = $admin->name ?? 'Tu asesor';
              $advisorParts = preg_split('/\s+/', trim($advisorName));
              $advisorInit = strtoupper(mb_substr($advisorParts[0] ?? '', 0, 1) . mb_substr($advisorParts[1] ?? '', 0, 1)) ?: 'AS';
            @endphp
            <div class="mt-advisor">
              <div class="mt-advisor-left">
                <div class="mt-avatar">
                  <span class="mt-avatar-letter">{{ $advisorInit }}</span>
                  <span class="mt-avatar-status"></span>
                </div>
                <div>
                  <div class="mt-advisor-name">{{ $advisorName }}</div>
                  <div class="mt-advisor-status">{{ __('Available right now') }}</div>
                </div>
              </div>
              <button type="button" class="mt-advisor-chat" onclick="window.location.href='{{ route('dashboard.messages') }}'">
                {{ __('Chat') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
              </button>
            </div>
          </div>

          <!-- Sticky bottom -->
          <div class="mt-cta-row">
            <button id="modalReserveBtn" type="button" class="mt-btn-secondary" onclick="openReservePage(currentOpenUnit)">{{ __('Reserve Online') }}</button>
            <button type="button" class="mt-btn-primary" onclick="openAdvisorVideoCall(currentOpenUnit)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg>
              {{ __('Book Video Call') }}
            </button>
          </div>
        </aside>

        <!-- RIGHT — gallery panel -->
        <section class="mt-right">

          <!-- Tabs -->
          <div class="mt-tabs">
            <button type="button" class="mt-tab" id="modalAddToListBtn" onclick="toggleModalWishlist()">
              <svg id="modalAddToListIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span id="modalAddToListLabel">{{ __('ADD TO LIST') }}</span>
            </button>
            <button type="button" class="mt-tab mt-tab-middle" onclick="openDisclaimer()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              {{ __('DISCLAIMER') }}
            </button>
            <button type="button" class="mt-tab" onclick="openShareModal()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              {{ __('SHARE') }}
            </button>
            <button type="button" class="mt-tab mt-tab-download" onclick="downloadUnitSheet()" title="{{ __('Descargar ficha PDF') }}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              {{ __('DOWNLOAD') }}
            </button>
          </div>

          <!-- Image -->
          <div class="mt-gallery">
            <img id="modalMainImg" src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp" alt="Unit" class="mt-gallery-img" onclick="openImgZoom()" title="{{ __('Click to zoom') }}">

            <!-- Zoom hint -->
            <button class="mt-zoom-hint" type="button" onclick="openImgZoom()" aria-label="{{ __('Zoom') }}">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </button>

            <!-- Arrows -->
            <button class="mt-arrow mt-arrow-left" type="button" onclick="prevModalImg()" aria-label="{{ __('Previous') }}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="mt-arrow mt-arrow-right" type="button" onclick="nextModalImg()" aria-label="{{ __('Next') }}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>

            <span class="mt-counter" id="modalImgCounter">1 / 4</span>
          </div>

          <!-- Thumbs -->
          <div class="mt-thumbs" id="mtThumbs">
            <button type="button" class="mt-thumb active" data-idx="0">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="1">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FB_Makai_Cards_Unit_Layout_111-T1A%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="2">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FC_Makai_Floorplans_First_Floor_111%2F1773673791087%2Ffull.webp" alt="">
            </button>
            <button type="button" class="mt-thumb" data-idx="3">
              <img src="https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FD_Makai_Floorplans_Second_Floor_111%2F1773673791087%2Ffull.webp" alt="">
            </button>
          </div>
        </section>
      </div><!-- /.mt-body -->
    </div><!-- /.mt-shell -->
  </div><!-- /#moreInfoModal -->

  <!-- IMAGE ZOOM LIGHTBOX -->
  <div id="imgZoomOverlay" class="iz-overlay" role="dialog" aria-modal="true" aria-label="{{ __('Zoom') }}" onclick="if(event.target===this)closeImgZoom()">
    <button class="iz-close" type="button" onclick="closeImgZoom()" aria-label="{{ __('Close') }}">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button class="iz-arrow iz-arrow-left" type="button" onclick="prevModalImg()" aria-label="{{ __('Previous') }}">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="iz-arrow iz-arrow-right" type="button" onclick="nextModalImg()" aria-label="{{ __('Next') }}">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="iz-stage" id="izStage">
      <img id="izImg" src="" alt="Unit" draggable="false">
    </div>
    <div class="iz-toolbar">
      <button type="button" onclick="izZoom(-1)" aria-label="{{ __('Zoom out') }}">&minus;</button>
      <span id="izLevel">100%</span>
      <button type="button" onclick="izZoom(1)" aria-label="{{ __('Zoom in') }}">+</button>
      <button type="button" onclick="izReset()" aria-label="{{ __('Reset') }}">&#x21bb;</button>
    </div>
  </div>
  <style>
    .iz-overlay{
      position:fixed; inset:0; z-index:1500;
      background:rgba(8,10,14,.92);
      display:none; align-items:center; justify-content:center;
      animation: izFade .18s ease-out; overscroll-behavior:contain;
    }
    .iz-overlay.open{ display:flex; }
    @keyframes izFade{ from{opacity:0} to{opacity:1} }
    .iz-stage{
      width:100%; height:100%;
      display:flex; align-items:center; justify-content:center;
      overflow:hidden; touch-action:none; cursor:zoom-in;
    }
    .iz-stage.zoomed{ cursor:grab; }
    .iz-stage.grabbing{ cursor:grabbing; }
    #izImg{
      max-width:92vw; max-height:88vh;
      object-fit:contain; user-select:none; -webkit-user-drag:none;
      transform-origin:center center;
      transition:transform .12s ease-out; will-change:transform;
    }
    .iz-stage.grabbing #izImg{ transition:none; }
    .iz-close{
      position:absolute; top:18px; right:18px; z-index:2;
      width:42px; height:42px; border-radius:999px;
      background:rgba(255,255,255,.12); color:#fff; border:none;
      display:inline-flex; align-items:center; justify-content:center;
      cursor:pointer; transition:background .15s ease;
    }
    .iz-close:hover{ background:rgba(255,255,255,.25); }
    .iz-arrow{
      position:absolute; top:50%; transform:translateY(-50%); z-index:2;
      width:46px; height:46px; border-radius:999px;
      background:rgba(255,255,255,.12); color:#fff; border:none;
      display:inline-flex; align-items:center; justify-content:center;
      cursor:pointer; transition:background .15s ease;
    }
    .iz-arrow:hover{ background:rgba(255,255,255,.25); }
    .iz-arrow-left{ left:18px; }
    .iz-arrow-right{ right:18px; }
    .iz-toolbar{
      position:absolute; bottom:20px; left:50%; transform:translateX(-50%); z-index:2;
      display:flex; align-items:center; gap:6px;
      background:rgba(255,255,255,.12); border-radius:999px; padding:5px 8px;
      backdrop-filter:blur(4px);
    }
    .iz-toolbar button{
      width:34px; height:34px; border-radius:999px; border:none;
      background:rgba(255,255,255,.0); color:#fff; font-size:18px; line-height:1;
      cursor:pointer; transition:background .15s ease;
    }
    .iz-toolbar button:hover{ background:rgba(255,255,255,.18); }
    .iz-toolbar span{ color:#fff; font-size:12px; font-weight:600; min-width:46px; text-align:center; }
    .mt-zoom-hint{
      position:absolute; bottom:16px; left:16px; z-index:2;
      width:34px; height:34px; border-radius:999px;
      background:rgba(255,255,255,.92); border:1px solid #eaecf0;
      box-shadow:0 1px 2px rgba(10,13,20,.06); color:#5c5c5c;
      display:inline-flex; align-items:center; justify-content:center;
      cursor:zoom-in; transition:all .15s ease;
    }
    .mt-zoom-hint:hover{ background:#fff; color:#171717; }
    .mt-gallery-img{ cursor:zoom-in; }
    @media (max-width:600px){
      .iz-arrow{ width:40px; height:40px; }
      .iz-arrow-left{ left:8px; } .iz-arrow-right{ right:8px; }
    }
  </style>
  <script>
    (function(){
      let izScale = 1, izTx = 0, izTy = 0;
      let dragging = false, sx = 0, sy = 0, stx = 0, sty = 0;
      // pinch state
      let pinchDist = 0, pinchScale = 1;
      const MIN = 1, MAX = 5;

      function stage(){ return document.getElementById('izStage'); }
      function imgEl(){ return document.getElementById('izImg'); }

      function apply(){
        const img = imgEl();
        if(!img) return;
        izScale = Math.min(MAX, Math.max(MIN, izScale));
        if(izScale === 1){ izTx = 0; izTy = 0; }
        img.style.transform = `translate(${izTx}px, ${izTy}px) scale(${izScale})`;
        const lvl = document.getElementById('izLevel');
        if(lvl) lvl.textContent = Math.round(izScale * 100) + '%';
        stage().classList.toggle('zoomed', izScale > 1);
      }

      window.openImgZoom = function(){
        const src = document.getElementById('modalMainImg')?.src;
        if(!src) return;
        imgEl().src = src;
        izScale = 1; izTx = 0; izTy = 0; apply();
        document.getElementById('imgZoomOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
      };
      window.closeImgZoom = function(){
        document.getElementById('imgZoomOverlay').classList.remove('open');
        // keep the unit modal scroll-lock if it's still open
        if(document.getElementById('moreInfoModal')?.style.display !== 'flex'){
          document.body.style.overflow = '';
        }
      };
      window.izZoom = function(dir){
        izScale += dir * 0.5;
        apply();
      };
      window.izReset = function(){ izScale = 1; izTx = 0; izTy = 0; apply(); };

      document.addEventListener('DOMContentLoaded', function(){
        const st = stage(), img = imgEl();
        if(!st || !img) return;

        // Keep the lightbox image synced when arrows/thumbs change the unit image.
        // Wrapped here (not at parse time) so updateModalImage is already defined.
        const _upd = window.updateModalImage;
        window.updateModalImage = function(){
          if(typeof _upd === 'function') _upd.apply(this, arguments);
          const ov = document.getElementById('imgZoomOverlay');
          if(ov && ov.classList.contains('open')){
            imgEl().src = document.getElementById('modalMainImg').src;
            izReset();
          }
        };

        // Wheel zoom
        st.addEventListener('wheel', function(e){
          e.preventDefault();
          izScale += (e.deltaY < 0 ? 0.3 : -0.3);
          apply();
        }, { passive:false });

        // Double click toggles zoom
        st.addEventListener('dblclick', function(){
          izScale = izScale > 1 ? 1 : 2.5; apply();
        });

        // Single click on the stage (not the image when zoomed) toggles too
        img.addEventListener('click', function(e){
          if(izScale === 1){ izScale = 2.5; apply(); }
        });

        // Mouse drag to pan
        st.addEventListener('mousedown', function(e){
          if(izScale <= 1) return;
          dragging = true; sx = e.clientX; sy = e.clientY; stx = izTx; sty = izTy;
          st.classList.add('grabbing'); e.preventDefault();
        });
        window.addEventListener('mousemove', function(e){
          if(!dragging) return;
          izTx = stx + (e.clientX - sx);
          izTy = sty + (e.clientY - sy);
          apply();
        });
        window.addEventListener('mouseup', function(){
          dragging = false; st.classList.remove('grabbing');
        });

        // Touch: pinch zoom + drag pan
        st.addEventListener('touchstart', function(e){
          if(e.touches.length === 2){
            pinchDist = touchDist(e.touches); pinchScale = izScale;
          } else if(e.touches.length === 1 && izScale > 1){
            dragging = true; sx = e.touches[0].clientX; sy = e.touches[0].clientY;
            stx = izTx; sty = izTy;
          }
        }, { passive:false });
        st.addEventListener('touchmove', function(e){
          if(e.touches.length === 2){
            e.preventDefault();
            const d = touchDist(e.touches);
            if(pinchDist > 0){ izScale = pinchScale * (d / pinchDist); apply(); }
          } else if(dragging && e.touches.length === 1){
            e.preventDefault();
            izTx = stx + (e.touches[0].clientX - sx);
            izTy = sty + (e.touches[0].clientY - sy);
            apply();
          }
        }, { passive:false });
        st.addEventListener('touchend', function(e){
          if(e.touches.length === 0){ dragging = false; pinchDist = 0; }
        });

        function touchDist(t){
          const dx = t[0].clientX - t[1].clientX;
          const dy = t[0].clientY - t[1].clientY;
          return Math.hypot(dx, dy);
        }
      });

      // ESC closes the lightbox first
      document.addEventListener('keydown', function(e){
        const ov = document.getElementById('imgZoomOverlay');
        if(!ov || !ov.classList.contains('open')) return;
        if(e.key === 'Escape'){ e.stopPropagation(); closeImgZoom(); }
        else if(e.key === 'ArrowLeft'){ prevModalImg(); }
        else if(e.key === 'ArrowRight'){ nextModalImg(); }
      });
    })();
  </script>


  <!-- SHARE PROPERTY MODAL (YouTube-style) -->
  <style>
    .sh-overlay {
        position:fixed; inset:0; z-index:1300;
        background:rgba(15,17,24,.55);
        display:none; align-items:center; justify-content:center;
        padding:1rem;
        animation: shFadeIn .18s ease-out;
    }
    .sh-overlay.open { display:flex; }
    @keyframes shFadeIn { from { opacity:0 } to { opacity:1 } }
    @keyframes shSlideIn { from { transform:translateY(12px) scale(.98); opacity:0 } to { transform:translateY(0) scale(1); opacity:1 } }

    .sh-shell {
        width:100%; max-width: 520px;
        background:#fff; border-radius: 18px; overflow:hidden;
        box-shadow: 0 30px 80px -20px rgba(10,13,20,.35);
        animation: shSlideIn .22s cubic-bezier(.4,0,.2,1);
        font-family: 'Inter', system-ui, sans-serif;
    }
    .sh-head {
        padding: 20px 22px 8px;
        display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
    }
    .sh-head-title {
        font-family:'Inter Tight', 'Inter', sans-serif;
        font-size:18px; font-weight:700; color:#171717;
    }
    .sh-head-sub { font-size:12px; color:#717784; margin-top:3px; }
    .sh-close {
        width:32px; height:32px; border-radius:8px;
        background:#f5f7fa; border:none; cursor:pointer;
        color:#717784; display:flex; align-items:center; justify-content:center;
    }
    .sh-close:hover { background:#eaecf0; color:#171717; }

    .sh-socials {
        padding: 14px 22px 6px;
        display:flex; gap:10px; overflow-x:auto;
    }
    .sh-socials::-webkit-scrollbar { display:none; }
    .sh-social-btn {
        display:flex; flex-direction:column; align-items:center; gap:6px;
        padding: 4px;
        background:transparent; border:none; cursor:pointer;
        font-size:11px; color:#525866; font-weight:500;
        min-width: 64px;
    }
    .sh-social-btn:hover .sh-social-icon { transform: scale(1.05); }
    .sh-social-icon {
        width:48px; height:48px; border-radius:999px;
        display:flex; align-items:center; justify-content:center;
        background:#f2f5f8; color:#5c5c5c;
        transition: transform .15s, background-color .15s;
    }
    .sh-social-btn.wa  .sh-social-icon { background:#25D366; color:#fff; }
    .sh-social-btn.tg  .sh-social-icon { background:#229ED9; color:#fff; }
    .sh-social-btn.fb  .sh-social-icon { background:#1877F2; color:#fff; }
    .sh-social-btn.tw  .sh-social-icon { background:#000;     color:#fff; }
    .sh-social-btn.em  .sh-social-icon { background:#5c7c68;  color:#fff; }
    .sh-social-btn.sm  .sh-social-icon { background:#222530;  color:#fff; }

    .sh-url-row {
        margin: 8px 22px 0;
        padding: 4px 4px 4px 14px;
        background:#f5f7fa; border:1px solid #eaecf0; border-radius:12px;
        display:flex; align-items:center; gap:10px;
    }
    .sh-url-row input {
        flex:1; min-width:0; background:transparent; border:none; outline:none;
        font-size:13px; color:#222530; padding: 10px 0;
        text-overflow:ellipsis;
    }
    .sh-url-row input:focus { color:#171717; }
    .sh-copy-btn {
        flex-shrink:0;
        padding: 9px 16px; border-radius: 9px;
        background:#5c7c68; color:#fff; border:none; cursor:pointer;
        font-size:13px; font-weight:600;
        transition: background-color .15s;
        display:inline-flex; align-items:center; gap:6px;
    }
    .sh-copy-btn:hover { background:#4a6354; }
    .sh-copy-btn.copied { background:#1fc16b; }

    .sh-divider {
        margin: 16px 22px 0;
        height:1px; background:#f2f5f8;
    }

    .sh-download {
        margin: 14px 22px 22px;
        display:flex; align-items:center; gap:12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #5c7c68 0%, #4a6354 100%);
        border-radius: 14px;
        cursor:pointer; border:none; width: calc(100% - 44px); text-align:left;
        color:#fff;
        transition: transform .12s, box-shadow .15s;
        box-shadow: 0 6px 16px -8px rgba(92,124,104,.55);
    }
    .sh-download:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -8px rgba(92,124,104,.65); }
    .sh-download:active { transform: translateY(0); }
    .sh-download-icon {
        width:42px; height:42px; border-radius:10px;
        background: rgba(255,255,255,.18);
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .sh-download-body { flex:1; min-width:0; }
    .sh-download-title { font-size:14px; font-weight:700; line-height:1.2; }
    .sh-download-sub { font-size:11px; opacity:.85; margin-top:2px; line-height:1.3; }
    .sh-download-arrow { opacity:.8; }

    /* Smaller share tab in modal */
    .mt-tab.mt-tab-download { color:#5c7c68;border-left: 1px solid #eaecf0;}
    .mt-tab.mt-tab-download:hover { background:#eef2ef; }
  </style>

  <div id="shareModal" class="sh-overlay" role="dialog" aria-modal="true" aria-label="Compartir propiedad">
    <div class="sh-shell">
      <div class="sh-head">
        <div>
          <div class="sh-head-title">Compartir esta unidad</div>
          <div class="sh-head-sub">Envía el link a un colega, familiar o socio.</div>
        </div>
        <button type="button" class="sh-close" onclick="closeShareModal()" aria-label="Cerrar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <div class="sh-socials" id="shSocials">
        <button type="button" class="sh-social-btn wa" data-net="wa">
          <span class="sh-social-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg></span>
          WhatsApp
        </button>
        <button type="button" class="sh-social-btn tg" data-net="tg">
          <span class="sh-social-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg></span>
          Telegram
        </button>
        <button type="button" class="sh-social-btn fb" data-net="fb">
          <span class="sh-social-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></span>
          Facebook
        </button>
        <button type="button" class="sh-social-btn tw" data-net="tw">
          <span class="sh-social-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></span>
          X / Twitter
        </button>
        <button type="button" class="sh-social-btn em" data-net="em">
          <span class="sh-social-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
          Email
        </button>
        <button type="button" class="sh-social-btn sm" data-net="sms">
          <span class="sh-social-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>
          SMS
        </button>
      </div>

      <div class="sh-url-row">
        <input type="text" id="shareUrlInput" readonly value="" aria-label="URL para compartir">
        <button type="button" id="shareCopyBtn" class="sh-copy-btn" onclick="copyShareUrl()">
          <i class="pi pi-copy" style="font-size:12px;"></i>
          <span id="shareCopyLabel">Copiar</span>
        </button>
      </div>

      <div class="sh-divider" id="shDownloadDivider"></div>

      <button type="button" class="sh-download" id="shDownloadBtn" onclick="downloadUnitSheet()">
        <span class="sh-download-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
        </span>
        <span class="sh-download-body">
          <span class="sh-download-title">Descargar ficha de la unidad</span>
          <span class="sh-download-sub">PDF con galería, precio, plano y datos clave</span>
        </span>
        <span class="sh-download-arrow">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </button>
    </div>
  </div>

  <!-- DISCLAIMER MODAL (reuses the share modal styles) -->
  <div id="disclaimerModal" class="sh-overlay" role="dialog" aria-modal="true" aria-label="Disclaimer" onclick="if(event.target===this)closeDisclaimer()">
    <div class="sh-shell" style="max-width:560px;">
      <div class="sh-head">
        <div>
          <div class="sh-head-title">Disclaimer / Aviso legal</div>
          <div class="sh-head-sub">Información importante sobre esta unidad</div>
        </div>
        <button type="button" class="sh-close" onclick="closeDisclaimer()" aria-label="Cerrar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div style="padding:8px 22px 22px;font-family:'Inter',system-ui,sans-serif;font-size:13px;line-height:1.65;color:#525866;max-height:62vh;overflow-y:auto;">
        <p style="margin:0 0 12px;">
          <strong>Información de prueba.</strong> Las imágenes, planos, medidas, precios y disponibilidad
          mostrados son meramente ilustrativos y pueden variar respecto del producto final.
        </p>
        <p style="margin:0 0 12px;">
          Las áreas internas y externas son aproximadas y están sujetas a verificación en sitio.
          Los renders y plantas son representaciones artísticas; los acabados, mobiliario y vistas
          pueden diferir de la unidad construida.
        </p>
        <p style="margin:0 0 12px;">
          Los precios están expresados en USD y no incluyen impuestos, gastos de escrituración,
          comisiones ni cargos administrativos, salvo indicación expresa. Toda promoción o descuento
          está sujeto a disponibilidad y a sus términos y condiciones.
        </p>
        <p style="margin:0 0 12px;">
          Esta información no constituye una oferta vinculante ni asesoramiento financiero o de inversión.
          Cualquier proyección de rentabilidad (ROI) es estimada y no garantiza resultados futuros.
        </p>
        <p style="margin:0;">
          Para datos definitivos, consultá con tu asesor antes de tomar cualquier decisión de compra o reserva.
        </p>
      </div>
    </div>
  </div>

  <script>
    // DISCLAIMER modal — opened from the unit modal "DISCLAIMER" tab.
    window.openDisclaimer  = function () { document.getElementById('disclaimerModal')?.classList.add('open'); };
    window.closeDisclaimer = function () { document.getElementById('disclaimerModal')?.classList.remove('open'); };
  </script>

  <script>
    // Returns the canonical shareable URL.
    //   • If a unit modal is open → link to that unit, but ALSO keep the current
    //     filter/view query string so the recipient lands on the same grid state.
    //   • Otherwise → link to the current filtered grid/list view.
    function buildShareUrl() {
      const params = new URLSearchParams(window.location.search);
      const unitId = (typeof currentOpenUnit !== 'undefined' && currentOpenUnit) ? String(currentOpenUnit) : '';
      if (unitId) {
        params.set('unit', unitId);
      } else {
        params.delete('unit');
      }
      const qs = params.toString();
      return window.location.origin + window.location.pathname + (qs ? '?' + qs : '');
    }

    // El botón de "Descargar ficha PDF" sólo aplica a una unidad concreta.
    // Cuando se comparte el resultado de los filtros (sin unidad abierta) se oculta.
    function setShareDownloadVisible(visible) {
      const btn = document.getElementById('shDownloadBtn');
      const div = document.getElementById('shDownloadDivider');
      if (btn) btn.style.display = visible ? '' : 'none';
      if (div) div.style.display = visible ? '' : 'none';
    }

    // Triggered by the green "N Matches" pill — copies the filtered URL.
    window.shareMatches = function () {
      const url = buildShareUrl();
      const input = document.getElementById('shareUrlInput');
      if (input) input.value = url;
      // Compartir matches de filtros: sin descarga de PDF de unidad.
      setShareDownloadVisible(false);
      document.getElementById('shareModal').classList.add('open');
      const lbl = document.getElementById('shareCopyLabel');
      if (lbl) lbl.textContent = 'Copiar';
      document.getElementById('shareCopyBtn').classList.remove('copied');
    };

    window.openShareModal = function () {
      // Allow sharing even with no unit open — falls back to the filtered URL.
      const url = buildShareUrl();
      document.getElementById('shareUrlInput').value = url;
      // Sólo mostramos la descarga de ficha si hay una unidad abierta.
      const hasUnit = (typeof currentOpenUnit !== 'undefined' && currentOpenUnit);
      setShareDownloadVisible(!!hasUnit);
      document.getElementById('shareModal').classList.add('open');
      // Reset copy button label
      const lbl = document.getElementById('shareCopyLabel');
      if (lbl) lbl.textContent = 'Copiar';
      document.getElementById('shareCopyBtn').classList.remove('copied');
    };

    window.closeShareModal = function () {
      document.getElementById('shareModal').classList.remove('open');
    };

    document.getElementById('shareModal').addEventListener('click', function (e) {
      if (e.target === this) closeShareModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.getElementById('shareModal').classList.contains('open')) {
        closeShareModal();
      }
    });

    window.copyShareUrl = async function () {
      const input = document.getElementById('shareUrlInput');
      const btn   = document.getElementById('shareCopyBtn');
      const lbl   = document.getElementById('shareCopyLabel');
      const value = input.value;
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(value);
        } else {
          input.select(); input.setSelectionRange(0, 99999);
          document.execCommand('copy');
        }
        lbl.textContent = '¡Copiado!';
        btn.classList.add('copied');
        setTimeout(() => { lbl.textContent = 'Copiar'; btn.classList.remove('copied'); }, 1800);
      } catch (e) {
        lbl.textContent = 'Error';
      }
    };

    document.querySelectorAll('#shSocials .sh-social-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const net  = btn.dataset.net;
        const url  = buildShareUrl();
        const unit = (document.getElementById('modalUnitNum')?.textContent || '').trim();
        const text = 'Mira esta unidad de Makai Residences (' + unit + ')';
        let target = '';
        switch (net) {
          case 'wa':  target = 'https://wa.me/?text=' + encodeURIComponent(text + ' — ' + url); break;
          case 'tg':  target = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text); break;
          case 'fb':  target = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url); break;
          case 'tw':  target = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url); break;
          case 'em':  target = 'mailto:?subject=' + encodeURIComponent('Makai Residences — Unidad ' + unit) + '&body=' + encodeURIComponent(text + '\n\n' + url); break;
          case 'sms': target = 'sms:?body=' + encodeURIComponent(text + ' ' + url); break;
        }
        if (target.startsWith('mailto:') || target.startsWith('sms:')) {
          window.location.href = target;
        } else {
          window.open(target, '_blank', 'noopener,width=720,height=620');
        }
      });
    });

    // Loads the property sheet into a hidden, off-screen iframe instead of
    // navigating to a new tab. The iframe page scales itself and auto-triggers
    // its own print dialog, so the user stays on the home page the whole time.
    // Hooked to both the DOWNLOAD tab and the green CTA inside the share modal.
    window.downloadUnitSheet = function () {
      if (typeof currentOpenUnit === 'undefined' || !currentOpenUnit) {
        alert('Primero abrí los detalles de una unidad.');
        return;
      }
      const url = '/property-pdf/' + encodeURIComponent(currentOpenUnit);
      // Reuse a single hidden iframe; drop any previous one first.
      const prev = document.getElementById('pdfPrintFrame');
      if (prev) prev.remove();
      const frame = document.createElement('iframe');
      frame.id = 'pdfPrintFrame';
      frame.setAttribute('aria-hidden', 'true');
      frame.style.position = 'fixed';
      frame.style.left = '-10000px';
      frame.style.top = '0';
      frame.style.width = '230mm';
      frame.style.height = '320mm';
      frame.style.border = '0';
      frame.src = url;
      document.body.appendChild(frame);
    };

    // Auto-open a unit if URL has ?unit=<id> (so share links work)
    document.addEventListener('DOMContentLoaded', function () {
      const params = new URLSearchParams(window.location.search);
      const u = params.get('unit');
      if (u && typeof openMoreInfo === 'function') {
        setTimeout(() => openMoreInfo(u), 350);
      }
    });
  </script>


  <!-- ADVISOR VIDEO CALL MODAL — Figma 812:50211 (modal-agendar-videollamada) -->
  <style>
    .vc-overlay {
        position:fixed; inset:0; z-index:1200;
        background:rgba(15,17,24,.55);
        display:none; align-items:center; justify-content:center;
        padding:1rem;
        animation: vcFadeIn .18s ease-out;
    }
    .vc-overlay.open { display:flex; }
    @keyframes vcFadeIn { from { opacity:0 } to { opacity:1 } }

    .vc-modal {
        width:100%; max-width:500px;
        background:#fff; border-radius:18px;
        box-shadow:0 30px 80px -20px rgba(10,13,20,.35);
        animation: vcSlide .22s cubic-bezier(.4,0,.2,1);
        overflow:hidden;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    @keyframes vcSlide { from { transform: translateY(12px) scale(.98); opacity:0 } to { transform:none; opacity:1 } }

    .vc-header {
        display:flex; align-items:flex-start; gap:12px;
        padding:18px 20px 16px;
        border-bottom:1px solid #eaecf0;
    }
    .vc-header-icon {
        width:40px; height:40px; border-radius:999px;
        background:#f4f5f7; color:#525866;
        display:inline-flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .vc-header-icon svg { width:20px; height:20px; }
    .vc-header-text { flex:1; min-width:0; }
    .vc-header-title {
        font-family:'Inter Tight', Inter, sans-serif;
        font-size:16px; font-weight:700; color:#171717; line-height:1.25;
    }
    .vc-header-sub {
        font-size:12px; color:#717784; margin-top:2px;
    }
    .vc-close {
        width:32px; height:32px; border-radius:8px;
        background:transparent; border:none; cursor:pointer;
        color:#717784; display:inline-flex; align-items:center; justify-content:center;
        font-size:18px; flex-shrink:0;
    }
    .vc-close:hover { background:#f5f7fa; color:#222530; }

    .vc-body { padding:18px 20px 4px; }
    .vc-field { margin-bottom:18px; }
    .vc-field-label {
        display:block;
        font-size:13px; font-weight:600; color:#222530;
        margin-bottom:6px;
    }
    .vc-field-label .opt { color:#99a0ae; font-weight:500; }

    .vc-select, .vc-input, .vc-textarea {
        width:100%; box-sizing:border-box;
        background:#fafbfc; border:1px solid #eaecf0;
        border-radius:10px; padding:10px 12px;
        font-size:13px; color:#222530; outline:none;
        transition: border-color .15s, box-shadow .15s, background .15s;
        font-family:inherit;
    }
    .vc-input:focus, .vc-select:focus, .vc-textarea:focus {
        border-color:#5c7c68; background:#fff;
        box-shadow:0 0 0 3px rgba(92,124,104,.18);
    }
    .vc-select { appearance:none; padding-right:36px;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23717784' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
        background-repeat:no-repeat; background-position: right 12px center;
    }
    .vc-input-icon {
        position:relative;
    }
    .vc-input-icon .pi {
        position:absolute; left:12px; top:50%; transform:translateY(-50%);
        color:#717784; font-size:14px; pointer-events:none;
    }
    .vc-input-icon input { padding-left:36px; }

    .vc-textarea { min-height:90px; resize:vertical; }
    .vc-textarea-wrap { position:relative; }
    .vc-textarea-count {
        position:absolute; right:12px; bottom:8px;
        font-size:11px; color:#a3a3a3;
        pointer-events:none;
    }

    .vc-slots {
        display:grid; grid-template-columns: repeat(3, minmax(0,1fr));
        gap:8px;
    }
    .vc-slot {
        padding:10px 8px; border-radius:10px;
        background:#fafbfc; border:1px solid #eaecf0;
        font-size:13px; font-weight:600; color:#525866;
        text-align:center; cursor:pointer;
        transition: background .15s, border-color .15s, color .15s, box-shadow .15s;
    }
    .vc-slot:hover { background:#fff; border-color:#cacfd8; color:#222530; }
    .vc-slot.active {
        background:#5c7c68; border-color:#5c7c68; color:#fff;
        box-shadow:0 4px 12px -4px rgba(92,124,104,.45);
    }
    .vc-slot[disabled], .vc-slot.disabled {
        opacity:.45; cursor:not-allowed; text-decoration:line-through;
        background:#f5f7fa; color:#99a0ae;
    }
    .vc-slot[disabled]:hover, .vc-slot.disabled:hover {
        background:#f5f7fa; border-color:#eaecf0; color:#99a0ae;
    }

    .vc-success {
        padding: 20px;
        text-align: center;
    }
    .vc-success-icon {
        width:56px; height:56px; border-radius:999px;
        background:#e3f7ec; color:#1daf61;
        display:inline-flex; align-items:center; justify-content:center;
        margin-bottom:14px;
    }
    .vc-success-title { font-weight:700; font-size:16px; color:#171717; margin-bottom:6px; }
    .vc-success-sub   { font-size:13px; color:#525866; margin-bottom:14px; }
    .vc-meet-link {
        display:flex; align-items:center; gap:8px;
        background:#f4f5f7; border:1px solid #eaecf0; border-radius:10px;
        padding:10px 12px; margin:12px 0;
    }
    .vc-meet-link a { flex:1; color:#1a73e8; text-decoration:none; font-size:13px; word-break:break-all; }
    .vc-meet-link button {
        background:#fff; border:1px solid #eaecf0; border-radius:8px;
        padding:6px 10px; font-size:12px; cursor:pointer; color:#525866;
    }
    .vc-meet-link button:hover { background:#f5f7fa; }

    .vc-footer {
        display:flex; align-items:center; justify-content:space-between; gap:10px;
        padding:14px 20px 18px;
        border-top:1px solid #eaecf0;
    }
    .vc-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        padding:11px 22px; border-radius:10px;
        font-size:13px; font-weight:600; cursor:pointer;
        transition: background .15s, border-color .15s;
        border:1px solid transparent; flex:1;
    }
    .vc-btn-ghost { background:#fff; color:#525866; border-color:#eaecf0; }
    .vc-btn-ghost:hover { background:#f5f7fa; }
    .vc-btn-primary { background:#5c7c68; color:#fff; border-color:#5c7c68; }
    .vc-btn-primary:hover { background:#4a6354; border-color:#4a6354; }

    .vc-alert {
        margin: 0 20px 12px;
        padding:9px 12px; border-radius:9px;
        font-size:12px; font-weight:500;
        display:flex; align-items:center; gap:8px;
    }
    .vc-alert-err { background:#ffebec; color:#e93544; border:1px solid rgba(251,55,72,.25); }
    .vc-alert-ok  { background:#e3f7ec; color:#1daf61; border:1px solid rgba(31,193,107,.25); }

    /* ===========================================================
       Filter + view transitions
       =========================================================== */
    @keyframes fgCardIn {
      0%   { opacity: 0; transform: translateY(10px) scale(.96); }
      100% { opacity: 1; transform: none; }
    }
    @keyframes fgCardOut {
      0%   { opacity: 1; transform: none; }
      100% { opacity: 0; transform: translateY(-6px) scale(.97); }
    }
    .fg-units-grid > .fg-card {
      animation: fgCardIn .32s cubic-bezier(.16,1,.3,1) both;
      will-change: opacity, transform;
    }
    .fg-units-grid > .fg-card.is-fading-out {
      animation: fgCardOut .22s ease forwards;
      pointer-events: none;
    }

    #fgListTable tbody tr[data-filter-unit] {
      transition: opacity .25s ease;
    }
    #fgListTable tbody tr[data-filter-unit].is-fading-out {
      opacity: 0;
      pointer-events: none;
    }
    @keyframes fgRowIn {
      from { opacity: 0; transform: translateX(-8px); }
      to   { opacity: 1; transform: none; }
    }
    #fgListTable tbody tr[data-filter-unit].is-fading-in {
      animation: fgRowIn .28s ease both;
    }

    /* Plan view — markers pop in/out + canvas crossfades on floor change. */
    @keyframes fgMarkerPop {
      0%   { opacity: 0; transform: scale(.4) translateY(6px); }
      60%  { opacity: 1; transform: scale(1.06); }
      100% { opacity: 1; transform: none; }
    }
    .fg-plan-marker {
      transition: opacity .22s ease, transform .22s ease;
    }
    .fg-plan-marker.is-hidden {
      opacity: 0 !important;
      transform: scale(.3);
      pointer-events: none;
    }
    .fg-plan-marker.is-popping {
      animation: fgMarkerPop .42s cubic-bezier(.34,1.56,.64,1) both;
    }
    @keyframes fgPlanFade {
      from { opacity: .35; }
      to   { opacity: 1;   }
    }
    .fg-plan-canvas.is-switching {
      animation: fgPlanFade .35s ease both;
    }

    /* "PISO X · N UNIDADES" — soft slide when label/count updates. */
    @keyframes fgPlanLabelIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: none; }
    }
    .fg-plan-piso.is-changing .fg-plan-piso-left,
    .fg-plan-piso.is-changing .fg-plan-piso-right {
      animation: fgPlanLabelIn .28s ease both;
    }

    /* Unit info modal — open animation. */
    @keyframes mtBackdropIn {
      from { opacity: 0; }
      to   { opacity: 1; }
    }
    @keyframes mtPanelIn {
      from { opacity: 0; transform: translateY(24px) scale(.96); }
      to   { opacity: 1; transform: none; }
    }
    #moreInfoModal.is-opening .mt-backdrop {
      animation: mtBackdropIn .25s ease both;
    }
    #moreInfoModal.is-opening .mt-shell {
      animation: mtPanelIn .42s cubic-bezier(.16,1,.3,1) both;
    }

    /* Share modal — same pattern (lighter). */
    @keyframes shFadeIn { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: none; } }
    #shareModal.open .sh-modal {
      animation: shFadeIn .3s cubic-bezier(.16,1,.3,1) both;
    }

    /* "N Matches" pill — pulse when the count changes. */
    @keyframes fgPillPulse {
      0%, 100% { transform: none; }
      50%      { transform: scale(1.08); }
    }
    .fg-pill-matches.is-pulsing {
      animation: fgPillPulse .35s ease;
    }

    /* ===========================================================
       Lazy progressive reveal (grid cards + list rows)
       — initial batch renders, the rest fade in on scroll.
       =========================================================== */
    @keyframes fgLazyIn {
      0%   { opacity: 0; transform: translateY(18px) scale(.97); }
      100% { opacity: 1; transform: none; }
    }
    .fg-units-grid > .fg-card.is-lazy-in {
      animation: fgLazyIn .55s cubic-bezier(.16,1,.3,1) both;
      animation-delay: calc(var(--lazy-i, 0) * 55ms);
      will-change: opacity, transform;
    }
    @keyframes fgLazyRowIn {
      0%   { opacity: 0; transform: translateY(10px); }
      100% { opacity: 1; transform: none; }
    }
    #fgListTable tbody tr[data-filter-unit].is-lazy-in {
      animation: fgLazyRowIn .42s cubic-bezier(.16,1,.3,1) both;
      animation-delay: calc(var(--lazy-i, 0) * 35ms);
    }
    /* "Loading more units" sentinel + indicator (observed for infinite scroll) */
    .fg-lazy-more {
      display: none;
      align-items: center; justify-content: center; gap: 12px;
      width: 100%;
      padding: 26px 0 34px;
      color: #5c7c68;
      font-family: 'Inter', system-ui, sans-serif;
      font-size: 13px; font-weight: 600; letter-spacing: .2px;
      opacity: 0;
      transition: opacity .3s ease;
    }
    .fg-lazy-more.is-active { display: flex; opacity: 1; }
    /* The grid sentinel is a sibling of (not inside) .fg-units-grid, so the
       view switch that off-screens the grid doesn't hide it. Keep it grid-only
       or its "Cargando más unidades…" leaks to the top of the list/plan views. */
    body[data-view="list"] #gridLazyMore,
    body[data-view="plan"] #gridLazyMore { display: none !important; }
    #listLazyMore { padding: 18px 0 28px; }
    .fg-lazy-dots { display: inline-flex; gap: 6px; }
    .fg-lazy-dots span {
      width: 8px; height: 8px; border-radius: 50%;
      background: #5c7c68;
      animation: fgLazyDot 1s ease-in-out infinite;
    }
    .fg-lazy-dots span:nth-child(2) { animation-delay: .15s; }
    .fg-lazy-dots span:nth-child(3) { animation-delay: .3s; }
    @keyframes fgLazyDot {
      0%, 100% { transform: scale(.55); opacity: .35; }
      40%      { transform: scale(1);   opacity: 1;   }
    }
    .fg-lazy-label { opacity: .85; }

    @media (prefers-reduced-motion: reduce) {
      .fg-units-grid > .fg-card.is-lazy-in,
      #fgListTable tbody tr[data-filter-unit].is-lazy-in,
      .fg-lazy-dots span,
      .fg-units-grid > .fg-card,
      .fg-plan-marker.is-popping,
      .fg-plan-canvas.is-switching,
      #moreInfoModal.is-opening .mt-backdrop,
      #moreInfoModal.is-opening .mt-shell,
      #shareModal.open .sh-modal,
      .fg-pill-matches.is-pulsing,
      .fg-plan-piso.is-changing .fg-plan-piso-left,
      .fg-plan-piso.is-changing .fg-plan-piso-right {
        animation: none !important;
        transition: none !important;
      }
    }
  </style>

  <div id="advisorModal" class="vc-overlay" role="dialog" aria-modal="true" aria-label="Agendar Videollamada" onclick="if(event.target===this) closeAdvisorVideoCall()">
    <div class="vc-modal">
      <div class="vc-header">
        <div class="vc-header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
          </svg>
        </div>
        <div class="vc-header-text">
          <div class="vc-header-title">Agendar Videollamada</div>
          <div class="vc-header-sub" id="advisorModalSub">Con tu asesor de Makai Residences</div>
        </div>
        <button type="button" class="vc-close" onclick="closeAdvisorVideoCall()" aria-label="Cerrar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div id="advisorAlert" class="vc-alert" style="display:none;"></div>

      <form id="advisorForm" class="vc-body" onsubmit="return submitAdvisorVideoCall(event)">
        <input type="hidden" name="unit_id" id="advisorModalUnitId" value="">
        <input type="hidden" name="preferred_time" id="advisorPreferredTime" value="">

        <div class="vc-field">
          <label class="vc-field-label" for="advisorUnitSelect">Propiedad de interés</label>
          <select id="advisorUnitSelect" name="unit_label" class="vc-select" required>
            <option value="" disabled selected>Selecciona una unidad</option>
            @foreach($units as $unitOpt)
              @php
                $optId    = $unitOpt->custom_id ?? $unitOpt->id;
                $optPrice = number_format((float)($unitOpt->price ?? 0), 0);
              @endphp
              <option value="{{ $optId }}" data-price="{{ $optPrice }}">Unit {{ $optId }} · ${{ $optPrice }} USD</option>
            @endforeach
          </select>
        </div>

        <div class="vc-field">
          <label class="vc-field-label" for="advisorDate">Fecha preferida</label>
          <div class="vc-input-icon">
            <i class="pi pi-calendar"></i>
            <input type="date" id="advisorDate" name="preferred_date" class="vc-input" required>
          </div>
        </div>

        <div class="vc-field">
          <label class="vc-field-label">Horario disponible</label>
          <div class="vc-slots" role="radiogroup" aria-label="Horario disponible">
            @foreach(['9:00 AM','10:00 AM','11:00 AM','2:00 PM','3:00 PM','4:00 PM'] as $slot)
              <button type="button" class="vc-slot" data-slot="{{ $slot }}" role="radio" aria-checked="false" onclick="selectAdvisorSlot(this)">{{ $slot }}</button>
            @endforeach
          </div>
        </div>

        <div class="vc-field">
          <label class="vc-field-label" for="advisorNote">Nota para el asesor <span class="opt">(Opcional)</span></label>
          <div class="vc-textarea-wrap">
            <textarea id="advisorNote" name="note" class="vc-textarea" maxlength="200" placeholder="Referencia bancaria, número de comprobante" oninput="updateAdvisorNoteCount(this)"></textarea>
            <span class="vc-textarea-count" id="advisorNoteCount">0/200</span>
          </div>
        </div>
      </form>

      <div class="vc-footer" id="advisorFooter">
        <button type="button" class="vc-btn vc-btn-ghost" onclick="closeAdvisorVideoCall()">Cancelar</button>
        <button type="submit" form="advisorForm" id="advisorSubmitBtn" class="vc-btn vc-btn-primary">Confirmar solicitud</button>
      </div>

      <div class="vc-success" id="advisorSuccess" style="display:none;">
        <div class="vc-success-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <div class="vc-success-title">¡Videollamada agendada!</div>
        <div class="vc-success-sub" id="advisorSuccessSub">Te enviamos la invitación por email. También aparece en tu Google Calendar.</div>
        <div class="vc-meet-link">
          <i class="pi pi-video" style="color:#1a73e8;"></i>
          <a id="advisorMeetLink" href="#" target="_blank" rel="noopener">Abrir en Google Meet</a>
          <button type="button" onclick="copyAdvisorMeetLink()">Copiar</button>
        </div>
        <button type="button" class="vc-btn vc-btn-primary" style="width:100%;" onclick="goToCalendarMeet()">Ver en mi calendario</button>
        <button type="button" class="vc-btn vc-btn-ghost" style="width:100%;margin-top:8px;" onclick="closeAdvisorVideoCall()">Cerrar</button>
      </div>
    </div>
  </div>



  <!-- MAIN PAGE -->
  <div id="mainPage">
    <!-- Navbar -->
    <nav style="display:flex;align-items:center;justify-content:center;width:100%;padding:16px 24px 12px;position:fixed;top:0;left:0;right:0;z-index:100;background:transparent;pointer-events:none;">
      <div style="pointer-events:auto;display:flex;align-items:center;justify-content:space-between;gap:1rem;width:100%;max-width:1280px;height:60px;padding:8px 8px 8px 12px;background:#ffffff;border-radius:9999px;box-shadow:0 5px 5px rgba(0,0,0,0.04),0 18px 16px rgba(0,0,0,0.05),0 41px 22.5px rgba(0,0,0,0.04),0 73px 30px rgba(0,0,0,0.02);">
        <!-- LEFT: Logo + animated project selector -->
        <div class="logo-section" style="position:relative;display:flex;align-items:center;gap:8px;flex-shrink:0;min-width:200px;max-width:300px;">
          <div class="logo-container" style="display:flex;align-items:center;">
            <a href="#" onclick="return false;" style="display:flex;align-items:center;text-decoration:none;height:44px;padding:0 4px;border-radius:9999px;">
              <img src="/images/makai-logo.png" alt="logo" class="logo-img" style="height:32px;width:auto;max-width:160px;object-fit:contain;">
            </a>
          </div>
          <button type="button" id="projectsToggle" class="logo-trigger" onclick="toggleProjects()" aria-label="Switch project" aria-expanded="false">
            <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>

          <!-- PROJECT SELECTOR DROPDOWN -->
          <div class="project-selector" id="projectsDropdown" role="menu" aria-label="Projects">
            <button type="button" class="project-card active" data-project="makai" onclick="selectProject('Makai')" aria-label="Makai Residences" role="menuitem">
              <img src="/images/projects/makai.png" alt="Makai Residences">
            </button>
            <button type="button" class="project-card" data-project="naviva" onclick="selectProject('Naviva')" aria-label="Naviva" role="menuitem">
              <img src="/images/projects/naviva.png" alt="Naviva">
            </button>
            <button type="button" class="project-card" data-project="liv" onclick="selectProject('Liv')" aria-label="Liv" role="menuitem">
              <img src="/images/projects/liv.png" alt="Liv">
            </button>
          </div>
        </div>

        <!-- CENTER: Units sold + online users -->
        <div style="display:flex;flex-direction:column;align-items:center;flex:0 1 auto;min-width:0;">
          <span style="font-family:'Poppins',sans-serif;font-weight:700;font-size:14px;line-height:20px;letter-spacing:1.12px;color:var(--brand);text-align:center;white-space:nowrap;text-transform:uppercase;">{{ __(':sold OF :total UNITS SOLD', ['sold' => $soldCount ?? 0, 'total' => $totalUnits ?? 0]) }}</span>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:inline-block;width:6px;height:6px;background:#db5858;border-radius:50%;box-shadow:0 0 6px rgba(219,88,88,0.6);animation:pulse 1.5s infinite;"></span>
            <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:10px;line-height:20px;letter-spacing:0.2px;color:#db5858;white-space:nowrap;text-transform:uppercase;"><span data-active-users>32</span> {{ __('online_users') }}</span>
          </div>
        </div>
        <style>
          @keyframes pulse {

            0%,
            100% {
              opacity: 1;
              transform: scale(1);
            }

            50% {
              opacity: 0.4;
              transform: scale(0.7);
            }
          }
        </style>

        <!-- RIGHT: Saved counter + Avatar + Hamburger -->
        <div style="position:relative;display:flex;align-items:center;gap:12px;flex-shrink:0;">
          @php $isAdminUser = auth()->check() && (auth()->user()->role ?? '') === 'admin'; @endphp
          @if($isAdminUser)
          <span aria-label="Saved units" aria-disabled="true" title="No disponible para administradores" style="display:inline-flex;align-items:center;gap:4px;padding:0;background:transparent;border:none;cursor:not-allowed;border-radius:9999px;text-decoration:none;opacity:0.4;pointer-events:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;letter-spacing:-0.072px;white-space:nowrap;">{{ __('guardados') }}</span>
          </span>
          @else
          <a href="{{ auth()->check() ? route('dashboard.guardados') : route('login') }}" aria-label="Saved units" style="display:inline-flex;align-items:center;gap:4px;padding:0;background:transparent;border:none;cursor:pointer;border-radius:9999px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;letter-spacing:-0.072px;white-space:nowrap;" data-saved-count>{{ __('guardados') }} ({{ count($wishlistIds ?? []) }})</span>
          </a>
          @endif

          <span aria-hidden="true" style="display:inline-block;width:1px;height:28px;background:#ebebeb;flex-shrink:0;"></span>

          <!-- Profile container with dropdown -->
          <div style="position:relative;">
            <button type="button" onclick="toggleProfileMenu()" aria-label="Profile" style="display:inline-flex;align-items:center;gap:6px;padding:0 0 0 6px;background:transparent;border:none;cursor:pointer;border-radius:9999px;">
              <span style="display:flex;flex-direction:column;align-items:flex-end;gap:2px;line-height:1;">
                <span style="font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:var(--brand);">{{ auth()->check() ? explode(' ', auth()->user()->name)[0] : 'Samuel' }}</span>
                <span style="font-family:'Poppins',sans-serif;font-weight:500;font-size:9px;color:#99a0ae;letter-spacing:0.72px;text-transform:uppercase;">{{ auth()->check() && (auth()->user()->role ?? '') === 'admin' ? 'Admin' : 'Agente' }}</span>
              </span>
              @if(auth()->check() && auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" style="display:inline-block;width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;" aria-hidden="true">
              @else
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:var(--brand);color:white;font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;flex-shrink:0;" aria-hidden="true">
                  {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'S' }}
                </span>
              @endif
            </button>

            <!-- PROFILE DROPDOWN -->
            <div class="menu-dropdown-container" id="profileDropdown">
              
              <!-- User Info Section -->
              <div style="display:flex;gap:8px;align-items:center;padding:8px;background:white;border-radius:10px;width:100%;flex-shrink:0;">
                <div style="position:relative;border-radius:999px;width:40px;height:40px;flex-shrink:0;overflow:hidden;">
                  @if(auth()->check() && auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" style="position:absolute;width:100%;height:100%;object-fit:cover;border-radius:999px;" />
                  @else
                    <span style="position:absolute;display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;background:var(--brand);color:white;font-family:'Poppins',sans-serif;font-weight:600;font-size:16px;border-radius:999px;">
                      {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'S' }}
                    </span>
                  @endif
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;min-width:0;">
                  <div style="font-family:'Poppins',sans-serif;font-weight:600;font-size:14px;color:#171717;letter-spacing:-0.084px;max-width: 100% !important;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{{ auth()->check() ? auth()->user()->name : 'Samuel Urbina' }}</div>
                  <div style="font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;max-width: 100% !important;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{{ auth()->check() ? auth()->user()->email : 'samuelurbi@gmail.com' }}</div>
                </div>
                <div style="position:relative;">
                  <button type="button" class="fg-filter-btn" onclick="toggleCurrencyDropdown()" id="currencyBtn">
                    <span id="currencyLabel">USD</span>
                    <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                  </button>
                  <div id="currencyDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);right:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:100px;padding:8px;">
                    <div style="font-family:'Poppins',sans-serif;">
                      <label style="display:block;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;cursor:pointer;padding:6px 8px;border-radius:6px;transition:background 0.15s;" onmouseover="this.style.background='#f2f5f8'" onmouseout="this.style.background='transparent'" onclick="selectCurrency('USD')">USD</label>
                      <label style="display:block;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;cursor:pointer;padding:6px 8px;border-radius:6px;transition:background 0.15s;" onmouseover="this.style.background='#f2f5f8'" onmouseout="this.style.background='transparent'" onclick="selectCurrency('EUR')">EUR</label>
                      <label style="display:block;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;cursor:pointer;padding:6px 8px;border-radius:6px;transition:background 0.15s;" onmouseover="this.style.background='#f2f5f8'" onmouseout="this.style.background='transparent'" onclick="selectCurrency('CAD')">CAD</label>
                      <label style="display:block;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;cursor:pointer;padding:6px 8px;border-radius:6px;transition:background 0.15s;" onmouseover="this.style.background='#f2f5f8'" onmouseout="this.style.background='transparent'" onclick="selectCurrency('MXN')">MXN</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Language Toggle -->
              <div style="display:flex;flex-direction:column;align-items:flex-start;padding:8px 0;position:relative;width:100%;flex-shrink:0;">
                <div style="background:#f2f5f8;display:flex;gap:4px;align-items:center;justify-content:center;overflow:hidden;padding:4px;border-radius:12px;width:308px;position:relative;">
                  <div style="position:absolute;background:white;height:32px;left:4px;border-radius:8px;top:4px;width:148px;" id="lang-indicator"></div>
                  <button onclick="setLanguage('es')" id="lang-es" style="display:flex;gap:6px;align-items:center;justify-content:center;padding:6px;border-radius:8px;flex:1;min-width:0;position:relative;z-index:1;background:transparent;border:none;cursor:pointer;">
                    <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;border-radius:50%;">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="20" height="20" rx="10" fill="#AA151B"/>
                        <rect y="6.67" width="20" height="6.66" fill="#F1BF00"/>
                      </svg>
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:center;font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:#525866;letter-spacing:-0.072px;white-space:nowrap;flex-shrink:0;">
                      <span style="line-height:20px;">{{ __('Español') }}</span>
                    </div>
                  </button>
                  <button onclick="setLanguage('en')" id="lang-en" style="display:flex;gap:8px;align-items:center;justify-content:center;padding:6px;border-radius:8px;flex:1;min-width:0;position:relative;z-index:1;background:transparent;border:none;cursor:pointer;opacity:0.52;">
                    <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;border-radius:50%;">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="20" height="20" rx="10" fill="#B22234"/>
                        <rect y="1.54" width="20" height="1.54" fill="white"/>
                        <rect y="4.62" width="20" height="1.54" fill="white"/>
                        <rect y="7.69" width="20" height="1.54" fill="white"/>
                        <rect y="10.77" width="20" height="1.54" fill="white"/>
                        <rect y="13.85" width="20" height="1.54" fill="white"/>
                        <rect y="16.92" width="20" height="1.54" fill="white"/>
                        <rect width="8.46" height="10.77" fill="#3C3B6E"/>
                      </svg>
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:center;font-family:'Poppins',sans-serif;font-weight:600;font-size:12px;color:#717784;letter-spacing:-0.072px;white-space:nowrap;flex-shrink:0;">
                      <span style="line-height:20px;">{{ __('English') }}</span>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Menu Items -->
              <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.crm.dashboard') : route('dashboard') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <rect x="3" y="3" width="7" height="7"></rect>
                      <rect x="14" y="3" width="7" height="7"></rect>
                      <rect x="14" y="14" width="7" height="7"></rect>
                      <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Dashboard') }}</div>
                </div>
              </a>

              <div class="menu-item" onclick="openProfileModal()" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Mi perfil') }}</div>
              </div>

              @if($isAdminUser)
              <div aria-disabled="true" title="No disponible para administradores" style="text-decoration:none;display:block;width:100%;cursor:not-allowed;opacity:0.4;pointer-events:none;">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Lista de guardados') }}</div>
                </div>
              </div>
              @else
              <a href="{{ auth()->check() ? route('dashboard.guardados') : route('login') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Lista de guardados') }}</div>
                </div>
              </a>
              @endif

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Contactar agente') }}</div>
              </div>

              <a href="{{ route('support') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <circle cx="12" cy="12" r="10"></circle>
                      <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                      <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Soporte') }}</div>
                </div>
              </a>

              <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.crm.avance-obra') : route('dashboard.progress') }}" style="text-decoration:none;display:block;width:100%">
                <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </div>
                  <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Progreso de la construcción') }}</div>
                </div>
              </a>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Sitio web') }}</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('FAQs') }}</div>
              </div>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Presentación comercial') }}</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Plantas') }}</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('ROIs') }}</div>
              </div>

              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Especificaciones') }}</div>
              </div>

              <!-- Divider -->
              <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;">
                <div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div>
              </div>

              <!-- Sign Out -->
              <form method="POST" action="/logout" style="margin:0;width:100%;flex-shrink:0;" data-logout-confirm>
                @csrf
                <button type="submit" class="logout-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;border:none;cursor:pointer;">
                  <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#dc2626;">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                      <polyline points="16 17 21 12 16 7"></polyline>
                      <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                  </div>
                  <div style="flex:1;text-align: start;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Cerrar sesión') }}</div>
                </button>
              </form>

              <!-- Footer -->
              <div style="background:white;display:flex;align-items:center;overflow:hidden;padding:8px;width:100%;flex-shrink:0;">
                <div style="flex:1;min-width:0;font-family:'Poppins',sans-serif;font-weight:500;font-size:12px;color:#a3a3a3;line-height:16px;">v.1.0.1 · {{ __('Términos y condiciones') }}</div>
              </div>

              <!-- Inner shadow effect -->
              <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;border-radius:inherit;box-shadow:inset 0px -1px 1px -0.5px rgba(23,23,23,0.06);"></div>
            </div>
          </div>

          <button type="button" onclick="toggleMenu()" aria-label="Menu" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:transparent;border:none;border-radius:9999px;cursor:pointer;padding:0;">
            <span aria-hidden="true" style="position:relative;display:inline-block;width:20px;height:20px;">
              <span style="position:absolute;left:0;top:4px;width:20px;height:2px;border-radius:2px;background:var(--brand);"></span>
              <span style="position:absolute;right:0;top:9px;width:15px;height:2px;border-radius:2px;background:var(--brand);"></span>
              <span style="position:absolute;right:0;top:14px;width:10px;height:2px;border-radius:2px;background:var(--brand);"></span>
            </span>
          </button>

        </div><!-- /right -->
      </div><!-- /pill -->
    </nav>

    </div><!-- /right -->
      </div><!-- /pill -->
    </nav>

    <!-- Hero -->
    <div class="fg-hero" id="hero" data-active="makai">
      <img class="fg-hero-layer fg-hero-sky" src="/images/hero/SKY.png" alt="" aria-hidden="true">

      <span class="fg-hero-text" data-project="makai"  aria-hidden="true">MAKAI</span>
      <span class="fg-hero-text" data-project="naviva" aria-hidden="true">NAVIVA</span>
      <span class="fg-hero-text" data-project="liv"    aria-hidden="true">LIV</span>

      <img class="fg-hero-building" data-project="makai"  src="/images/hero/MAKAI.png"  alt="Makai Residences">
      <img class="fg-hero-building" data-project="naviva" src="/images/hero/NAVIVA.png" alt="Naviva Residences">
      <img class="fg-hero-building" data-project="liv"    src="/images/hero/LIV.png"    alt="Liv Residences">

    </div>
    <div class="fg-hero-spacer" aria-hidden="true"></div>

    <!-- Main Content -->
    <div id="main-unit-reserve-list" style="min-height:100vh;background:#f2f5f8;">

      <!-- Empty state for projects without units (Naviva / Liv) -->
      <div class="fg-project-empty" aria-hidden="true">
        <div class="fg-project-empty-card">
          <div class="fg-project-empty-icon" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
          </div>
          <h2 class="fg-project-empty-title">{{ __('Estamos trabajando en ello') }}</h2>
          <p class="fg-project-empty-text">{{ __('Las unidades de') }} <span data-empty-project-name>{{ __('este proyecto') }}</span> {{ __('aún no están disponibles. Estamos preparando todo para que muy pronto puedas explorarlas aquí.') }}</p>
          <button type="button" class="fg-project-empty-cta" onclick="toggleProjects()">{{ __('Ver otros proyectos') }}</button>
        </div>
      </div>

      <!-- Grid/List/Planta Toggle -->
      <div class="fg-toggle-bar">
        <div class="fg-toggle-container" role="tablist" aria-label="View mode">
          <div class="fg-toggle" data-node-id="171:10199">
            <button type="button" class="active" data-view="grid" role="tab" aria-selected="true">
              <div class="fg-icon-grid">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
              </div>
              <span>{{ __('Grid') }}</span>
            </button>
            <button type="button" data-view="list" role="tab" aria-selected="false">
              <div class="fg-icon-list">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="8" y1="6" x2="21" y2="6"></line>
                  <line x1="8" y1="12" x2="21" y2="12"></line>
                  <line x1="8" y1="18" x2="21" y2="18"></line>
                  <line x1="4" y1="6" x2="4" y2="6"></line>
                  <line x1="4" y1="12" x2="4" y2="12"></line>
                  <line x1="4" y1="18" x2="4" y2="18"></line>
                </svg>
              </div>
              <span>{{ __('List') }}</span>
            </button>
            <div class="fg-toggle-bg-active" aria-hidden="true"></div>
          </div>

          <!-- Floating Map-Pin (Planta) Button -->
          <div class="fg-location-button">
            <button type="button" class="fg-location-btn" data-view="plan" role="tab" aria-selected="false" aria-label="Planta view">
              <div class="fg-icon-location">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Filter Bar — placed below the view toggle, visible only in Grid view -->
      <div class="fg-filter-bar" data-grid-only>
        <div class="fg-filters-left">
          <label class="fg-search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="{{ __('Unit No.') }}">
          </label>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('price')">
              <span id="priceLabel">{{ __('Precio') }}</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="priceDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:170px;padding:12px;">
              <div style="margin-bottom:8px;">
                <label style="font-family:'Poppins',sans-serif;font-size:12px;color:#5c5c5c;font-weight:500;display:block;margin-bottom:4px;">{{__('Min Price')}}</label>
                <input type="number" id="minPrice" placeholder="0" style="width:100%;padding:6px 8px;border:1px solid #ebebeb;border-radius:6px;font-size:14px;font-family:'Poppins',sans-serif;">
              </div>
              <div style="margin-bottom:8px;">
                <label style="font-family:'Poppins',sans-serif;font-size:12px;color:#5c5c5c;font-weight:500;display:block;margin-bottom:4px;">{{__('Max Price')}}</label>
                <input type="number" id="maxPrice" placeholder="1000000" style="width:100%;padding:6px 8px;border:1px solid #ebebeb;border-radius:6px;font-size:14px;font-family:'Poppins',sans-serif;">
              </div>
              <button onclick="applyPriceFilter()" style="width:100%;padding:8px;background:var(--brand);color:white;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;font-family:'Poppins',sans-serif;">{{__('Apply')}}</button>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('floor')">
              <span id="floorLabel">Piso</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="floorDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:120px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                @forelse($floorLabels as $floorKey => $floorText)
                  <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="{{ $floorKey }}" onchange="applyFloorFilter()"> {{ $floorText }}</label>
                @empty
                  <div style="font-size:12px;color:#a3a3a3;padding:4px 0;">{{ __('Sin opciones') }}</div>
                @endforelse
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('type')">
              <span id="typeLabel">{{ __('Tipo de unidad') }}</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="typeDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:140px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                @forelse($typeLabels as $typeKey => $typeText)
                  <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="{{ $typeKey }}" onchange="applyTypeFilter()"> {{ $typeText }}</label>
                @empty
                  <div style="font-size:12px;color:#a3a3a3;padding:4px 0;">{{ __('Sin opciones') }}</div>
                @endforelse
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('direction')">
              <span id="directionLabel">{{ __('Direction') }}</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="directionDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:120px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="N" onchange="applyDirectionFilter()"> {{ __('North') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="S" onchange="applyDirectionFilter()"> {{ __('South') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="E" onchange="applyDirectionFilter()"> {{ __('East') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="W" onchange="applyDirectionFilter()"> {{ __('West') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="NE" onchange="applyDirectionFilter()"> {{ __('NE') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="NW" onchange="applyDirectionFilter()"> {{ __('NW') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="SE" onchange="applyDirectionFilter()"> {{ __('SE') }}</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="SW" onchange="applyDirectionFilter()"> {{ __('SW') }}</label>
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('outlook')">
              <span id="outlookLabel">{{ __('Outlook') }}</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="outlookDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:140px;padding:12px;">
              <div style="max-height:220px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                {{-- Las opciones (value) coinciden con las del formulario de unidad en el admin. --}}
                @foreach($outlookLabels as $outlookKey => $outlookText)
                  <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="checkbox" value="{{ $outlookKey }}" onchange="applyOutlookFilter()"> {{ $outlookText }}</label>
                @endforeach
              </div>
            </div>
          </div>

          <div style="position:relative;">
            <button class="fg-filter-btn" onclick="toggleFilterDropdown('sort')">
              <span id="sortLabel">{{ __('Sort') }}</span>
              <svg class="fg-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="sortDropdown" class="filter-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;z-index:50;background:white;border:1px solid #ebebeb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:160px;padding:12px;">
              <div style="max-height:240px;overflow-y:auto;font-family:'Poppins',sans-serif;">
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="price-asc" onchange="applySortFilter()"> Price: Low to High</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="price-desc" onchange="applySortFilter()"> Price: High to Low</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="size-asc" onchange="applySortFilter()"> Size: Small to Large</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="size-desc" onchange="applySortFilter()"> Size: Large to Small</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="bedrooms-asc" onchange="applySortFilter()"> Bedrooms: Low to High</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="bedrooms-desc" onchange="applySortFilter()"> Bedrooms: High to Low</label>
                <label style="display:block;font-size:13px;color:#5c5c5c;cursor:pointer;padding:4px 0;"><input type="radio" name="sort" value="custom_id" onchange="applySortFilter()" checked> Unit Number</label>
              </div>
            </div>
          </div>

          <button class="fg-filter-icon" type="button" aria-label="Reset filters" title="Reset filters" onclick="if(typeof resetFilters==='function'){resetFilters();}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="1 4 1 10 7 10"></polyline>
              <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
          </button>
        </div>

        <button class="fg-pill-matches" type="button" onclick="shareMatches()">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
          </svg>
          {{ __('Mostrando :shown de :total unidades', ['shown' => $units->count(), 'total' => $units->count()]) }}
        </button>
      </div>
      <!-- Cards Grid -->
      <div class="fg-units-grid" style="padding-top:10px">
        @foreach($gridUnits as $unit)
          @include('partials.home-unit-card')
        @endforeach
      </div>
      <!-- Infinite-scroll sentinel for the grid (revealed batch by batch). -->
      <div class="fg-lazy-more" id="gridLazyMore" aria-hidden="true">
        <span class="fg-lazy-dots"><span></span><span></span><span></span></span>
        <span class="fg-lazy-label">{{ __('Cargando más unidades…') }}</span>
      </div>

      <!-- LIST VIEW -->
      <div class="fg-list-wrap" id="fgListWrap">
        <div class="fg-list-toolbar">
          <label class="fg-list-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a3a3a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="{{ __('Search unit, floor, type…') }}" oninput="filterListRows(this.value)">
          </label>
          <div class="fg-list-tabs" role="tablist" aria-label="{{ __('Status filter') }}">
            <button type="button" class="fg-list-tab active" data-tab="all" onclick="setListTab(this)">{{ __('All') }} <span class="badge">{{ $units->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="available" onclick="setListTab(this)">{{ __('Available') }} <span class="badge">{{ $units->whereIn('status', ['available', null, ''])->count() ?: $units->where('status', '!=', 'sold')->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="hot" onclick="setListTab(this)">{{ __('Hot') }} <span class="badge">{{ $units->filter(fn($u)=>!empty($u->is_high_demand))->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="pending" onclick="setListTab(this)">{{ __('Pending') }} <span class="badge">{{ $units->where('status', 'PENDING')->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="second" onclick="setListTab(this)">{{ __('2nd Chance') }} <span class="badge">{{ $units->filter(fn($u)=>!empty($u->is_second_chance))->count() }}</span></button>
            <button type="button" class="fg-list-tab" data-tab="sold" onclick="setListTab(this)">{{ __('Sold') }} <span class="badge">{{ $units->where('status', 'SOLD')->count() }}</span></button>
          </div>
          <button class="fg-pill-matches" type="button" style="margin-left:auto;" onclick="shareMatches()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            {{ __('Mostrando :shown de :total unidades', ['shown' => $units->count(), 'total' => $units->count()]) }}
          </button>
        </div>
        <table class="fg-list-table" id="fgListTable">
          <thead>
            <tr>
              <th>{{ __('Unit') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Floor') }}</th>
              <th>{{ __('Type') }}</th>
              <th>{{ __('Direction') }}</th>
              <th>{{ __('Bed/Bath') }}</th>
              <th>{{ __('Int sqft') }}</th>
              <th>{{ __('Ext sqft') }}</th>
              <th>{{ __('Price') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($gridUnits as $unit)
              @include('partials.home-unit-row')
            @endforeach
          </tbody>
        </table>
        <!-- Infinite-scroll sentinel for the list (revealed batch by batch). -->
        <div class="fg-lazy-more" id="listLazyMore" aria-hidden="true">
          <span class="fg-lazy-dots"><span></span><span></span><span></span></span>
          <span class="fg-lazy-label">{{ __('Cargando más unidades…') }}</span>
        </div>
        <div class="fg-list-pagination">
          <span>{{ __('Page :current of :total', ['current' => 1, 'total' => 1]) }}</span>
          <div class="fg-list-pages">
            <button class="fg-list-page" type="button" aria-label="{{ __('Previous') }}">‹</button>
            <button class="fg-list-page active" type="button">1</button>
            <button class="fg-list-page" type="button" aria-label="{{ __('Next') }}">›</button>
          </div>
        </div>
      </div>

      <!-- PLAN VIEW (Figma 193:9116 — Property 1=planta, makai=true) -->
      <div class="fg-plan-wrap" id="fgPlanWrap">
        <div class="fg-plan-board">

          @php
            // Group all public units by floor (DB-driven). Units without a floor
            // value are bucketed under "Ground". Floors are sorted naturally so
            // "Ground" comes first, then 1, 2, 3, …
            $floorBuckets = collect($units ?? [])->groupBy(function($u) {
                $f = trim((string) ($u->floor ?? ''));
                if ($f === '' || strcasecmp($f, 'ground') === 0 || strcasecmp($f, 'pb') === 0) {
                    return 'Ground';
                }
                return $f;
            });

            $floorOrder = $floorBuckets->keys()->sort(function($a, $b) {
                if ($a === 'Ground') return -1;
                if ($b === 'Ground') return 1;
                $na = (int) preg_replace('/\D+/', '', $a);
                $nb = (int) preg_replace('/\D+/', '', $b);
                if ($na === $nb) return strcmp($a, $b);
                return $na <=> $nb;
            })->values();

            // Active floor = first bucket with any AVAILABLE unit, else first
            $activeFloor = $floorOrder->first(function($f) use ($floorBuckets) {
                return $floorBuckets[$f]->contains(fn($u) =>
                    !in_array(strtolower((string)$u->status), ['sold','reserved','pending'])
                );
            }) ?? ($floorOrder->first() ?? 'Ground');

            // Available count per floor (excludes sold/reserved/pending)
            $availableByFloor = $floorOrder->mapWithKeys(function($f) use ($floorBuckets) {
                $n = $floorBuckets[$f]->filter(fn($u) =>
                    !in_array(strtolower((string)$u->status), ['sold','reserved','pending'])
                )->count();
                return [$f => $n];
            });

            // Distinct, well-spread anchor coords across the 1366×769 planview.
            // Markers cycle through this list per floor; "side" flips to keep the
            // tail pointing into the canvas. Add more entries if a floor has more.
            $anchorPoints = [
                ['x'=>1184,   'y'=>295,   'side'=>'left'],
                ['x'=>289,    'y'=>349,   'side'=>'right'],
                ['x'=>1129,   'y'=>394,   'side'=>'left'],
                ['x'=>375,    'y'=>412,   'side'=>'right'],
                ['x'=>1082.5, 'y'=>474.5, 'side'=>'left'],
                ['x'=>460,    'y'=>500,   'side'=>'right'],
                ['x'=>980,    'y'=>540,   'side'=>'left'],
                ['x'=>560,    'y'=>360,   'side'=>'right'],
                ['x'=>880,    'y'=>320,   'side'=>'left'],
                ['x'=>650,    'y'=>440,   'side'=>'right'],
                ['x'=>790,    'y'=>250,   'side'=>'left'],
                ['x'=>320,    'y'=>260,   'side'=>'right'],
            ];

            $markerStateFor = function($u) {
                $s = strtolower((string) $u->status);
                if (in_array($s, ['sold'])) return 'sold';
                if (in_array($s, ['reserved','pending'])) return 'reserved';
                if (!empty($u->is_second_chance)) return '2nd';
                if (!empty($u->is_high_demand)) return 'hot';
                return 'default';
            };
          @endphp

          <!-- chips-filters bar (Figma 193:6017) -->
          <div class="fg-plan-topbar">
            <div class="fg-plan-chips" role="tablist" aria-label="Floor filter">
              @forelse($floorOrder as $floorLabel)
                @php $isActive = ($floorLabel === $activeFloor); @endphp
                <button type="button"
                        class="fg-chip-floor{{ $isActive ? ' is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        data-floor="{{ $floorLabel }}">
                  <span class="fg-chip-left">
                    <span class="fg-chip-dot"></span>
                    <span class="fg-chip-text">{{ $floorLabel }}</span>
                  </span>
                  <span class="fg-chip-count">{{ $floorBuckets[$floorLabel]->count() }}</span>
                </button>
              @empty
                <div class="fg-plan-empty-chips" style="color:#9aa3a0;font-size:13px;padding:8px 12px;">
                  Sin unidades publicadas.
                </div>
              @endforelse
            </div>

            <!-- Active floor label · N UNIDADES DISPONIBLES -->
            <div class="fg-plan-piso">
              <div class="fg-plan-piso-left" id="fgPlanPisoLabel">
                {{ strtoupper($activeFloor === 'Ground' ? 'PLANTA BAJA' : 'PISO '.$activeFloor) }}
              </div>
              <div class="fg-plan-piso-right" id="fgPlanPisoCount">
                {{ $availableByFloor[$activeFloor] ?? 0 }} UNIDADES DISPONIBLES
              </div>
            </div>
          </div>

          <!-- Map container (Figma 193:6600 — ContainerMap, 1366×769) -->
          <div class="fg-plan-canvas" style="background-color: white!important;" id="fgPlanCanvas">
            <!-- Planview image — labels, compass, and PHASE 1 are baked in -->
            <img src="/images/plan-view/makai-planview.png"
                 alt="Plan view"
                 class="fg-plan-img"
                 draggable="false">

            {{-- Render one marker per real unit, grouped by floor. JS toggles
                 visibility based on the active floor chip. --}}
            @foreach($floorOrder as $floorLabel)
              @foreach($floorBuckets[$floorLabel]->values() as $idx => $unit)
                @php
                  $anchor   = $anchorPoints[$idx % count($anchorPoints)];
                  $leftPct  = ($anchor['x'] / 1366) * 100;
                  $topPct   = ($anchor['y'] / 769) * 100;
                  $state    = $markerStateFor($unit);
                  $side     = $anchor['side'];
                  $unitId   = $unit->id;
                  $unitLbl  = $unit->custom_id ?? ('U-'.$unit->id);
                  $price    = (float) ($unit->price ?? 0);
                  $priceTxt = $price > 0 ? '$'.number_format($price/1000, 0).'k' : '—';
                  $area     = $unit->internal_area ?? 0;
                  $areaTxt  = $area > 0 ? rtrim(rtrim(number_format($area, 0), '0'), '.') : '—';
                  $hidden   = ($floorLabel !== $activeFloor);
                  $uid      = $floorLabel.'_'.$idx.'_'.$state;
                @endphp
                <button type="button"
                        class="fg-plan-marker is-{{ $state }} side-{{ $side }}{{ $hidden ? ' is-hidden' : '' }}"
                        style="left:{{ number_format($leftPct, 4, '.', '') }}%;top:{{ number_format($topPct, 4, '.', '') }}%;{{ $hidden ? 'display:none;' : '' }}"
                        data-floor="{{ $floorLabel }}"
                        data-unit-id="{{ $unitId }}"
                        onclick="openMoreInfo('{{ $unitId }}')"
                        aria-label="Unit {{ $unitLbl }}">
                  <span class="fg-plan-marker-bubble">
                    @include('partials._plan_marker_svg', [
                        'state' => $state,
                        'side'  => $side,
                        'uid'   => $uid,
                    ])
                    <span class="fg-plan-marker-text">
                      <span class="fg-plan-marker-price">{{ $priceTxt }}</span>
                      <span class="fg-plan-marker-sqft">{{ $areaTxt }}</span>
                    </span>
                    @if($state === 'hot')
                      <span class="fg-plan-marker-fire" aria-hidden="true">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7 7 0 0 1-7-7c0-2 1-3 1-3 0 1 1 2 2 2 0-3 2-5 2-8 0-2-1-3-1-3 4 0 8 4 8 9 1-1 2-2 2-4 2 1 3 4 3 7a7 7 0 0 1-7 7z"/></svg>
                      </span>
                    @endif
                  </span>
                </button>
              @endforeach
            @endforeach
          </div>

        </div>
      </div>

    </div>

    <!-- Footer -->
    <footer class="fg-footer" data-node-id="124:3620">
      <div class="fg-footer-content" data-node-id="124:3621">
        <!-- Logo -->
        <div class="fg-footer-logo" data-node-id="124:3656">
          <img src="/images/makai-logo.png" alt="logo" class="logo-img" style="max-height:30px;max-width:160px;object-fit:contain;">
        </div>
        
        <!-- Copyright -->
        <div class="fg-footer-copyright" data-node-id="124:3626">
          <p>{{ __('©2026 Duna Development — Todos los derechos reservados') }}</p>
        </div>
        
        <!-- Social Icons -->
        <div class="fg-footer-social" data-node-id="124:3627">
          <!-- Facebook -->
          <div class="fg-social-icon" data-node-id="124:3628">
            <div class="fg-icon-facebook" data-node-id="124:3629">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </div>
          </div>
          
          <!-- Instagram -->
          <div class="fg-social-icon" data-node-id="124:3630">
            <div class="fg-icon-instagram" data-node-id="124:3631">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
              </svg>
            </div>
          </div>
          
          <!-- Twitter/X -->
          <div class="fg-social-icon" data-node-id="124:3632">
            <div class="fg-icon-twitter" data-node-id="124:3633">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-3.46 13.523l.916-1.115L6.644 4.236H4.853l7.234 9.06 2.632 3.317z"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <div style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:999;">
      <button style="width:3.5rem;height:3.5rem;border-radius:50%;background:#25D366;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="white">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path>
        </svg>
      </button>
    </div>

  </div><!-- end mainPage -->


  <script>
    // ============================
    // MORE INFO MODAL
    // ============================
    let modalImages = [
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FA_16_LA_MA_AXO_T1A_HR%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FB_Makai_Cards_Unit_Layout_111-T1A%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FC_Makai_Floorplans_First_Floor_111%2F1773673791087%2Ffull.webp',
      'https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2Funits%2FSYibpx5i469nMCLpZHP5%2FD_Makai_Floorplans_Second_Floor_111%2F1773673791087%2Ffull.webp'
    ];
    let currentModalImg = 0;


    // Helper function for number formatting (similar to PHP's number_format)
    function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ' ') {
      const n = !isFinite(+number) ? 0 : +number;
      const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
      const sep = typeof thousands_sep === 'undefined' ? ',' : thousands_sep;
      const dec = typeof dec_point === 'undefined' ? '.' : dec_point;
      
      const s = (prec ? n.toFixed(prec) : Math.round(n)).toString().split('.');
      if (sep) {
        let re = /(-?\d+)(\d{3})/;
        while (re.test(s[0])) {
          s[0] = s[0].replace(re, '$1' + sep + '$2');
        }
      }
      if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    }

    function openMoreInfo(unitId) {
      console.log('Opening unit info for ID:', unitId);
      
      // Update currentOpenUnit
      currentOpenUnit = unitId;
      
      // Fetch unit data from API
      fetch(`/api/units/${unitId}`)
        .then(response => {
          console.log('API response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(unit => {
          console.log('Unit data received:', unit);
          
          // Update modal content with real data
          const unitNum = document.getElementById('modalUnitNum');
          const unitPrice = document.getElementById('modalPrice');
          const unitDesc = document.getElementById('modalDesc');
          
          if (unitNum) unitNum.textContent = unit.custom_id || unit.name || 'Unit ' + unit.id;
          if (unitPrice) {
            unitPrice.dataset.usd = unit.price || 0;
            unitPrice.textContent = unit.price ? `$${number_format(unit.price, 0, ' ', ' ')}` : 'Price not available';
            // Default the modal currency toggle to whatever was picked on the
            // home (hamburger menu), persisted in localStorage.
            const selectedCur = localStorage.getItem('selectedCurrency') || 'USD';
            const modalToggle = document.querySelector('#moreInfoModal .mt-currency-toggle');
            if (modalToggle) {
              modalToggle.querySelectorAll('button').forEach(x => x.classList.remove('active'));
              const curBtn = modalToggle.querySelector(`button[data-cur="${selectedCur}"]`)
                          || modalToggle.querySelector('button[data-cur="USD"]');
              if (curBtn) curBtn.classList.add('active');
            }
            // Render the price in that currency (only if a real price exists).
            if (unit.price) updateModalCurrencyDisplay(selectedCur);
          }

          // Stat boxes — populate from DB; show — when missing
          const setStat = (id, value, suffix='') => {
              const el = document.getElementById(id);
              if (!el) return;
              if (value === null || value === undefined || value === '' || value === 0) {
                  el.textContent = (id === 'modalStatPool' || id === 'modalStatPark') ? '0' : '—';
              } else {
                  el.textContent = (typeof value === 'number' ? number_format(value, 0, ',', ',') : value) + suffix;
              }
          };
          setStat('modalStatBed',   unit.bedrooms);
          setStat('modalStatBath',  unit.bathrooms);
          setStat('modalStatPark',  unit.parking_bays);
          setStat('modalStatPool',  unit.pools);
          setStat('modalStatInt',   unit.internal_area);
          setStat('modalStatExt',   unit.external_area);
          setStat('modalStatTotal', unit.total_area);

          // Shortlisted count
          const sl = document.getElementById('modalShortlistedCount');
          if (sl) sl.textContent = unit.shortlisted_count || 0;

          // Reflect saved state on the modal "ADD TO LIST" toggle (read from the
          // matching card heart, which renders the user's current wishlist state).
          if (typeof setModalAddToListState === 'function') {
            const favBtn = document.querySelector(`[data-wishlist-toggle][data-unit-id="${unit.id}"]`);
            setModalAddToListState(favBtn ? favBtn.classList.contains('is-fav') : false, unit.shortlisted_count || 0);
          }

          // Financial rows — hide when no DB value
          const fmtMoney = v => '$' + number_format(v, 0, ',', ',');
          const toggleRow = (rowId, valId, value) => {
              const row = document.getElementById(rowId);
              const val = document.getElementById(valId);
              if (!row || !val) return;
              if (value && Number(value) > 0) {
                  val.textContent = fmtMoney(value);
                  row.style.display = '';
              } else {
                  row.style.display = 'none';
              }
          };
          // Investment view
          toggleRow('modalRowLevies', 'modalLevies', unit.levies);
          toggleRow('modalRowRental', 'modalRental', unit.est_rental);
          const feesSum = [unit.expense_1, unit.expense_2, unit.expense_3].reduce((a, b) => Number(a||0) + Number(b||0), 0);
          toggleRow('modalRowFees',   'modalFees',  feesSum);
          toggleRow('modalRowRates',  'modalRates', unit.rates);

          // Living extras
          const setTextRow = (rowId, valId, value) => {
              const row = document.getElementById(rowId);
              const val = document.getElementById(valId);
              if (!row || !val) return;
              if (value !== null && value !== undefined && value !== '') {
                  val.textContent = value;
                  row.style.display = '';
              } else { row.style.display = 'none'; }
          };
          // Amenities with icons
          const amenRow = document.getElementById('modalRowAmen');
          const amenVal = document.getElementById('modalAmenities');
          if (amenRow && amenVal) {
              const amenities = unit.amenities || [];
              if (Array.isArray(amenities) && amenities.length > 0) {
                  const amenityIcons = {
                      'pool': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M4 12v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6"/><path d="M6 12V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/></svg>',
                      'gym': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11"/><path d="M6 20v-8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><path d="M18 11V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v5"/></svg>',
                      'beach_club': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22h20"/><path d="M12 2v20"/><path d="M4 12c0-4 3-7 8-7s8 3 8 7"/></svg>',
                      'restaurant': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>',
                      'spa': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c-5.5 0-10 4.5-10 10s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>',
                      'tennis': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2v20"/><path d="M4.93 4.93l14.14 14.14"/><path d="M19.07 4.93L4.93 19.07"/></svg>',
                      'golf': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 13v8"/><path d="M9 6l3-4 3 4"/></svg>',
                      'security': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                      'parking': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M7 15V9"/><path d="M17 15V9"/></svg>',
                      'concierge': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                      'playground': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
                      'bbq': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M6 12v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4"/><path d="M8 12V8a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/></svg>',
                  };
                  const amenityLabels = {
                      'pool': 'Pool',
                      'gym': 'Gym',
                      'beach_club': 'Beach Club',
                      'restaurant': 'Restaurant',
                      'spa': 'Spa',
                      'tennis': 'Tennis Court',
                      'golf': 'Golf Course',
                      'security': '24/7 Security',
                      'parking': 'Parking',
                      'concierge': 'Concierge',
                      'playground': 'Playground',
                      'bbq': 'BBQ Area',
                  };
                  let html = '<div class="mt-amenities-grid">';
                  let renderedAmenities = 0;
                  amenities.forEach(key => {
                      const label = amenityLabels[key] || String(key).replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
                      const icon = amenityIcons[key] || '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
                      html += '<div class="mt-amenity-pill"><span class="mt-amenity-ico">' + icon + '</span><span>' + label + '</span></div>';
                      renderedAmenities++;
                  });
                  html += '</div>';
                  if (renderedAmenities > 0) {
                      amenVal.innerHTML = html;
                      amenRow.style.display = '';
                  } else {
                      amenRow.style.display = 'none';
                  }
              } else {
                  amenRow.style.display = 'none';
              }
          }
          // Show the "Amenities" block only when amenities exist
          const amenBlock = document.getElementById('modalAmenBlock');
          if (amenBlock) {
              const anyVisible = document.getElementById('modalRowAmen')?.style.display !== 'none';
              amenBlock.style.display = anyVisible ? '' : 'none';
          }

          // Investment longform (optional) + "A day here" (DB overrides the default copy)
          const showText = (id, text) => {
              const el = document.getElementById(id);
              if (!el) return;
              if (text && String(text).trim() !== '') { el.textContent = text; el.style.display = ''; }
              else { el.style.display = 'none'; }
          };
          showText('modalInvestmentText', unit.for_investment_text);
          if (unit.for_living_text && String(unit.for_living_text).trim() !== '') {
              const lt = document.getElementById('modalLivingText');
              if (lt) lt.textContent = unit.for_living_text;
          }

          // A · Precio inteligente — $/m² calculado
          const area = Number(unit.total_area || unit.internal_area || 0);
          const ppm  = (area > 0 && unit.price) ? Math.round(Number(unit.price) / area) : 0;
          const smartPpm = document.getElementById('modalSmartPpm');
          if (smartPpm) smartPpm.textContent = ppm > 0 ? '$' + number_format(ppm, 0, ',', ',') + '/m²' : '—';

          // B · Valorización — escenario (histórico real desde projected_value)
          const proj      = Number(unit.projected_value || 0);
          const projYear  = unit.projected_value_year || '';
          const roi       = unit.roi_percent ? Number(unit.roi_percent) : null;
          const projBox   = document.getElementById('modalProjected');
          if (projBox) {
              if (proj > 0) {
                  document.getElementById('modalProjectedNow').textContent    = '$' + number_format(unit.price || 0, 0, ',', ',');
                  document.getElementById('modalProjectedFuture').textContent = '$' + number_format(proj, 0, ',', ',');
                  document.getElementById('modalProjectedHint').textContent   = projYear ? ('est. ' + projYear) : (roi !== null ? roi + '% ROI' : '');
                  projBox.style.display = '';
              } else { projBox.style.display = 'none'; }
          }

          // C · Renta estimada — desde est_rental (mensual)
          const rent      = Number(unit.est_rental || 0);
          const rentBlock = document.getElementById('modalRentBlock');
          if (rentBlock) {
              if (rent > 0) {
                  document.getElementById('modalRentVal').textContent = '$' + number_format(rent, 0, ',', ',');
                  const yieldEl = document.getElementById('modalRentYield');
                  if (yieldEl && unit.price) {
                      const gy = (rent * 12 / Number(unit.price)) * 100;
                      yieldEl.textContent = '· ~' + gy.toFixed(1) + '% {{ __('gross') }}';
                  }
                  rentBlock.style.display = '';
              } else { rentBlock.style.display = 'none'; }
          }

          // D · CONFOTUR — ahorro de transferencia (3% del precio)
          const confSav = document.getElementById('modalConfoturSavings');
          if (confSav) confSav.textContent = unit.price ? '~$' + number_format(Number(unit.price) * 0.03, 0, ',', ',') : '~3%';

          // 1 · Tu espacio — interiores + terraza
          const spaceBlock = document.getElementById('modalSpaceBlock');
          const spaceVal   = document.getElementById('modalSpaceVal');
          if (spaceBlock && spaceVal) {
              const inA = Number(unit.internal_area || 0), exA = Number(unit.external_area || 0);
              if (inA > 0 || exA > 0) {
                  let s = '';
                  if (inA > 0) s += number_format(inA, 0, ',', ',') + ' m² {{ __('indoor') }}';
                  if (exA > 0) s += (s ? ' + ' : '') + number_format(exA, 0, ',', ',') + ' m² {{ __('terrace') }}';
                  spaceVal.textContent = s;
                  spaceBlock.style.display = '';
              } else { spaceBlock.style.display = 'none'; }
          }

          // Comparison text (investment)
          const cmpBox = document.getElementById('modalCompare');
          if (cmpBox) {
              if (unit.comparison_text && String(unit.comparison_text).trim() !== '') {
                  document.getElementById('modalCompareText').textContent = unit.comparison_text;
                  cmpBox.style.display = '';
              } else { cmpBox.style.display = 'none'; }
          }

          // Reflect availability — disable Reserve Online if the unit is on hold or sold
          const statusRaw = (unit.status || 'AVAILABLE').toString().toLowerCase();
          const isReserved = statusRaw === 'reserved';
          const isSold     = statusRaw === 'sold';
          const isPending  = statusRaw === 'pending';

          const badgeEl = document.getElementById('modalStatusBadge');
          const textEl  = document.getElementById('modalStatusText');
          const btn     = document.getElementById('modalReserveBtn');

          if (textEl) {
              textEl.textContent = isSold ? 'SOLD'
                                : isReserved ? 'RESERVED'
                                : isPending ? 'PENDING'
                                : 'AVAILABLE';
          }
          if (badgeEl) {
              // override pill colors when not available
              badgeEl.className = isReserved ? 'mt-badge-reserved'
                               : isSold ? 'mt-badge-reserved'
                               : isPending ? 'mt-badge-available'
                               : 'mt-badge-available';
              badgeEl.style.background = isSold || isReserved ? '#fde2e1'
                                       : isPending ? '#fef3c7'
                                       : '';
              badgeEl.style.color      = isSold || isReserved ? '#b91c1c'
                                       : isPending ? '#92400e'
                                       : '';
          }
          if (btn) {
              const blocked = isSold || isReserved || isPending;
              btn.disabled = blocked;
              btn.style.opacity = blocked ? '0.45' : '';
              btn.style.cursor  = blocked ? 'not-allowed' : '';
              if (isReserved) {
                  const until = unit.reserved_until ? new Date(unit.reserved_until) : null;
                  btn.textContent = until && until > new Date()
                      ? 'Reserved until ' + until.toLocaleDateString()
                      : 'Reserved';
              } else if (isSold) {
                  btn.textContent = 'Sold';
              } else if (isPending) {
                  btn.textContent = 'Pending';
              } else {
                  btn.textContent = 'Reserve Online';
              }
          }
          
          // Build description
          const outlookLabels = @json($outlookLabels);
          let description = '';
          if (unit.floor) description += unit.floor.charAt(0).toUpperCase() + unit.floor.slice(1);
          if (unit.bedrooms) description += ` | ${unit.bedrooms} Bed`;
          if (unit.bathrooms) description += ` | ${unit.bathrooms} Bath`;
          if (unit.direction) description += ` | ${unit.direction.toUpperCase()}`;
          if (unit.outlook) description += ` | ${outlookLabels[unit.outlook] || unit.outlook}`;
          if (unitDesc) unitDesc.textContent = description || 'Unit details';

          // Update images
          modalImages = unit.images && unit.images.length > 0 
            ? unit.images.map(img => img.path) 
            : ['https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2FctaCards%2FUIpwnmJz8oBQ2cKHMMA6%2Ftwo_bed%2F1773908343700%2Ffull.webp'];

          console.log('Modal images:', modalImages);

          // Reset image to first
          currentModalImg = 0;
          updateModalImage();

          // Re-apply current buyer mode to hide/show the right blocks
          if (typeof window.applyBuyerMode === 'function') {
            const activeBtn = document.querySelector('.mt-buyer-toggle button.active');
            window.applyBuyerMode(activeBtn?.dataset.buyer || 'investment');
          }

          // Show modal
          const modal = document.getElementById('moreInfoModal');
          if (modal) {
            modal.style.display = 'flex';
            // Replay open animation
            modal.classList.remove('is-opening');
            void modal.offsetWidth;
            modal.classList.add('is-opening');
            document.body.style.overflow = 'hidden';
            console.log('Modal should be visible now');
          } else {
            console.error('Modal element not found!');
          }

          // Track this view (fire-and-forget; dedupe handled server-side)
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            fetch(`/api/units/${unit.id}/view`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              credentials: 'same-origin',
            }).catch(() => {});
          } catch(e) {}
        })
        .catch(error => {
          console.error('Error fetching unit data:', error);
          console.log('Using fallback data');
          
          // Fallback to default data if API fails
          const data = unitModalData['111'];
          document.getElementById('modalUnitNum').textContent = data.unit;
          document.getElementById('modalPrice').textContent = data.price;
          document.getElementById('modalDesc').textContent = data.floor + ' | ' + data.desc;
          
          modalImages = ['https://storage.googleapis.com/makai-savyo.firebasestorage.app/assets%2Fimages%2FctaCards%2FUIpwnmJz8oBQ2cKHMMA6%2Ftwo_bed%2F1773908343700%2Ffull.webp'];
          currentModalImg = 0;
          updateModalImage();
          
          const modal = document.getElementById('moreInfoModal');
          if (modal) {
            modal.style.display = 'flex';
            // Replay open animation
            modal.classList.remove('is-opening');
            void modal.offsetWidth;
            modal.classList.add('is-opening');
            document.body.style.overflow = 'hidden';
          }
        });
    }

    function closeMoreInfo() {
      document.getElementById('moreInfoModal').style.display = 'none';
      document.body.style.overflow = '';
    }

    function updateModalImage() {
      const img = document.getElementById('modalMainImg');
      if (img) {
        img.src = modalImages[currentModalImg];
        const counter = document.getElementById('modalImgCounter');
        if (counter) counter.textContent = (currentModalImg + 1) + ' / ' + modalImages.length;
      }
      // Sync thumbs (Figma modal-tipologia)
      const thumbsWrap = document.getElementById('mtThumbs');
      if (thumbsWrap) {
        const thumbs = thumbsWrap.querySelectorAll('.mt-thumb');
        thumbs.forEach((t, i) => {
          t.classList.toggle('active', i === currentModalImg);
          const tImg = t.querySelector('img');
          if (tImg && modalImages[i]) tImg.src = modalImages[i];
          t.style.display = modalImages[i] ? '' : 'none';
        });
      }
    }

    function prevModalImg() {
      currentModalImg = (currentModalImg - 1 + modalImages.length) % modalImages.length;
      updateModalImage();
    }

    function nextModalImg() {
      currentModalImg = (currentModalImg + 1) % modalImages.length;
      updateModalImage();
    }

    // Modal Tipologia — thumb clicks + toggles
    document.addEventListener('DOMContentLoaded', function () {
      const thumbsWrap = document.getElementById('mtThumbs');
      if (thumbsWrap) {
        thumbsWrap.addEventListener('click', function (e) {
          const btn = e.target.closest('.mt-thumb');
          if (!btn) return;
          const idx = parseInt(btn.dataset.idx, 10);
          if (Number.isFinite(idx) && modalImages[idx]) {
            currentModalImg = idx;
            updateModalImage();
          }
        });
      }
      // Currency toggle (modal only)
      document.querySelectorAll('.mt-currency-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('button').forEach(x => x.classList.remove('active'));
          this.classList.add('active');
          const cur = this.dataset.cur || 'USD';
          updateModalCurrencyDisplay(cur);
        });
      });
      // Buyer toggle — toggles a `living-mode` class on .mt-shell; CSS does the rest
      function applyBuyerMode(mode) {
        const shell = document.querySelector('#moreInfoModal .mt-shell');
        if (!shell) return;
        shell.classList.toggle('living-mode', mode === 'living');
      }
      window.applyBuyerMode = applyBuyerMode;
      const activeBuyerBtn = document.querySelector('.mt-buyer-toggle button.active');
      if (activeBuyerBtn) applyBuyerMode(activeBuyerBtn.dataset.buyer || 'investment');

      document.querySelectorAll('.mt-buyer-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
          this.parentElement.querySelectorAll('button').forEach(x => x.classList.remove('active'));
          this.classList.add('active');
          applyBuyerMode(this.dataset.buyer || 'investment');
        });
      });
      // Plan floor chips → filter markers + update floor label
      (function () {
        const chips     = document.querySelectorAll('.fg-chip-floor');
        const canvas    = document.getElementById('fgPlanCanvas');
        const labelEl   = document.getElementById('fgPlanPisoLabel');
        const countEl   = document.getElementById('fgPlanPisoCount');
        if (!chips.length || !canvas) return;

        function activate(floor) {
          chips.forEach(x => {
            const on = x.dataset.floor === floor;
            x.classList.toggle('is-active', on);
            x.setAttribute('aria-selected', on ? 'true' : 'false');
          });

          // Cross-fade the whole canvas while markers swap.
          canvas.classList.remove('is-switching');
          void canvas.offsetWidth;
          canvas.classList.add('is-switching');
          setTimeout(() => canvas.classList.remove('is-switching'), 380);

          let available = 0;
          let i = 0;
          canvas.querySelectorAll('.fg-plan-marker').forEach(m => {
            const match = m.dataset.floor === floor;
            if (match) {
              m.style.display = '';
              m.classList.remove('is-hidden');
              // Stagger the pop-in slightly per marker.
              m.classList.remove('is-popping');
              const idx = i++;
              setTimeout(() => {
                void m.offsetWidth;
                m.classList.add('is-popping');
                setTimeout(() => m.classList.remove('is-popping'), 460);
              }, idx * 45);
              if (!m.classList.contains('is-sold') && !m.classList.contains('is-reserved')) {
                available++;
              }
            } else {
              m.classList.add('is-hidden');
              // Keep in DOM (no display:none) so the next show animates clean.
              setTimeout(() => {
                if (m.classList.contains('is-hidden')) m.style.display = 'none';
              }, 220);
            }
          });

          // Animate the PISO label/count change.
          const piso = labelEl ? labelEl.closest('.fg-plan-piso') : null;
          if (piso) {
            piso.classList.remove('is-changing');
            void piso.offsetWidth;
            piso.classList.add('is-changing');
            setTimeout(() => piso.classList.remove('is-changing'), 320);
          }
          if (labelEl) {
            labelEl.textContent = (floor === 'Ground')
              ? 'PLANTA BAJA'
              : 'PISO ' + floor.toUpperCase();
          }
          if (countEl) {
            countEl.textContent = available + ' UNIDADES DISPONIBLES';
          }
        }

        chips.forEach(b => {
          b.addEventListener('click', function () {
            activate(this.dataset.floor);
          });
        });
      })();
    });

    // ============================
    // RESERVE PAGE
    // ============================
    let reserveTimerInterval = null;
    let reserveTimeLeft = 600; // 10 minutes

    function openReservePage(unitId = null) {
      closeMoreInfo();

      const finalUnitId = unitId || currentOpenUnit || '1';
      currentOpenUnit = finalUnitId;

      // Si el usuario no está autenticado, redirigir al login con intent de reservar la unidad
      @guest
        window.location.href = '/login?intent=reserve&unit=' + encodeURIComponent(finalUnitId);
        return;
      @endguest

      // Usuario autenticado → crear la reserva directamente con datos del account y saltar al /form
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      fetch('/reservations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ unit_id: finalUnitId, _token: csrfToken })
      })
      .then(r => r.json().then(d => ({ ok: r.ok, body: d })))
      .then(({ ok, body }) => {
        if (ok && body.success) {
          window.location.href = body.redirect_to || '/form';
        } else {
          alert(body.message || 'No se pudo crear la reserva. Intenta de nuevo.');
        }
      })
      .catch(err => {
        console.error('Error creando reserva:', err);
        alert('Error de red. Intenta de nuevo.');
      });
    }

    function closeReservePage() {
      document.getElementById('reservePage').style.display = 'none';
      document.body.style.overflow = '';
      clearInterval(reserveTimerInterval);
      reserveTimeLeft = 600;
    }

    function startReserveTimer() {
      clearInterval(reserveTimerInterval);
      reserveTimerInterval = setInterval(() => {
        reserveTimeLeft--;
        if (reserveTimeLeft <= 0) {
          clearInterval(reserveTimerInterval);
          reserveTimeLeft = 0;
        }
        updateTimerDisplays();
      }, 1000);
      updateTimerDisplays();
    }

    function updateTimerDisplays() {
      const mins = Math.floor(reserveTimeLeft / 60).toString().padStart(2, '0');
      const secs = (reserveTimeLeft % 60).toString().padStart(2, '0');
      const timeStr = mins + ':' + secs;
      ['reserveTimer1', 'reserveTimer2', 'reserveTimer3'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = timeStr;
      });
      // Update circle timer
      const path = document.getElementById('timerPath');
      if (path) {
        const fraction = reserveTimeLeft / 600;
        const dashArray = (fraction * 283).toFixed(0) + ' 283';
        path.setAttribute('stroke-dasharray', dashArray);
        // Color changes
        const color = reserveTimeLeft > 300 ? 'rgb(65,184,131)' : reserveTimeLeft > 120 ? 'orange' : 'red';
        path.style.stroke = color;
      }
      // Update min/sec displays
      const minEl = document.getElementById('timerMin3');
      const secEl = document.getElementById('timerSec3');
      if (minEl) minEl.textContent = mins;
      if (secEl) secEl.textContent = secs;
    }

    function goToReserveStep2(e) {
      if (e) e.preventDefault();
      
      const form = document.getElementById('reservationForm');
      const formData = new FormData(form);
      
      // Check if unit_id is set
      const unitId = formData.get('unit_id');
      if (!unitId) {
        alert('Unit ID is missing. Please try again.');
        console.error('Unit ID not found in form');
        return;
      }
      
      // Check if terms checkbox is checked
      const termsCheckbox = form.querySelector('input[type="checkbox"]');
      if (!termsCheckbox.checked) {
        alert('Please accept the Terms and Conditions to continue.');
        return;
      }
      
      // Show loading state
      const submitButton = form.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.textContent = 'Processing...';
      submitButton.disabled = true;
      
      // Send data to API
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      const requestData = {
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        country: formData.get('country'),
        unit_id: formData.get('unit_id'),
        _token: csrfToken
      };
      
      console.log('Sending reservation data:', requestData);
      console.log('CSRF Token:', csrfToken);
      
      fetch('/reservations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
      })
      .then(response => {
        if (!response.ok) {
          if (response.status === 422) {
            return response.json().then(data => {
              throw new Error('Validation failed: ' + JSON.stringify(data.errors || data.message));
            });
          }
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Redirect to /form page
          window.location.href = data.redirect_to || '/form';
        } else {
          throw new Error(data.message || 'Error creating reservation');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('There was an error processing your reservation. Please try again.');
        
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
      });
    }

    function backToStep1() {
      document.getElementById('reserveStep1').style.display = 'block';
      document.getElementById('reserveStep2').style.display = 'none';
      document.getElementById('reserveStep3').style.display = 'none';
    }

    function goToReserveStep3() {
      document.getElementById('reserveStep1').style.display = 'none';
      document.getElementById('reserveStep2').style.display = 'none';
      document.getElementById('reserveStep3').style.display = 'block';
      document.getElementById('reservePage').scrollTo(0, 0);
    }

    // Active Users System
    let activeUsersInterval;
    let heartbeatInterval;
    let dynamicUsersInterval;
    const MIN_DYNAMIC_USERS = 22;
    let currentDynamicUsers = 0;

    // Function to calculate dynamic users based on timezone
    function calculateDynamicUsers() {
      const now = new Date();
      const hour = now.getHours();

      // Base users based on time of day (peak hours: 9-12, 14-18, 19-22)
      let baseUsers;
      if (hour >= 9 && hour < 12) {
        baseUsers = Math.floor(Math.random() * 12) + 42; // 42-53 users
      } else if (hour >= 14 && hour < 18) {
        baseUsers = Math.floor(Math.random() * 15) + 50; // 50-64 users
      } else if (hour >= 19 && hour < 22) {
        baseUsers = Math.floor(Math.random() * 10) + 36; // 36-45 users
      } else if (hour >= 6 && hour < 9) {
        baseUsers = Math.floor(Math.random() * 8) + 28; // 28-35 users
      } else if (hour >= 22 || hour < 6) {
        baseUsers = Math.floor(Math.random() * 6) + 22; // 22-27 users
      } else {
        baseUsers = Math.floor(Math.random() * 10) + 32; // 32-41 users
      }

      return baseUsers;
    }

    // Function to update dynamic users with fluctuation
    function updateDynamicUsers() {
      const change = Math.random() > 0.5 ? 1 : -1;
      const shouldChange = Math.random() > 0.7; // 30% chance to change

      if (shouldChange) {
        currentDynamicUsers = Math.max(MIN_DYNAMIC_USERS, currentDynamicUsers + change);

        // Occasionally reset to base calculation
        if (Math.random() > 0.95) {
          currentDynamicUsers = calculateDynamicUsers();
        }
      }

      const elements = document.querySelectorAll('[data-active-users]');
      elements.forEach(element => {
        element.textContent = currentDynamicUsers;
      });
    }

    // Function to update active users count
    function updateActiveUsersCount() {
      fetch('/api/active-users')
        .then(response => response.json())
        .then(data => {
          const elements = document.querySelectorAll('[data-active-users]');
          elements.forEach(element => {
            // If real count is below the floor, use dynamic fake count
            if (data.count < MIN_DYNAMIC_USERS) {
              if (currentDynamicUsers < MIN_DYNAMIC_USERS) {
                currentDynamicUsers = calculateDynamicUsers();
              }
              element.textContent = currentDynamicUsers;
            } else {
              element.textContent = data.count;
              currentDynamicUsers = data.count;
            }
          });
        })
        .catch(error => {
          console.error('Error fetching active users:', error);
          // Fallback to dynamic users on error
          if (currentDynamicUsers < MIN_DYNAMIC_USERS) {
            currentDynamicUsers = calculateDynamicUsers();
          }
          const elements = document.querySelectorAll('[data-active-users]');
          elements.forEach(element => {
            element.textContent = currentDynamicUsers;
          });
        });
    }

    // Function to send heartbeat (update last seen)
    function sendHeartbeat() {
      fetch('/api/update-last-seen', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.count !== undefined) {
            const elements = document.querySelectorAll('[data-active-users]');
            elements.forEach(element => {
              // If real count is below the floor, use dynamic fake count
              if (data.count < MIN_DYNAMIC_USERS) {
                if (currentDynamicUsers < MIN_DYNAMIC_USERS) {
                  currentDynamicUsers = calculateDynamicUsers();
                }
                element.textContent = currentDynamicUsers;
              } else {
                element.textContent = data.count;
                currentDynamicUsers = data.count;
              }
            });
          }
        })
        .catch(error => console.error('Error sending heartbeat:', error));
    }

    // Initialize active users tracking
    function initActiveUsersTracking() {
      // Initial count
      updateActiveUsersCount();

      // Send heartbeat every 2 minutes
      heartbeatInterval = setInterval(sendHeartbeat, 120000);

      // Update count every 30 seconds
      activeUsersInterval = setInterval(updateActiveUsersCount, 30000);

      // Update dynamic users every 5-10 seconds (fluctuation)
      dynamicUsersInterval = setInterval(updateDynamicUsers, 7000);

      // Send initial heartbeat
      sendHeartbeat();
    }

    // Start tracking when page loads
    document.addEventListener('DOMContentLoaded', initActiveUsersTracking);

    // Clean up intervals when page unloads
    window.addEventListener('beforeunload', () => {
      if (activeUsersInterval) clearInterval(activeUsersInterval);
      if (heartbeatInterval) clearInterval(heartbeatInterval);
      if (dynamicUsersInterval) clearInterval(dynamicUsersInterval);
    });

    // ============================
    // PROJECTS DROPDOWN
    // ============================
    function toggleProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('projects-open');
      if (isOpen) {
        closeProjects();
      } else {
        openProjects();
      }
    }

    function openProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      const trigger = document.getElementById('projectsToggle');
      if (dropdown) dropdown.classList.add('projects-open');
      if (trigger) {
        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
      }
      // Close when clicking outside
      setTimeout(() => {
        document.addEventListener('click', closeProjectsOnOutsideClick);
        document.addEventListener('keydown', closeProjectsOnEscape);
      }, 10);
    }

    function closeProjects() {
      const dropdown = document.getElementById('projectsDropdown');
      const trigger = document.getElementById('projectsToggle');
      if (dropdown) dropdown.classList.remove('projects-open');
      if (trigger) {
        trigger.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
      }
      document.removeEventListener('click', closeProjectsOnOutsideClick);
      document.removeEventListener('keydown', closeProjectsOnEscape);
    }

    function closeProjectsOnOutsideClick(e) {
      const dropdown = document.getElementById('projectsDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleProjects"]')) {
        closeProjects();
      }
    }

    function closeProjectsOnEscape(e) {
      if (e.key === 'Escape') closeProjects();
    }

    function selectProject(projectName) {
      const key = (projectName || '').toLowerCase();
      const cards = document.querySelectorAll('#projectsDropdown .project-card');
      cards.forEach(card => {
        if (card.getAttribute('data-project') === key) {
          card.classList.add('active');
        } else {
          card.classList.remove('active');
        }
      });
      // Reflect project name inside the empty-state card
      document.querySelectorAll('[data-empty-project-name]').forEach(el => {
        el.textContent = projectName || 'este proyecto';
      });
      switchHeroProject(key);
      closeProjects();
    }

    const PROJECT_BRAND_RGB = {
      makai:  [92, 124, 104],
      naviva: [197, 191, 86],
      liv:    [85, 127, 128],
    };

    function setBrandFor(project) {
      const rgb = PROJECT_BRAND_RGB[project] || PROJECT_BRAND_RGB.makai;
      const root = document.documentElement;
      const tuple = rgb.join(',');
      root.style.setProperty('--brand',         'rgb(' + tuple + ')');
      root.style.setProperty('--brand-soft',    'rgba(' + tuple + ',0.10)');
      root.style.setProperty('--brand-soft-2',  'rgba(' + tuple + ',0.08)');
      root.style.setProperty('--brand-soft-3',  'rgba(' + tuple + ',0.18)');
      root.style.setProperty('--brand-ring',    'rgba(' + tuple + ',0.45)');
      root.style.setProperty('--brand-overlay', 'rgba(' + tuple + ',0.78)');
      root.style.setProperty('--brand-toggle-shadow',
        '0 55px 16px 0 rgba(' + tuple + ',0.01),' +
        '0 36px 14px 0 rgba(' + tuple + ',0.05),' +
        '0 20px 12px 0 rgba(' + tuple + ',0.16),' +
        '0 9px 9px 0 rgba('  + tuple + ',0.27),' +
        '0 2px 5px 0 rgba('  + tuple + ',0.31)');

      // Mark active project on body so CSS can show/hide empty-state for projects without units
      document.body.setAttribute('data-active-project', project);
      
      // Update logo images with smooth animation and spacing adjustment
      const logoPath = '/images/' + project + '-logo.png';
      const logoImages = document.querySelectorAll('.logo-img');
      const logoContainers = document.querySelectorAll('.logo-container');
      const buttonTriggers = document.querySelectorAll('.logo-trigger');
      
      // Trigger button animation
      buttonTriggers.forEach(button => {
        button.classList.add('switching');
        setTimeout(() => {
          button.classList.remove('switching');
        }, 800);
      });
      
      logoImages.forEach((img, index) => {
        // Fade out current logo
        img.classList.add('switching');
        
        // Change image and fade in after a short delay
        setTimeout(() => {
          img.src = logoPath;
          img.classList.remove('switching');
          
          // Adjust spacing after image loads
          img.onload = () => {
            const logoWidth = img.naturalWidth;
            const logoHeight = img.naturalHeight;
            const maxHeight = 32; // CSS height for logo
            const maxWidth = 160;
            
            // Calculate scaled dimensions maintaining aspect ratio
            const scaleFactor = Math.min(maxHeight / logoHeight, maxWidth / logoWidth, 1);
            const actualWidth = logoWidth * scaleFactor;
            const actualHeight = logoHeight * scaleFactor;
            
            // Adjust container width and spacing based on actual logo width
            if (logoContainers[index]) {
              // Set container to exact width of logo + padding
              const containerPadding = 8; // padding from link element
              const containerWidth = actualWidth + (containerPadding * 2);
              
              logoContainers[index].style.width = containerWidth + 'px';
              
              // Adjust gap between logo and button based on width
              const baseGap = 8;
              const widthFactor = actualWidth / 100; // normalize around 100px
              const adjustedGap = baseGap + (widthFactor * 2);
              
              logoContainers[index].parentElement.style.gap = Math.max(8, Math.min(16, adjustedGap)) + 'px';
            }
          };
        }, 200);
      });
    }

    function switchHeroProject(project) {
      const hero = document.getElementById('hero');
      if (!hero || !project) return;
      const current = hero.dataset.active;
      if (current === project) return;

      const prevText = hero.querySelector('.fg-hero-text[data-project="' + current + '"]');
      const prevBuilding = hero.querySelector('.fg-hero-building[data-project="' + current + '"]');

      // Trigger color swap immediately — CSS transition (0.9s) makes it gradual
      // so the palette settles right around when the new project finishes entering.
      setBrandFor(project);

      [prevText, prevBuilding].forEach(el => {
        if (!el) return;
        el.classList.remove('is-exiting');
        void el.offsetWidth; // force reflow to restart animation
        el.classList.add('is-exiting');
      });

      // After exit completes, swap active project (entrance triggers via CSS)
      setTimeout(() => {
        [prevText, prevBuilding].forEach(el => el && el.classList.remove('is-exiting'));
        hero.dataset.active = project;
      }, 700);
    }

    // Apply initial brand on load (matches the default data-active on the hero)
    document.addEventListener('DOMContentLoaded', () => {
      const hero = document.getElementById('hero');
      if (hero && hero.dataset.active) setBrandFor(hero.dataset.active);
    });

    // ============================
    // MENU DROPDOWN
    // ============================
    function toggleMenu() {
      // Toggle profile dropdown instead
      toggleProfileMenu();
      
      // Also toggle regular menu if needed
      const dropdown = document.getElementById('menuDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('menu-open');
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    function openMenu() {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown) dropdown.classList.add('menu-open');
      // Close when clicking outside
      setTimeout(() => {
        document.addEventListener('click', closeMenuOnOutsideClick);
      }, 10);
    }

    function closeMenu() {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown) dropdown.classList.remove('menu-open');
      document.removeEventListener('click', closeMenuOnOutsideClick);
    }

    function closeMenuOnOutsideClick(e) {
      const dropdown = document.getElementById('menuDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleMenu"]')) {
        closeMenu();
      }
    }

    // ============================
    // PROFILE DROPDOWN
    // ============================
    function toggleProfileMenu() {
      const dropdown = document.getElementById('profileDropdown');
      if (!dropdown) return;
      const isOpen = dropdown.classList.contains('menu-open');
      
      // Close other dropdowns first
      closeMenu();
      closeProjects();
      
      if (isOpen) {
        dropdown.classList.remove('menu-open');
        document.removeEventListener('click', closeProfileMenuOnOutsideClick);
      } else {
        dropdown.classList.add('menu-open');
        setTimeout(() => {
          document.addEventListener('click', closeProfileMenuOnOutsideClick);
        }, 10);
      }
    }

    function closeProfileMenu() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) dropdown.classList.remove('menu-open');
      document.removeEventListener('click', closeProfileMenuOnOutsideClick);
    }

    function closeProfileMenuOnOutsideClick(e) {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown && !dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleProfileMenu"]')) {
        closeProfileMenu();
      }
    }

    // Currency selection
    function setCurrency(currency) {
      // Remove active state from all currency buttons
      const currencyButtons = ['currency-usd', 'currency-eur', 'currency-rd'];
      currencyButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
          btn.style.background = '#fff';
          btn.style.color = '#374151';
          btn.style.borderColor = '#d1d5db';
        }
      });
      
      // Set active state for selected currency
      const activeBtn = document.getElementById('currency-' + currency.toLowerCase());
      if (activeBtn) {
        activeBtn.style.background = '#111827';
        activeBtn.style.color = '#fff';
        activeBtn.style.borderColor = '#111827';
      }
      
      // Update the select dropdown value
      const currencySelect = document.getElementById('currencySelect');
      if (currencySelect) {
        currencySelect.value = currency;
      }
      
      // Store currency preference
      localStorage.setItem('selectedCurrency', currency);
      
      // Update currency display throughout the page
      updateCurrencyDisplay(currency);
    }

    // Language selection — actualiza la UI del toggle y persiste el idioma en el backend.
    // El backend almacena el locale en sesión + cookie y se recarga la página para que
    // todos los `__('...')` se rerendericen en el nuevo idioma.
    function setLanguage(lang, opts = {}) {
      const langButtons = ['lang-es', 'lang-en'];
      const indicator = document.getElementById('lang-indicator');

      langButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
          btn.style.opacity = '0.52';
          const textSpan = btn.querySelector('span');
          const svg = btn.querySelector('svg');
          if (textSpan) textSpan.style.color = '#717784';
          if (svg) svg.style.color = '#717784';
        }
      });

      const activeBtn = document.getElementById('lang-' + lang);
      if (activeBtn) {
        activeBtn.style.opacity = '1';
        const textSpan = activeBtn.querySelector('span');
        const svg = activeBtn.querySelector('svg');
        if (textSpan) textSpan.style.color = '#525866';
        if (svg) svg.style.color = '#525866';
        if (indicator) indicator.style.left = (lang === 'es') ? '4px' : '156px';
      }

      localStorage.setItem('selectedLanguage', lang);

      const serverLang = (document.documentElement.getAttribute('lang') || 'es').toLowerCase().split('-')[0];
      if (opts.skipServer || serverLang === lang) return;

      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      fetch('{{ route("locale.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ locale: lang }),
      }).then(r => r.ok ? r.json() : Promise.reject(r)).then(() => {
        window.location.reload();
      }).catch(() => {
        // Si falla la persistencia, al menos la UI local quedó actualizada.
      });
    }

    // Exchange rates (relative to 1 USD)
    const EXCHANGE_RATES = {
      USD: 1,
      EUR: 0.92,
      CAD: 1.36,
      MXN: 17.45,
      DOP: 58.5,
    };

    const CURRENCY_SYMBOLS = {
      USD: '$',
      EUR: '€',
      CAD: 'C$',
      MXN: 'MX$',
      DOP: 'RD$',
    };

    // Update currency display — converts all prices marked with data-usd
    function updateCurrencyDisplay(currency) {
      const rate = EXCHANGE_RATES[currency] || 1;
      const symbol = CURRENCY_SYMBOLS[currency] || '$';

      // Convert all .price elements with data-usd (cards + list)
      document.querySelectorAll('.price[data-usd]').forEach(el => {
        const usd = parseFloat(el.dataset.usd);
        if (!isNaN(usd)) {
          const converted = Math.round(usd * rate);
          el.textContent = symbol + number_format(converted, 0, ' ', ' ');
        }
      });

      // Convert all .sqft and .price-meta with data-usd-sqft
      document.querySelectorAll('[data-usd-sqft]').forEach(el => {
        const usd = parseFloat(el.dataset.usdSqft || el.dataset.usd_sqft);
        if (!isNaN(usd)) {
          const converted = Math.round(usd * rate);
          el.textContent = symbol + number_format(converted, 0) + '/m²';
        }
      });

      // Convert modal price
      const modalPrice = document.getElementById('modalPrice');
      if (modalPrice && modalPrice.dataset.usd) {
        const usd = parseFloat(modalPrice.dataset.usd);
        if (!isNaN(usd)) {
          const converted = Math.round(usd * rate);
          modalPrice.textContent = symbol + number_format(converted, 0, ' ', ' ');
        }
      }
    }

    // Update modal currency display only — converts only modal prices
    function updateModalCurrencyDisplay(currency) {
      const rate = EXCHANGE_RATES[currency] || 1;
      const symbol = CURRENCY_SYMBOLS[currency] || '$';

      // Convert modal price only
      const modalPrice = document.getElementById('modalPrice');
      if (modalPrice && modalPrice.dataset.usd) {
        const usd = parseFloat(modalPrice.dataset.usd);
        if (!isNaN(usd)) {
          const converted = Math.round(usd * rate);
          modalPrice.textContent = symbol + number_format(converted, 0, ' ', ' ');
        }
      }

      // Convert modal sqft price if exists
      const modalSqft = document.getElementById('modalSqft');
      if (modalSqft && modalSqft.dataset.usdSqft) {
        const usd = parseFloat(modalSqft.dataset.usdSqft);
        if (!isNaN(usd)) {
          const converted = Math.round(usd * rate);
          modalSqft.textContent = symbol + number_format(converted, 0) + '/m²';
        }
      }
    }

    // Update language display
    function updateLanguageDisplay(lang) {
      // This function can be expanded to update all text content
      console.log('Language changed to:', lang);
      // You can add logic here to update text throughout the page
    }

    // Initialize currency and language on page load.
    // Para el idioma usamos lo que el servidor ya resolvió (html lang) y NO escribimos
    // localStorage hasta que el usuario haga clic — así evitamos un reload infinito.
    document.addEventListener('DOMContentLoaded', function() {
      const savedCurrency = localStorage.getItem('selectedCurrency') || 'USD';
      const serverLang = (document.documentElement.getAttribute('lang') || 'es').toLowerCase().split('-')[0];

      setCurrency(savedCurrency);
      // skipServer: solo refleja el idioma actual del servidor en la UI del toggle.
      setLanguage(serverLang, { skipServer: true });
    });

    // Buyer profile toggle (Para Vivir / Para Invertir)
    function setBuyerProfile(profile) {
      const btnVivir = document.getElementById('btnProfileVivir');
      const btnInvertir = document.getElementById('btnProfileInvertir');
      const financial = document.getElementById('financialBlock');
      const lifestyle = document.getElementById('lifestyleBlock');
      if (!btnVivir || !btnInvertir) return;
      const activeStyle = 'background:rgb(102,123,106);color:white;';
      const inactiveStyle = 'background:transparent;color:rgb(98,84,65);';
      const baseStyle = 'border:none;padding:3px 10px;border-radius:9999px;cursor:pointer;font-weight:600;';
      if (profile === 'vivir') {
        btnVivir.style.cssText = baseStyle + activeStyle;
        btnInvertir.style.cssText = baseStyle + inactiveStyle;
        if (financial) financial.style.display = 'none';
        if (lifestyle) lifestyle.style.display = 'block';
      } else {
        btnInvertir.style.cssText = baseStyle + activeStyle;
        btnVivir.style.cssText = baseStyle + inactiveStyle;
        if (financial) financial.style.display = 'flex';
        if (lifestyle) lifestyle.style.display = 'none';
      }
    }

    // (shareMatches is defined earlier near the share modal — opens the share
    // dialog with the current filtered/grid URL pre-filled.)

    // Show toast notification
    function showToast(message) {
      // Remove existing toast if any
      const existingToast = document.querySelector('.fg-toast');
      if (existingToast) existingToast.remove();
      
      // Create toast element
      const toast = document.createElement('div');
      toast.className = 'fg-toast';
      toast.textContent = message;
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--brand, #5c7c68);
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideUp 0.3s ease;
      `;
      
      // Add animation keyframes if not exists
      if (!document.querySelector('#toast-animation')) {
        const style = document.createElement('style');
        style.id = 'toast-animation';
        style.textContent = `
          @keyframes slideUp {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
          }
        `;
        document.head.appendChild(style);
      }
      
      document.body.appendChild(toast);
      
      // Remove after 3 seconds
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function openVideoCall() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const subject = encodeURIComponent('Agendar videollamada - Unidad ' + unitNum + ' Makai Residences');
      window.location.href = 'mailto:support+makai_residences@launchbase.co.za?subject=' + subject;
    }

    function openAdvisorVideoCall(unitId) {
      @auth
      @else
        window.location.href = '{{ route('login') }}';
        return;
      @endauth

      const modal = document.getElementById('advisorModal');
      document.getElementById('advisorModalUnitId').value = unitId || '';

      // Pre-seleccionar la unidad en el dropdown si vino unitId
      const sel = document.getElementById('advisorUnitSelect');
      if (sel && unitId) {
        const opt = Array.from(sel.options).find(o => o.value === String(unitId));
        if (opt) sel.value = opt.value;
      }

      // Fecha default: mañana
      const dateInput = document.getElementById('advisorDate');
      if (dateInput && !dateInput.value) {
        const t = new Date(); t.setDate(t.getDate() + 1);
        dateInput.min = new Date().toISOString().slice(0, 10);
        dateInput.value = t.toISOString().slice(0, 10);
      }

      // Reset slots y note
      document.querySelectorAll('#advisorModal .vc-slot').forEach(s => {
        s.classList.remove('active', 'disabled');
        s.disabled = false;
        s.setAttribute('aria-checked', 'false');
      });
      document.getElementById('advisorPreferredTime').value = '';
      const note = document.getElementById('advisorNote');
      if (note) { note.value = ''; document.getElementById('advisorNoteCount').textContent = '0/200'; }
      hideAdvisorAlert();

      // Restablecer estado (volver a la vista de formulario)
      document.getElementById('advisorForm').style.display = '';
      document.getElementById('advisorFooter').style.display = '';
      document.getElementById('advisorSuccess').style.display = 'none';
      const submitBtn = document.getElementById('advisorSubmitBtn');
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Confirmar solicitud'; }

      modal.classList.add('open');
      document.body.style.overflow = 'hidden';

      // Cargar disponibilidad para la fecha + unidad seleccionadas
      fetchAdvisorAvailability();
    }
    function closeAdvisorVideoCall() {
      document.getElementById('advisorModal').classList.remove('open');
      document.body.style.overflow = '';
    }
    function selectAdvisorSlot(btn) {
      if (btn.disabled || btn.classList.contains('disabled')) return;
      document.querySelectorAll('#advisorModal .vc-slot').forEach(s => {
        s.classList.remove('active');
        s.setAttribute('aria-checked', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-checked', 'true');
      document.getElementById('advisorPreferredTime').value = btn.dataset.slot || btn.textContent.trim();
    }
    async function fetchAdvisorAvailability() {
      const date = document.getElementById('advisorDate').value;
      const unitId = document.getElementById('advisorModalUnitId').value
        || document.getElementById('advisorUnitSelect').value;
      if (!date) return;

      const slots = document.querySelectorAll('#advisorModal .vc-slot');
      slots.forEach(s => { s.classList.remove('disabled'); s.disabled = false; });

      try {
        const params = new URLSearchParams({ date });
        if (unitId) params.append('unit_id', unitId);
        const res = await fetch('/api/meetings/availability?' + params.toString(), {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        const taken = Array.isArray(data.taken) ? data.taken : [];
        slots.forEach(s => {
          const slotLabel = s.dataset.slot || s.textContent.trim();
          if (taken.includes(slotLabel)) {
            s.classList.add('disabled');
            s.disabled = true;
            if (s.classList.contains('active')) {
              s.classList.remove('active');
              s.setAttribute('aria-checked', 'false');
              document.getElementById('advisorPreferredTime').value = '';
            }
          }
        });
      } catch (e) { /* silencioso */ }
    }
    function goToCalendarMeet() {
      let url = '/dashboard/calendario';
      if (window._lastMeetingId) {
        url += '?meeting=' + encodeURIComponent(window._lastMeetingId);
      }
      window.location.href = url;
    }
    function copyAdvisorMeetLink() {
      const link = document.getElementById('advisorMeetLink').href;
      if (!link) return;
      navigator.clipboard?.writeText(link).then(() => {
        const btn = event?.target;
        if (btn) { const t = btn.textContent; btn.textContent = '¡Copiado!'; setTimeout(() => btn.textContent = t, 1500); }
      });
    }
    function updateAdvisorNoteCount(el) {
      document.getElementById('advisorNoteCount').textContent = (el.value.length || 0) + '/200';
    }
    function showAdvisorAlert(msg, type) {
      const el = document.getElementById('advisorAlert');
      el.className = 'vc-alert ' + (type === 'err' ? 'vc-alert-err' : 'vc-alert-ok');
      el.innerHTML = (type === 'err' ? '<i class="pi pi-exclamation-circle"></i> ' : '<i class="pi pi-check-circle"></i> ') + msg;
      el.style.display = 'flex';
    }
    function hideAdvisorAlert(){ document.getElementById('advisorAlert').style.display = 'none'; }

    // ESC cierra modal
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && document.getElementById('advisorModal')?.classList.contains('open')) {
        closeAdvisorVideoCall();
      }
    });

    async function submitAdvisorVideoCall(e) {
      e.preventDefault();
      const unitLabel = document.getElementById('advisorUnitSelect').value;
      const date      = document.getElementById('advisorDate').value;
      const time      = document.getElementById('advisorPreferredTime').value;
      const note      = document.getElementById('advisorNote').value;
      const unitId    = document.getElementById('advisorModalUnitId').value || unitLabel;

      if (!unitLabel) { showAdvisorAlert('Seleccioná una propiedad de interés.', 'err'); return false; }
      if (!date)      { showAdvisorAlert('Indicá una fecha preferida.', 'err'); return false; }
      if (!time)      { showAdvisorAlert('Elegí un horario disponible.', 'err'); return false; }

      hideAdvisorAlert();
      const submitBtn = document.getElementById('advisorSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Agendando...';

      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      try {
        const res = await fetch('/meetings', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            unit_id: unitId || null,
            unit_label: 'Unit ' + unitLabel,
            preferred_date: date,
            preferred_time: time,
            note: note || null,
          }),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          if (res.status === 409) {
            await fetchAdvisorAvailability();
          }
          showAdvisorAlert(data.error || 'No pudimos agendar la videollamada.', 'err');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Confirmar solicitud';
          return false;
        }

        const link = data?.meeting?.meet_link;
        // Guardamos el id de la reunión recién creada para resaltarla en el calendario
        window._lastMeetingId = data?.meeting?.id || null;
        const meetLinkEl = document.getElementById('advisorMeetLink');
        if (link) {
          meetLinkEl.href = link;
          meetLinkEl.textContent = link;
        } else {
          meetLinkEl.textContent = 'Link disponible en el email';
          meetLinkEl.removeAttribute('href');
        }
        document.getElementById('advisorSuccessSub').textContent =
          'Te enviamos la invitación a ' + (data?.meeting?.advisor ? 'tu asesor (' + data.meeting.advisor + ') y a tu email.' : 'tu email.') +
          ' También aparece en tu Google Calendar.';

        document.getElementById('advisorForm').style.display = 'none';
        document.getElementById('advisorFooter').style.display = 'none';
        document.getElementById('advisorSuccess').style.display = 'block';
      } catch (err) {
        showAdvisorAlert('Error de red. Probá de nuevo.', 'err');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirmar solicitud';
      }
      return false;
    }

    // Listeners para refrescar slots al cambiar fecha/unidad
    document.addEventListener('DOMContentLoaded', function() {
      const dateInput = document.getElementById('advisorDate');
      const unitSelect = document.getElementById('advisorUnitSelect');
      if (dateInput) dateInput.addEventListener('change', fetchAdvisorAvailability);
      if (unitSelect) unitSelect.addEventListener('change', function() {
        document.getElementById('advisorModalUnitId').value = unitSelect.value;
        fetchAdvisorAvailability();
      });
    });

    function openWhatsAppBroker() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const price = document.getElementById('modalPrice')?.textContent || '';
      const text = encodeURIComponent('Hola, tengo interés en la Unidad ' + unitNum + ' de Makai Residences (' + price + '). ¿Podemos hablar?');
      window.open('https://wa.me/?text=' + text, '_blank');
    }

    function shareWithCoInvestor() {
      const unitNum = document.getElementById('modalUnitNum')?.textContent || '';
      const subject = encodeURIComponent('Makai Residences - Unidad ' + unitNum);
      const body = encodeURIComponent('Te comparto esta unidad para que la veamos juntos: ' + window.location.href);
      window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
    }

    // Opens the property PDF in a new tab; that page auto-triggers the browser
    // print dialog so the user can "Save as PDF" / download.
    function sharePropertyPdf() {
      if (typeof currentOpenUnit === 'undefined' || !currentOpenUnit) {
        alert('Primero abrí los detalles de una unidad.');
        return;
      }
      const recipient = prompt('¿Para quién es esta ficha? (opcional)', '');
      const params = new URLSearchParams();
      if (recipient && recipient.trim()) params.set('to', recipient.trim());
      const qs = params.toString();
      const url = '/property-pdf/' + encodeURIComponent(currentOpenUnit) + (qs ? '?' + qs : '');
      window.open(url, '_blank', 'noopener');
    }

    function toggleAlerts() {
      alert('Alertas activadas. Te avisamos si baja de precio o quedan menos de 3 unidades.');
    }

    // ============================
    // FILTER FUNCTIONALITY
    // ============================
    let currentFilters = {
      unitNumber: '',
      minPrice: null,
      maxPrice: null,
      types: [],
      directions: [],
      outlooks: [],
      floors: [],
      sort: 'custom_id'
    };

    // Toggle filter dropdown
    function toggleFilterDropdown(filterType) {
      const dropdown = document.getElementById(filterType + 'Dropdown');
      if (!dropdown) return;
      
      // Close all other dropdowns first
      document.querySelectorAll('.filter-dropdown').forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
      });
      
      // Toggle current dropdown
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
      
      // Close on outside click
      if (dropdown.style.display === 'block') {
        setTimeout(() => {
          document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleFilterDropdown"]')) {
              dropdown.style.display = 'none';
              document.removeEventListener('click', closeDropdown);
            }
          });
        }, 10);
      }
    }

    // Toggle currency dropdown
    function toggleCurrencyDropdown() {
      const dropdown = document.getElementById('currencyDropdown');
      if (!dropdown) return;
      
      // Close all other dropdowns first
      document.querySelectorAll('.filter-dropdown').forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
      });
      
      // Toggle current dropdown
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
      
      // Close on outside click
      if (dropdown.style.display === 'block') {
        setTimeout(() => {
          document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !e.target.closest('#currencyBtn')) {
              dropdown.style.display = 'none';
              document.removeEventListener('click', closeDropdown);
            }
          });
        }, 10);
      }
    }

    // Select currency
    function selectCurrency(currency) {
      document.getElementById('currencyLabel').textContent = currency;
      document.getElementById('currencyDropdown').style.display = 'none';
      setCurrency(currency);
    }

    // Apply price filter
    function applyPriceFilter() {
      const minPrice = document.getElementById('minPrice').value;
      const maxPrice = document.getElementById('maxPrice').value;
      
      currentFilters.minPrice = minPrice ? parseFloat(minPrice) : null;
      currentFilters.maxPrice = maxPrice ? parseFloat(maxPrice) : null;
      
      updatePriceLabel();
      toggleFilterDropdown('price');
      applyFilters();
    }

    // Apply type filter
    function applyTypeFilter() {
      const checkboxes = document.querySelectorAll('#typeDropdown input[type="checkbox"]:checked');
      currentFilters.types = Array.from(checkboxes).map(cb => cb.value);
      updateTypeLabel();
      applyFilters();
    }

    // Apply direction filter
    function applyDirectionFilter() {
      const checkboxes = document.querySelectorAll('#directionDropdown input[type="checkbox"]:checked');
      currentFilters.directions = Array.from(checkboxes).map(cb => cb.value);
      updateDirectionLabel();
      applyFilters();
    }

    // Apply outlook filter
    function applyOutlookFilter() {
      const checkboxes = document.querySelectorAll('#outlookDropdown input[type="checkbox"]:checked');
      currentFilters.outlooks = Array.from(checkboxes).map(cb => cb.value);
      updateOutlookLabel();
      applyFilters();
    }

    // Apply floor filter
    function applyFloorFilter() {
      const checkboxes = document.querySelectorAll('#floorDropdown input[type="checkbox"]:checked');
      currentFilters.floors = Array.from(checkboxes).map(cb => cb.value);
      updateFloorLabel();
      applyFilters();
    }

    // Apply sort filter
    function applySortFilter() {
      const radio = document.querySelector('#sortDropdown input[type="radio"]:checked');
      if (radio) {
        currentFilters.sort = radio.value;
        updateSortLabel();
        applyFilters();
      }
    }

    // Mark a filter button as active (corporate border + text) by its label id.
    function setFilterActive(labelId, active) {
      const el = document.getElementById(labelId);
      const btn = el ? el.closest('.fg-filter-btn') : null;
      if (btn) btn.classList.toggle('is-active', !!active);
    }

    // Update filter labels
    function updatePriceLabel() {
      const label = document.getElementById('priceLabel');
      const active = !!(currentFilters.minPrice || currentFilters.maxPrice);
      if (active) {
        const min = currentFilters.minPrice ? `$${number_format(currentFilters.minPrice, 0)}` : 'Any';
        const max = currentFilters.maxPrice ? `$${number_format(currentFilters.maxPrice, 0)}` : 'Any';
        label.textContent = `${min} - ${max}`;
      } else {
        label.textContent = 'Price';
      }
      setFilterActive('priceLabel', active);
    }

    function updateTypeLabel() {
      const label = document.getElementById('typeLabel');
      const active = currentFilters.types.length > 0;
      label.textContent = active ? `Types (${currentFilters.types.length})` : 'Unit Type';
      setFilterActive('typeLabel', active);
    }

    function updateDirectionLabel() {
      const label = document.getElementById('directionLabel');
      const active = currentFilters.directions.length > 0;
      label.textContent = active ? `Directions (${currentFilters.directions.length})` : 'Direction';
      setFilterActive('directionLabel', active);
    }

    function updateOutlookLabel() {
      const label = document.getElementById('outlookLabel');
      const active = currentFilters.outlooks.length > 0;
      label.textContent = active ? `Outlooks (${currentFilters.outlooks.length})` : 'Outlook';
      setFilterActive('outlookLabel', active);
    }

    function updateFloorLabel() {
      const label = document.getElementById('floorLabel');
      const active = currentFilters.floors.length > 0;
      label.textContent = active ? `Floors (${currentFilters.floors.length})` : 'Floor';
      setFilterActive('floorLabel', active);
    }

    function updateSortLabel() {
      const label = document.getElementById('sortLabel');
      const sortOptions = {
        'price-asc': 'Price ↑',
        'price-desc': 'Price ↓',
        'size-asc': 'Size ↑',
        'size-desc': 'Size ↓',
        'bedrooms-asc': 'Beds ↑',
        'bedrooms-desc': 'Beds ↓',
        'custom_id': 'Unit #'
      };
      label.textContent = sortOptions[currentFilters.sort] || 'Sort';
      // Default sort (Unit #) is not considered an active filter
      setFilterActive('sortLabel', !!currentFilters.sort && currentFilters.sort !== 'custom_id');
    }

    // Animate a card/row in or out. Uses CSS keyframes (.is-fading-in /
    // .is-fading-out) and only flips display:none after the exit anim completes
    // so the element actually leaves the grid flow.
    function animateToggle(el, visible, opts) {
      opts = opts || {};
      const isHidden = el.style.display === 'none';
      if (visible) {
        if (isHidden || el.classList.contains('is-fading-out')) {
          el.style.display = '';
          el.classList.remove('is-fading-out');
          // Re-trigger entry animation
          el.classList.remove('is-fading-in');
          // Force reflow so the keyframe replays
          void el.offsetWidth;
          el.classList.add('is-fading-in');
          setTimeout(() => el.classList.remove('is-fading-in'), 400);
        }
      } else if (!isHidden) {
        if (opts.kind === 'row') {
          el.classList.add('is-fading-out');
          setTimeout(() => {
            if (el.classList.contains('is-fading-out')) el.style.display = 'none';
          }, 240);
        } else {
          el.classList.add('is-fading-out');
          setTimeout(() => {
            if (el.classList.contains('is-fading-out')) el.style.display = 'none';
          }, 220);
        }
      }
    }

    // Apply all filters — pure client-side. Toggles visibility on the
    // already-rendered grid cards (.fg-card) and list rows (tr[data-filter-unit]),
    // sorts the grid in place, and updates the match counters + URL params.
    function applyFilters(options) {
      options = options || {};
      const unitNumberInput = document.querySelector('input[placeholder="Unit No."]');
      if (unitNumberInput) {
        currentFilters.unitNumber = (unitNumberInput.value || '').trim();
        const searchBox = unitNumberInput.closest('.fg-search');
        if (searchBox) searchBox.classList.toggle('is-active', currentFilters.unitNumber.length > 0);
      }

      const f = currentFilters;
      const q = f.unitNumber ? f.unitNumber.toLowerCase() : '';

      function matches(el) {
        const status   = (el.dataset.filterStatus   || '').toLowerCase();
        const floor    = (el.dataset.filterFloor    || '');
        const type     = (el.dataset.filterType     || '');
        const beds     = parseInt(el.dataset.filterBedrooms || '0', 10);
        const dir      = (el.dataset.filterDirection|| '').toUpperCase();
        const outlook  = (el.dataset.filterOutlook  || '');
        const price    = parseFloat(el.dataset.filterPrice  || '0');
        const area     = parseFloat(el.dataset.filterArea   || '0');
        const search   = (el.dataset.filterSearch   || '');
        const unitLbl  = (el.dataset.filterUnit     || '').toLowerCase();

        if (q && !search.includes(q) && !unitLbl.includes(q)) return false;
        if (f.minPrice != null && price < f.minPrice) return false;
        if (f.maxPrice != null && price > f.maxPrice) return false;

        // Los tipos provienen de la configuración global (Unidades →
        // Configuraciones); el valor del checkbox coincide con data-filter-type
        // (el campo `type` guardado en la unidad), por lo que basta match exacto.
        if (f.types && f.types.length && !f.types.includes(type)) return false;
        if (f.directions && f.directions.length && !f.directions.includes(dir)) return false;
        if (f.outlooks   && f.outlooks.length   && !f.outlooks.includes(outlook)) return false;
        if (f.floors     && f.floors.length     && !f.floors.includes(floor))    return false;
        return true;
      }

      // Server pagination gate: the client filter engine works on the cards
      // currently in the DOM. While not everything is loaded yet, any *active*
      // filter first pulls the remaining units in (one shot) so the results are
      // complete; an unfiltered view just shows what's painted and lets the
      // infinite-scroll observer stream the rest.
      if (!allUnitsLoaded && !options._afterLoad && hasActiveFilters()) {
        setLazyLoading(true);
        ensureAllLoaded().then(() => {
          setLazyLoading(false);
          applyFilters(Object.assign({}, options, { _afterLoad: true }));
        });
        return;
      }

      const cards = Array.from(document.querySelectorAll('.fg-units-grid > .fg-card'));
      let visibleGrid = 0;
      cards.forEach(c => {
        const ok = matches(c);
        animateToggle(c, ok);
        if (ok) visibleGrid++;
      });

      const rows = Array.from(document.querySelectorAll('#fgListTable tbody tr[data-filter-unit]'));
      let visibleList = 0;
      const activeTab = document.querySelector('.fg-list-tab.active')?.dataset.tab || 'all';
      rows.forEach(r => {
        const ok = matches(r);
        const tabOk = (activeTab === 'all')
          || (activeTab === 'hot' ? r.dataset.hot === '1' : r.dataset.tab === activeTab);
        const show = ok && tabOk;
        animateToggle(r, show, { kind: 'row' });
        if (show) visibleList++;
      });

      // Sort cards in place when a sort is selected
      const grid = document.querySelector('.fg-units-grid');
      if (grid && f.sort) {
        const sortFns = {
          'price-asc':     (a,b) => parseFloat(a.dataset.filterPrice||0) - parseFloat(b.dataset.filterPrice||0),
          'price-desc':    (a,b) => parseFloat(b.dataset.filterPrice||0) - parseFloat(a.dataset.filterPrice||0),
          'size-asc':      (a,b) => parseFloat(a.dataset.filterArea||0)  - parseFloat(b.dataset.filterArea||0),
          'size-desc':     (a,b) => parseFloat(b.dataset.filterArea||0)  - parseFloat(a.dataset.filterArea||0),
          'bedrooms-asc':  (a,b) => parseInt(a.dataset.filterBedrooms||0)- parseInt(b.dataset.filterBedrooms||0),
          'bedrooms-desc': (a,b) => parseInt(b.dataset.filterBedrooms||0)- parseInt(a.dataset.filterBedrooms||0),
          // Default order: follow the server's global ordering (display_on_home_page,
          // status grouping, custom_id, id) via the stamped index — not a naive
          // string compare, which scrambled featured/sold grouping across pages.
          'custom_id':     (a,b) => (parseInt(a.dataset.order)||0) - (parseInt(b.dataset.order)||0)
        };
        const fn = sortFns[f.sort];
        if (fn) {
          // Sort all cards (visible + hidden). Skip the inline CTA card which
          // has no dataset.filterUnit and lives mid-grid — keep its DOM order.
          const sortable = cards.slice().sort(fn);
          sortable.forEach(c => grid.appendChild(c));
        }
      }

      // With no active filter and more units still streaming in, the real
      // total is the catalog size — not just what's painted so far.
      const showingAll = !hasActiveFilters();
      updateMatchCount((showingAll && !allUnitsLoaded) ? TOTAL_UNITS : visibleGrid);
      updateListMatchCount((showingAll && !allUnitsLoaded) ? TOTAL_UNITS : visibleList);
      if (!options.skipUrl) syncFiltersToUrl();
    }

    // ── Infinite scroll: stream additional unit pages from the API ──────────
    // The server renders only the first page (HOME_PAGE_SIZE) of heavy cards /
    // rows; the rest arrive as rendered HTML on scroll, keeping the DOM light.
    const HOME_PAGE_SIZE = {{ \App\Http\Controllers\HomeController::HOME_PAGE_SIZE }};
    let lazyOffset = 0;          // how many units are currently in the DOM
    let serverOrderSeq = 0;      // monotonic index = each card's position in the server's global order
    let allUnitsLoaded = false;  // true once every public unit is painted
    let lazyBusy = false;        // a page fetch is in flight
    let allLoadPromise = null;   // de-dupes concurrent "load everything" calls

    function hasActiveFilters() {
      const f = currentFilters;
      if (f.unitNumber) return true;
      if (f.minPrice != null || f.maxPrice != null) return true;
      if ((f.types && f.types.length) || (f.directions && f.directions.length)
          || (f.outlooks && f.outlooks.length) || (f.floors && f.floors.length)) return true;
      if (f.sort && f.sort !== 'custom_id') return true;
      const tab = document.querySelector('.fg-list-tab.active')?.dataset.tab || 'all';
      return tab !== 'all';
    }

    function updateLazyLoaders() {
      const show = !allUnitsLoaded;
      document.getElementById('gridLazyMore')?.classList.toggle('is-active', show);
      document.getElementById('listLazyMore')?.classList.toggle('is-active', show);
    }
    function setLazyLoading(on) {
      if (!on) { updateLazyLoaders(); return; }
      document.getElementById('gridLazyMore')?.classList.add('is-active');
      document.getElementById('listLazyMore')?.classList.add('is-active');
    }

    // Insert a page of server-rendered HTML into the grid + list, optionally
    // with the staggered entrance animation (skipped for the bulk "load all").
    function appendUnits(cardsHtml, rowsHtml, animate) {
      const grid  = document.querySelector('.fg-units-grid');
      const tbody = document.querySelector('#fgListTable tbody');
      if (grid && cardsHtml) {
        const tmp = document.createElement('div');
        tmp.innerHTML = cardsHtml;
        let i = 0;
        Array.from(tmp.children).forEach(node => {
          // Stamp the card's place in the server's global order so the default
          // (custom_id) sort can keep it slotted correctly instead of dumping
          // every freshly loaded card at the bottom of the grid.
          node.dataset.order = serverOrderSeq++;
          if (animate) {
            node.style.setProperty('--lazy-i', i++ % HOME_PAGE_SIZE);
            node.classList.add('is-lazy-in');
            setTimeout(() => node.classList.remove('is-lazy-in'), 1000);
          }
          grid.appendChild(node);
        });
      }
      if (tbody && rowsHtml) {
        const tmp = document.createElement('tbody');
        tmp.innerHTML = rowsHtml;
        let j = 0;
        Array.from(tmp.children).forEach(node => {
          if (animate) {
            node.style.setProperty('--lazy-i', j++ % HOME_PAGE_SIZE);
            node.classList.add('is-lazy-in');
            setTimeout(() => node.classList.remove('is-lazy-in'), 1000);
          }
          tbody.appendChild(node);
        });
      }
      // Freshly inserted prices must reflect the currently selected currency.
      try { updateCurrencyDisplay(localStorage.getItem('selectedCurrency') || 'USD'); } catch (e) {}
    }

    function fetchLazyPage(all) {
      const url = '/api/home-units?offset=' + lazyOffset + (all ? '&all=1' : '');
      return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
          appendUnits(data.cards, data.rows, !all);
          lazyOffset = data.offset;
          allUnitsLoaded = !data.hasMore;
          updateLazyLoaders();
          return data;
        })
        .catch(() => { /* network hiccup — the observer will retry on next scroll */ });
    }

    // Pull every remaining unit in one shot (used right before a filter runs).
    function ensureAllLoaded() {
      if (allUnitsLoaded) return Promise.resolve();
      if (allLoadPromise) return allLoadPromise;
      allLoadPromise = fetchLazyPage(true).finally(() => { allLoadPromise = null; });
      return allLoadPromise;
    }

    function initLazyScroll() {
      const initialCards = document.querySelectorAll('.fg-units-grid > .fg-card');
      // Seed the server-order index on the first page (server-rendered) so later
      // pages continue the same sequence and the grid stays globally ordered.
      initialCards.forEach((c, idx) => { c.dataset.order = idx; });
      serverOrderSeq = initialCards.length;
      lazyOffset = initialCards.length;
      allUnitsLoaded = lazyOffset >= TOTAL_UNITS;
      updateLazyLoaders();
      if (!('IntersectionObserver' in window)) return;
      const grid = document.getElementById('gridLazyMore');
      const list = document.getElementById('listLazyMore');
      const obs = new IntersectionObserver((entries) => {
        if (allUnitsLoaded || lazyBusy) return;
        const hit = entries.some(e => e.isIntersecting && e.target.classList.contains('is-active'));
        if (!hit) return;
        lazyBusy = true;
        fetchLazyPage(false)
          .then(() => {
            // Re-apply the default sort so the freshly appended page slots into
            // the grid's global order (and refresh the counters).
            applyFilters({ skipUrl: true });
          })
          .finally(() => { lazyBusy = false; });
      }, { rootMargin: '0px 0px 300px 0px' });
      if (grid) obs.observe(grid);
      if (list) obs.observe(list);
    }

    // Sync current filter state to URL query string (no reload).
    function syncFiltersToUrl() {
      const params = new URLSearchParams();
      const f = currentFilters;
      if (f.unitNumber)             params.set('q', f.unitNumber);
      if (f.minPrice != null)       params.set('min', f.minPrice);
      if (f.maxPrice != null)       params.set('max', f.maxPrice);
      if (f.types?.length)          params.set('type', f.types.join(','));
      if (f.directions?.length)     params.set('dir',  f.directions.join(','));
      if (f.outlooks?.length)       params.set('out',  f.outlooks.join(','));
      if (f.floors?.length)         params.set('floor', f.floors.join(','));
      if (f.sort && f.sort !== 'custom_id') params.set('sort', f.sort);
      // Preserve `unit` and `view` if already in URL
      const existing = new URLSearchParams(window.location.search);
      if (existing.get('unit')) params.set('unit', existing.get('unit'));
      if (existing.get('view')) params.set('view', existing.get('view'));
      const qs = params.toString();
      const url = window.location.pathname + (qs ? '?' + qs : '');
      window.history.replaceState({}, '', url);
    }

    // Restore filter state from URL on first load.
    function applyFiltersFromUrl() {
      const p = new URLSearchParams(window.location.search);
      if (!p.toString()) {
        // No URL params — still compute the initial match counts from the
        // server-rendered DOM so the pill doesn't show a stale placeholder.
        applyFilters({ skipUrl: true });
        return;
      }
      currentFilters.unitNumber = p.get('q') || '';
      currentFilters.minPrice   = p.get('min') ? parseFloat(p.get('min')) : null;
      currentFilters.maxPrice   = p.get('max') ? parseFloat(p.get('max')) : null;
      currentFilters.types      = p.get('type')  ? p.get('type').split(',').filter(Boolean)  : [];
      currentFilters.directions = p.get('dir')   ? p.get('dir').split(',').filter(Boolean)   : [];
      currentFilters.outlooks   = p.get('out')   ? p.get('out').split(',').filter(Boolean)   : [];
      currentFilters.floors     = p.get('floor') ? p.get('floor').split(',').filter(Boolean) : [];
      currentFilters.sort       = p.get('sort')  || 'custom_id';

      // Reflect into the UI controls
      const setCheckGroup = (selector, values) => {
        document.querySelectorAll(selector).forEach(cb => {
          cb.checked = values.includes(cb.value);
        });
      };
      const minEl = document.getElementById('minPrice'); if (minEl) minEl.value = currentFilters.minPrice ?? '';
      const maxEl = document.getElementById('maxPrice'); if (maxEl) maxEl.value = currentFilters.maxPrice ?? '';
      setCheckGroup('#typeDropdown input[type="checkbox"]',      currentFilters.types);
      setCheckGroup('#directionDropdown input[type="checkbox"]', currentFilters.directions);
      setCheckGroup('#outlookDropdown input[type="checkbox"]',   currentFilters.outlooks);
      setCheckGroup('#floorDropdown input[type="checkbox"]',     currentFilters.floors);
      const sortRadio = document.querySelector('#sortDropdown input[value="'+currentFilters.sort+'"]');
      if (sortRadio) sortRadio.checked = true;
      const unitInput = document.querySelector('input[placeholder="Unit No."]');
      if (unitInput) unitInput.value = currentFilters.unitNumber;

      updatePriceLabel();
      updateTypeLabel();
      updateDirectionLabel();
      updateOutlookLabel();
      updateFloorLabel();
      updateSortLabel();
      applyFilters({ skipUrl: true });
    }

    // Legacy stubs kept for any external callers — no-ops now (client-side filter).
    function updateUnitsGrid() {}
    function createUnitCard(unit) {
      const div = document.createElement('div');
      div.style.cssText = 'position:relative;width:100%;max-width:24rem;overflow:hidden;background:rgb(249,248,246);border-radius:1rem;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1),0 4px 6px -4px rgba(0,0,0,0.1);';
      
      // Status overlay
      if (unit.status === 'sold' || unit.status === 'pending') {
        const statusDiv = document.createElement('div');
        statusDiv.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(67,58,45,0.8);z-index:20;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-top:7rem;cursor:not-allowed;';
        statusDiv.innerHTML = `<span style="color:white;font-size:1.5rem;font-weight:700;letter-spacing:0.15em;">${unit.status.toUpperCase()}</span>`;
        div.appendChild(statusDiv);
      }

      const isAvailable = unit.status !== 'sold' && unit.status !== 'pending';
      const viewers = Math.floor(Math.random() * 11) + 8;

      // Image section
      const imageSection = document.createElement('div');
      imageSection.style.cssText = 'min-height:14rem;';
      imageSection.innerHTML = `
        <div style="position:relative;overflow:hidden;">
          ${unit.images && unit.images.length > 0
            ? `<img src="${unit.images[0].path}" alt="Unit Render" style="width:100%;object-fit:cover;display:block;${unit.status === 'sold' ? 'filter:grayscale(20%);' : ''}" onerror="this.style.height='200px';this.style.background='rgb(218,211,200)'">`
            : '<div style="width:100%;height:200px;background:rgb(218,211,200);display:flex;align-items:center;justify-content:center;color:rgb(98,84,65);">No Image Available</div>'
          }
          ${isAvailable
            ? `<div class="live-viewers" style="position:absolute;top:0.5rem;left:0.5rem;background:rgba(0,0,0,0.55);color:white;font-size:0.62rem;font-weight:600;padding:3px 8px;border-radius:9999px;display:flex;align-items:center;gap:5px;letter-spacing:0.02em;">
                 <span style="width:6px;height:6px;border-radius:50%;background:rgb(34,197,94);" class="advisor-dot"></span>
                 <span>${viewers} viendo ahora</span>
               </div>`
            : ''
          }
        </div>
      `;
      div.appendChild(imageSection);

      // Details section
      const pricePerSqft = unit.internal_area && unit.internal_area > 0
        ? `<span style="display:block;font-size:0.62rem;color:rgb(98,84,65);opacity:0.75;line-height:1.2;">$${number_format(Math.round(unit.price / unit.internal_area), 0)}/m² <span style="color:rgb(34,197,94);font-weight:600;">· -12% mercado</span></span>`
        : '';
      const detailsSection = document.createElement('div');
      detailsSection.style.cssText = 'position:relative;padding:1.25rem 1.5rem 0.5rem 1.5rem;';
      detailsSection.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div style="text-align:left;">
            <span style="display:block;font-weight:700;color:rgb(98,84,65);font-size:1rem;">${unit.custom_id || unit.name}</span>
            <span style="display:block;font-weight:600;font-size:0.875rem;color:rgb(98,84,65);">${unit.floor ? ucfirst(unit.floor) : 'Ground Floor'}</span>
            <span style="font-size:0.8rem;color:rgb(98,84,65);max-width:10rem;display:block;">${unit.bedrooms} Bed ${unit.bathrooms ? '| ' + unit.bathrooms + ' Bath' : ''} ${unit.direction ? '| ' + unit.direction.toUpperCase() : ''} ${unit.outlook ? '| ' + unit.outlook : ''}</span>
          </div>
          <div style="text-align:right;position:absolute;right:1.25rem;top:1.25rem;">
            ${unit.status === 'sold' || unit.status === 'pending'
              ? `<span style="font-size:1rem;font-weight:800;color:rgb(98,84,65);">${unit.status.toUpperCase()}</span>`
              : `<div style="display:inline-block;text-align:right;">
                  <span style="display:block;font-weight:800;background:linear-gradient(to bottom right,rgb(210,182,144),rgb(107,80,43));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:1.1rem;">$${number_format(unit.price, 0, ' ', ' ')}</span>
                  ${pricePerSqft}
                  <span style="display:block;font-size:0.6rem;color:rgb(180,134,72);font-weight:600;line-height:1.2;margin-top:1px;">Reserva desde $5000</span>
                  ${unit.discount && unit.discount > 0
                    ? `<button title="Válido hasta el 30 de abril" style="background:rgb(34,197,94);color:white;font-size:0.65rem;border:none;border-radius:4px;padding:2px 6px;cursor:pointer;line-height:1.4;font-weight:600;margin-top:3px;">UNLOCK <b>$${number_format(unit.discount, 0, ' ', ' ')}</b><br>DISCOUNT</button>`
                    : ''
                  }
                </div>`
            }
          </div>
        </div>
      `;
      div.appendChild(detailsSection);

      // Action buttons
      const actionButtons = document.createElement('div');
      actionButtons.style.cssText = 'display:flex;flex-direction:row;';
      const rightButton = unit.status === 'sold'
        ? `<button style="width:50%;height:2.5rem;background:rgb(98,84,65);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:not-allowed;opacity:0.9;">SOLD</button>`
        : unit.status === 'pending'
        ? `<button style="width:50%;height:2.5rem;background:rgb(180,180,180);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:not-allowed;">PENDING</button>`
        : `<button onclick="openReservePage('${unit.id}')" style="width:50%;height:2.5rem;background:rgb(102,123,106);color:white;border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:pointer;">RESERVE</button>`;
      actionButtons.innerHTML = `
        <button onclick="openMoreInfo('${unit.id}')" style="width:50%;height:2.5rem;background:rgb(239,235,230);color:rgb(98,84,65);border:none;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;cursor:pointer;">MORE INFO</button>
        ${rightButton}
      `;
      div.appendChild(actionButtons);

      // Advisor live + video call CTA
      if (isAvailable) {
        const advisorBlock = document.createElement('div');
        advisorBlock.style.cssText = 'padding:0.5rem 0.75rem 0.75rem 0.75rem;display:flex;flex-direction:column;align-items:center;gap:0.4rem;';
        advisorBlock.innerHTML = `
          <div class="advisor-live" style="display:flex;align-items:center;gap:6px;font-size:0.7rem;color:rgb(34,150,80);font-weight:600;">
            <span class="advisor-dot" style="width:7px;height:7px;border-radius:50%;background:rgb(34,197,94);"></span>
            <span>Tu asesor está disponible ahora mismo</span>
          </div>
          <button onclick="openAdvisorVideoCall('${unit.id}')" style="display:inline-flex;align-items:center;gap:6px;background:white;color:rgb(98,84,65);border:1px solid rgb(218,211,200);border-radius:0.5rem;padding:5px 12px;font-size:0.72rem;font-weight:600;cursor:pointer;letter-spacing:0.02em;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="rgb(180,134,72)"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
            Agendar una Videollamada
          </button>
        `;
        div.appendChild(advisorBlock);
      }

      return div;
    }

    // Pulse the pill briefly whenever its number changes — small visual cue
    // that the filter just updated.
    function pulseMatchPill(pill, newCount) {
      if (!pill) return;
      const prev = pill.dataset.lastCount;
      if (prev !== String(newCount)) {
        pill.classList.remove('is-pulsing');
        void pill.offsetWidth; // restart anim
        pill.classList.add('is-pulsing');
        setTimeout(() => pill.classList.remove('is-pulsing'), 380);
        pill.dataset.lastCount = String(newCount);
      }
    }
    // Total public units rendered on the page (denominator for the pill text).
    const TOTAL_UNITS = {{ $units->count() }};
    // Update match count — the pill in the grid filter bar now reads
    // "Mostrando X de Y unidades" (X = visible/matched, Y = total).
    function updateMatchCount(count) {
      const pill = document.querySelector('.fg-filter-bar .fg-pill-matches');
      if (!pill) return;
      pill.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
        </svg>
        Mostrando ${count} de ${TOTAL_UNITS} unidades`;
      pulseMatchPill(pill, count);
    }
    // The list view also has its own pill in the toolbar.
    function updateListMatchCount(count) {
      const pill = document.querySelector('#fgListWrap .fg-pill-matches');
      if (!pill) return;
      pill.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
        Mostrando ${count} de ${TOTAL_UNITS} unidades`;
      pulseMatchPill(pill, count);
    }

    // Loading states
    function showFilterLoading() {
      const gridContainer = document.querySelector('.grid-template-columns');
      if (gridContainer) {
        gridContainer.style.opacity = '0.5';
      }
    }

    function hideFilterLoading() {
      const gridContainer = document.querySelector('.grid-template-columns');
      if (gridContainer) {
        gridContainer.style.opacity = '1';
      }
    }

    // Reset filters
    function resetFilters() {
      currentFilters = {
        unitNumber: '',
        minPrice: null,
        maxPrice: null,
        types: [],
        directions: [],
        outlooks: [],
        floors: [],
        sort: 'custom_id'
      };

      // Reset form elements
      document.getElementById('minPrice').value = '';
      document.getElementById('maxPrice').value = '';
      document.querySelectorAll('#typeDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#directionDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#outlookDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('#floorDropdown input[type="checkbox"]').forEach(cb => cb.checked = false);
      document.querySelector('input[placeholder="Unit No."]').value = '';
      document.querySelector('#sortDropdown input[value="custom_id"]').checked = true;

      // Update labels
      updatePriceLabel();
      updateTypeLabel();
      updateDirectionLabel();
      updateOutlookLabel();
      updateFloorLabel();
      updateSortLabel();

      // Apply filters
      applyFilters();
    }

    // Helper function
    function number_format(number, decimals, dec_point, thousands_sep) {
      if (decimals === 0) {
        thousands_sep = thousands_sep || ',';
        return Math.floor(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
      }
      // Full implementation for decimal cases if needed
      return number.toLocaleString();
    }

    function ucfirst(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Add event listener for reset button + restore filters from URL
    document.addEventListener('DOMContentLoaded', function() {
      const resetButton = document.querySelector('button[style*="rgb(239,68,68)"]');
      if (resetButton) {
        resetButton.addEventListener('click', resetFilters);
      }

      // Unit-number search (grid) — typing filters in real time
      const unitNumberInput = document.querySelector('input[placeholder="Unit No."]');
      if (unitNumberInput) {
        unitNumberInput.addEventListener('input', function() {
          currentFilters.unitNumber = this.value;
          applyFilters();
        });
      }

      // Wire infinite scroll (counts the server-rendered first page) before the
      // initial filter pass so counters & "load more" state are consistent.
      initLazyScroll();

      // Restore filter state from URL (?q=&min=&max=&type=&dir=&out=&floor=&sort=)
      applyFiltersFromUrl();

      // If URL has ?unit=X, open that unit's modal once everything is wired.
      const urlUnit = new URLSearchParams(window.location.search).get('unit');
      if (urlUnit && typeof openMoreInfo === 'function') {
        setTimeout(() => openMoreInfo(urlUnit), 250);
      }
      // If URL has ?view=list, switch to list view tab.
      const urlView = new URLSearchParams(window.location.search).get('view');
      if (urlView === 'list') {
        const listBtn = document.querySelector('[data-view="list"]');
        if (listBtn) listBtn.click();
      } else if (urlView === 'plan') {
        const planBtn = document.querySelector('[data-view="plan"]');
        if (planBtn) planBtn.click();
      }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeMoreInfo();
        closeReservePage();
        closeMenu();
        closeProjects();
        closeProfileMenu();
        if (typeof closeDisclaimer === 'function') closeDisclaimer();
        // Close all filter dropdowns
        document.querySelectorAll('.filter-dropdown').forEach(d => d.style.display = 'none');
      }
    });

    // ============================
    // WISHLIST — shared UI sync between cards, the unit modal and the header
    // ============================
    // Reflect saved/unsaved state on the modal "ADD TO LIST" toggle.
    function setModalAddToListState(wishlisted, unitCount) {
      const btn = document.getElementById('modalAddToListBtn');
      if (btn) {
        btn.classList.toggle('is-fav', !!wishlisted);
        btn.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
        const icon = document.getElementById('modalAddToListIcon');
        if (icon) icon.setAttribute('fill', wishlisted ? 'currentColor' : 'none');
        const lbl = document.getElementById('modalAddToListLabel');
        if (lbl) lbl.textContent = wishlisted ? 'SAVED' : 'ADD TO LIST';
      }
      if (typeof unitCount !== 'undefined') {
        const sl = document.getElementById('modalShortlistedCount');
        if (sl) sl.textContent = unitCount;
      }
    }

    // Sync every piece of wishlist UI for a unit: card hearts + counts, the
    // header "Guardados (N)" counter, and the modal toggle if that unit is open.
    function syncWishlistUI(unitId, wishlisted, unitCount, total) {
      document.querySelectorAll(`[data-wishlist-toggle][data-unit-id="${unitId}"]`).forEach(b => {
        b.classList.toggle('is-fav', !!wishlisted);
        b.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
        const svg = b.querySelector('svg');
        if (svg) svg.setAttribute('fill', wishlisted ? 'currentColor' : 'none');
        const label = b.querySelector('.label');
        if (label) label.textContent = wishlisted ? 'Saved' : 'Add to list';
      });
      if (typeof unitCount !== 'undefined') {
        document.querySelectorAll(`[data-unit-count="${unitId}"]`).forEach(el => el.textContent = unitCount);
      }
      if (typeof total !== 'undefined') {
        const headerCnt = document.querySelector('[data-saved-count]');
        if (headerCnt) headerCnt.textContent = `Guardados (${total})`;
      }
      if (typeof currentOpenUnit !== 'undefined' && String(currentOpenUnit) === String(unitId)) {
        setModalAddToListState(!!wishlisted, unitCount);
      }
    }

    // Toggle wishlist for the unit currently open in the modal.
    window.toggleModalWishlist = function () {
      if (typeof currentOpenUnit === 'undefined' || !currentOpenUnit) return;
      const unitId = currentOpenUnit;
      const btn = document.getElementById('modalAddToListBtn');
      const wasFav = btn ? btn.classList.contains('is-fav') : false;
      // Al quitar de guardados, pedir confirmación (línea gráfica de la web)
      if (wasFav && typeof window.confirmDialog === 'function') {
        window.confirmDialog({
          title: '¿Quitar de guardados?',
          text: 'Esta unidad dejará de aparecer en tu lista de guardados. Podrás volver a guardarla cuando quieras.',
          confirmLabel: 'Quitar',
          icon: 'pi pi-heart',
          onConfirm: () => runModalWishlistToggle(unitId, wasFav),
        });
        return;
      }
      runModalWishlistToggle(unitId, wasFav);
    };

    function runModalWishlistToggle(unitId, wasFav) {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      // Optimistic flip
      setModalAddToListState(!wasFav);
      fetch(`/api/wishlist/toggle/${unitId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        credentials: 'same-origin',
      }).then(r => r.ok ? r.json() : Promise.reject(r))
        .then(data => {
          if (data && data.success) syncWishlistUI(unitId, data.wishlisted, data.unit_count, data.total);
        }).catch(err => {
          setModalAddToListState(wasFav);
          if (err && err.status === 401) window.location.href = '/login';
        });
    };

    // ============================
    // WISHLIST TOGGLE — hearts on cards persist to the DB for logged-in users
    // ============================
    (function initWishlist(){
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      document.addEventListener('click', function(e){
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const unitId = btn.dataset.unitId;
        if (!unitId) return;

        const wasFav = btn.classList.contains('is-fav');
        // Al quitar de guardados, pedir confirmación (línea gráfica de la web)
        if (wasFav && typeof window.confirmDialog === 'function') {
          window.confirmDialog({
            title: '¿Quitar de guardados?',
            text: 'Esta unidad dejará de aparecer en tu lista de guardados. Podrás volver a guardarla cuando quieras.',
            confirmLabel: 'Quitar',
            icon: 'pi pi-heart',
            onConfirm: () => runWishlistToggle(btn, unitId, wasFav),
          });
          return;
        }
        runWishlistToggle(btn, unitId, wasFav);
      });

      function runWishlistToggle(btn, unitId, wasFav){
        // Optimistic UI flip
        btn.classList.toggle('is-fav', !wasFav);
        const svg = btn.querySelector('svg');
        if (svg) svg.setAttribute('fill', !wasFav ? 'currentColor' : 'none');
        const label = btn.querySelector('.label');
        if (label) label.textContent = !wasFav ? 'Saved' : 'Add to list';
        // Re-trigger heart pop animation on every toggle
        const heartSpan = btn.querySelector('.heart');
        if (heartSpan) {
          heartSpan.style.animation = 'none';
          void heartSpan.offsetHeight;
          heartSpan.style.animation = '';
        }

        fetch(`/api/wishlist/toggle/${unitId}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          credentials: 'same-origin',
        }).then(r => r.ok ? r.json() : Promise.reject(r))
          .then(data => {
            if (data && data.success) {
              // Sync to authoritative state in case of race
              btn.classList.toggle('is-fav', !!data.wishlisted);
              if (svg) svg.setAttribute('fill', data.wishlisted ? 'currentColor' : 'none');
              if (label) label.textContent = data.wishlisted ? 'Saved' : 'Add to list';
              const heartSpan = btn.querySelector('.heart');
              if (heartSpan) {
                heartSpan.style.animation = 'none';
                void heartSpan.offsetHeight;
                heartSpan.style.animation = '';
              }
              const cnt = document.querySelector(`[data-unit-count="${unitId}"]`);
              if (cnt && typeof data.unit_count !== 'undefined') cnt.textContent = data.unit_count;
              const headerCnt = document.querySelector('[data-saved-count]');
              if (headerCnt && typeof data.total !== 'undefined') headerCnt.textContent = `Guardados (${data.total})`;
              // Keep the modal toggle in sync if this unit happens to be open
              if (typeof currentOpenUnit !== 'undefined' && String(currentOpenUnit) === String(unitId)) {
                setModalAddToListState(!!data.wishlisted, data.unit_count);
              }
            }
          }).catch(err => {
            // Revert optimistic UI
            btn.classList.toggle('is-fav', wasFav);
            if (svg) svg.setAttribute('fill', wasFav ? 'currentColor' : 'none');
            if (label) label.textContent = wasFav ? 'Saved' : 'Add to list';
            const heartSpan = btn.querySelector('.heart');
            if (heartSpan) {
              heartSpan.style.animation = 'none';
              void heartSpan.offsetHeight;
              heartSpan.style.animation = '';
            }
            if (err && err.status === 401) {
              window.location.href = '/login';
            }
          });
      }
    })();

    // ============================
    // RESERVED CARD COUNTDOWN (HH:MM:SS)
    // ============================
    (function initReservedCountdowns(){
      function pad(n){ return n.toString().padStart(2, '0'); }
      function tick(){
        const now = Date.now();
        document.querySelectorAll('[data-reserved-until]').forEach(el => {
          const until = Date.parse(el.getAttribute('data-reserved-until'));
          const target = el.querySelector('[data-countdown]');
          if (!target || isNaN(until)) return;
          let diff = Math.max(0, Math.floor((until - now) / 1000));
          const h = Math.floor(diff / 3600);
          const m = Math.floor((diff % 3600) / 60);
          const s = diff % 60;
          target.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
          if (diff === 0) el.removeAttribute('data-reserved-until');
        });
      }
      tick();
      setInterval(tick, 1000);
    })();

    // ============================
    // GRID / LIST / PLAN TOGGLE
    // ============================
    // Posiciona la barra activa midiendo el botón seleccionado (grid/list).
    // Independiente del idioma: funciona con "Grid"/"Cuadrícula" o cualquier texto.
    function positionToggleBg() {
      const toggle = document.querySelector('.fg-toggle');
      if (!toggle) return;
      const bg = toggle.querySelector('.fg-toggle-bg-active');
      const active = toggle.querySelector('button[data-view].active');
      if (!bg || !active) return;
      bg.style.left  = active.offsetLeft + 'px';
      bg.style.width = active.offsetWidth + 'px';
    }
    window.addEventListener('resize', positionToggleBg);

    function setViewMode(view) {
      const allButtons = document.querySelectorAll('.fg-toggle button[data-view], .fg-location-btn[data-view]');
      allButtons.forEach(b => {
        const isActive = b.dataset.view === view;
        b.classList.toggle('active', isActive);
        b.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      const toggle = document.querySelector('.fg-toggle');
      if (toggle) {
        toggle.classList.toggle('list-active', view === 'list');
        toggle.classList.toggle('plan-active', view === 'plan');
      }
      // Recoloca la barra activa según el botón ahora seleccionado (grid/list).
      if (view === 'grid' || view === 'list') positionToggleBg();
      // Drive show/hide via body[data-view] for grid/list/plan
      document.body.setAttribute('data-view', view);
      // Reflect view in URL so the share link preserves grid/list/plan choice.
      const params = new URLSearchParams(window.location.search);
      if (view === 'grid') params.delete('view');
      else params.set('view', view);
      const qs = params.toString();
      window.history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : ''));
    }

    document.querySelectorAll('.fg-toggle button[data-view], .fg-location-btn[data-view]').forEach(btn => {
      btn.addEventListener('click', function() {
        setViewMode(this.dataset.view);
      });
    });

    // Posición inicial de la barra activa (tras render y carga de fuentes,
    // para que la medida del botón sea exacta con cualquier idioma).
    positionToggleBg();
    window.addEventListener('load', positionToggleBg);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(positionToggleBg);

    // List view tabs — delegate to the unified filter so status tab + filters compose.
    function setListTab(btn) {
      document.querySelectorAll('.fg-list-tab').forEach(t => t.classList.remove('active'));
      btn.classList.add('active');
      if (typeof applyFilters === 'function') applyFilters();
    }
    function filterListRows(q) {
      currentFilters.unitNumber = (q || '').trim();
      // Also reflect into the grid search input so both stay in sync.
      const gridInput = document.querySelector('input[placeholder="Unit No."]');
      if (gridInput) gridInput.value = currentFilters.unitNumber;
      if (typeof applyFilters === 'function') applyFilters();
    }

    // "View Similar Units" — on a sold card, filter the grid to other available
    // units that match the sold one's profile (same bedrooms; same floor or
    // direction when present). Hides the sold/reserved/pending ones.
    window.viewSimilarUnits = function (btn) {
      const card = btn.closest('.fg-card');
      if (!card) return;
      const beds   = parseInt(card.dataset.filterBedrooms || '0', 10);
      const dir    = (card.dataset.filterDirection || '').toUpperCase();
      const floor  = (card.dataset.filterFloor || '');
      const type   = (card.dataset.filterType || '');
      const refUnit= (card.dataset.filterUnit || '');

      // Reset to a clean state, then apply the similarity criteria.
      resetFilters();
      const typeLbl = (type === 'Studio' || type === 'Penthouse')
        ? type
        : (beds >= 1 && beds <= 3 ? beds + ' Bed' : null);
      if (typeLbl) {
        currentFilters.types = [typeLbl];
        document.querySelectorAll('#typeDropdown input[type="checkbox"]').forEach(cb => {
          cb.checked = (cb.value === typeLbl);
        });
        updateTypeLabel();
      }

      // Finding *every* similar unit needs the full catalog painted first.
      ensureAllLoaded().then(() => {
        // Custom matcher: bedrooms must match; exclude sold/reserved/pending and
        // exclude the reference unit itself.
        const cards = Array.from(document.querySelectorAll('.fg-units-grid > .fg-card'));
        let visible = 0;
        cards.forEach(c => {
          const cBeds   = parseInt(c.dataset.filterBedrooms || '0', 10);
          const cStatus = (c.dataset.filterStatus || '').toLowerCase();
          const cUnit   = (c.dataset.filterUnit || '');
          const show = (cBeds === beds) && !['sold','reserved','pending'].includes(cStatus) && cUnit !== refUnit;
          animateToggle(c, show);
          if (show) visible++;
        });
        // Also apply on the list view in case the user toggles.
        const rows = Array.from(document.querySelectorAll('#fgListTable tbody tr[data-filter-unit]'));
        let listVisible = 0;
        rows.forEach(r => {
          const rBeds   = parseInt(r.dataset.filterBedrooms || '0', 10);
          const rStatus = (r.dataset.filterStatus || '').toLowerCase();
          const rUnit   = (r.dataset.filterUnit || '');
          const show = (rBeds === beds) && !['sold','reserved','pending'].includes(rStatus) && rUnit !== refUnit;
          animateToggle(r, show, { kind: 'row' });
          if (show) listVisible++;
        });
        updateMatchCount(visible);
        updateListMatchCount(listVisible);

        // Smooth scroll to the grid so the user sees the result.
        const grid = document.querySelector('.fg-units-grid');
        if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // No matches → fall back to opening the original unit modal so the user
        // can still see why it was sold.
        if (visible === 0 && typeof openMoreInfo === 'function') openMoreInfo(refUnit);
      });
    };
  </script>

@include('partials.logout-modal')
@include('partials.confirm-dialog')
@include('partials.profile-modal')
</body>

</html>
