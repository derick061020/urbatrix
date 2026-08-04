{{--
  Banda de portada de un TIPO de villa (Figma: panel "Villa Coral").
  ---------------------------------------------------------------------------
  La home ya no lista villa por villa: lista los modelos. Cada banda parte la
  pantalla en dos mitades — foto a sangre y panel claro — y el panel muestra
  exactamente cuatro cosas, en este orden:

     1. eyebrow  → fase o zona           ("FRENTE A PLAYA", "FASE 4")
     2. nombre   → nombre del modelo     ("Villa Coral")
     3. specs    → "2 habitaciones · 240 m² · Piscina privada"
     4. CTAs     → "Ver villas" (abre el grid del modelo) + "Agendar videollamada"

  El precio "desde" se lee como chip blanco sobre la foto y el número de orden
  queda de fondo, detrás del panel.

  Variables: $type (objeto de HomeController::villaTypes()), $loop.
--}}
@php
  $specs = array_values(array_filter([
      $type->bedrooms ? $type->bedrooms.' '.__('habitaciones') : null,
      $type->internal_area > 0 ? number_format($type->internal_area, 0).' m²' : null,
      $type->pools > 0 ? __('Piscina privada') : null,
  ]));
@endphp
<article class="fg-type-band" data-model="{{ $type->key }}" data-model-name="{{ $type->name }}">
  <div class="fg-card-inner">

    <!-- Número de orden de fondo -->
    <div class="fg-band-num" aria-hidden="true">{{ str_pad((string) ($loop->iteration ?? 0), 2, '0', STR_PAD_LEFT) }}</div>

    <!-- Mitad foto -->
    <div class="fg-card-img" onclick="openVillaType('{{ $type->key }}')" style="cursor:pointer">
      @if($type->image)
        <img src="{{ $type->image }}" alt="{{ $type->name }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
      @else
        <div class="fg-card-img-noimage">{{ __('No Image Available') }}</div>
      @endif
    </div>

    <!-- Chip de precio "desde" sobre la foto -->
    @if($type->price_from > 0)
      <div class="fg-card-price">
        <span class="price" data-usd="{{ $type->price_from }}">${{ number_format($type->price_from, 0, ' ', ' ') }}</span>
      </div>
    @endif

    <!-- Panel -->
    <div class="fg-card-body">
      @if($type->eyebrow)
        <div class="fg-card-subtitle">{{ $type->eyebrow }}</div>
      @endif
      <div class="fg-card-title-row"><span class="name">{{ $type->name }}</span></div>
      @if($specs)
        <div class="fg-card-specs">{{ implode(' · ', $specs) }}</div>
      @endif
    </div>

    <div class="fg-card-actions">
      <div class="fg-card-buttons">
        <button class="fg-btn-info" type="button" onclick="openVillaType('{{ $type->key }}')">{{ __('Ver villas') }}</button>
        <button class="fg-btn-cta" type="button" onclick="openAdvisorVideoCall()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
          </svg>
          {{ __('Book Video Call') }}
        </button>
      </div>
    </div>

  </div>
</article>
