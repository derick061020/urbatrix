              @php
                $st = strtolower($unit->status ?? '');
                $rowCls = 'row-available'; $statusCls = 'available'; $statusLabel = 'Available';
                if ($st === 'sold')                       { $rowCls = 'row-sold is-sold'; $statusCls = 'sold'; $statusLabel = 'Sold'; }
                elseif ($st === 'reserved')               { $rowCls = 'row-reserved'; $statusCls = 'reserved'; $statusLabel = 'Reserved'; }
                elseif ($st === 'pending')                { $rowCls = 'row-pending'; $statusCls = 'pending'; $statusLabel = 'Pending'; }
                elseif (!empty($unit->is_second_chance))  { $rowCls = 'row-second'; $statusCls = 'second'; $statusLabel = '2nd Chance'; }
                elseif (!empty($unit->is_high_demand))    { $rowCls = 'row-hot'; $statusCls = 'hot'; $statusLabel = 'Hot'; }
                $unitId = $unit->custom_id ?? $unit->id;
                $tabKey = $statusCls === 'hot' || $statusCls === 'available' || $statusCls === 'reserved' ? 'available' : $statusCls;
              @endphp
              @php
                $rowBeds = (int) ($unit->bedrooms ?? 0);
                if (!empty($unit->type) && strcasecmp($unit->type, 'Penthouse') === 0) {
                    $rowTypeLbl = 'Penthouse';
                } elseif ($rowBeds === 0) {
                    $rowTypeLbl = 'Studio';
                } else {
                    $rowTypeLbl = $rowBeds . ' Bed';
                }
                $rowFloorRaw  = trim((string) ($unit->floor ?? ''));
                $rowFloorNorm = ($rowFloorRaw === '' || strcasecmp($rowFloorRaw, 'ground') === 0) ? 'Ground' : $rowFloorRaw;
              @endphp
              <tr class="{{ $rowCls }}"
                  data-tab="{{ $tabKey }}"
                  data-search="{{ strtolower(($unitId ?? '') . ' ' . ($unit->floor ?? '') . ' ' . ($unit->bedrooms ?? '') . ' bed ' . ($unit->direction ?? '') . ' ' . ($unit->outlook ?? '')) }}"
                  data-filter-search="{{ strtolower(trim(($unit->name ?? '') . ' ' . ($unitId ?? '') . ' ' . ($unit->floor ?? '') . ' ' . ($unit->type ?? '') . ' ' . ($unit->direction ?? '') . ' ' . ($unit->outlook ?? '') . ' ' . ($rowBeds ?? '') . ' bed')) }}"
                  data-filter-unit="{{ $unitId }}"
                  data-filter-floor="{{ $unit->floor ?? '' }}"
                  data-filter-type="{{ $unit->type ?? '' }}"
                  data-filter-bedrooms="{{ $rowBeds }}"
                  data-filter-direction="{{ strtoupper($unit->direction ?? '') }}"
                  data-filter-outlook="{{ $unit->outlook ?? '' }}"
                  data-filter-price="{{ (float) $unit->price }}"
                  data-filter-area="{{ (float) ($unit->internal_area ?? 0) }}"
                  data-filter-status="{{ $st }}"
                  data-filter-second="{{ !empty($unit->is_second_chance) ? '1' : '0' }}"
                  data-hot="{{ !empty($unit->is_high_demand) ? '1' : '0' }}">
                <td><b>{{ $unit->name }}</b></td>
                <td>
                    <span class="fg-list-status {{ $statusCls }}">{{ strtoupper(__($statusLabel)) }}</span>
                    @if($statusCls === 'reserved' && !empty($unit->reserved_until) && \Carbon\Carbon::parse($unit->reserved_until)->isFuture())
                        <div style="font-size:10px;color:#92400e;margin-top:2px;">{{ __('Expira') }} {{ \Carbon\Carbon::parse($unit->reserved_until)->diffForHumans() }}</div>
                    @endif
                </td>
                <td>{{ $unit->floor ? ucfirst($unit->floor) : __('Ground') }}</td>
                <td>{{ ($unit->bedrooms ?? 0) }} {{ __('Bed') }}</td>
                <td>
                    {{ strtoupper($unit->direction ?? '—') }}
                    @if($unit->outlook)
                        <div style="font-size:10px;color:#a3a3a3;line-height:1.2;">{{ $outlookLabels[$unit->outlook] ?? $unit->outlook }}</div>
                    @endif
                </td>
                <td>{{ ($unit->bedrooms ?? 0) }} / {{ ($unit->bathrooms ?? 0) }}</td>
                <td>{{ number_format($unit->internal_area ?? 0) }}<sub style="font-size:9px;color:#a3a3a3;">sf</sub></td>
                <td>{{ number_format($unit->external_area ?? 0) }}<sub style="font-size:9px;color:#a3a3a3;">sf</sub></td>
                <td>
                  <span class="price" data-usd="{{ $unit->price }}">${{ number_format($unit->price, 0, ',', ',') }}</span>
                  @if($unit->internal_area && $unit->internal_area > 0)
                    <span class="price-meta" data-usd-sqft="{{ round($unit->price / $unit->internal_area) }}">${{ number_format($unit->price / $unit->internal_area, 0) }}/m²</span>
                  @endif
                </td>
                <td>
                  <div class="fg-list-actions">
                    <button class="fg-list-icon-btn" type="button" aria-label="{{ __('Save') }}">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                      </svg>
                    </button>
                    <button class="fg-list-info-btn" type="button" onclick="openMoreInfo('{{ $unitId }}')">{{ __('INFO') }}</button>
                    @if($statusCls === 'sold')
                      <button class="fg-list-cta" type="button" disabled>{{ __('Sold') }}</button>
                    @elseif($statusCls === 'reserved')
                      <button class="fg-list-cta" type="button" disabled style="opacity:.5;cursor:not-allowed;">{{ __('Reserved') }}</button>
                    @else
                      <button class="fg-list-cta" type="button" onclick="openAdvisorVideoCall('{{ $unitId }}')">{{ __('Book Video Call') }}</button>
                    @endif
                  </div>
                </td>
              </tr>
