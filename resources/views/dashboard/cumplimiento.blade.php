@extends('layouts.client')
@section('title', __('Verificación KYC').' — SIEBE')
@section('page_title', __('Verificación KYC'))
@section('page_breadcrumb', __('Mi portal').' · '.__('Verificación KYC'))
@php $activeRoute = 'cumplimiento'; @endphp

{{--
  Formulario «Conozca a su cliente» de Siebe Group (persona física o jurídica,
  según lo que eligió cumplimiento al pedirlo). Campos en App\Support\CddForm;
  el envío lo recibe CddController::clientSubmit.
--}}
@php
    $cdd    = $cdd ?? null;
    $status = $cdd?->kyc_status ?? 'no_solicitado';
    $type   = $cdd?->buyer_type === 'legal' ? 'legal' : 'natural';
    $form   = (array) ($cdd?->kyc_form ?? []);
    $have   = (array) ($cdd?->kyc_docs ?? []);
    $r      = $reservation;
    $editable = in_array($status, ['solicitado', 'info_adicional'], true);
    $precio = (float) ($r?->unit?->price ?? $r?->unit_price ?? 0);
    // Comprador 1: lo que ya sabemos de la reserva, si aún no ha rellenado nada.
    $b1 = (array) ($form['buyer1'] ?? []) + [
        'full_name'         => trim(($r->first_name ?? '').' '.($r->last_name ?? '')),
        'email'             => $r->email ?? '',
        'phone'             => $r->phone ?? '',
        'nationality'       => $r->nationality ?? '',
        'country_residency' => $r->country ?? '',
        'address'           => trim(collect([$r->address ?? null, $r->city ?? null])->filter()->implode(', ')),
        'dob'               => optional($r?->birth_date)->format('Y-m-d') ?? '',
    ];
    $b2 = (array) ($form['buyer2'] ?? []);
    $hasB2 = (bool) old('has_buyer2', $form['has_buyer2'] ?? false);
    $ubos = array_values((array) old('ubo', $form['ubo'] ?? [[]]));
    $ents = array_values((array) old('entities', $form['entities'] ?? []));
@endphp

