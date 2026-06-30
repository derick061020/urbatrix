{{--
    Shared form fields for Unit create/edit.
    Required vars: $unit (Unit or null), $agents (Collection of Agent)

    Estructura (de la mano con el modal "Configuración de opciones"):
      1. Información general   2. Especificaciones   3. Dimensiones
      4. Gastos & rentabilidad 5. Contenido Investment / Living
      6. Disponibilidad & demanda
    Los selects "Tipo", "Planta" y "Vista" se alimentan de UnitOptions
    (editables desde el modal de configuración).
--}}
@php
    use App\Support\UnitOptions;
    $u = $unit ?? null;
    $typeOptions    = UnitOptions::map('types');
    $currentType    = old('type', $u->type ?? array_key_first($typeOptions) ?? '');
    if ($currentType && !array_key_exists($currentType, $typeOptions)) {
        $typeOptions = [$currentType => $currentType] + $typeOptions;
    }
    $floorOptions   = UnitOptions::map('floors');
    $outlookOptions = UnitOptions::map('outlooks');
    $statusOptions = [
        'AVAILABLE' => 'Disponible',
        'PENDING'   => 'Pendiente',
        'RESERVED'  => 'Reservada',
        'HELD'      => 'En espera',
        'SOLD'      => 'Vendida',
    ];
@endphp

