        @php
          $st = strtolower($unit->status ?? '');
          $isSold      = $st === 'sold';
          $isReserved  = $st === 'reserved';
          $isPending   = $st === 'pending';
          $isHighDem   = !empty($unit->is_high_demand) || ($unit->demand_level ?? '') === 'high';
          $isSecond    = !empty($unit->is_second_chance);
          $hasDiscount = !empty($unit->discount) && $unit->discount > 0;
          $cardCls = 'fg-card';
          if ($isSold)         $cardCls .= ' is-sold';
          elseif ($isReserved) $cardCls .= ' is-reserved';
          elseif ($isPending)  $cardCls .= ' is-pending';
          elseif ($isSecond)   $cardCls .= ' is-second-chance';
          elseif ($isHighDem)  $cardCls .= ' is-high-demand';
          $unitId = $unit->custom_id ?? $unit->id;
          $shortlistedCount = (int) ($unit->shortlisted_count ?? 0);
        @endphp
        @php
          $beds = (int) ($unit->bedrooms ?? 0);
          if (!empty($unit->type) && strcasecmp($unit->type, 'Penthouse') === 0) {
              $unitTypeLbl = 'Penthouse';
          } elseif ($beds === 0) {
              $unitTypeLbl = 'Studio';
          } else {
              $unitTypeLbl = $beds . ' Bed';
          }
          $floorRaw = trim((string) ($unit->floor ?? ''));
          $floorNorm = ($floorRaw === '' || strcasecmp($floorRaw, 'ground') === 0) ? 'Ground' : $floorRaw;
          $searchBlob = strtolower(implode(' ', array_filter([
              $unitId, $unit->name, $unit->floor, $unit->direction,
              $unit->outlook, $unit->type, $beds.' bed',
          ])));
        @endphp
        <!--- unit - start  ---->
        <div class="{{ $cardCls }}"
             data-filter-unit="{{ $unitId }}"
             data-filter-name="{{ strtolower($unit->name ?? $unitId) }}"
             data-filter-search="{{ $searchBlob }}"
             data-filter-floor="{{ $unit->floor ?? '' }}"
             data-filter-type="{{ $unit->type ?? '' }}"
             data-filter-bedrooms="{{ $beds }}"
             data-filter-direction="{{ strtoupper($unit->direction ?? '') }}"
             data-filter-outlook="{{ $unit->outlook ?? '' }}"
             data-filter-price="{{ (float) $unit->price }}"
             data-filter-area="{{ (float) ($unit->internal_area ?? 0) }}"
             data-filter-status="{{ $st }}"
             data-filter-second="{{ !empty($unit->is_second_chance) ? '1' : '0' }}">
          <div class="fg-card-inner">

            <!-- Image area -->
            <div class="fg-card-img">
              @if($unit->images->isNotEmpty())
                <img src="{{ $unit->images->first()->path }}" alt="{{ $unitId }}" loading="lazy" decoding="async" onerror="this.style.display='none'" onclick="openMoreInfo('{{ $unitId }}')" style="cursor:pointer">
              @else
                <div class="fg-card-img-noimage" onclick="openMoreInfo('{{ $unitId }}')" style="cursor:pointer">{{ __('No Image Available') }}</div>
              @endif

              <!-- Top row: status badge (left) + ADD TO LIST (right) -->
              <div class="fg-chip-row">
                @if($isReserved)
                  <span class="fg-status-badge reserved">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    {{ __('RESERVED') }}
                  </span>
                @elseif($isPending)
                  <span class="fg-status-badge pending">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ __('PENDING') }}
                  </span>
                @elseif(!$isReserved && !$isSold && $isHighDem)
                  <span class="fg-status-badge high-demand">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 23a7 7 0 0 1-7-7c0-2 1-3 1-3 0 1 1 2 2 2 0-3 2-5 2-8 0-2-1-3-1-3 4 0 8 4 8 9 1-1 2-2 2-4 2 1 3 4 3 7a7 7 0 0 1-7 7z"/></svg>
                    {{ __('HIGH DEMAND') }}
                  </span>
                @elseif(!$isReserved && !$isSold && $isSecond)
                  <span class="fg-status-badge second">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ __('2ND CHANCE') }}
                  </span>
                @else
                  <span></span>
                @endif

                @php $isFav = in_array($unit->id, $wishlistIds ?? []); @endphp
                <button type="button"
                        class="fg-add-to-list {{ $isFav ? 'is-fav' : '' }} {{ app()->getLocale() === 'es' ? 'icon-only' : '' }}"
                        aria-label="{{ __('Add to list') }}"
                        aria-pressed="{{ $isFav ? 'true' : 'false' }}"
                        data-wishlist-toggle data-unit-id="{{ $unit->id }}"
                        title="Shortlisted by {{ $unit->shortlisted_count ?? 0 }} other">
                  <span class="heart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $isFav ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                  </span>
                  <span class="text">
                    <span class="label">{{ $isFav ? __('Saved') : __('Add to list') }}</span>
                    <span class="meta">{{ __('Shortlisted by') }} <span data-unit-count="{{ $unit->id }}">{{ $unit->shortlisted_count ?? 0 }}</span> {{ __('other') }}</span>
                  </span>
                </button>
              </div>

              <!-- Gold "RESERVE FROM $5000" banner -->
              <div class="fg-reserve-banner" onclick="openMoreInfo('{{ $unitId }}')" style="cursor:pointer">{{ __('Reserve from $5000') }}</div>

              @if($isSold)
                <!-- SOLD overlay (Figma 125:5048) -->
                <div class="fg-sold-badge"><span>{{ __('SOLD') }}</span></div>
              @endif
            </div>

            <!-- Body -->
            <div class="fg-card-body">

              <!-- Head: name + ROI, subtitle, divider, price, discount -->
              <div class="fg-card-head">
                <div class="fg-card-title-row">
                  <span class="name">{{ $unit->name }}</span>
                  @if(!empty($unit->fully_furnished))
                    <span class="furnished">{{ __('Fully furnished') }}</span>
                  @endif
                </div>
                <div class="fg-card-subtitle">
                  {{ $unit->floor ? ucfirst($unit->floor) . ' ' . __('Floor') : __('Ground Floor') }}
                  @if($unit->direction) · {{ strtoupper($unit->direction) }} @endif
                  @if($unit->outlook) · {{ $outlookLabels[$unit->outlook] ?? $unit->outlook }} @endif
                </div>
                <div class="fg-card-divider"></div>
                <div class="fg-card-price" onclick="openMoreInfo('{{ $unitId }}')" style="cursor:pointer">
                  <span class="price" data-usd="{{ $unit->price }}">${{ number_format($unit->price, 0, ' ', ' ') }}</span>
                  @if($unit->internal_area && $unit->internal_area > 0)
                    <span class="sqft" data-usd-sqft="{{ round($unit->price / $unit->internal_area) }}">${{ number_format($unit->price / $unit->internal_area, 0) }}/m</span>
                  @endif
                </div>
                @if($hasDiscount)
                  <button type="button" class="fg-discount" title="{{ __('Limited time offer') }}">{{ __('Unlock :amount Discount', ['amount' => '$'.number_format($unit->discount, 0, ',', ',')]) }}</button>
                @endif
              </div>

              <!-- Stats row (6 boxes) -->
              <div class="fg-stats" onclick="openMoreInfo('{{ $unitId }}')" style="cursor:pointer">
                <div class="fg-stat" title="Bedrooms">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 14v4h20v-4a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3z"/><path d="M2 14V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7"/><path d="M7 11V9h10v2"/></svg>
                  <span class="v">{{ $unit->bedrooms ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Bathrooms">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6V4a2 2 0 0 1 4 0"/><path d="M2 11h20"/><path d="M5 11v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6"/><line x1="6" y1="22" x2="6" y2="20"/><line x1="18" y1="22" x2="18" y2="20"/></svg>
                  <span class="v">{{ $unit->bathrooms ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Parking">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14"/><path d="M5 17V9l1.5-4h11L19 9v8"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                  <span class="v">{{ $unit->parking_bays ?? 0 }}</span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Internal area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="2 2"/></svg>
                  <span class="v">{{ number_format(($unit->internal_area ?? 0)) }}m<sup>2</sup></span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="External area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 3 3 8 3"/><polyline points="16 3 21 3 21 8"/><polyline points="21 16 21 21 16 21"/><polyline points="8 21 3 21 3 16"/></svg>
                  <span class="v">{{ number_format(($unit->external_area ?? 0)) }}m<sup>2</sup></span>
                </div>
                <span class="fg-stat-divider"></span>
                <div class="fg-stat" title="Total area">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V3h18"/><line x1="3" y1="9" x2="9" y2="9"/><line x1="3" y1="15" x2="9" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="9"/></svg>
                  <span class="v">{{ number_format(($unit->total_area ?? 0)) }}m<sup>2</sup></span>
                </div>
              </div>

              <!-- Buttons + availability -->
              <div class="fg-card-actions">
                @if($isSold)
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info-similar" type="button" onclick="viewSimilarUnits(this)">{{ __('View Similar Units') }}</button>
                  </div>
                  <div class="fg-card-availability">
                    <span class="dot"></span>
                    <span>{{ __('This unit has been sold.') }}</span>
                  </div>
                @elseif($isReserved)
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info" onclick="openMoreInfo('{{ $unitId }}')">{{ __('More Info') }}</button>
                    <button class="fg-btn-cta" type="button" onclick="notifyWhenAvailable('{{ $unitId }}')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                      {{ __('Notificar si se libera') }}
                    </button>
                  </div>
                  <div class="fg-card-availability">
                    <span class="dot"></span>
                    <span>{{ __('Currently on hold by another buyer.') }}</span>
                  </div>
                @else
                  <div class="fg-card-buttons">
                    <button class="fg-btn-info" onclick="openMoreInfo('{{ $unitId }}')">{{ __('More Info') }}</button>
                    <button class="fg-btn-cta" type="button" onclick="openAdvisorVideoCall('{{ $unitId }}')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="23 7 16 12 23 17 23 7" fill="currentColor"></polygon>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                      </svg>
                      {{ __('Book Video Call') }}
                    </button>
                  </div>
                  <div class="fg-card-availability advisor-live" role="button" tabindex="0" title="{{ __('Chat now with the administrator') }}" style="cursor:pointer;" onclick="window.location.href='{{ route('dashboard.messages', ['urgent' => 1]) }}'" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href='{{ route('dashboard.messages', ['urgent' => 1]) }}';}">
                    <span class="dot"></span>
                    <span>{{ __('An advisor is available right now.') }}</span>
                  </div>
                @endif
              </div>
            </div>
          </div>

          @if($isHighDem && !$isReserved && !$isPending && !$isSecond)
            <div class="fg-card-status-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <span>{{ __(':count people viewed this unit today', ['count' => (int)($unit->views_today ?? 0) ?: $shortlistedCount]) }}</span>
            </div>
          @elseif($isPending)
            <div class="fg-card-status-strip">
              <span class="fg-card-status-dot"></span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>{{ __('Pending review · Hold expires soon') }}</span>
            </div>
          @elseif($isSecond)
            <div class="fg-card-status-strip">
              <span class="fg-card-status-dot"></span>
              @php
                $releasedDays = $unit->released_at ? (int) \Carbon\Carbon::parse($unit->released_at)->diffInDays(now()) : null;
              @endphp
              <span>{{ __('This unit was released') }} {{ $releasedDays !== null ? ($releasedDays === 0 ? __('today') : $releasedDays.' '.__(\Illuminate\Support\Str::plural('day', $releasedDays)).' '.__('ago')) : __('recently') }}</span>
            </div>
          @elseif($isReserved)
            @php
              $reservedFuture = !empty($unit->reserved_until) && \Carbon\Carbon::parse($unit->reserved_until)->isFuture();
            @endphp
            <div class="fg-card-status-strip is-reserved-strip" @if($reservedFuture) data-reserved-until="{{ \Carbon\Carbon::parse($unit->reserved_until)->toIso8601String() }}" @endif>
              <span class="fg-card-status-dot"></span>
              @if($reservedFuture)
                <span>{{ __('Reserved for') }} <span class="fg-countdown" data-countdown>00:00:00</span> {{ __('remaining') }}</span>
              @else
                <span>{{ __('Reserved · Awaiting deposit') }}</span>
              @endif
            </div>
          @endif
        </div>
        <!---- unit - end   ---->
