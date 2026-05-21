{{--
    Shared form fields for Unit create/edit.
    Required vars: $unit (Unit or null), $agents (Collection of Agent)
--}}
@php
    $u = $unit ?? null;
    $typeOptions = [
        '1_bed' => '1 Bed',
        '1_bed_family' => '1 Bed & Family Room',
        '1_bed_studio' => '1 Bed & Studio Lock-off',
        '2_bed' => '2 Bed',
        '3_bed' => '3 Bed',
        'penthouse_1_bed' => 'Penthouse 1 Bed',
        'penthouse_2_bed' => 'Penthouse 2 Bed',
    ];
    $currentType = old('type', $u->type ?? '2_bed');
    if ($currentType && !array_key_exists($currentType, $typeOptions)) {
        $typeOptions = [$currentType => $currentType] + $typeOptions;
    }
    $floorOptions = ['ground' => 'Planta baja', '1st' => '1°', '2nd' => '2°', '3rd' => '3°', '4th' => '4°', '5th' => '5°', '6th' => '6°'];
    $outlookOptions = ['golf_course' => 'Vista al campo de golf', 'lake' => 'Vista al lago', 'ocean_lake' => 'Vista al mar y al lago', 'ocean' => 'Vista al mar', 'mountain' => 'Vista a la montaña'];
    $statusOptions = [
        'AVAILABLE' => 'Disponible',
        'PENDING'   => 'Pendiente',
        'RESERVED'  => 'Reservada',
        'HELD'      => 'En espera',
        'SOLD'      => 'Vendida',
    ];
@endphp

