{{--
    Simulador del plan de pago, dentro de la ficha de la unidad.

    Dos piezas: un adelanto junto al precio («desde $X/mes durante la obra»),
    siempre visible, y el simulador completo, que se abre al tocarlo.

    Los porcentajes NO están escritos aquí: salen de los planes que el proyecto
    ya firmó (ver PaymentPlanModel). Si todavía no hay planes de los que
    deducir, este partial no pinta nada — es el caso hoy de Bahía Mar y de
    Siebe, que no tienen ninguna reserva con plan.

    Lo que el simulador NO dice, porque no hay de dónde sacarlo: la fecha de
    entrega. La muestra traía «hasta entrega Q4 2027» y eso no es un dato que
    tengamos en ninguna parte.
--}}
@php
    $planRules = \App\Support\PaymentPlanModel::rules();
@endphp

@if($planRules)
<style>
  /* Adelanto de la cuota, junto al precio */
  .ps-teaser {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    margin-top: 13px;
    padding: 11px 14px;
    border: 1px solid var(--brand-soft-3);
    border-radius: 12px;
    background: var(--brand-soft);
    font: inherit;
    text-align: left;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
  }
  .ps-teaser:hover { background: #ffffff; border-color: var(--brand); }
  .ps-teaser-ic {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px; height: 36px;
    border-radius: 9px;
    background: var(--brand);
  }
  .ps-teaser-ic svg { width: 18px; height: 18px; stroke: #fff; stroke-width: 2; fill: none; }
  .ps-teaser-txt { flex: 1; min-width: 0; }
  .ps-teaser-k { font-size: 10.5px; font-weight: 600; color: #6d7b72; }
  .ps-teaser-v { font-size: 19px; font-weight: 700; line-height: 1.1; color: var(--brand); }
  .ps-teaser-v small { font-size: 11.5px; font-weight: 400; color: #6d7b72; }
  .ps-teaser-go {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    color: var(--brand);
  }
  .ps-teaser-go svg { width: 14px; height: 14px; stroke: var(--brand); stroke-width: 2; fill: none; }

  /* Simulador */
  .ps-acc { margin-top: 12px; border: 1px solid var(--brand-soft-3); border-radius: 12px; overflow: hidden; }
  .ps-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    padding: 13px 15px;
    border: none;
    background: none;
    font: inherit;
    cursor: pointer;
  }
  .ps-hd-t { display: flex; align-items: center; gap: 9px; font-size: 13.5px; font-weight: 700; color: #20302a; }
  .ps-hd-t svg { width: 16px; height: 16px; stroke: var(--brand); stroke-width: 2; fill: none; }
  .ps-hd-r { display: flex; align-items: center; gap: 8px; }
  .ps-hd-v { font-size: 12.5px; font-weight: 700; color: var(--brand); }
  .ps-chev { width: 15px; height: 15px; stroke: #6d7b72; stroke-width: 2; fill: none; transition: transform 0.2s; }
  .ps-acc.is-open .ps-chev { transform: rotate(180deg); }
  .ps-body { display: none; padding: 0 15px 15px; }
  .ps-acc.is-open .ps-body { display: block; }

  .ps-ctrl { margin-bottom: 14px; }
  .ps-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .ps-l { font-size: 12.5px; font-weight: 600; color: #20302a; }
  .ps-v { font-size: 13px; font-weight: 700; color: var(--brand); }
  .ps-s { font-size: 11px; color: #6d7b72; }
  .ps-ctrl input[type="range"] { width: 100%; margin: 7px 0 0; accent-color: var(--brand); }

  .ps-break { border: 1px solid #e6e3da; border-radius: 10px; overflow: hidden; }
  .ps-break-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 12px;
    font-size: 12.5px;
  }
  .ps-break-row + .ps-break-row { border-top: 1px solid #f0eee8; }
  .ps-break-k { color: #6d7b72; }
  .ps-break-v { font-weight: 700; color: #20302a; }

  .ps-result {
    margin-top: 12px;
    padding: 14px;
    border-radius: 10px;
    background: var(--brand);
    color: #ffffff;
    text-align: center;
  }
  .ps-result-k { font-size: 11px; font-weight: 600; opacity: 0.8; }
  .ps-result-v { font-size: 26px; font-weight: 700; line-height: 1.15; }
  .ps-result-v small { font-size: 13px; font-weight: 400; opacity: 0.85; }
  .ps-result-s { margin-top: 2px; font-size: 11.5px; opacity: 0.85; }

  .ps-disc { margin: 10px 0 0; font-size: 10.5px; line-height: 1.5; color: #6d7b72; }
</style>

<button type="button" class="ps-teaser" id="psTeaser" onclick="psAbrir()">
  <span class="ps-teaser-ic">
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9a3 3 0 0 0 0 6h6a3 3 0 0 1 0 6H7"/></svg>
  </span>
  <span class="ps-teaser-txt">
    <span class="ps-teaser-k">{{ __('Desde') }}</span>
    <span class="ps-teaser-v" id="psTeaserVal">—</span>
  </span>
  <span class="ps-teaser-go">{{ __('Simular pago') }}
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
  </span>
</button>

<div class="ps-acc" id="psAcc">
  <button type="button" class="ps-hd" onclick="document.getElementById('psAcc').classList.toggle('is-open')">
    <span class="ps-hd-t">
      <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h5"/></svg>
      {{ __('Simular mi plan de pago') }}
    </span>
    <span class="ps-hd-r">
      <span class="ps-hd-v" id="psAccVal">—</span>
      <svg class="ps-chev" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </span>
  </button>

  <div class="ps-body">
    <div class="ps-ctrl">
      <div class="ps-row">
        <span class="ps-l">{{ __('Entrada inicial') }}</span>
        <span class="ps-v" id="psEntradaPct">—</span>
      </div>
      <input type="range" id="psEntrada" min="{{ $planRules['entrada_min'] }}" max="{{ $planRules['entrada_max'] }}" step="1" value="{{ $planRules['entrada_tipica'] }}">
      <div class="ps-row">
        <span class="ps-s">{{ __('Mínimo :n%', ['n' => $planRules['entrada_min']]) }}</span>
        <span class="ps-s" id="psEntradaMonto">—</span>
      </div>
    </div>

    <div class="ps-ctrl">
      <div class="ps-row">
        <span class="ps-l">{{ __('Cuotas durante la obra') }}</span>
        <span class="ps-v" id="psCuotasVal">—</span>
      </div>
      <input type="range" id="psCuotas" min="{{ $planRules['cuotas_min'] }}" max="{{ $planRules['cuotas_max'] }}" step="1" value="{{ $planRules['cuotas_tipicas'] }}">
      <div class="ps-row">
        <span class="ps-s">{{ __('Entre :min y :max cuotas', ['min' => $planRules['cuotas_min'], 'max' => $planRules['cuotas_max']]) }}</span>
        <span class="ps-s">{{ __('Sin intereses') }}</span>
      </div>
    </div>

    <div class="ps-break">
      <div class="ps-break-row"><span class="ps-break-k">{{ __('Reserva') }}</span><span class="ps-break-v" id="psReserva">—</span></div>
      <div class="ps-break-row"><span class="ps-break-k">{{ __('Separación al firmar') }}</span><span class="ps-break-v" id="psSeparacion">—</span></div>
      <div class="ps-break-row"><span class="ps-break-k" id="psObraK">{{ __('Cuotas en obra') }}</span><span class="ps-break-v" id="psObra">—</span></div>
      <div class="ps-break-row"><span class="ps-break-k">{{ __('Contra entrega') }}</span><span class="ps-break-v" id="psEntrega">—</span></div>
    </div>

    <div class="ps-result">
      <div class="ps-result-k">{{ __('Tu cuota mensual') }}</div>
      <div class="ps-result-v" id="psCuota">—</div>
      <div class="ps-result-s" id="psCuotaDesc">—</div>
    </div>

    <p class="ps-disc">
      {{ __('Simulación referencial, sin intereses durante la obra. El reparto sale de los :n planes de pago ya firmados en el proyecto; el tuyo se acuerda al reservar. No es una oferta ni una aprobación de financiamiento.', ['n' => $planRules['planes']]) }}
    </p>
  </div>
</div>

<script>
(function () {
  // Reparto deducido de los planes firmados (ver PaymentPlanModel).
  const REGLAS = @json($planRules);

  const $ = id => document.getElementById(id);
  const dinero = n => '$' + Math.round(n).toLocaleString('en-US');

  // El precio lo pone el modal al abrir cada unidad.
  let precio = 0;

  function calcular() {
    if (!precio) return;
    const entradaPct = +$('psEntrada').value / 100;
    const meses      = +$('psCuotas').value;

    const entrada = precio * entradaPct;
    const entrega = precio * REGLAS.entrega;
    // La obra es lo que queda: así los cuatro tramos suman siempre el precio,
    // aunque las medianas por separado no sumen exactamente 100%.
    const obra    = Math.max(0, precio - entrada - entrega);
    const reserva = Math.min(REGLAS.reserva_monto, entrada);
    const cuota   = meses > 0 ? obra / meses : 0;

    $('psEntradaPct').textContent   = Math.round(entradaPct * 100) + '%';
    $('psEntradaMonto').textContent = dinero(entrada);
    $('psCuotasVal').textContent    = @json(__(':n cuotas')).replace(':n', meses);
    $('psReserva').textContent      = dinero(reserva);
    $('psSeparacion').textContent   = dinero(entrada - reserva);
    $('psObraK').textContent        = @json(__(':n cuotas en obra')).replace(':n', meses);
    $('psObra').textContent         = dinero(obra);
    $('psEntrega').textContent      = dinero(entrega);
    $('psCuota').innerHTML          = dinero(cuota) + '<small>' + @json(__('/mes')) + '</small>';
    $('psCuotaDesc').textContent    = @json(__(':n cuotas sin intereses durante la obra')).replace(':n', meses);

    const resumen = dinero(cuota) + @json(__('/mes'));
    $('psAccVal').textContent  = resumen;
    $('psTeaserVal').innerHTML = dinero(cuota) + '<small>' + @json(__('/mes durante la obra')) + '</small>';
  }

  // Lo llama el modal con el precio de la unidad que acaba de abrir.
  window.psPrecio = function (p) {
    precio = Number(p) || 0;
    const caja = $('psTeaser');
    const acc  = $('psAcc');
    const hay  = precio > 0;
    if (caja) caja.style.display = hay ? '' : 'none';
    if (acc)  acc.style.display  = hay ? '' : 'none';
    if (hay) calcular();
  };

  window.psAbrir = function () {
    const acc = $('psAcc');
    if (!acc) return;
    acc.classList.add('is-open');
    acc.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  ['psEntrada', 'psCuotas'].forEach(id => {
    const el = $(id);
    if (el) el.addEventListener('input', calcular);
  });
})();
</script>
@endif
