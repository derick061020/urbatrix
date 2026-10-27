@extends('layouts.broker')
@section('title', __('Alta de broker — Portal Broker'))
@section('page_title', __('Completa tu alta de broker'))
@section('page_breadcrumb', __('Portal Broker · Alta profesional'))

{{--
  Ficha de alta en cuatro pasos (Miguel, 26 oct, «Popup_Alta_Broker.html»):
  1 tipo · 2 datos · 3 fiscal y bancario · 4 documentos KYC. Es un solo
  formulario; los pasos sólo se enseñan de uno en uno. Comisiones, retenciones
  y documentos salen de config('company.broker_alta') de cada proyecto.
--}}
@php
    $cfg      = config('company.broker_alta');
    $kind     = old('kind', $profile->kind ?: 'broker');
    $d        = old('data', (array) ($profile->data ?? []));
    $f        = old('fiscal', (array) ($profile->fiscal ?? []));
    $have     = (array) ($profile->docs ?? []);
    $d['email'] = $d['email'] ?? $user->email;
    $d['nombre'] = $d['nombre'] ?? $user->name;
    $d['tel']   = $d['tel'] ?? $user->phone;
    $status   = $profile->status ?: 'borrador';

    $camposDatos = [
        ['nombre',    __('Nombre / Razón social'),        true,  __('Tu nombre o el de tu empresa'), 'name'],
        ['apellido',  __('Apellido (persona física)'),    false, '', 'family-name'],
        ['email',     __('Email profesional'),            true,  'email@tuempresa.com', 'email'],
        ['tel',       __('Teléfono / WhatsApp'),          true,  $cfg['phone_ph'] ?? '', 'tel'],
        ['pais',      __('País de residencia'),           true,  '', 'country-name'],
        ['direccion', __('Dirección fiscal'),             false, __('Dirección completa'), 'street-address'],
        ['licencia',  __('Nº de licencia inmobiliaria'),  false, __('Si aplica en tu país'), 'off'],
        ['web',       __('Sitio web / redes'),            false, 'https://', 'url'],
    ];
    $camposFiscal = [
        ['tax_id',  __($cfg['tax_id_label'] ?? 'Número de identificación fiscal'), true,  __('Número de contribuyente')],
        ['banco',   __('Banco'),                     true,  __('Nombre del banco')],
        ['cuenta',  __('Número de cuenta / IBAN'),   true,  __('IBAN o número de cuenta')],
        ['swift',   __('SWIFT / BIC'),               true,  __('Código SWIFT del banco')],
        ['titular', __('Titular de la cuenta'),      true,  __('Nombre exacto del titular')],
        ['moneda',  __('Moneda de la cuenta'),       false, $cfg['currency_ph'] ?? 'USD / EUR…'],
    ];
    $tipos = [
        'broker' => [__('Broker independiente'), __('Persona física.'), 'pi-user'],
        'agency' => [__('Agencia inmobiliaria'), __('Empresa registrada.'), 'pi-building'],
    ];
    // Paso al que volver si la validación falla: el del primer campo con error.
    $pasoError = 1;
    if ($errors->any()) {
        $k = array_key_first($errors->messages());
        $pasoError = str_starts_with($k, 'docs.') || $k === 'consent' ? 4 : (str_starts_with($k, 'fiscal.') ? 3 : (str_starts_with($k, 'data.') ? 2 : 1));
    }
@endphp

