<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Verificación KYC') }} · SIEBE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/primeicons/primeicons.css') }}" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans:    ['Inter', 'system-ui', 'sans-serif'],
              display: ['"Inter Tight"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
              brand: { DEFAULT:'#2b2b1a', dark:'#3f3f28', soft:'#2b2b1a33', tint:'#f1ede9' },
              ink: { 950:'#171717', 900:'#222530', 700:'#2b303b', 600:'#5c5c5c', 500:'#717784', 400:'#a3a3a3', 300:'#cacfd8', 200:'#ebebeb', 100:'#f2f5f8', 50:'#f8f8f8' },
              err: { DEFAULT:'#fb3748', soft:'#ffebec' },
              ok:  { DEFAULT:'#1fc16b', soft:'#e3f7ec', dark:'#178c4e' },
              warn:{ DEFAULT:'#fa7319', soft:'#fff3eb', dark:'#c2540a' },
            },
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; background:#fff; color:#171717; }
      .bg-pattern { background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px); background-size: 24px 24px; }

      /* Mismos campos y botones que el registro (auth/auth.blade.php). */
      .cli-input {
        width:100%; height:40px; padding:0 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; color:#171717; font-size:16px;
        transition: border-color .15s, box-shadow .15s;
      }
      @media (min-width: 641px) { .cli-input { font-size:14px; } }
      textarea.cli-input { height:auto; padding:10px 14px; }
      select.cli-input { appearance:none; padding-right:36px; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a3a3a3' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat:no-repeat; background-position: right 12px center; }
      .cli-input:focus { outline:none; border-color:#2b2b1a; box-shadow:0 0 0 3px rgba(43,43,26,.15); }
      .cli-input:disabled { background:#f8f8f8; color:#717784; }
      .auth-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        height:40px; padding:0 16px; border-radius:10px;
        font-weight:500; font-size:14px; line-height:1; cursor:pointer;
        transition: background-color .15s, border-color .15s, color .15s, transform .12s;
      }
      .auth-btn:active { transform: translateY(1px); }
      .auth-btn-primary { background:#2b2b1a; color:#fff; border:1px solid #2b2b1a; box-shadow: 0 1px 2px 0 rgba(10,13,20,.06); }
      .auth-btn-primary:hover { background:#3f3f28; border-color:#3f3f28; }
      .auth-btn-primary:disabled { background:#a3a3a3; border-color:#a3a3a3; cursor:not-allowed; }
      .auth-btn-ghost { background:#f5f5f5; color:#171717; border:1px solid transparent; }
      .auth-btn-ghost:hover { background:#ebebeb; }

      .step-pill { display:inline-flex; align-items:center; gap:8px; font-size:14px; color:#a3a3a3; font-weight:500; white-space:nowrap; }
      .step-pill .num { width:22px; height:22px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1px solid #ebebeb; font-size:11px; font-weight:600; color:#a3a3a3; transition: background-color .2s, color .2s, border-color .2s; }
      .step-pill.active { color:#171717; }
      .step-pill.active .num { background:#222530; color:#fff; border-color:#222530; }
      .step-pill.done { color:#171717; }
      .step-pill.done .num { background:#1fc16b; color:#fff; border-color:#1fc16b; }

      @keyframes kyc-in { from { opacity:0; transform: translateX(28px); } to { opacity:1; transform:none; } }
      @keyframes kyc-in-back { from { opacity:0; transform: translateX(-28px); } to { opacity:1; transform:none; } }
      .kyc-step { display:none; }
      .kyc-step.active { display:block; animation: kyc-in .42s cubic-bezier(.22,1,.36,1) both; }
      body[data-dir="back"] .kyc-step.active { animation-name: kyc-in-back; }
      @media (prefers-reduced-motion: reduce) { .kyc-step.active { animation:none; } }

      .kyc-icon { width:80px; height:80px; border-radius:999px; border:1px solid #ebebeb; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; background:#fff; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
      .kyc-icon i { font-size:26px; color:#5c5c5c; }
      .kyc-h1 { font-family:'Inter Tight','Inter',sans-serif; font-size:26px; line-height:32px; font-weight:500; color:#171717; }
      @media (max-width: 640px) { .kyc-h1 { font-size:22px; line-height:28px; } }
      .kyc-lead { font-size:14px; color:#717784; margin-top:8px; }

      .role-card { display:flex; align-items:center; gap:14px; padding:16px; border:1px solid #ebebeb; border-radius:14px; background:#fff; cursor:pointer; transition: border-color .15s, background-color .15s; }
      .role-card:hover { background:#f8f8f8; }
      .role-card.selected { border-color:#2b2b1a; box-shadow:0 0 0 1px #2b2b1a; }
      .role-card .ic { width:40px; height:40px; border-radius:10px; background:#f5f5f5; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
      .role-card .radio-dot { width:18px; height:18px; border-radius:999px; border:1.5px solid #cacfd8; margin-left:auto; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
      .role-card.selected .radio-dot { border-color:#2b2b1a; }
      .role-card.selected .radio-dot::after { content:""; width:9px; height:9px; border-radius:999px; background:#2b2b1a; }

      .cdd-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
      @media (max-width: 640px) { .cdd-grid { grid-template-columns:1fr; } }
      .cdd-fld.full { grid-column: 1 / -1; }
      .cdd-fld label { display:block; font-size:13px; font-weight:500; color:#171717; margin-bottom:6px; }
      .cdd-fld .req { color:#fb3748; }
      .cdd-fld .msg { font-size:11px; color:#fb3748; margin-top:4px; }
      .cdd-fld.err .cli-input, .cli-input.is-invalid { border-color:#fb3748; box-shadow:0 0 0 3px rgba(251,55,72,.14); }
      .cdd-sect { font-size:10px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:#a3a3a3; margin:24px 0 10px; }
      .cdd-box { border:1px solid #ebebeb; border-radius:14px; padding:18px; background:#fff; }
      .cdd-box + .cdd-box { margin-top:14px; }
      .cdd-box-h { font-size:14px; font-weight:600; color:#171717; margin-bottom:12px; }
      .cdd-opts { display:flex; flex-wrap:wrap; gap:8px; }
      .cdd-opt { display:inline-flex; align-items:center; gap:8px; border:1px solid #ebebeb; border-radius:10px; padding:9px 12px; font-size:13px; color:#222530; cursor:pointer; background:#fff; transition: border-color .15s, background-color .15s; }
      .cdd-opt:hover { background:#f8f8f8; }
      .cdd-opt:has(input:checked) { border-color:#2b2b1a; box-shadow:0 0 0 1px #2b2b1a; background:#fff; }
      .cdd-opt input { accent-color:#2b2b1a; }
      .cdd-opts.is-invalid .cdd-opt { border-color:#fb3748; }
      .cdd-block { border:1px dashed #ebebeb; border-radius:12px; padding:14px; margin-top:10px; }
      .cdd-block[hidden], .cdd-box[hidden] { display:none; }
      .cdd-doc { display:flex; align-items:center; gap:14px; padding:12px 0; flex-wrap:wrap; }
      .cdd-doc + .cdd-doc { border-top:1px dashed #ebebeb; }
      .cdd-doc .dn { flex:1 1 240px; min-width:0; font-size:13px; color:#222530; }
      .cdd-doc .fn { display:block; font-size:11px; color:#717784; margin-top:2px; }
      .cdd-doc .fn.ok { color:#178c4e; }
      .cdd-doc.err .dn, .cdd-doc.is-invalid .dn { color:#fb3748; }
      .cdd-note { display:flex; gap:10px; padding:12px 14px; border-radius:10px; font-size:13px; line-height:1.5; }
      .kyc-unit { display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:10px; }
      .kyc-unit > div { border:1px solid #ebebeb; border-radius:12px; padding:10px 14px; background:#fff; }
      .kyc-unit dt { font-size:10px; text-transform:uppercase; letter-spacing:.12em; color:#a3a3a3; font-weight:600; }
      .kyc-unit dd { font-size:14px; font-weight:600; color:#171717; margin-top:2px; }
    </style>
</head>
<body data-dir="fwd">
{{--
  Formulario «Conozca a su cliente» de Siebe Group (persona física o jurídica).
  Página propia, con el aspecto del registro y por pasos: en Siebe la reserva se
  hace con este formulario (sin depósito, ver CddFile::kycAtReservation).
  Campos en App\Support\CddForm; el envío lo recibe CddController::clientSubmit.
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
    // Sólo el comprador de una reserva web elige el tipo; si lo pidió cumplimiento, ya viene dado.
    $chooseType = $editable && \App\Models\CddFile::kycAtReservation() && $status === 'solicitado' && ! $cdd->kyc_submitted_at;
    $steps = $type === 'natural'
        ? [__('Reserva'), __('Compradores'), __('Pago'), __('Documentos'), __('Firma')]
        : [__('Reserva'), __('Sociedad'), __('Beneficiarios'), __('Finalidad'), __('Documentos'), __('Firma')];
    $unitName = $r?->unit?->custom_id ?? $r?->unit_name ?? '—';
    $sinDeposito = $r && (float) $r->reservation_fee <= 0;
@endphp

<div class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-ink-100">
        <div class="relative flex items-center justify-between px-5 sm:px-7 lg:px-11 py-5">
            @include('auth._logo')

            @if ($r && $editable)
                <div id="step-indicator" class="hidden xl:flex absolute left-1/2 -translate-x-1/2 items-center gap-4">
                    @foreach ($steps as $i => $label)
                        <div class="step-pill" data-pill="{{ $i }}"><span class="num">{{ $i + 1 }}</span><span>{{ $label }}</span></div>
                        @if (! $loop->last)<i class="pi pi-angle-right text-ink-300 text-[12px]"></i>@endif
                    @endforeach
                </div>
            @endif

            <div class="flex items-center gap-3">
                @if ($r && $editable)
                    <span class="xl:hidden text-[13px] text-ink-500" id="step-count"></span>
                @endif
                <a href="{{ url('/dashboard') }}" class="auth-btn auth-btn-ghost w-10 px-0" title="{{ __('Ir a mi portal') }}" aria-label="{{ __('Ir a mi portal') }}"><i class="pi pi-times text-[12px]"></i></a>
            </div>
        </div>
    </header>

    <main class="flex-1 bg-pattern">
        <div class="w-full max-w-[640px] mx-auto px-4 sm:px-5 pt-10 pb-16">

            @if (session('success'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-ok-soft border border-ok/30 text-ok-dark text-[13px] font-medium flex items-center gap-2"><i class="pi pi-check-circle"></i> {{ session('success') }}</div>
            @endif

            @if (! $r)
                <div class="text-center">
                    <div class="kyc-icon"><i class="pi pi-home"></i></div>
                    <h1 class="kyc-h1">{{ __('Aún no tienes una reserva') }}</h1>
                    <p class="kyc-lead">{{ __('La verificación KYC empieza cuando reservas una unidad.') }}</p>
                    <a href="{{ url('/') }}" class="auth-btn auth-btn-primary mt-7">{{ __('Ver unidades') }}</a>
                </div>
            @elseif (! $editable)
                @php
                    [$icon, $tone, $title, $text] = match (true) {
                        $status === 'enviado' => ['pi-clock', '#fa7319', __('Formulario en revisión'), \App\Models\CddFile::kycAtReservation()
                            ? __('Hemos recibido tu formulario y la unidad queda apartada para ti. El equipo de cumplimiento lo está revisando y te avisará si necesita algo más.')
                            : __('Hemos recibido tu formulario. El equipo de cumplimiento lo está revisando y te avisará si necesita algo más.')],
                        $status === 'revisado' && $cdd->cdd_decision === 'aprobado' => ['pi-verified', '#1fc16b', __('Verificación completada'), __('Tu verificación KYC está aprobada. No necesitas hacer nada más por ahora.')],
                        $status === 'revisado' => ['pi-info-circle', '#5c5c5c', __('Verificación revisada'), __('El equipo de cumplimiento ha revisado tu expediente. Tu asesor se pondrá en contacto contigo.')],
                        default => ['pi-shield', '#5c5c5c', __('Verificación pendiente de iniciar'), __('Cuando el equipo de cumplimiento te pida el formulario «Conozca a su cliente», lo rellenarás aquí. Te avisaremos por email.')],
                    };
                @endphp
                <div class="text-center">
                    <div class="kyc-icon"><i class="pi {{ $icon }}" style="color: {{ $tone }}"></i></div>
                    <h1 class="kyc-h1">{{ $title }}</h1>
                    <p class="kyc-lead max-w-md mx-auto">{{ $text }}</p>
                    <dl class="kyc-unit text-left mt-8">
                        <div><dt>{{ __('Unidad') }}</dt><dd>{{ $unitName }}</dd></div>
                        <div><dt>{{ __('Código') }}</dt><dd>{{ $r->reservation_code }}</dd></div>
                        @if ($cdd?->kyc_submitted_at)<div><dt>{{ __('Enviado') }}</dt><dd>{{ $cdd->kyc_submitted_at->translatedFormat('j M Y, H:i') }}</dd></div>@endif
                    </dl>
                    <a href="{{ url('/dashboard') }}" class="auth-btn auth-btn-primary mt-8">{{ __('Ir a mi portal') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('dashboard.cumplimiento.submit') }}" enctype="multipart/form-data" id="cdd-form" novalidate>
                    @csrf

                    {{-- ===== 1 · Reserva ===== --}}
                    <section class="kyc-step" data-step="0">
                        <div class="text-center mb-7">
                            <div class="kyc-icon"><i class="pi pi-home"></i></div>
                            <h1 class="kyc-h1">{{ __('Reserva la unidad :u', ['u' => $unitName]) }}</h1>
                            <p class="kyc-lead">{{ $sinDeposito ? __('Sin depósito: envía este formulario con tus documentos y la unidad queda apartada para ti mientras el equipo de cumplimiento lo revisa.') : __('Formulario «Conozca a su cliente» de Siebe Group.') }}</p>
                        </div>
                        <div class="h-px bg-ink-200/70 mb-6"></div>

                        @if ($status === 'info_adicional' && $cdd->info_request)
                            <div class="cdd-note bg-warn-soft text-warn-dark mb-5"><i class="pi pi-exclamation-triangle mt-0.5"></i><div><b>{{ __('El equipo de cumplimiento necesita:') }}</b> {{ $cdd->info_request }}</div></div>
                        @endif
                        @if ($errors->any())
                            <div class="cdd-note bg-err-soft text-err mb-5" role="alert"><i class="pi pi-exclamation-circle mt-0.5"></i><div>{{ __('Revisa los campos marcados.') }} {{ $errors->first() }}</div></div>
                        @endif

                        <dl class="kyc-unit mb-7">
                            <div><dt>{{ __('Unidad') }}</dt><dd>{{ $unitName }}</dd></div>
                            <div><dt>{{ __('Precio de compra') }}</dt><dd>{{ $precio ? '$'.number_format($precio) : '—' }}</dd></div>
                            @if ($sinDeposito)
                                <div><dt>{{ __('Depósito') }}</dt><dd>{{ __('Sin depósito') }}</dd></div>
                            @else
                                <div><dt>{{ __('Importe de la reserva') }}</dt><dd>${{ number_format((float) $r->reservation_fee) }}</dd></div>
                            @endif
                        </dl>

                        <div class="text-[13px] font-medium text-ink-950 mb-3">{{ __('¿Cómo compras?') }}</div>
                        <div class="space-y-3">
                            @foreach (['natural' => ['pi-user', __('A mi nombre'), __('Persona física: una o dos personas compran la unidad.')], 'legal' => ['pi-building', __('A través de una sociedad'), __('Persona jurídica: una empresa, fundación o trust.')]] as $k => [$ic, $tt, $dd])
                                @php $sel = $type === $k; @endphp
                                @continue(! $chooseType && ! $sel)
                                <a @if($chooseType && ! $sel) href="?reservation={{ $r->id }}&tipo={{ $k }}" @endif class="role-card {{ $sel ? 'selected' : '' }}">
                                    <span class="ic"><i class="pi {{ $ic }} text-ink-600"></i></span>
                                    <span><span class="block text-[14px] font-semibold text-ink-950">{{ $tt }}</span><span class="block text-[12.5px] text-ink-500 mt-0.5">{{ $dd }}</span></span>
                                    <span class="radio-dot"></span>
                                </a>
                            @endforeach
                        </div>
                        <p class="text-[12px] text-ink-500 mt-5 leading-relaxed">{{ __('Nuestro procedimiento «Conozca a su cliente» es un requisito previo para prestarte nuestros servicios. Toda la información que pedimos la exige la ley y la normativa del Banco Central de Aruba. Siebe Group puede pedir información adicional para verificar tu identidad, el origen de los fondos y del patrimonio, y cualquier otra documentación necesaria.') }}</p>
                    </section>

                    @if ($type === 'natural')
                        {{-- ===== 2 · Compradores ===== --}}
                        <section class="kyc-step" data-step="1">
                            <div class="text-center mb-7">
                                <div class="kyc-icon"><i class="pi pi-users"></i></div>
                                <h1 class="kyc-h1">{{ __('Datos de los compradores') }}</h1>
                                <p class="kyc-lead">{{ __('Datos personales y fuente de ingresos de cada comprador.') }}</p>
                            </div>
                            <div class="h-px bg-ink-200/70 mb-6"></div>
                            @foreach ([1, 2] as $n)
                                @php $vals = $n === 1 ? $b1 : $b2; @endphp
                                @if ($n === 2)
                                    <label class="cdd-opt w-full mt-4 mb-4"><input type="checkbox" name="has_buyer2" value="1" id="has_buyer2" @checked($hasB2)> <span>{{ __('Compramos dos personas (añadir el comprador 2)') }}</span></label>
                                @endif
                                <div class="cdd-box" @if($n === 2) id="buyer2-card" @if(! $hasB2) hidden @endif @endif>
                                    <div class="cdd-box-h">{{ __('Comprador :n', ['n' => $n]) }}</div>
                                    <div class="cdd-grid">
                                        @foreach (\App\Support\CddForm::buyerFields() as $f)
                                            @include('dashboard._cdd.field', ['name' => "buyer{$n}[{$f[0]}]", 'key' => "buyer{$n}.{$f[0]}", 'label' => $f[1], 'type' => $f[2], 'req' => $f[3], 'opts' => $f[4] ?? [], 'val' => $vals[$f[0]] ?? ''])
                                        @endforeach
                                    </div>
                                    <div class="cdd-sect">{{ __('Fuente de ingresos') }} <span class="text-err">*</span></div>
                                    @php $it = old("buyer{$n}.income_type", $vals['income_type'] ?? ''); $inc = (array) old("buyer{$n}.income", $vals['income'] ?? []); @endphp
                                    <div class="cdd-opts" data-income-for="{{ $n }}" data-required-group>
                                        @foreach (\App\Support\CddForm::INCOME as $k => $l)
                                            <label class="cdd-opt"><input type="radio" name="buyer{{ $n }}[income_type]" value="{{ $k }}" @checked($it === $k)> <span>{{ __($l) }}</span></label>
                                        @endforeach
                                    </div>
                                    @error("buyer{$n}.income_type")<div class="text-[11px] text-err mt-1" data-err>{{ $message }}</div>@enderror
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
                        </section>

                        {{-- ===== 3 · Pago ===== --}}
                        <section class="kyc-step" data-step="2">
                            <div class="text-center mb-7">
                                <div class="kyc-icon"><i class="pi pi-wallet"></i></div>
                                <h1 class="kyc-h1">{{ __('Información de pago') }}</h1>
                                <p class="kyc-lead">{{ __('¿Cómo vas a pagar la unidad? Puedes marcar más de una opción. Siebe Group puede pedir justificantes adicionales según el caso.') }}</p>
                            </div>
                            <div class="h-px bg-ink-200/70 mb-6"></div>
                            @php $pm = (array) old('payment.methods', $form['payment']['methods'] ?? []); $pd = (array) old('payment.detail', $form['payment']['detail'] ?? []); @endphp
                            <div class="cdd-opts" data-required-group>
                                @foreach (\App\Support\CddForm::PAYMENT as $k => $l)
                                    <label class="cdd-opt"><input type="checkbox" name="payment[methods][]" value="{{ $k }}" data-pay="{{ $k }}" @checked(in_array($k, $pm, true))> <span>{{ __($l) }}</span></label>
                                @endforeach
                            </div>
                            @error('payment.methods')<div class="text-[11px] text-err mt-1" data-err>{{ $message }}</div>@enderror
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
                        </section>
                    @else
                        {{-- ===== 2 · Sociedad ===== --}}
                        <section class="kyc-step" data-step="1">
                            <div class="text-center mb-7">
                                <div class="kyc-icon"><i class="pi pi-building"></i></div>
                                <h1 class="kyc-h1">{{ __('Identificación de la sociedad') }}</h1>
                                <p class="kyc-lead">{{ __('Adjunta un extracto de la Cámara de Comercio de menos de 3 meses. Sociedades extranjeras: registro de la sociedad y copia certificada de la escritura de constitución.') }}</p>
                            </div>
                            <div class="h-px bg-ink-200/70 mb-6"></div>
                            <div class="cdd-grid">
                                @foreach (\App\Support\CddForm::companyFields() as $f)
                                    @include('dashboard._cdd.field', ['name' => "company[{$f[0]}]", 'key' => "company.{$f[0]}", 'label' => $f[1], 'type' => $f[2], 'req' => $f[3], 'opts' => $f[4] ?? [], 'val' => $form['company'][$f[0]] ?? ''])
                                @endforeach
                            </div>
                        </section>

                        {{-- ===== 3 · Beneficiarios ===== --}}
                        <section class="kyc-step" data-step="2">
                            <div class="text-center mb-7">
                                <div class="kyc-icon"><i class="pi pi-sitemap"></i></div>
                                <h1 class="kyc-h1">{{ __('Beneficiarios finales (UBO)') }}</h1>
                                <p class="kyc-lead">{{ __('Personas físicas con más del 25 % del capital o de los derechos de voto, o que ejercen el control de la sociedad de otra forma; o beneficiarias del 25 % o más del patrimonio de una fundación o un trust.') }}</p>
                            </div>
                            <div class="h-px bg-ink-200/70 mb-6"></div>
                            @for ($i = 0; $i < 4; $i++)
                                <div class="cdd-box" data-ubo="{{ $i }}" @if($i > 0 && empty(array_filter((array) ($ubos[$i] ?? [])))) hidden @endif>
                                    <div class="cdd-box-h">{{ __('Beneficiario final :n', ['n' => $i + 1]) }}</div>
                                    <div class="cdd-grid">
                                        @foreach (\App\Support\CddForm::uboFields() as $f)
                                            @include('dashboard._cdd.field', ['name' => "ubo[{$i}][{$f[0]}]", 'key' => "ubo.{$i}.{$f[0]}", 'label' => $f[1] . (in_array($f[2], ['percent']) ? ' (%)' : ''), 'type' => $f[2], 'req' => $i === 0 && $f[3], 'opts' => $f[4] ?? [], 'val' => $ubos[$i][$f[0]] ?? ''])
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                            <button type="button" class="auth-btn auth-btn-ghost mt-4" id="add-ubo"><i class="pi pi-plus text-[12px]"></i> {{ __('Añadir beneficiario final') }}</button>

                            <div class="cdd-sect">{{ __('Entidades de la estructura con un 25 % o más de propiedad o control') }}</div>
                            <p class="text-[12.5px] text-ink-500 -mt-1 mb-2">{{ __('Si no hay ninguna, déjalo en blanco. Si la estructura es más compleja, adjunta un organigrama certificado con la declaración de un asesor independiente.') }}</p>
                            @for ($i = 0; $i < 3; $i++)
                                <div class="cdd-box" data-ent="{{ $i }}" @if(empty(array_filter((array) ($ents[$i] ?? [])))) hidden @endif>
                                    <div class="cdd-grid">
                                        @foreach (\App\Support\CddForm::entityFields() as $f)
                                            @include('dashboard._cdd.field', ['name' => "entities[{$i}][{$f[0]}]", 'key' => "entities.{$i}.{$f[0]}", 'label' => $f[1] . ($f[2] === 'percent' ? ' (%)' : ''), 'type' => $f[2], 'req' => false, 'opts' => [], 'val' => $ents[$i][$f[0]] ?? ''])
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                            <button type="button" class="auth-btn auth-btn-ghost mt-3" id="add-ent"><i class="pi pi-plus text-[12px]"></i> {{ __('Añadir entidad') }}</button>
                        </section>

                        {{-- ===== 4 · Finalidad ===== --}}
                        <section class="kyc-step" data-step="3">
                            <div class="text-center mb-7">
                                <div class="kyc-icon"><i class="pi pi-compass"></i></div>
                                <h1 class="kyc-h1">{{ __('Naturaleza de la relación') }}</h1>
                                <p class="kyc-lead">{{ __('Por qué compras a través de una sociedad y para qué usarás la unidad.') }}</p>
                            </div>
                            <div class="h-px bg-ink-200/70 mb-6"></div>
                            @php $rs = (array) old('reasons', $form['reasons'] ?? []); $pu = old('purpose', $form['purpose'] ?? ''); @endphp
                            <div class="text-[13px] font-medium text-ink-950 mb-3">{{ __('¿Por qué compras la unidad a través de una sociedad?') }} <span class="text-err">*</span></div>
                            <div class="cdd-opts" data-required-group>
                                @foreach (\App\Support\CddForm::REASONS as $k => $l)
                                    <label class="cdd-opt"><input type="checkbox" name="reasons[]" value="{{ $k }}" @checked(in_array($k, $rs, true))> <span>{{ __($l) }}</span></label>
                                @endforeach
                            </div>
                            @error('reasons')<div class="text-[11px] text-err mt-1" data-err>{{ $message }}</div>@enderror
                            <div class="cdd-grid mt-3">@include('dashboard._cdd.field', ['name' => 'reasons_other', 'key' => 'reasons_other', 'label' => 'Otro motivo (explícalo)', 'type' => 'text', 'req' => false, 'opts' => [], 'val' => $form['reasons_other'] ?? ''])</div>
                            <div class="text-[13px] font-medium text-ink-950 mt-6 mb-3">{{ __('¿Para qué vas a usar la unidad?') }} <span class="text-err">*</span></div>
                            <div class="cdd-opts" data-required-group>
                                @foreach (\App\Support\CddForm::PURPOSE as $k => $l)
                                    <label class="cdd-opt"><input type="radio" name="purpose" value="{{ $k }}" @checked($pu === $k)> <span>{{ __($l) }}</span></label>
                                @endforeach
                            </div>
                            @error('purpose')<div class="text-[11px] text-err mt-1" data-err>{{ $message }}</div>@enderror
                            <div class="cdd-grid mt-3">
                                @include('dashboard._cdd.field', ['name' => 'purpose_other', 'key' => 'purpose_other', 'label' => 'Otro uso (explícalo)', 'type' => 'text', 'req' => false, 'opts' => [], 'val' => $form['purpose_other'] ?? ''])
                                @include('dashboard._cdd.field', ['name' => 'description', 'key' => 'description', 'label' => 'Breve descripción de lo anterior (si aplica)', 'type' => 'textarea', 'req' => false, 'opts' => [], 'val' => $form['description'] ?? ''])
                            </div>
                        </section>
                    @endif

                    {{-- ===== Documentos ===== --}}
                    <section class="kyc-step" data-step="{{ count($steps) - 2 }}">
                        <div class="text-center mb-7">
                            <div class="kyc-icon"><i class="pi pi-file"></i></div>
                            <h1 class="kyc-h1">{{ __('Documentos') }}</h1>
                            <p class="kyc-lead">{{ __('PDF, JPG o PNG · máximo 10 MB por archivo. Puedes subir varios archivos por apartado.') }}</p>
                        </div>
                        <div class="h-px bg-ink-200/70 mb-4"></div>
                        <div class="cdd-box !py-2">
                            @foreach (\App\Support\CddForm::docs($type) as $key => [$label, $required])
                                @php $n = count($have[$key] ?? []); @endphp
                                <div class="cdd-doc {{ $errors->has('docs.'.$key) ? 'err' : '' }}" @if($required && ! $n) data-doc-required @endif>
                                    <div class="dn">{{ __($label) }} @if($required)<span class="text-err">*</span>@endif
                                        <span class="fn {{ $n ? 'ok' : '' }}" data-fn>{{ $n ? trans_choice(':n archivo entregado|:n archivos entregados', $n, ['n' => $n]) : '' }}</span>
                                        @error('docs.'.$key)<span class="fn" style="color:#fb3748">{{ $message }}</span>@enderror
                                    </div>
                                    <label class="auth-btn auth-btn-ghost cursor-pointer shrink-0">
                                        <i class="pi pi-upload text-[12px]"></i> {{ $n ? __('Añadir') : __('Subir') }}
                                        <input type="file" name="docs[{{ $key }}][]" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" data-doc-input>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- ===== Firma ===== --}}
                    <section class="kyc-step" data-step="{{ count($steps) - 1 }}">
                        <div class="text-center mb-7">
                            <div class="kyc-icon"><i class="pi pi-pencil"></i></div>
                            <h1 class="kyc-h1">{{ __('Firma y envío') }}</h1>
                            <p class="kyc-lead">{{ __('El firmante declara que la información facilitada es correcta y verdadera, y que avisará a Siebe Group si cambia.') }}</p>
                        </div>
                        <div class="h-px bg-ink-200/70 mb-6"></div>
                        <div class="cdd-grid">
                            @include('dashboard._cdd.field', ['name' => 'decl[name]', 'key' => 'decl.name', 'label' => 'Nombre del firmante', 'type' => 'text', 'req' => true, 'opts' => [], 'val' => $form['decl']['name'] ?? ($b1['full_name'] ?? '')])
                            <div class="cdd-fld"><label>{{ __('Fecha') }}</label><input class="cli-input" value="{{ now()->translatedFormat('j M Y') }}" disabled></div>
                        </div>
                        <label class="flex items-start gap-2 mt-5 text-[13px] text-ink-600 cursor-pointer">
                            <input type="checkbox" name="decl[accept]" value="1" required class="w-4 h-4 mt-0.5 accent-brand" @checked(old('decl.accept'))>
                            <span>{{ __('Declaro que la información es correcta y verdadera, que he adjuntado los documentos indicados y que avisaré a Siebe Group si cambia algún dato.') }}</span>
                        </label>
                        @error('decl.accept')<div class="text-[11px] text-err mt-1" data-err>{{ $message }}</div>@enderror
                    </section>

                    <div id="kyc-err" class="hidden mt-6 cdd-note bg-err-soft text-err" role="alert"><i class="pi pi-exclamation-circle mt-0.5"></i><div></div></div>

                    <div class="flex items-center gap-3 mt-8">
                        <button type="button" id="kyc-back" class="auth-btn auth-btn-ghost"><i class="pi pi-angle-left text-[12px]"></i> {{ __('Volver') }}</button>
                        <button type="button" id="kyc-next" class="auth-btn auth-btn-primary flex-1">{{ __('Continuar') }}</button>
                        <button type="submit" id="cdd-send" class="auth-btn auth-btn-primary flex-1 hidden"><i class="pi pi-send text-[12px]"></i> {{ $sinDeposito ? __('Enviar y reservar') : __('Enviar formulario') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </main>

    <footer class="flex flex-col sm:flex-row gap-2 items-center justify-between px-5 sm:pl-24 sm:pr-7 lg:pr-11 py-5 text-[12px] text-ink-500 border-t border-ink-100">
        <span>{{ __('© 2026 SIEBE RESIDENCES') }}</span>
        <span class="inline-flex items-center gap-1.5"><i class="pi pi-lock text-[11px]"></i> {{ __('Tus datos viajan cifrados y sólo los ve el equipo de cumplimiento.') }}</span>
    </footer>
</div>

@if ($r && $editable)
<script>
(function () {
    var form  = document.getElementById('cdd-form');
    var steps = Array.prototype.slice.call(form.querySelectorAll('.kyc-step'));
    var pills = document.querySelectorAll('[data-pill]');
    var back  = document.getElementById('kyc-back'), next = document.getElementById('kyc-next'), send = document.getElementById('cdd-send');
    var errBox = document.getElementById('kyc-err');
    var cur = 0;
    var T = { of: {{ Js::from(__('Paso :a de :b')) }}, fix: {{ Js::from(__('Completa los campos marcados para continuar.')) }}, sending: {{ Js::from(__('Enviando…')) }} };

    function visible(el) { return !el.closest('[hidden]'); }
    function show(n, dir) {
        document.body.dataset.dir = dir || 'fwd';
        cur = Math.max(0, Math.min(n, steps.length - 1));
        steps.forEach(function (s, i) { s.classList.toggle('active', i === cur); });
        pills.forEach(function (p, i) { p.classList.toggle('active', i === cur); p.classList.toggle('done', i < cur); });
        var c = document.getElementById('step-count'); if (c) c.textContent = T.of.replace(':a', cur + 1).replace(':b', steps.length);
        back.classList.toggle('hidden', cur === 0);
        next.classList.toggle('hidden', cur === steps.length - 1);
        send.classList.toggle('hidden', cur !== steps.length - 1);
        errBox.classList.add('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    // Lo obligatorio del paso, antes de pasar al siguiente (el servidor lo vuelve a validar).
    function valid(step) {
        var ok = true, first = null;
        var mark = function (el, bad) { el.classList.toggle('is-invalid', bad); if (bad) { ok = false; first = first || el; } };
        step.querySelectorAll('input[required], select[required], textarea[required]').forEach(function (i) {
            if (!visible(i) || i.type === 'radio') return;
            mark(i, i.type === 'checkbox' ? !i.checked : (!i.value.trim() || !i.checkValidity()));
        });
        step.querySelectorAll('[data-required-group]').forEach(function (g) { if (visible(g)) mark(g, !g.querySelector('input:checked')); });
        step.querySelectorAll('.cdd-fld .cdd-opts').forEach(function (g) {
            if (visible(g) && g.querySelector('input[required]')) mark(g, !g.querySelector('input:checked'));
        });
        step.querySelectorAll('[data-doc-required]').forEach(function (d) { mark(d, !d.querySelector('[data-doc-input]').files.length); });
        if (!ok) {
            errBox.querySelector('div').textContent = T.fix;
            errBox.classList.remove('hidden');
            first.scrollIntoView({ block: 'center', behavior: 'smooth' });
            if (first.focus) first.focus({ preventScroll: true });
        }
        return ok;
    }
    next.addEventListener('click', function () { if (valid(steps[cur])) show(cur + 1); });
    back.addEventListener('click', function () { show(cur - 1, 'back'); });
    var clear = function (e) { e.target.classList.remove('is-invalid'); var g = e.target.closest('.is-invalid'); if (g) g.classList.remove('is-invalid'); };
    form.addEventListener('input', clear);
    form.addEventListener('change', clear);
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT' && cur < steps.length - 1) { e.preventDefault(); next.click(); }
    });
    form.addEventListener('submit', function (e) {
        if (!valid(steps[cur])) { e.preventDefault(); return; }
        send.disabled = true; send.textContent = T.sending;
    });

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
        btn.addEventListener('click', function () { var nx = form.querySelector(p[1] + '[hidden]'); if (nx) nx.hidden = false; sync(); });
        sync();
    });
    form.querySelectorAll('[data-doc-input]').forEach(function (i) {
        i.addEventListener('change', function () {
            var row = i.closest('.cdd-doc'), fn = row.querySelector('[data-fn]');
            fn.textContent = Array.prototype.map.call(i.files, function (f) { return f.name; }).join(' · ');
            fn.classList.toggle('ok', i.files.length > 0);
            row.classList.remove('err', 'is-invalid');
        });
    });

    // Vuelve del servidor con errores: al primer paso que tenga alguno.
    var withErr = steps.findIndex(function (s) { return s.querySelector('.cdd-fld.err, .cdd-doc.err, [data-err]'); });
    show(withErr > 0 ? withErr : 0);
})();
</script>
@endif
</body>
</html>