{{-- ===================== GENERAL ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-home text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Información general</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Nombre / Identificador <span class="text-err">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $u->name ?? '') }}" placeholder="Ej. A-1201" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Código interno</label>
            <input type="text" name="custom_id" value="{{ old('custom_id', $u->custom_id ?? '') }}" placeholder="A-1201" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Estado <span class="text-err">*</span></label>
            <select name="status" required class="crm-input pl-3 mt-1">
                @php $currentStatus = old('status', $u->status ?? 'AVAILABLE'); @endphp
                @foreach($statusOptions as $val => $label)
                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Tipo <span class="text-err">*</span></label>
            <select name="type" required class="crm-input pl-3 mt-1">
                @foreach($typeOptions as $val => $label)
                    <option value="{{ $val }}" {{ $currentType === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Precio <span class="text-err">*</span></label>
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-ink-500">$</span>
                <input type="number" step="0.01" min="0" name="price" required value="{{ old('price', $u->price ?? 0) }}" class="crm-input pl-7">
            </div>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Texto del precio</label>
            <input type="text" name="price_wording" value="{{ old('price_wording', $u->price_wording ?? '') }}" placeholder="Desde / Por consultar…" class="crm-input pl-3 mt-1">
        </div>
        <div class="sm:col-span-2">
            <label class="text-[12px] font-semibold text-ink-700">Dirección</label>
            <input type="text" name="address" value="{{ old('address', $u->address ?? '') }}" placeholder="Ej. 1A Launch Boulevard" class="crm-input pl-3 mt-1">
        </div>
        <div class="flex flex-wrap items-center gap-6 pt-7">
            @include('admin.units._partials.toggle', ['name' => 'public',       'label' => 'Público',       'checked' => old('public', $u->public ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'pre_arranged', 'label' => 'Pre-reservada', 'checked' => old('pre_arranged', $u->pre_arranged ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'plot',         'label' => 'Lote',          'checked' => old('plot', $u->plot ?? false)])
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-[12px] font-semibold text-ink-700">Descripción</label>
            <textarea name="description" rows="3" class="crm-input pl-3 pt-2 mt-1 h-auto resize-none" placeholder="Notas internas sobre la unidad…">{{ old('description', $u->description ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- ===================== RESERVATION DETAILS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-file-edit text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Reserva — Detalles</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Descuento</label>
            <input type="number" step="0.01" name="discount" value="{{ old('discount', $u->discount ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Parqueos adicionales</label>
            <select name="additional_parking" class="crm-input pl-3 mt-1">
                @for($i = 0; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ (int)old('additional_parking', $u->additional_parking ?? 0) === $i ? 'selected' : '' }}>{{ $i === 0 ? 'Ninguno' : $i }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Ajuste de precio</label>
            <input type="number" step="0.01" name="price_adjustment" value="{{ old('price_adjustment', $u->price_adjustment ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Precio de compra</label>
            <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $u->purchase_price ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
    </div>
</div>

{{-- ===================== RESERVATION CUSTOMER + AGENT ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-user text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Cliente y asesor</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Nombre</label>
            <input type="text" name="first_name" value="{{ old('first_name', $u->first_name ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Apellido</label>
            <input type="text" name="last_name" value="{{ old('last_name', $u->last_name ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Teléfono</label>
            <input type="text" name="contact_number" value="{{ old('contact_number', $u->contact_number ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $u->email ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div class="lg:col-span-2">
            <label class="text-[12px] font-semibold text-ink-700">Asesor asignado</label>
            <select name="agent_id" class="crm-input pl-3 mt-1">
                @php $currentAgent = old('agent_id', $u->agent_id ?? null); @endphp
                <option value="" {{ $currentAgent === null || $currentAgent === '' ? 'selected' : '' }}>Ninguno</option>
                @foreach($agents ?? [] as $agent)
                    <option value="{{ $agent->id }}" {{ (int)$currentAgent === (int)$agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- ===================== SPECIFICATIONS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-th-large text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Especificaciones</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Planta</label>
            <select name="floor" class="crm-input pl-3 mt-1">
                @php $currentFloor = old('floor', $u->floor ?? ''); @endphp
                <option value="" {{ $currentFloor === '' ? 'selected' : '' }}>—</option>
                @foreach($floorOptions as $val => $label)
                    <option value="{{ $val }}" {{ $currentFloor === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Layout</label>
            <input type="text" name="layout" value="{{ old('layout', $u->layout ?? '') }}" placeholder="Ej. 2B-A" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Camas</label>
            <input type="number" min="0" name="bedrooms" value="{{ old('bedrooms', $u->bedrooms ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Baños</label>
            <input type="number" step="0.1" min="0" name="bathrooms" value="{{ old('bathrooms', $u->bathrooms ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Parqueos</label>
            <input type="number" min="0" name="parking_bays" value="{{ old('parking_bays', $u->parking_bays ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Piscinas</label>
            <input type="number" min="0" name="pools" value="{{ old('pools', $u->pools ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Orientación</label>
            <select name="direction" class="crm-input pl-3 mt-1">
                @php $currentDir = old('direction', $u->direction ?? ''); @endphp
                <option value="" {{ $currentDir === '' ? 'selected' : '' }}>—</option>
                @foreach(['N','NE','E','SE','S','SW','W','NW'] as $dir)
                    <option value="{{ $dir }}" {{ $currentDir === $dir ? 'selected' : '' }}>{{ $dir }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Vista</label>
            <select name="outlook" class="crm-input pl-3 mt-1">
                @php $currentOutlook = old('outlook', $u->outlook ?? ''); @endphp
                <option value="" {{ $currentOutlook === '' ? 'selected' : '' }}>—</option>
                @foreach($outlookOptions as $val => $label)
                    <option value="{{ $val }}" {{ $currentOutlook === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-6 pt-2">
            @include('admin.units._partials.toggle', ['name' => 'aircon',            'label' => 'Aire acondicionado',   'checked' => old('aircon', $u->aircon ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'guaranteed_rental', 'label' => 'Alquiler garantizado', 'checked' => old('guaranteed_rental', $u->guaranteed_rental ?? false)])
            @include('admin.units._partials.toggle', ['name' => 'override_action',   'label' => 'Override acción',      'checked' => old('override_action', $u->override_action ?? false)])
        </div>
    </div>
</div>

{{-- ===================== DIMENSIONS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-arrows-h text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Dimensiones</div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Área interior (m²)</label>
            <input type="number" step="0.01" min="0" name="internal_area" value="{{ old('internal_area', $u->internal_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Área exterior / terraza (m²)</label>
            <input type="number" step="0.01" min="0" name="external_area" value="{{ old('external_area', $u->external_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
        <div>
            <label class="text-[12px] font-semibold text-ink-700">Área total (m²)</label>
            <input type="number" step="0.01" min="0" name="total_area" value="{{ old('total_area', $u->total_area ?? '') }}" class="crm-input pl-3 mt-1">
        </div>
    </div>
</div>

{{-- ===================== EXPENSES + CUSTOM ===================== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="crm-card">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
            <i class="pi pi-wallet text-ink-500"></i>
            <div class="text-[13px] font-bold text-ink-700">Gastos mensuales</div>
        </div>
        <div class="p-5 grid grid-cols-3 gap-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Gasto 1</label>
                <input type="number" step="0.01" min="0" name="expense_1" value="{{ old('expense_1', $u->expense_1 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Gasto 2</label>
                <input type="number" step="0.01" min="0" name="expense_2" value="{{ old('expense_2', $u->expense_2 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Gasto 3</label>
                <input type="number" step="0.01" min="0" name="expense_3" value="{{ old('expense_3', $u->expense_3 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Mantenimiento (levies)</label>
                <input type="number" step="0.01" min="0" name="levies" value="{{ old('levies', $u->levies ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Impuestos (rates)</label>
                <input type="number" step="0.01" min="0" name="rates" value="{{ old('rates', $u->rates ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Alquiler estimado</label>
                <input type="number" step="0.01" min="0" name="est_rental" value="{{ old('est_rental', $u->est_rental ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
        </div>
    </div>

    <div class="crm-card">
        <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
            <i class="pi pi-tag text-ink-500"></i>
            <div class="text-[13px] font-bold text-ink-700">Campos personalizados</div>
        </div>
        <div class="p-5 grid grid-cols-1 gap-4">
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Custom 1</label>
                <input type="text" name="custom_1" value="{{ old('custom_1', $u->custom_1 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Custom 2</label>
                <input type="text" name="custom_2" value="{{ old('custom_2', $u->custom_2 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
            <div>
                <label class="text-[12px] font-semibold text-ink-700">Custom 3</label>
                <input type="text" name="custom_3" value="{{ old('custom_3', $u->custom_3 ?? '') }}" class="crm-input pl-3 mt-1">
            </div>
        </div>
    </div>
</div>

{{-- ===================== SETTINGS ===================== --}}
<div class="crm-card">
    <div class="px-5 py-3 bg-ink-50 border-b border-ink-100 flex items-center gap-2">
        <i class="pi pi-cog text-ink-500"></i>
        <div class="text-[13px] font-bold text-ink-700">Configuración avanzada</div>
    </div>
    <div class="p-5 space-y-4">
        @include('admin.units._partials.toggle', [
            'name' => 'bypass_launch_date',
            'label' => 'Saltar contador de lanzamiento',
            'description' => 'Si está activo, esta unidad podrá reservarse vía URL antes de que el contador llegue a cero.',
            'checked' => old('bypass_launch_date', $u->bypass_launch_date ?? false),
        ])
        <div class="h-px bg-ink-100"></div>
        <div class="text-[11px] uppercase font-semibold text-ink-400 tracking-wide">Visualización</div>
        @include('admin.units._partials.toggle', [
            'name' => 'display_on_home_page',
            'label' => 'Mostrar en la página principal',
            'checked' => old('display_on_home_page', $u->display_on_home_page ?? false),
        ])
        @include('admin.units._partials.toggle', [
            'name' => 'show_enquire_button',
            'label' => 'Mostrar botón de consulta',
            'description' => 'Reemplaza el botón RESERVAR por un botón CONSULTAR que abre el formulario de contacto.',
            'checked' => old('show_enquire_button', $u->show_enquire_button ?? false),
        ])
        <div class="h-px bg-ink-100"></div>
        <div class="text-[11px] uppercase font-semibold text-ink-400 tracking-wide">Precio</div>
        @include('admin.units._partials.toggle', [
            'name' => 'set_discount_globally',
            'label' => 'Aplicar descuento globalmente',
            'checked' => old('set_discount_globally', $u->set_discount_globally ?? false),
        ])
        @include('admin.units._partials.toggle', [
            'name' => 'hide_original_price',
            'label' => 'Ocultar precio original',
            'description' => 'Si está activo, el precio original se ocultará cuando el usuario califique para un descuento.',
            'checked' => old('hide_original_price', $u->hide_original_price ?? false),
        ])
        @include('admin.units._partials.toggle', [
            'name' => 'show_price_alternative',
            'label' => 'Mostrar precio alternativo',
            'description' => 'Si está activo, el precio de la unidad se reemplaza por el precio alternativo proporcionado.',
            'checked' => old('show_price_alternative', $u->show_price_alternative ?? false),
        ])
    </div>
</div>
