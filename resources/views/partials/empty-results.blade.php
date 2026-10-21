{{--
    Qué se ve cuando una búsqueda o una combinación de filtros no devuelve nada.

    Antes no se veía nada: el contador decía «Mostrando 0 de 233» y debajo
    quedaba la página en blanco, sin explicar qué había pasado ni cómo volver.

    Dos casos, porque la salida es distinta:
      · Se buscó un número de unidad que no existe → se ofrece volver al último
        resultado bueno o limpiar la búsqueda.
      · Los filtros son demasiado estrictos → se listan como fichas que se
        quitan de a una, además de quitarlos todos.

    Las sugerencias no son de adorno: se piden al servidor relajando un filtro
    cada vez y se muestra el primero que devuelve algo, diciendo cuál se soltó.
--}}
@php
    // Etiquetas legibles para armar las fichas de filtro activo.
    $erTypeLabels    = \App\Support\UnitOptions::map('types');
    $erOutlookLabels = \App\Support\UnitOptions::map('outlooks');
@endphp

<style>
  .fg-empty { display: none; }
  body.has-empty-results .fg-empty { display: block; }
  /* Con el estado vacío a la vista sobran el cargador y el hueco de la rejilla. */
  body.has-empty-results #gridLazyMore,
  body.has-empty-results #listLazyMore { display: none !important; }

  .fg-empty-card {
    max-width: 760px;
    margin: 16px auto 48px;
    padding: 40px 32px;
    background: #ffffff;
    border: 1px solid #e3e0d7;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(32, 48, 42, 0.07);
    text-align: center;
  }

  .fg-empty-ill {
    position: relative;
    width: 92px; height: 92px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: var(--brand-soft);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .fg-empty-ill svg { width: 44px; height: 44px; stroke: var(--brand); stroke-width: 1.4; fill: none; }
  .fg-empty-zero {
    position: absolute;
    right: -4px; bottom: -4px;
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 3px solid #ffffff;
    background: #c08a2e;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .fg-empty-card h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    line-height: 1.15;
    color: var(--brand);
  }
  .fg-empty-msg {
    margin: 10px auto 0;
    max-width: 440px;
    font-size: 14.5px;
    line-height: 1.55;
    color: #6d7b72;
  }
  .fg-empty-msg b {
    padding: 1px 7px;
    border-radius: 6px;
    background: #fbf1dc;
    color: #20302a;
    font-weight: 600;
  }

  /* `display` gana al atributo hidden, así que hay que decirlo explícitamente. */
  .fg-empty [hidden] { display: none !important; }
  .fg-empty-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 22px; }
  .fg-empty-chips-label { width: 100%; margin-bottom: -4px; font-size: 11px; color: #6d7b72; }
  .fg-empty-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 10px 7px 13px;
    border: 1px solid var(--brand-soft-3);
    border-radius: 30px;
    background: var(--brand-soft);
    color: var(--brand);
    font: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }
  .fg-empty-chip:hover { background: #ffffff; }
  .fg-empty-chip .x {
    width: 17px; height: 17px;
    border-radius: 50%;
    background: var(--brand);
    color: #ffffff;
    font-size: 11px;
    line-height: 17px;
  }

  .fg-empty-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 24px; }
  .fg-empty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 22px;
    border: none;
    border-radius: 12px;
    font: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s, filter 0.2s;
  }
  .fg-empty-btn svg { width: 17px; height: 17px; stroke-width: 2; fill: none; }
  .fg-empty-btn--primary { background: var(--brand); color: #ffffff; }
  .fg-empty-btn--primary svg { stroke: #ffffff; }
  .fg-empty-btn--primary:hover { filter: brightness(1.15); }
  .fg-empty-btn--ghost { background: #ffffff; color: var(--brand); border: 1.5px solid var(--brand-soft-3); }
  .fg-empty-btn--ghost svg { stroke: var(--brand); }
  .fg-empty-btn--ghost:hover { border-color: var(--brand); }

  .fg-empty-sugg { margin-top: 28px; padding-top: 24px; border-top: 1px solid #e3e0d7; text-align: left; }
  .fg-empty-sugg-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    font-size: 13px;
    font-weight: 700;
    color: #20302a;
  }
  .fg-empty-sugg-head svg { width: 16px; height: 16px; stroke: #c08a2e; stroke-width: 2; fill: none; }
  .fg-empty-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .fg-empty-mini {
    overflow: hidden;
    border: 1px solid #e3e0d7;
    border-radius: 14px;
    background: #f7f5f0;
    text-align: left;
    cursor: pointer;
    font: inherit;
    padding: 0;
    transition: transform 0.2s;
  }
  .fg-empty-mini:hover { transform: translateY(-3px); }
  .fg-empty-mini img { display: block; width: 100%; height: 92px; object-fit: cover; }
  .fg-empty-mini-b { display: block; padding: 10px 12px; }
  .fg-empty-mini-t { display: block; font-size: 15px; font-weight: 600; line-height: 1.25; color: var(--brand); }
  .fg-empty-mini-p { display: block; margin-top: 3px; font-size: 13px; font-weight: 700; color: #20302a; }
  .fg-empty-mini-w {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 7px;
    border-radius: 12px;
    background: #ffffff;
    font-size: 10.5px;
    color: #6d7b72;
  }

  .fg-empty-lead {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    margin-top: 26px;
    padding: 18px 20px;
    border-radius: 16px;
    background: var(--brand);
    text-align: left;
  }
  .fg-empty-lead-t { flex: 1; min-width: 180px; }
  .fg-empty-lead-t .h { color: #ffffff; font-size: 14px; font-weight: 700; }
  .fg-empty-lead-t .s { margin-top: 2px; font-size: 12px; color: rgba(255, 255, 255, 0.72); }
  .fg-empty-lead a {
    padding: 12px 18px;
    border-radius: 10px;
    background: #ffffff;
    color: var(--brand);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
  }
  .fg-empty-lead a:hover { filter: brightness(0.95); }

  @media (max-width: 560px) {
    .fg-empty-card { margin: 12px 16px 32px; padding: 32px 20px; }
    .fg-empty-cards { grid-template-columns: 1fr 1fr; }
    .fg-empty-actions { flex-direction: column; }
    .fg-empty-btn { width: 100%; }
  }
</style>

<div class="fg-empty" id="fgEmpty" role="status" aria-live="polite">
  <div class="fg-empty-card">
    <div class="fg-empty-ill">
      <svg id="fgEmptyIcon" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/>
      </svg>
      <span class="fg-empty-zero">0</span>
    </div>

    <h2 id="fgEmptyTitle"></h2>
    <p class="fg-empty-msg" id="fgEmptyMsg"></p>

    <div class="fg-empty-chips" id="fgEmptyChips" hidden>
      <span class="fg-empty-chips-label">{{ __('Toca un filtro para quitarlo:') }}</span>
    </div>

    <div class="fg-empty-actions" id="fgEmptyActions"></div>

    <div class="fg-empty-sugg" id="fgEmptySugg" hidden>
      <div class="fg-empty-sugg-head">
        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
        <span id="fgEmptySuggHead"></span>
      </div>
      <div class="fg-empty-cards" id="fgEmptyCards"></div>
    </div>

    <div class="fg-empty-lead">
      <div class="fg-empty-lead-t">
        <div class="h">{{ __('¿No encuentras lo que buscas?') }}</div>
        <div class="s">{{ __('Cuéntanos qué estás buscando y te avisamos cuando haya algo que encaje.') }}</div>
      </div>
      @php $waNum = preg_replace('/\D+/', '', (string) config('company.whatsapp')); @endphp
      @if($waNum)
        <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener">{{ __('Hablar con un asesor') }}</a>
      @else
        <a aria-disabled="true" style="opacity:.45;cursor:not-allowed;pointer-events:none;">{{ __('Hablar con un asesor') }}</a>
      @endif
    </div>
  </div>
</div>

<script>
(function () {
  var box = document.getElementById('fgEmpty');
  if (!box) return;

  var T = {
    noUnit:      @json(__('No encontramos la unidad :q')),
    noUnitMsg:   @json(__('No hay ninguna que coincida con :q. Puede que ese número no exista aquí, o que esté escrito de otra forma.')),
    noMatch:     @json(__('Nada cumple todos los filtros')),
    noMatchMsg:  @json(__('La combinación que has elegido es muy específica. Prueba a quitar un filtro para ver más opciones.')),
    back:        @json(__('Volver a los resultados')),
    clearSearch: @json(__('Limpiar búsqueda')),
    clearAll:    @json(__('Quitar todos los filtros')),
    suggSearch:  @json(__('¿Quizá buscabas alguna de estas?')),
    suggDrop:    @json(__('Lo más parecido, quitando :filter')),
    fPrice:      @json(__('precio')),
    fType:       @json(__('tipo')),
    fFloor:      @json(__('piso')),
    fDir:        @json(__('orientación')),
    fOut:        @json(__('vista')),
  };

  var TYPE_LABELS    = @json($erTypeLabels);
  var OUTLOOK_LABELS = @json($erOutlookLabels);

  // Último estado de filtros que sí devolvió resultados, para el botón «volver».
  var lastGood = null;

  function esc(s) {
    return String(s).replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }
  function money(n) {
    return '$' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  }

  // Filtros activos de la barra, ya con etiqueta legible y con la forma de
  // deshacerlos. El modelo de villa no entra: no es un filtro de la barra.
  function activeFilters() {
    var f = currentFilters, out = [];
    if (f.minPrice != null || f.maxPrice != null) {
      var label = (f.minPrice != null && f.maxPrice != null)
        ? money(f.minPrice) + ' – ' + money(f.maxPrice)
        : (f.minPrice != null ? '≥ ' + money(f.minPrice) : '≤ ' + money(f.maxPrice));
      out.push({
        key: 'price', name: T.fPrice, label: label,
        strip: function (f2) { f2.minPrice = null; f2.maxPrice = null; },
        clear: function () {
          currentFilters.minPrice = null; currentFilters.maxPrice = null;
          var a = document.getElementById('minPrice'), b = document.getElementById('maxPrice');
          if (a) a.value = ''; if (b) b.value = '';
        }
      });
    }
    [['types', 'typeDropdown', T.fType, TYPE_LABELS],
     ['floors', 'floorDropdown', T.fFloor, null],
     ['directions', 'directionDropdown', T.fDir, null],
     ['outlooks', 'outlookDropdown', T.fOut, OUTLOOK_LABELS]
    ].forEach(function (spec) {
      var field = spec[0], dropdown = spec[1], name = spec[2], labels = spec[3];
      (f[field] || []).forEach(function (value) {
        out.push({
          key: field + ':' + value,
          name: name,
          label: (labels && labels[value]) || value,
          strip: function (f2) {
            f2[field] = (f2[field] || []).filter(function (v) { return v !== value; });
          },
          clear: function () {
            currentFilters[field] = currentFilters[field].filter(function (v) { return v !== value; });
            document.querySelectorAll('#' + dropdown + ' input[type="checkbox"]').forEach(function (cb) {
              if (cb.value === value) cb.checked = false;
            });
          }
        });
      });
    });
    return out;
  }

  function button(cls, icon, text, onClick) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'fg-empty-btn ' + cls;
    b.innerHTML = icon + '<span>' + esc(text) + '</span>';
    b.addEventListener('click', onClick);
    return b;
  }

  var IC_BACK  = '<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>';
  var IC_RESET = '<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>';

  // Sugerencias: se pide al servidor soltando un filtro cada vez y se queda la
  // primera tanda que devuelva algo.
  function loadSuggestions(drops) {
    var sugg = document.getElementById('fgEmptySugg');
    var cards = document.getElementById('fgEmptyCards');
    sugg.hidden = true;
    cards.innerHTML = '';

    (function next(i) {
      if (i >= drops.length) return;
      var d = drops[i];
      // Se arma la consulta con una copia modificada y se deja el estado real
      // intacto: tantear no debe cambiar lo que el usuario tiene puesto.
      var saved   = currentFilters;
      var probe   = JSON.parse(JSON.stringify(currentFilters));
      d.strip(probe);
      currentFilters = probe;
      var qs = buildUnitParams(0);
      currentFilters = saved;

      fetch('{{ route('home.units') }}?' + qs)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.total) return next(i + 1);
          var doc = new DOMParser().parseFromString('<div>' + data.cards + '</div>', 'text/html');
          var found = Array.prototype.slice.call(doc.querySelectorAll('[data-filter-unit]')).slice(0, 3);
          if (!found.length) return next(i + 1);

          document.getElementById('fgEmptySuggHead').textContent = d.head;
          found.forEach(function (card) {
            var id    = card.getAttribute('data-filter-unit');
            var name  = (card.querySelector('.fg-card-title-row .name') || {}).textContent || id;
            var price = (card.querySelector('.fg-card-price .price') || {}).textContent || '';
            var note  = (card.querySelector('.fg-card-subtitle') || {}).textContent || '';
            var img   = card.querySelector('.fg-card-img img');

            var el = document.createElement('button');
            el.type = 'button';
            el.className = 'fg-empty-mini';
            el.innerHTML =
              (img ? '<img src="' + esc(img.getAttribute('src')) + '" alt="" loading="lazy">' : '') +
              '<span class="fg-empty-mini-b">' +
                '<span class="fg-empty-mini-t">' + esc(name.trim()) + '</span>' +
                '<span class="fg-empty-mini-p">' + esc(price.trim()) + '</span>' +
                (note.trim() ? '<span class="fg-empty-mini-w">' + esc(note.replace(/\s+/g, ' ').trim()) + '</span>' : '') +
              '</span>';
            el.addEventListener('click', function () { openMoreInfo(id); });
            cards.appendChild(el);
          });
          sugg.hidden = false;
        })
        .catch(function () { next(i + 1); });
    })(0);
  }

  // Punto de entrada: lo llama reloadUnits con el total que devolvió el servidor.
  window.renderEmptyResults = function (total) {
    if (total > 0) {
      lastGood = JSON.parse(JSON.stringify(currentFilters));
      document.body.classList.remove('has-empty-results');
      return;
    }
    document.body.classList.add('has-empty-results');

    var q = (currentFilters.unitNumber || '').trim();
    var chipsBox = document.getElementById('fgEmptyChips');
    var actions  = document.getElementById('fgEmptyActions');
    actions.innerHTML = '';
    chipsBox.hidden = true;
    Array.prototype.slice.call(chipsBox.querySelectorAll('.fg-empty-chip')).forEach(function (c) { c.remove(); });

    if (q) {
      // ── Se buscó un número que no existe ────────────────────────────────
      document.getElementById('fgEmptyTitle').textContent = T.noUnit.replace(':q', '“' + q + '”');
      document.getElementById('fgEmptyMsg').innerHTML = esc(T.noUnitMsg).replace(':q', '<b>' + esc(q) + '</b>');

      if (lastGood) {
        actions.appendChild(button('fg-empty-btn--primary', IC_BACK, T.back, function () {
          currentFilters = JSON.parse(JSON.stringify(lastGood));
          var input = document.getElementById('gridUnitSearch');
          if (input) input.value = currentFilters.unitNumber || '';
          reloadUnits();
        }));
      }
      actions.appendChild(button(lastGood ? 'fg-empty-btn--ghost' : 'fg-empty-btn--primary', IC_RESET, T.clearSearch, function () {
        currentFilters.unitNumber = '';
        var input = document.getElementById('gridUnitSearch');
        if (input) { input.value = ''; var b = input.closest('.fg-search'); if (b) b.classList.remove('is-active'); }
        applyFilters();
      }));

      loadSuggestions([{ head: T.suggSearch, strip: function (f2) { f2.unitNumber = ''; } }]);
      return;
    }

    // ── Los filtros son demasiado estrictos ───────────────────────────────
    document.getElementById('fgEmptyTitle').textContent = T.noMatch;
    document.getElementById('fgEmptyMsg').textContent = T.noMatchMsg;

    var actives = activeFilters();
    actives.forEach(function (f) {
      var chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'fg-empty-chip';
      chip.innerHTML = esc(f.name + ': ' + f.label) + '<span class="x">×</span>';
      chip.addEventListener('click', function () { f.clear(); applyFilters(); });
      chipsBox.appendChild(chip);
    });
    chipsBox.hidden = actives.length === 0;

    actions.appendChild(button('fg-empty-btn--primary', IC_RESET, T.clearAll, function () {
      resetFiltersState();
      applyFilters();
    }));
    if (lastGood) {
      actions.appendChild(button('fg-empty-btn--ghost', IC_BACK, T.back, function () {
        currentFilters = JSON.parse(JSON.stringify(lastGood));
        reloadUnits();
      }));
    }

    loadSuggestions(actives.map(function (f) {
      return { head: T.suggDrop.replace(':filter', '«' + f.name + '»'), strip: f.strip };
    }));
  };
})();
</script>