@push('styles')
<style>
    .cdd-wrap { max-width: 880px; }
    .cdd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 640px) { .cdd-grid { grid-template-columns: 1fr; } }
    .cdd-fld.full { grid-column: 1 / -1; }
    .cdd-fld label { display: block; font-size: 12px; font-weight: 600; color: #525866; margin-bottom: 5px; }
    .cdd-fld .req { color: #fb3748; }
    .cdd-fld .msg { font-size: 11px; color: #fb3748; margin-top: 4px; }
    .cdd-fld.err .cli-input { border-color: #fb3748; }
    .cdd-fld .cli-input { width: 100%; font-size: 16px; }
    @media (min-width: 641px) { .cdd-fld .cli-input { font-size: 13.5px; } }
    .cdd-h { font-size: 14px; font-weight: 600; color: #0a0d14; }
    .cdd-sub { font-size: 12px; color: #6b7280; margin: 2px 0 16px; }
    .cdd-sect { font-size: 10px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; color: #868c98; margin: 18px 0 10px; }
    .cdd-opts { display: flex; flex-wrap: wrap; gap: 8px; }
    .cdd-opt { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #eaecf0; border-radius: 9px; padding: 8px 12px; font-size: 13px; color: #222530; cursor: pointer; }
    .cdd-opt:has(input:checked) { border-color: #2b2b1a; background: #f7f7f2; }
    .cdd-block { border: 1px solid #eaecf0; border-radius: 12px; padding: 14px; margin-top: 10px; }
    .cdd-block[hidden] { display: none; }
    .cdd-doc { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f2f4; flex-wrap: wrap; }
    .cdd-doc:last-child { border-bottom: 0; }
    .cdd-doc .dn { flex: 1 1 240px; min-width: 0; font-size: 13px; color: #222530; }
    .cdd-doc .fn { display: block; font-size: 11px; color: #868c98; margin-top: 2px; }
    .cdd-doc.err .dn { color: #fb3748; }
    .cdd-note { display: flex; gap: 10px; padding: 12px 14px; border-radius: 10px; font-size: 12.5px; line-height: 1.5; }
</style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-7">
<div class="cdd-wrap space-y-5">

    @if (session('success'))
        <div class="px-4 py-3 rounded-lg bg-ok-soft border border-ok/30 text-ok-dark text-[13px] font-medium flex items-center gap-2"><i class="pi pi-check-circle"></i> {{ session('success') }}</div>
    @endif

    @if (! $r)
        <div class="cli-card p-8 text-center text-[13px] text-ink-500">{{ __('Aún no tienes una reserva. La verificación KYC empieza cuando reservas una unidad.') }}</div>
    @elseif (! $editable)
        @php
            [$icon, $tone, $title, $text] = match (true) {
                $status === 'enviado' => ['pi-clock', 'bg-warn-soft text-warn', __('Formulario en revisión'), \App\Models\CddFile::kycAtReservation()
                    ? __('Hemos recibido tu formulario y la unidad queda apartada para ti. El equipo de cumplimiento lo está revisando y te avisará si necesita algo más.')
                    : __('Hemos recibido tu formulario. El equipo de cumplimiento lo está revisando y te avisará si necesita algo más.')],
                $status === 'revisado' && $cdd->cdd_decision === 'aprobado' => ['pi-verified', 'bg-ok-soft text-ok', __('Verificación completada'), __('Tu verificación KYC está aprobada. No necesitas hacer nada más por ahora.')],
                $status === 'revisado' => ['pi-info-circle', 'bg-ink-100 text-ink-600', __('Verificación revisada'), __('El equipo de cumplimiento ha revisado tu expediente. Tu asesor se pondrá en contacto contigo.')],
                default => ['pi-shield', 'bg-ink-100 text-ink-600', __('Verificación pendiente de iniciar'), __('Cuando el equipo de cumplimiento te pida el formulario «Conozca a su cliente», lo rellenarás aquí. Te avisaremos por email.')],
            };
        @endphp
        <div class="cli-card p-8 text-center">
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-[24px] {{ $tone }}"><i class="pi {{ $icon }}"></i></div>
            <div class="text-[17px] font-semibold text-ink-900">{{ $title }}</div>
            <p class="text-[13px] text-ink-500 mt-2 max-w-md mx-auto">{{ $text }}</p>
            @if ($cdd?->kyc_submitted_at)<p class="text-[11px] text-ink-400 mt-4">{{ __('Enviado el :f', ['f' => $cdd->kyc_submitted_at->translatedFormat('j M Y, H:i')]) }}</p>@endif
        </div>
    @else
        <div class="cli-card p-5 sm:p-6">
            <div class="cdd-h">{{ __('Formulario «Conozca a su cliente»') }} · {{ $type === 'legal' ? __('Persona jurídica') : __('Persona física') }}</div>
            @if (\App\Models\CddFile::kycAtReservation() && $status === 'solicitado' && ! $cdd->kyc_submitted_at)
                <div class="cdd-note bg-ink-50 text-ink-700 mt-3"><i class="pi pi-home mt-0.5"></i><div>{{ __('Para reservar la unidad no se paga ningún depósito: envía este formulario con los documentos y la unidad queda apartada para ti mientras el equipo de cumplimiento lo revisa.') }}</div></div>
                <div class="cdd-opts mt-3" role="radiogroup" aria-label="{{ __('¿Cómo compras?') }}">
                    <a href="?reservation={{ $r->id }}&tipo=natural" class="cdd-opt {{ $type === 'natural' ? 'font-semibold' : '' }}" style="{{ $type === 'natural' ? 'border-color:#2b2b1a;background:#f7f7f2' : '' }}"><i class="pi pi-user"></i> {{ __('Compro a mi nombre (persona física)') }}</a>
                    <a href="?reservation={{ $r->id }}&tipo=legal" class="cdd-opt {{ $type === 'legal' ? 'font-semibold' : '' }}" style="{{ $type === 'legal' ? 'border-color:#2b2b1a;background:#f7f7f2' : '' }}"><i class="pi pi-building"></i> {{ __('Compro a través de una sociedad (persona jurídica)') }}</a>
                </div>
            @endif
            <p class="cdd-sub">{{ __('Nuestro procedimiento «Conozca a su cliente» es un requisito previo para prestarte nuestros servicios. Toda la información que pedimos la exige la ley y la normativa del Banco Central de Aruba. Siebe Group puede pedir información adicional para verificar tu identidad, el origen de los fondos y del patrimonio, y cualquier otra documentación necesaria.') }}</p>
            @if ($status === 'info_adicional' && $cdd->info_request)
                <div class="cdd-note bg-warn-soft text-warn-dark"><i class="pi pi-exclamation-triangle mt-0.5"></i><div><b>{{ __('El equipo de cumplimiento necesita:') }}</b> {{ $cdd->info_request }}</div></div>
            @endif
            @if ($errors->any())
                <div class="cdd-note bg-err-soft text-err mt-3" role="alert"><i class="pi pi-exclamation-circle mt-0.5"></i><div>{{ __('Revisa los campos marcados.') }} {{ $errors->first() }}</div></div>
            @endif
            <div class="cdd-sect">{{ __('Datos de la unidad') }}</div>
            <dl class="grid grid-cols-1 {{ (float) $r->reservation_fee > 0 ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-3 text-[13px]">
                <div class="border border-ink-200 rounded-lg px-3 py-2"><dt class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Unidad') }}</dt><dd class="font-semibold text-ink-900">{{ $r->unit?->custom_id ?? $r->unit_name ?? '—' }}</dd></div>
                <div class="border border-ink-200 rounded-lg px-3 py-2"><dt class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Precio de compra') }}</dt><dd class="font-semibold text-ink-900">{{ $precio ? '$'.number_format($precio) : '—' }}</dd></div>
                @if ((float) $r->reservation_fee > 0)
                <div class="border border-ink-200 rounded-lg px-3 py-2"><dt class="text-[10px] uppercase tracking-wide text-ink-400 font-semibold">{{ __('Importe de la reserva') }}</dt><dd class="font-semibold text-ink-900">{{ $r->reservation_fee ? '$'.number_format((float) $r->reservation_fee) : '—' }}</dd></div>
                @endif
            </dl>
        </div>

        <form method="POST" action="{{ route('dashboard.cumplimiento.submit') }}" enctype="multipart/form-data" class="space-y-5" id="cdd-form" novalidate>
            @csrf

            @if ($type === 'natural')
                @foreach ([1, 2] as $n)
                    @php $vals = $n === 1 ? $b1 : $b2; @endphp
                    @if ($n === 2)
                        <label class="cli-card p-4 flex items-center gap-3 cursor-pointer text-[13px] text-ink-800">
                            <input type="checkbox" name="has_buyer2" value="1" id="has_buyer2" class="accent-brand w-4 h-4" @checked($hasB2)>
                            {{ __('Compramos dos personas (añadir el comprador 2)') }}
                        </label>
                    @endif
                    <div class="cli-card p-5 sm:p-6" @if($n === 2) id="buyer2-card" @if(! $hasB2) hidden @endif @endif>
                        <div class="cdd-h">{{ __('Comprador :n', ['n' => $n]) }}</div>
                        <p class="cdd-sub">{{ __('Datos personales') }}</p>
                        <div class="cdd-grid">
                            @foreach (\App\Support\CddForm::buyerFields() as $f)
                                @include('dashboard._cdd.field', ['name' => "buyer{$n}[{$f[0]}]", 'key' => "buyer{$n}.{$f[0]}", 'label' => $f[1], 'type' => $f[2], 'req' => $f[3], 'opts' => $f[4] ?? [], 'val' => $vals[$f[0]] ?? ''])
                            @endforeach
                        </div>
                        <div class="cdd-sect">{{ __('Fuente de ingresos') }}</div>
                        @php $it = old("buyer{$n}.income_type", $vals['income_type'] ?? ''); $inc = (array) old("buyer{$n}.income", $vals['income'] ?? []); @endphp
                        <div class="cdd-opts" data-income-for="{{ $n }}">
                            @foreach (\App\Support\CddForm::INCOME as $k => $l)
                                <label class="cdd-opt"><input type="radio" name="buyer{{ $n }}[income_type]" value="{{ $k }}" @checked($it === $k)> <span>{{ __($l) }}</span></label>
                            @endforeach
                        </div>
                        @error("buyer{$n}.income_type")<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
                        @foreach (\App\Support\CddForm::incomeFields() as $k => $fields)
                            <div class="cdd-block" data-income-block="{{ $n }}-{{ $k }}" @if($it !== $k) hidden @endif>
                                <div class="cdd-grid">
                                    @foreach ($fields as [$fk, $fl])
                                        @include('dashboard._cdd.field', ['name' => "buyer{$n}[income][{$k}][{$fk}]", 'key' => "buyer{$n}.income.{$k}.{$fk}", 'label' => $fl, 'type' => 'text', 'req' => false, 'opts' => [], 'val' => $inc[$k][$fk] ?? ''])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="cli-card p-5 sm:p-6">
                    <div class="cdd-h">{{ __('Información de pago') }}</div>
                    <p class="cdd-sub">{{ __('¿Cómo vas a pagar la unidad? Puedes marcar más de una opción. Siebe Group puede pedir justificantes adicionales según el caso.') }}</p>
                    @php $pm = (array) old('payment.methods', $form['payment']['methods'] ?? []); $pd = (array) old('payment.detail', $form['payment']['detail'] ?? []); @endphp
                    <div class="cdd-opts">
                        @foreach (\App\Support\CddForm::PAYMENT as $k => $l)
                            <label class="cdd-opt"><input type="checkbox" name="payment[methods][]" value="{{ $k }}" data-pay="{{ $k }}" @checked(in_array($k, $pm, true))> <span>{{ __($l) }}</span></label>
                        @endforeach
                    </div>
                    @error('payment.methods')<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
                    @foreach (\App\Support\CddForm::paymentFields() as $k => $fields)
                        <div class="cdd-block" data-pay-block="{{ $k }}" @if(! in_array($k, $pm, true)) hidden @endif>
                            <div class="text-[12px] font-semibold text-ink-700 mb-2">{{ __(\App\Support\CddForm::PAYMENT[$k]) }}</div>
                            <div class="cdd-grid">
                                @foreach ($fields as [$fk, $fl])
                                    @include('dashboard._cdd.field', ['name' => "payment[detail][{$k}][{$fk}]", 'key' => "payment.detail.{$k}.{$fk}", 'label' => $fl, 'type' => $fk === 'amount' ? 'money' : 'text', 'req' => false, 'opts' => [], 'val' => $pd[$k][$fk] ?? ''])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cli-card p-5 sm:p-6">
                    <div class="cdd-h">{{ __('Identificación de la sociedad') }}</div>
                    <p class="cdd-sub">{{ __('Adjunta un extracto de la Cámara de Comercio de menos de 3 meses. Sociedades extranjeras: registro de la sociedad y copia certificada de la escritura de constitución.') }}</p>
                    <div class="cdd-grid">
                        @foreach (\App\Support\CddForm::companyFields() as $f)
                            @include('dashboard._cdd.field', ['name' => "company[{$f[0]}]", 'key' => "company.{$f[0]}", 'label' => $f[1], 'type' => $f[2], 'req' => $f[3], 'opts' => $f[4] ?? [], 'val' => $form['company'][$f[0]] ?? ''])
                        @endforeach
                    </div>
                </div>

                <div class="cli-card p-5 sm:p-6">
                    <div class="cdd-h">{{ __('Beneficiarios finales (UBO)') }}</div>
                    <p class="cdd-sub">{{ __('Personas físicas con más del 25 % del capital o de los derechos de voto, o que ejercen el control de la sociedad de otra forma; o beneficiarias del 25 % o más del patrimonio de una fundación o un trust.') }}</p>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="cdd-block" data-ubo="{{ $i }}" @if($i > 0 && empty(array_filter((array) ($ubos[$i] ?? [])))) hidden @endif>
                            <div class="text-[12px] font-semibold text-ink-700 mb-2">{{ __('Beneficiario final :n', ['n' => $i + 1]) }}</div>
                            <div class="cdd-grid">
                                @foreach (\App\Support\CddForm::uboFields() as $f)
                                    @include('dashboard._cdd.field', ['name' => "ubo[{$i}][{$f[0]}]", 'key' => "ubo.{$i}.{$f[0]}", 'label' => $f[1] . (in_array($f[2], ['percent']) ? ' (%)' : ''), 'type' => $f[2], 'req' => $i === 0 && $f[3], 'opts' => $f[4] ?? [], 'val' => $ubos[$i][$f[0]] ?? ''])
                                @endforeach
                            </div>
                        </div>
                    @endfor
                    <button type="button" class="cli-btn cli-btn-ghost mt-3" id="add-ubo"><i class="pi pi-plus"></i> {{ __('Añadir beneficiario final') }}</button>
                </div>

                <div class="cli-card p-5 sm:p-6">
                    <div class="cdd-h">{{ __('Entidades de la estructura con un 25 % o más de propiedad o control') }}</div>
                    <p class="cdd-sub">{{ __('Si no hay ninguna, déjalo en blanco. Si la estructura es más compleja, adjunta un organigrama certificado con la declaración de un asesor independiente.') }}</p>
                    @for ($i = 0; $i < 3; $i++)
                        <div class="cdd-block" data-ent="{{ $i }}" @if(empty(array_filter((array) ($ents[$i] ?? [])))) hidden @endif>
                            <div class="cdd-grid">
                                @foreach (\App\Support\CddForm::entityFields() as $f)
                                    @include('dashboard._cdd.field', ['name' => "entities[{$i}][{$f[0]}]", 'key' => "entities.{$i}.{$f[0]}", 'label' => $f[1] . ($f[2] === 'percent' ? ' (%)' : ''), 'type' => $f[2], 'req' => false, 'opts' => [], 'val' => $ents[$i][$f[0]] ?? ''])
                                @endforeach
                            </div>
                        </div>
                    @endfor
                    <button type="button" class="cli-btn cli-btn-ghost mt-3" id="add-ent"><i class="pi pi-plus"></i> {{ __('Añadir entidad') }}</button>
                </div>

                <div class="cli-card p-5 sm:p-6">
                    <div class="cdd-h">{{ __('Naturaleza de la relación') }}</div>
                    @php $rs = (array) old('reasons', $form['reasons'] ?? []); $pu = old('purpose', $form['purpose'] ?? ''); @endphp
                    <div class="cdd-sect">{{ __('¿Por qué compras la unidad a través de una sociedad?') }}</div>
                    <div class="cdd-opts">
                        @foreach (\App\Support\CddForm::REASONS as $k => $l)
                            <label class="cdd-opt"><input type="checkbox" name="reasons[]" value="{{ $k }}" @checked(in_array($k, $rs, true))> <span>{{ __($l) }}</span></label>
                        @endforeach
                    </div>
                    @error('reasons')<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
                    <div class="cdd-grid mt-3">@include('dashboard._cdd.field', ['name' => 'reasons_other', 'key' => 'reasons_other', 'label' => 'Otro motivo (explícalo)', 'type' => 'text', 'req' => false, 'opts' => [], 'val' => $form['reasons_other'] ?? ''])</div>
                    <div class="cdd-sect">{{ __('¿Para qué vas a usar la unidad?') }}</div>
                    <div class="cdd-opts">
                        @foreach (\App\Support\CddForm::PURPOSE as $k => $l)
                            <label class="cdd-opt"><input type="radio" name="purpose" value="{{ $k }}" @checked($pu === $k)> <span>{{ __($l) }}</span></label>
                        @endforeach
                    </div>
                    @error('purpose')<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
                    <div class="cdd-grid mt-3">
                        @include('dashboard._cdd.field', ['name' => 'purpose_other', 'key' => 'purpose_other', 'label' => 'Otro uso (explícalo)', 'type' => 'text', 'req' => false, 'opts' => [], 'val' => $form['purpose_other'] ?? ''])
                        @include('dashboard._cdd.field', ['name' => 'description', 'key' => 'description', 'label' => 'Breve descripción de lo anterior (si aplica)', 'type' => 'textarea', 'req' => false, 'opts' => [], 'val' => $form['description'] ?? ''])
                    </div>
                </div>
            @endif

            <div class="cli-card p-5 sm:p-6">
                <div class="cdd-h">{{ __('Documentos') }}</div>
                <p class="cdd-sub">{{ __('PDF, JPG o PNG · máximo 10 MB por archivo. Puedes subir varios archivos por apartado.') }}</p>
                @foreach (\App\Support\CddForm::docs($type) as $key => [$label, $required])
                    @php $n = count($have[$key] ?? []); @endphp
                    <div class="cdd-doc {{ $errors->has('docs.'.$key) ? 'err' : '' }}">
                        <div class="dn">{{ __($label) }} @if($required)<span class="text-err">*</span>@endif
                            <span class="fn" data-fn>{{ $n ? trans_choice(':n archivo entregado|:n archivos entregados', $n, ['n' => $n]) : '' }}</span>
                            @error('docs.'.$key)<span class="fn text-err">{{ $message }}</span>@enderror
                        </div>
                        <label class="cli-btn cli-btn-ghost cursor-pointer shrink-0">
                            <i class="pi pi-upload"></i> {{ $n ? __('Añadir') : __('Subir') }}
                            <input type="file" name="docs[{{ $key }}][]" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" data-doc-input>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="cli-card p-5 sm:p-6">
                <div class="cdd-h">{{ __('Firma') }}</div>
                <p class="cdd-sub">{{ __('El firmante declara que la información facilitada es correcta y verdadera, y que avisará a Siebe Group si cambia.') }}</p>
                <div class="cdd-grid">
                    @include('dashboard._cdd.field', ['name' => 'decl[name]', 'key' => 'decl.name', 'label' => 'Nombre del firmante', 'type' => 'text', 'req' => true, 'opts' => [], 'val' => $form['decl']['name'] ?? ($b1['full_name'] ?? '')])
                    <div class="cdd-fld"><label>{{ __('Fecha') }}</label><input class="cli-input pl-3" value="{{ now()->translatedFormat('j M Y') }}" disabled></div>
                </div>
                <label class="flex items-start gap-2 mt-4 text-[12.5px] text-ink-700 cursor-pointer">
                    <input type="checkbox" name="decl[accept]" value="1" class="mt-0.5 accent-brand" @checked(old('decl.accept'))>
                    <span>{{ __('Declaro que la información es correcta y verdadera, que he adjuntado los documentos indicados y que avisaré a Siebe Group si cambia algún dato.') }}</span>
                </label>
                @error('decl.accept')<div class="text-[11px] text-err mt-1">{{ $message }}</div>@enderror
                <div class="flex justify-end mt-5">
                    <button type="submit" class="cli-btn cli-btn-primary" id="cdd-send"><i class="pi pi-send"></i> {{ __('Enviar formulario') }}</button>
                </div>
            </div>
        </form>
    @endif
</div>
</div>
@endsection

@if ($editable ?? false)
@push('scripts')
<script>
(function () {
    var form = document.getElementById('cdd-form');
    // Fuente de ingresos: un bloque de detalle por tipo.
    form.querySelectorAll('[data-income-for] input').forEach(function (r) {
        r.addEventListener('change', function () {
            var n = r.closest('[data-income-for]').dataset.incomeFor;
            form.querySelectorAll('[data-income-block^="' + n + '-"]').forEach(function (b) { b.hidden = b.dataset.incomeBlock !== n + '-' + r.value; });
        });
    });
    // Métodos de pago: un bloque por método marcado.
    form.querySelectorAll('[data-pay]').forEach(function (c) {
        c.addEventListener('change', function () { var b = form.querySelector('[data-pay-block="' + c.dataset.pay + '"]'); if (b) b.hidden = !c.checked; });
    });
    var b2 = document.getElementById('has_buyer2');
    if (b2) b2.addEventListener('change', function () { document.getElementById('buyer2-card').hidden = !b2.checked; });
    // UBO y entidades: «Añadir» enseña el siguiente hueco.
    [['add-ubo', '[data-ubo]'], ['add-ent', '[data-ent]']].forEach(function (p) {
        var btn = document.getElementById(p[0]); if (!btn) return;
        var sync = function () { btn.hidden = !form.querySelector(p[1] + '[hidden]'); };
        btn.addEventListener('click', function () { var next = form.querySelector(p[1] + '[hidden]'); if (next) next.hidden = false; sync(); });
        sync();
    });
    form.querySelectorAll('[data-doc-input]').forEach(function (i) {
        i.addEventListener('change', function () {
            var fn = i.closest('.cdd-doc').querySelector('[data-fn]');
            fn.textContent = Array.prototype.map.call(i.files, function (f) { return f.name; }).join(' · ');
            i.closest('.cdd-doc').classList.remove('err');
        });
    });
    form.addEventListener('submit', function () { var s = document.getElementById('cdd-send'); s.disabled = true; s.textContent = {{ Js::from(__('Enviando…')) }}; });
})();
</script>
@endpush
@endif