@push('styles')
<style>
    .alta-wrap { max-width: 760px; margin: 0 auto; }
    .alta-steps { display: flex; gap: 6px; margin-bottom: 22px; }
    .alta-st { flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 10px; background: #f6f7f9; color: #868c98; font-size: 12px; font-weight: 600; }
    .alta-st .dot { width: 22px; height: 22px; border-radius: 50%; border: 1px solid #d5d8de; display: flex; align-items: center; justify-content: center; font-size: 11px; flex: none; background: #fff; }
    .alta-st span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .alta-st.active { background: #5c7c68; color: #fff; }
    .alta-st.active .dot { border-color: transparent; color: #5c7c68; }
    .alta-st.done { color: #5c7c68; }
    .alta-st.done .dot { background: #1fc16b; border-color: #1fc16b; color: #fff; }
    @media (max-width: 640px) {
        .alta-st { justify-content: center; padding: 9px 6px; }
        .alta-st span:last-child { display: none; }
        .alta-st.active span:last-child { display: inline; }
        .alta-st.active { flex: 2.2; }
    }
    .alta-sect { font-size: 10px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; color: #868c98; }
    .alta-h { font-size: 18px; font-weight: 700; color: #0a0d14; margin: 4px 0 4px; }
    .alta-intro { font-size: 13px; color: #525866; margin: 0 0 18px; }
    .alta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .alta-grid .full { grid-column: 1 / -1; }
    @media (max-width: 640px) { .alta-grid { grid-template-columns: 1fr; } }
    .alta-fld label { font-size: 11.5px; font-weight: 600; color: #525866; margin-bottom: 5px; display: block; }
    .alta-fld .req { color: #fb3748; }
    .alta-fld input { font-size: 16px; color: #222530; border: 1px solid #eaecf0; border-radius: 8px; background: #fff; padding: 9px 11px; width: 100%; outline: none; }
    @media (min-width: 641px) { .alta-fld input { font-size: 13.5px; } }
    .alta-fld input:focus { border-color: #5c7c68; box-shadow: 0 0 0 3px rgba(92,124,104,.18); }
    .alta-fld.err input { border-color: #fb3748; }
    .alta-fld .msg { font-size: 11px; color: #fb3748; margin-top: 4px; }
    .alta-types { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 640px) { .alta-types { grid-template-columns: 1fr; } }
    .alta-type { position: relative; display: block; border: 1.5px solid #eaecf0; border-radius: 12px; padding: 16px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .alta-type:hover { border-color: #b5b9c2; }
    .alta-type.sel { border-color: #5c7c68; box-shadow: 0 0 0 3px rgba(92,124,104,.12); }
    .alta-type .ic { width: 38px; height: 38px; border-radius: 10px; background: #f3f3ee; color: #5c7c68; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
    .alta-type h4 { font-size: 14px; font-weight: 700; color: #0a0d14; margin: 0 0 3px; }
    .alta-type p { font-size: 12px; color: #525866; margin: 0; }
    .alta-type .comm { display: inline-block; margin-top: 10px; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: #f3f3ee; color: #5c7c68; }
    .alta-callout { display: flex; gap: 10px; padding: 11px 13px; border-radius: 10px; font-size: 12px; line-height: 1.5; margin-bottom: 16px; }
    .alta-callout.amber { background: #fff6e5; color: #7a5300; }
    .alta-callout.blue { background: #eef4ff; color: #1d3f8a; }
    .alta-callout i { margin-top: 2px; }
    .alta-doc { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f2f4; }
    .alta-doc:last-of-type { border-bottom: 0; }
    .alta-doc .ck { width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #d5d8de; display: flex; align-items: center; justify-content: center; color: transparent; flex: none; font-size: 11px; }
    .alta-doc.done .ck { background: #1fc16b; border-color: #1fc16b; color: #fff; }
    .alta-doc .dn { flex: 1; min-width: 0; font-size: 13px; color: #222530; }
    .alta-doc .fn { display: block; font-size: 11px; color: #868c98; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .alta-doc.err .dn { color: #fb3748; }
    .alta-nav { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid #f1f2f4; }
    .alta-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 600; padding: 10px 18px; border-radius: 9px; border: 1px solid transparent; cursor: pointer; }
    .alta-btn.primary { background: #5c7c68; color: #fff; }
    .alta-btn.primary:disabled { opacity: .45; cursor: not-allowed; }
    .alta-btn.ghost { background: #fff; border-color: #eaecf0; color: #222530; }
    .alta-btn.small { font-size: 12px; padding: 7px 12px; }
    .alta-step[hidden] { display: none !important; }
    .alta-done { text-align: center; padding: 28px 8px 8px; }
    .alta-done .big { width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
<div class="alta-wrap">

@if ($readOnly)
    {{-- Ya enviada: en revisión o verificada --}}
    <div class="alta-done">
        @if ($status === 'verificado')
            <div class="big bg-ok-soft text-ok"><i class="pi pi-verified"></i></div>
            <div class="alta-h">{{ __('Alta verificada') }}</div>
            <p class="alta-intro">{{ __('Tu cuenta profesional está activa. Ya puedes operar desde el portal.') }}</p>
            <a href="{{ route('broker.dashboard') }}" class="alta-btn primary">{{ __('Ir al portal') }} <i class="pi pi-arrow-right"></i></a>
        @else
            <div class="big bg-warn-soft text-warn"><i class="pi pi-clock"></i></div>
            <div class="alta-h">{{ __('¡Alta enviada!') }}</div>
            <p class="alta-intro" style="max-width:440px;margin-left:auto;margin-right:auto">{{ __('Hemos recibido tu registro profesional. Nuestro equipo revisará tus datos y documentos y te avisaremos por email en cuanto quedes verificado para operar.') }}</p>
            <span class="brk-pill bg-warn-soft text-warn-dark">{{ __('Estado: En revisión') }}</span>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ route('broker.inventario') }}" class="alta-btn ghost">{{ __('Mientras tanto, explorar el inventario') }}</a>
            </div>
        @endif
        <div class="mt-8 text-left brk-card p-4 sm:p-5">
            <div class="alta-sect mb-3">{{ __('Resumen de tu alta') }}</div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-[12.5px]">
                <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ __('Tipo') }}</dt><dd class="font-semibold text-ink-900 text-right">{{ $tipos[$kind][0] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ __('Nombre') }}</dt><dd class="font-semibold text-ink-900 text-right">{{ trim(($d['nombre'] ?? '').' '.($d['apellido'] ?? '')) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ __('Email') }}</dt><dd class="font-semibold text-ink-900 text-right break-all">{{ $d['email'] ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ __('Documentos') }}</dt><dd class="font-semibold text-ink-900 text-right">{{ count($have) }} / {{ count(\App\Models\BrokerProfile::requiredDocs($kind)) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ __('Enviada') }}</dt><dd class="font-semibold text-ink-900 text-right">{{ $profile->submitted_at?->translatedFormat('j M Y, H:i') }}</dd></div>
            </dl>
        </div>
    </div>
@else
    @if ($status === 'rechazado')
        <div class="alta-callout amber"><i class="pi pi-exclamation-triangle"></i><div>{{ __('El equipo ha pedido que revises tu alta. Corrige los datos o vuelve a subir los documentos y envíala de nuevo.') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="alta-callout amber" role="alert"><i class="pi pi-exclamation-triangle"></i><div>{{ __('Revisa los campos marcados.') }} {{ $errors->first() }}</div></div>
    @endif

    <div class="alta-steps" id="alta-steps">
        @foreach ([__('Tipo'), __('Datos'), __('Fiscal'), __('Documentos')] as $i => $t)
            <div class="alta-st" data-st="{{ $i + 1 }}"><span class="dot">{{ $i + 1 }}</span><span>{{ $t }}</span></div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('broker.alta.store') }}" enctype="multipart/form-data" id="alta-form" novalidate>
        @csrf

        {{-- 1 · Tipo --}}
        <section class="alta-step" data-step="1">
            <div class="alta-sect">{{ __('Paso :n de 4', ['n' => 1]) }}</div>
            <div class="alta-h">{{ __('Tipo de profesional') }}</div>
            <p class="alta-intro">{{ __('Indícanos si operas como persona física (broker) o como empresa (agencia). Esto define los documentos que te pediremos.') }}</p>
            <div class="alta-types">
                @foreach ($tipos as $k => [$titulo, $desc, $icon])
                    @php $kc = $cfg['kinds'][$k] ?? []; @endphp
                    <label class="alta-type {{ $kind === $k ? 'sel' : '' }}" data-kind="{{ $k }}">
                        <input type="radio" name="kind" value="{{ $k }}" class="sr-only" @checked($kind === $k)>
                        <div class="ic"><i class="pi {{ $icon }}"></i></div>
                        <h4>{{ $titulo }}</h4>
                        <p>{{ $desc }} @if(!empty($kc['tax_note'])) {{ __($kc['tax_note']) }} @endif</p>
                        @if(!empty($kc['commission']))<span class="comm">{{ __('Comisión :p %', ['p' => $kc['commission']]) }}</span>@endif
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 2 · Datos --}}
        <section class="alta-step" data-step="2" hidden>
            <div class="alta-sect">{{ __('Paso :n de 4', ['n' => 2]) }}</div>
            <div class="alta-h">{{ __('Datos personales y profesionales') }}</div>
            <p class="alta-intro">{{ __('Los campos con * son obligatorios.') }}</p>
            <div class="alta-grid">
                @foreach ($camposDatos as [$k, $label, $req, $ph, $ac])
                    <div class="alta-fld {{ $k === 'direccion' ? 'full' : '' }} {{ $errors->has('data.'.$k) ? 'err' : '' }}">
                        <label for="d_{{ $k }}">{{ $label }} @if($req)<span class="req">*</span>@endif</label>
                        <input id="d_{{ $k }}" name="data[{{ $k }}]" value="{{ $d[$k] ?? '' }}" placeholder="{{ $ph }}" autocomplete="{{ $ac }}" @if($req) required @endif @if($k === 'email') type="email" inputmode="email" @elseif($k === 'tel') type="tel" @endif>
                        @error('data.'.$k)<div class="msg">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 3 · Fiscal y bancario --}}
        <section class="alta-step" data-step="3" hidden>
            <div class="alta-sect">{{ __('Paso :n de 4', ['n' => 3]) }}</div>
            <div class="alta-h">{{ __('Datos fiscales y bancarios') }}</div>
            <div class="alta-callout amber"><i class="pi pi-info-circle"></i><div>{{ __('Sirven para emitir las facturas de comisión y hacer las transferencias. Deberás facturar a nombre de :entidad.', ['entidad' => config('company.legal_name')]) }} {{ __('Se guardan cifrados y sólo los ve el equipo de administración.') }}</div></div>
            <div class="alta-grid">
                @foreach ($camposFiscal as [$k, $label, $req, $ph])
                    <div class="alta-fld {{ $errors->has('fiscal.'.$k) ? 'err' : '' }}">
                        <label for="f_{{ $k }}">{{ $label }} @if($req)<span class="req">*</span>@endif</label>
                        <input id="f_{{ $k }}" name="fiscal[{{ $k }}]" value="{{ $f[$k] ?? '' }}" placeholder="{{ $ph }}" autocomplete="off" @if($req) required @endif>
                        @error('fiscal.'.$k)<div class="msg">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 4 · Documentos --}}
        <section class="alta-step" data-step="4" hidden>
            <div class="alta-sect">{{ __('Paso :n de 4', ['n' => 4]) }}</div>
            <div class="alta-h">{{ __('Documentos de identificación (KYC)') }}</div>
            @if(!empty($cfg['law_note']))
                <div class="alta-callout blue"><i class="pi pi-shield"></i><div>{{ __($cfg['law_note']) }}</div></div>
            @endif
            @foreach (['broker', 'agency'] as $k)
                <div class="alta-docs" data-docs-for="{{ $k }}" @if($kind !== $k) hidden @endif>
                    @foreach (\App\Models\BrokerProfile::requiredDocs($k) as $key => $label)
                        @php $onFile = !empty($have[$key]) || ($idOnFile && in_array($key, ['id_doc', 'rep_id'], true)); @endphp
                        <div class="alta-doc {{ $onFile ? 'done' : '' }} {{ $errors->has('docs.'.$key) ? 'err' : '' }}" data-doc="{{ $key }}" data-on-file="{{ $onFile ? 1 : 0 }}">
                            <span class="ck"><i class="pi pi-check"></i></span>
                            <span class="dn">{{ __($label) }} <span class="req text-err">*</span>
                                <span class="fn">{{ $onFile ? __('Ya entregado') : '' }}</span>
                            </span>
                            <input type="file" name="docs[{{ $key }}]" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @if($kind !== $k) disabled @endif>
                            <button type="button" class="alta-btn ghost small" data-pick>{{ $onFile ? __('Cambiar') : __('Subir') }}</button>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <p class="text-[11px] text-ink-400 mt-2">{{ __('PDF, JPG o PNG · máximo 10 MB por archivo.') }}</p>
            <div class="alta-callout amber mt-3" id="alta-doc-msg" role="alert" hidden><i class="pi pi-exclamation-triangle"></i><div></div></div>
            <label class="flex items-start gap-2 mt-4 text-[12px] text-ink-600 cursor-pointer">
                <input type="checkbox" name="consent" value="1" class="mt-0.5 accent-brand" @checked(old('consent'))>
                <span>{{ __('Declaro que la información es correcta y autorizo a :entidad a tratar mis datos para verificar mi identidad y gestionar la colaboración.', ['entidad' => config('company.legal_name')]) }}</span>
            </label>
            @error('consent')<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
        </section>

        <div class="alta-nav">
            <button type="button" class="alta-btn ghost" id="alta-prev"><i class="pi pi-arrow-left"></i> {{ __('Anterior') }}</button>
            <button type="button" class="alta-btn primary" id="alta-next">{{ __('Continuar') }} <i class="pi pi-arrow-right"></i></button>
            <button type="submit" class="alta-btn primary" id="alta-send" hidden>{{ __('Completar registro') }} <i class="pi pi-check"></i></button>
        </div>
    </form>
@endif

</div>
</div>
@endsection

@unless ($readOnly)
@push('scripts')
<script>
(function () {
    var form = document.getElementById('alta-form');
    var step = {{ $pasoError }};
    var steps = form.querySelectorAll('.alta-step');
    var pills = document.querySelectorAll('#alta-steps .alta-st');
    var prev = document.getElementById('alta-prev'), next = document.getElementById('alta-next'), send = document.getElementById('alta-send');
    var NEED = {{ Js::from(__('Este campo es obligatorio.')) }}, MAIL = {{ Js::from(__('Introduce un email válido.')) }}, DOCS = {{ Js::from(__('Sube los documentos marcados para continuar.')) }};

    function kind() { var r = form.querySelector('input[name=kind]:checked'); return r ? r.value : 'broker'; }

    function show(n) {
        step = n;
        steps.forEach(function (s) { s.hidden = +s.dataset.step !== n; });
        pills.forEach(function (p) {
            var i = +p.dataset.st;
            p.classList.toggle('active', i === n);
            p.classList.toggle('done', i < n);
            p.querySelector('.dot').innerHTML = i < n ? '<i class="pi pi-check" style="font-size:10px"></i>' : i;
        });
        prev.style.visibility = n > 1 ? 'visible' : 'hidden';
        next.hidden = n === 4; send.hidden = n !== 4;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function marca(fld, msg) {
        var w = fld.closest('.alta-fld'); if (!w) return;
        w.classList.add('err');
        var m = w.querySelector('.msg') || w.appendChild(Object.assign(document.createElement('div'), { className: 'msg' }));
        m.textContent = msg;
    }

    function valida(n) {
        var ok = true, sec = form.querySelector('.alta-step[data-step="' + n + '"]');
        sec.querySelectorAll('.alta-fld').forEach(function (w) { w.classList.remove('err'); var m = w.querySelector('.msg'); if (m) m.remove(); });
        sec.querySelectorAll('input[required]').forEach(function (i) {
            if (!i.value.trim()) { marca(i, NEED); ok = false; }
            else if (i.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(i.value.trim())) { marca(i, MAIL); ok = false; }
        });
        if (!ok) { var f = sec.querySelector('.alta-fld.err input'); if (f) f.focus(); }
        return ok;
    }

    // Tipo
    form.querySelectorAll('.alta-type').forEach(function (c) {
        c.addEventListener('click', function () {
            form.querySelectorAll('.alta-type').forEach(function (x) { x.classList.toggle('sel', x === c); });
            c.querySelector('input').checked = true;
            form.querySelectorAll('.alta-docs').forEach(function (g) {
                var on = g.dataset.docsFor === c.dataset.kind;
                g.hidden = !on;
                g.querySelectorAll('input[type=file]').forEach(function (i) { i.disabled = !on; });
            });
        });
    });

    // Documentos
    form.querySelectorAll('.alta-doc').forEach(function (row) {
        var input = row.querySelector('input[type=file]');
        row.querySelector('[data-pick]').addEventListener('click', function () { input.click(); });
        input.addEventListener('change', function () {
            var f = input.files[0];
            row.classList.toggle('done', !!f || row.dataset.onFile === '1');
            row.classList.remove('err');
            document.getElementById('alta-doc-msg').hidden = true;
            row.querySelector('.fn').textContent = f ? f.name : '';
            row.querySelector('[data-pick]').textContent = f ? {{ Js::from(__('Cambiar')) }} : {{ Js::from(__('Subir')) }};
        });
    });

    form.querySelector('input[name=consent]').addEventListener('change', function () { document.getElementById('alta-doc-msg').hidden = true; });

    next.addEventListener('click', function () { if (step === 1 || valida(step)) show(step + 1); });
    prev.addEventListener('click', function () { if (step > 1) show(step - 1); });

    form.addEventListener('submit', function (e) {
        var falta = false;
        form.querySelectorAll('.alta-docs:not([hidden]) .alta-doc').forEach(function (row) {
            if (!row.classList.contains('done')) { row.classList.add('err'); falta = true; }
        });
        var consent = form.querySelector('input[name=consent]');
        if (falta || !consent.checked) {
            e.preventDefault();
            var box = document.getElementById('alta-doc-msg');
            box.querySelector('div').textContent = falta ? DOCS : {{ Js::from(__('Debes aceptar la declaración para enviar el alta.')) }};
            box.hidden = false;
            return;
        }
        send.disabled = true;
        send.textContent = {{ Js::from(__('Enviando…')) }};
    });

    show(step);
})();
</script>
@endpush
@endunless