{{-- ===================== 1 · GENERAL ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-home text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Información general') }}</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Nombre / Identificador') }} <span class="text-err">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $u->name ?? '') }}" placeholder="{{ __('Ej. A-1201') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Código interno') }}</label>
            <input type="text" name="custom_id" value="{{ old('custom_id', $u->custom_id ?? '') }}" placeholder="A-1201" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Estado') }} <span class="text-err">*</span></label>
            @php $currentStatus = old('status', $u->status ?? 'AVAILABLE'); @endphp
            @include('admin.units._partials.select', ['name' => 'status', 'options' => $statusOptions, 'selected' => $currentStatus, 'required' => true])
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Tipo') }} <span class="text-err">*</span></label>
            @include('admin.units._partials.select', ['name' => 'type', 'options' => $typeOptions, 'selected' => $currentType, 'required' => true])
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Precio') }} <span class="text-err">*</span></label>
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                <input type="number" step="0.01" min="0" name="price" required value="{{ old('price', $u->price ?? 0) }}" class="crm-input pl-7">
            </div>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Descuento') }}</label>
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                <input type="number" step="0.01" min="0" name="discount" value="{{ old('discount', $u->discount ?? '') }}" class="crm-input pl-7">
            </div>
            <p class="text-[10px] text-ink-400 mt-1">{{ __('Se resta del precio en las tarjetas. También editable en masa desde "Descuentos".') }}</p>
        </div>
        <div class="sm:col-span-2">
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Dirección') }}</label>
            <input type="text" name="address" value="{{ old('address', $u->address ?? '') }}" placeholder="{{ __('Ej. 1A Launch Boulevard') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Asesor asignado') }}</label>
            @php
                $currentAgent = old('agent_id', $u->agent_id ?? null);
                $agentOptions = ['' => __('Ninguno')];
                foreach ($agents ?? [] as $agent) { $agentOptions[$agent->id] = $agent->name; }
            @endphp
            @include('admin.units._partials.select', ['name' => 'agent_id', 'options' => $agentOptions, 'selected' => $currentAgent, 'placeholder' => __('Ninguno')])
        </div>
        <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-6 pt-1">
            @include('admin.units._partials.toggle', ['name' => 'public',               'label' => 'Público',          'checked' => old('public', $u->public ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'display_on_home_page',  'label' => 'Destacar en home', 'checked' => old('display_on_home_page', $u->display_on_home_page ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'plot',                  'label' => 'Lote',             'checked' => old('plot', $u->plot ?? false)])
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Descripción') }}</label>
            <textarea name="description" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="{{ __('Notas internas sobre la unidad…') }}">{{ old('description', $u->description ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- ===================== 2 · SPECIFICATIONS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-th-large text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Especificaciones') }}</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Planta') }}</label>
            @php $currentFloor = old('floor', $u->floor ?? ''); @endphp
            @include('admin.units._partials.select', ['name' => 'floor', 'options' => ['' => '—'] + $floorOptions, 'selected' => $currentFloor])
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Layout') }}</label>
            <input type="text" name="layout" value="{{ old('layout', $u->layout ?? '') }}" placeholder="{{ __('Ej. 2B-A') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Camas') }}</label>
            <input type="number" min="0" name="bedrooms" value="{{ old('bedrooms', $u->bedrooms ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Baños') }}</label>
            <input type="number" step="0.1" min="0" name="bathrooms" value="{{ old('bathrooms', $u->bathrooms ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Parqueos') }}</label>
            <input type="number" min="0" name="parking_bays" value="{{ old('parking_bays', $u->parking_bays ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Piscinas') }}</label>
            <input type="number" min="0" name="pools" value="{{ old('pools', $u->pools ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Orientación') }}</label>
            @php
                $currentDir = old('direction', $u->direction ?? '');
                $dirOptions = ['' => '—'];
                foreach (['N','NE','E','SE','S','SW','W','NW'] as $dir) { $dirOptions[$dir] = $dir; }
            @endphp
            @include('admin.units._partials.select', ['name' => 'direction', 'options' => $dirOptions, 'selected' => $currentDir])
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Vista') }}</label>
            @php $currentOutlook = old('outlook', $u->outlook ?? ''); @endphp
            @include('admin.units._partials.select', ['name' => 'outlook', 'options' => ['' => '—'] + $outlookOptions, 'selected' => $currentOutlook])
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-6 pt-2">
            @include('admin.units._partials.toggle', ['name' => 'aircon',          'label' => 'Aire acondicionado', 'checked' => old('aircon', $u->aircon ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'fully_furnished', 'label' => __('Fully furnished'), 'checked' => old('fully_furnished', $u->fully_furnished ?? false)])
        </div>
    </div>
</div>

{{-- ===================== 3 · DIMENSIONS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-arrows-h text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Dimensiones') }}</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Área interior (m²)') }}</label>
            <input type="number" step="0.01" min="0" name="internal_area" value="{{ old('internal_area', $u->internal_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Área exterior / terraza (m²)') }}</label>
            <input type="number" step="0.01" min="0" name="external_area" value="{{ old('external_area', $u->external_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Área total (m²)') }}</label>
            <input type="number" step="0.01" min="0" name="total_area" value="{{ old('total_area', $u->total_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
    </div>
</div>

{{-- ===================== 4 · EXPENSES & YIELD ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-wallet text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Gastos & rentabilidad') }}</div>
    </div>
    <div class="p-5 space-y-4">
        <div>
            <div class="text-[11px] uppercase font-semibold text-ink-400 tracking-wide mb-2">{{ __('Gastos comunes mensuales') }}</div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Gasto 1') }}</label>
                    <input type="number" step="0.01" min="0" name="expense_1" value="{{ old('expense_1', $u->expense_1 ?? '') }}" class="crm-input pl-3 mt-1">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Gasto 2') }}</label>
                    <input type="number" step="0.01" min="0" name="expense_2" value="{{ old('expense_2', $u->expense_2 ?? '') }}" class="crm-input pl-3 mt-1">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Gasto 3') }}</label>
                    <input type="number" step="0.01" min="0" name="expense_3" value="{{ old('expense_3', $u->expense_3 ?? '') }}" class="crm-input pl-3 mt-1">
                </div>
            </div>
            <p class="text-[10px] text-ink-400 mt-1">{{ __('Los tres se suman como "gastos comunes" en la vista de inversión del front.') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Mantenimiento (levies)') }}</label>
                <input type="number" step="0.01" min="0" name="levies" value="{{ old('levies', $u->levies ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Impuestos (rates)') }}</label>
                <input type="number" step="0.01" min="0" name="rates" value="{{ old('rates', $u->rates ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">{{ __('Alquiler estimado') }}</label>
                <input type="number" step="0.01" min="0" name="est_rental" value="{{ old('est_rental', $u->est_rental ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
        </div>
    </div>
</div>

{{-- ===================== 5 · INVESTMENT / LIVING ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-chart-line text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Contenido segmentado · Investment / Living') }}</div>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            {{-- FOR INVESTMENT --}}
            <div class="space-y-4 pb-6 lg:pb-0 lg:pr-8 border-b lg:border-b-0 lg:border-r border-ink-200">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-info-soft text-info flex items-center justify-center"><i class="pi pi-chart-line text-[13px]"></i></span>
                    <h3 class="text-[13px] font-bold text-ink-900">{{ __('For Investment') }}</h3>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Texto "For Investment"') }}</label>
                    <textarea name="for_investment_text" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"
                              placeholder="{{ __('Mensaje orientado a inversores. Ej: ROI proyectado, alquiler corto plazo, plusvalía...') }}">{{ old('for_investment_text', $u->for_investment_text ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">{{ __('Valor proyectado ($)') }}</label>
                        <input type="number" step="0.01" min="0" name="projected_value" value="{{ old('projected_value', $u->projected_value ?? '') }}" class="crm-input pl-3 mt-1">
                    </div>
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">{{ __('Año proyección') }}</label>
                        <input type="text" maxlength="10" name="projected_value_year" value="{{ old('projected_value_year', $u->projected_value_year ?? '') }}" class="crm-input pl-3 mt-1" placeholder="2027">
                    </div>
                    <div>
                        <label class="text-[12px] font-semibold text-ink-700">{{ __('ROI (%)') }}</label>
                        <input type="number" step="0.01" min="0" max="999" name="roi_percent" value="{{ old('roi_percent', $u->roi_percent ?? '') }}" class="crm-input pl-3 mt-1" placeholder="8.5">
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Comentario comparativo') }}</label>
                    <input type="text" maxlength="500" name="comparison_text" value="{{ old('comparison_text', $u->comparison_text ?? '') }}" class="crm-input pl-3 mt-1" placeholder="{{ __('Miami Beach reference: $900/m² · Makai $450/m² — 50% menos') }}">
                </div>
            </div>

            {{-- FOR LIVING --}}
            <div class="space-y-4 pt-6 lg:pt-0 lg:pl-8">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-brand-soft text-brand flex items-center justify-center"><i class="pi pi-home text-[13px]"></i></span>
                    <h3 class="text-[13px] font-bold text-ink-900">{{ __('For Living') }}</h3>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Texto "For Living"') }}</label>
                    <textarea name="for_living_text" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none"
                              placeholder="{{ __('Mensaje orientado a residentes. Ej: barrio, escuelas, lifestyle, terraza...') }}">{{ old('for_living_text', $u->for_living_text ?? '') }}</textarea>
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">{{ __('Amenities') }}</label>
                    @php
                        $amenitiesOptions = UnitOptions::get('amenities');
                        $selectedAmenities = old('amenities', $u->amenities ?? []);
                        if (is_string($selectedAmenities)) {
                            $selectedAmenities = json_decode($selectedAmenities, true) ?? [];
                        }
                    @endphp
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                        @foreach($amenitiesOptions as $amenity)
                            @php $key = $amenity['value'] ?? ''; @endphp
                            <label class="relative cursor-pointer">
                                <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, $selectedAmenities) ? 'checked' : '' }} class="peer sr-only">
                                <div class="flex flex-col items-center gap-1 p-3 rounded-lg border-2 border-ink-200 bg-white peer-checked:border-brand peer-checked:bg-brand-soft hover:border-brand/50 transition-all">
                                    <div class="text-ink-400 peer-checked:text-brand transition-colors">
                                        {!! UnitOptions::amenityIcon($amenity['icon'] ?? null) !!}
                                    </div>
                                    <span class="text-[10px] font-medium text-ink-600 peer-checked:text-brand-dark text-center leading-tight">{{ $amenity['label'] ?? $key }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== 6 · AVAILABILITY & DEMAND ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-bolt text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">{{ __('Disponibilidad & demanda') }}</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Reservada hasta') }}</label>
            <input type="datetime-local" name="reserved_until"
                   value="{{ old('reserved_until', $u && $u->reserved_until ? \Carbon\Carbon::parse($u->reserved_until)->format('Y-m-d\TH:i') : '') }}"
                   class="crm-input pl-3 mt-1">
            <p class="text-[10px] text-ink-400 mt-1">{{ __('Aparece como contador en la card del front.') }}</p>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Liberada el') }}</label>
            <input type="datetime-local" name="released_at"
                   value="{{ old('released_at', $u && $u->released_at ? \Carbon\Carbon::parse($u->released_at)->format('Y-m-d\TH:i') : '') }}"
                   class="crm-input pl-3 mt-1">
            <p class="text-[10px] text-ink-400 mt-1">{{ __('Usado por el texto "released N days ago" de 2nd Chance.') }}</p>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">{{ __('Vistas hoy') }}</label>
            <input type="number" min="0" name="views_today" value="{{ old('views_today', $u->views_today ?? 0) }}" class="crm-input pl-3 mt-1">
            <p class="text-[10px] text-ink-400 mt-1">{{ __('Total acumulado:') }} <b>{{ (int)($u->views_total ?? 0) }}</b>{{ __('. Poné 0 para reiniciar el contador del día.') }}</p>
        </div>
        <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-6 pt-1">
            @include('admin.units._partials.toggle', ['name' => 'is_high_demand',   'label' => 'High Demand',  'checked' => old('is_high_demand',   $u->is_high_demand   ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'is_second_chance', 'label' => '2nd Chance',   'checked' => old('is_second_chance', $u->is_second_chance ?? false)])
        </div>
    </div>
</div>
