<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Verificación previa') }} {{ $reservation->reservation_code ?? '' }} · SIEBE</title>
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
              ok:  { DEFAULT:'#1fc16b', soft:'#e3f7ec' },
              warn:{ DEFAULT:'#fa7319', soft:'#fff3eb' },
            },
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; background:#fff; }

      .auth-input {
        width:100%; height:40px; padding:0 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; color:#171717; font-size:14px;
        transition: border-color .15s, box-shadow .15s;
      }
      .auth-input:focus { outline:none; border-color:#2b2b1a; box-shadow:0 0 0 3px rgba(43,43,26,.15); }
      .auth-input::placeholder { color:#a3a3a3; }
      .auth-input.is-invalid { border-color:#fb3748 !important; box-shadow:0 0 0 3px rgba(251,55,72,.14) !important; }
      .auth-select { appearance:none; padding-right:36px; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a3a3a3' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat:no-repeat; background-position: right 12px center; }

      /* Stripe Elements styled to match .auth-input — the iframe lives inside */
      .stripe-field {
        min-height:40px; padding:11px 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; transition: border-color .15s, box-shadow .15s;
      }
      .stripe-field.StripeElement--focus { border-color:#2b2b1a; box-shadow:0 0 0 3px rgba(43,43,26,.15); }
      .stripe-field.StripeElement--invalid { border-color:#fb3748; box-shadow:0 0 0 3px rgba(251,55,72,.14); }

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
      .auth-btn-ghost { background:#fff; color:#171717; border:1px solid #ebebeb; }
      .auth-btn-ghost:hover { background:#f8f8f8; }

      .field-label { display:block; font-size:13px; font-weight:500; color:#171717; margin-bottom:6px; }
      .field-required { color:#fb3748; }

      .step-pill { display:inline-flex; align-items:center; gap:8px; font-size:14px; color:#a3a3a3; font-weight:500; white-space:nowrap; }
      .step-pill .num {
        width:22px; height:22px; border-radius:999px;
        display:inline-flex; align-items:center; justify-content:center;
        background:#fff; border:1px solid #ebebeb;
        font-size:11px; font-weight:600; color:#a3a3a3;
        transition: background-color .2s, color .2s, border-color .2s;
      }
      .step-pill.active       { color:#171717; }
      .step-pill.active .num  { background:#222530; color:#fff; border-color:#222530; }
      .step-pill.done         { color:#171717; }
      .step-pill.done .num    { background:#1fc16b; color:#fff; border-color:#1fc16b; }

      .bg-pattern { background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px); background-size: 24px 24px; }

      /* Billing option cards (reuse pay-card look) */
      .pay-card {
          border:1px solid #ebebeb; border-radius:14px; padding:14px 16px;
          background:#fff; cursor:pointer; transition: border-color .15s, background-color .15s;
      }
      .pay-card:hover { background:#f8f8f8; }
      .pay-card.selected { border-color:#2b2b1a; background:#fff; box-shadow:0 0 0 1px #2b2b1a; }

      .check-circle {
          width:64px; height:64px; border-radius:999px;
          background:#2b2b1a; color:#fff;
          display:flex; align-items:center; justify-content:center;
          margin: 0 auto 18px;
          position: relative;
      }

      /* ===== Celebration animations (success view) ===== */
      #success-view:not(.hidden) .check-circle { animation: pop-in .55s cubic-bezier(.18,.89,.32,1.28) both; }
      #success-view:not(.hidden) .check-circle i { animation: check-draw .4s ease-out .25s both; }
      #success-view:not(.hidden) h2 { animation: rise-in .5s ease-out .15s both; }
      #success-view:not(.hidden) h3 { animation: rise-in .5s ease-out .28s both; }
      #success-view:not(.hidden) p  { animation: rise-in .5s ease-out .4s both; }
      #success-view:not(.hidden) .flex { animation: rise-in .5s ease-out .52s both; }

      .check-circle::after {
          content:""; position:absolute; inset:-6px; border-radius:999px;
          border:3px solid #2b2b1a; opacity:0;
      }
      #success-view:not(.hidden) .check-circle::after { animation: ring-pulse .9s ease-out .35s 2; }

      @keyframes pop-in   { 0% { transform: scale(0); } 60% { transform: scale(1.15); } 100% { transform: scale(1); } }
      @keyframes check-draw { 0% { transform: scale(0) rotate(-25deg); opacity:0; } 100% { transform: scale(1) rotate(0); opacity:1; } }
      @keyframes rise-in  { 0% { transform: translateY(14px); opacity:0; } 100% { transform: translateY(0); opacity:1; } }
      @keyframes ring-pulse { 0% { transform: scale(.85); opacity:.65; } 100% { transform: scale(1.5); opacity:0; } }

      @media (max-width: 640px) {
          #step-indicator { display:none !important; }
      }
          .doc-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-top:1px solid #f2f5f8; flex-wrap:wrap; }
    </style>
</head>
<body>
{{--
  Verificación previa al depósito (CDD de Siebe Group): mientras cumplimiento
  no haya hecho el screening de sanciones y PEP, el checkout enseña esto en vez
  del pago. Ver ReservationController::showCheckout y CddController.
--}}
@php
    $unitName = $reservation->unit_name ?? optional($unit)->custom_id ?? optional($unit)->name ?? '—';
    $ids = (array) (($cdd->kyc_docs ?? [])['reserva_id'] ?? []);
    $hit = $cdd->screening_status === 'coincidencia';
@endphp
<div class="min-h-screen flex flex-col">
    <header class="flex items-center justify-between px-5 sm:px-7 lg:px-11 py-5 border-b border-ink-100 bg-white">
        <a href="/" class="flex items-center gap-3 select-none">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 shadow-sm" style="background:#2b2b1a">
                <span class="block w-6 h-6"><img src="{{ asset('images/brand/siebe-logo-mark.svg') }}" alt="" class="block w-full h-full"></span>
            </span>
            <span class="flex flex-col leading-none">
                <span class="font-display text-[14px] font-bold text-ink-950 tracking-tight">SIEBE</span>
                <span class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">{{ __('Siebe Property Group') }}</span>
            </span>
        </a>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">{{ __('Reserva') }}</div>
                <div class="text-[11px] font-bold text-ink-950">{{ $reservation->reservation_code ?? '—' }}</div>
            </div>
            <a href="/" class="auth-btn auth-btn-ghost w-10 px-0" title="{{ __('Cerrar') }}"><i class="pi pi-times text-[12px]"></i></a>
        </div>
    </header>

    <main class="flex-1 flex items-start justify-center px-4 sm:px-5 py-8 relative">
        <div class="absolute inset-x-0 top-0 h-[300px] bg-pattern opacity-50 pointer-events-none" aria-hidden="true"></div>
        <div class="w-full max-w-[620px] relative bg-white border border-ink-200 rounded-2xl p-6 sm:p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full mx-auto mb-5 flex items-center justify-center text-[24px] {{ $hit ? 'bg-err-soft text-err' : 'bg-warn-soft text-warn' }}"><i class="pi {{ $hit ? 'pi-ban' : 'pi-shield' }}"></i></div>
            @if ($hit)
                <h1 class="font-display text-[22px] font-semibold text-ink-950 text-center">{{ __('No podemos continuar con la reserva') }}</h1>
                <p class="text-[14px] text-ink-600 text-center mt-3">{{ __('Por obligaciones legales no podemos aceptar el depósito. Si crees que es un error, escríbenos a :mail.', ['mail' => config('company.support_email')]) }}</p>
            @else
                <h1 class="font-display text-[22px] font-semibold text-ink-950 text-center">{{ __('Verificación previa al depósito') }}</h1>
                <p class="text-[14px] text-ink-600 text-center mt-3">{{ __('Hemos registrado tu solicitud de reserva de la unidad :u. Antes de aceptar el depósito, el equipo de cumplimiento verifica tu identidad en las listas de sanciones y de personas expuestas políticamente (PEP), como exige la normativa del Banco Central de Aruba.', ['u' => $unitName]) }}</p>
                <p class="text-[13px] text-ink-500 text-center mt-2">{{ __('Te avisaremos por email en cuanto puedas pagar. Mantenemos tu solicitud 7 días.') }}</p>

                @if (session('success'))<div class="mt-5 px-4 py-2.5 rounded-lg bg-ok-soft text-[13px] text-ink-800 flex gap-2"><i class="pi pi-check-circle text-ok mt-0.5"></i> {{ session('success') }}</div>@endif
                @if ($errors->any())<div class="mt-5 px-4 py-2.5 rounded-lg bg-err-soft text-[13px] text-err">{{ $errors->first() }}</div>@endif

                <form method="POST" action="{{ route('checkout.screening-id') }}" enctype="multipart/form-data" class="mt-6">
                    @csrf
                    <div class="text-[13px] font-semibold text-ink-950">{{ __('Agiliza la verificación') }}</div>
                    <div class="text-[12px] text-ink-500 mt-0.5">{{ __('Sube una copia de tu documento de identidad. Si no eres de Aruba, el pasaporte es obligatorio. PDF, JPG o PNG, máximo 10 MB.') }}</div>
                    <div class="doc-row mt-3">
                        <i class="pi {{ $ids ? 'pi-check-circle text-ok' : 'pi-id-card text-ink-400' }} text-[18px]"></i>
                        <div class="flex-1 min-w-[180px] text-[13px] text-ink-800">{{ __('Documento de identidad') }}
                            <div class="text-[11px] text-ink-500" id="id-names">{{ $ids ? trans_choice(':n archivo entregado|:n archivos entregados', count($ids), ['n' => count($ids)]) : '' }}</div>
                        </div>
                        <label class="auth-btn auth-btn-ghost cursor-pointer text-[13px]"><i class="pi pi-upload"></i> {{ $ids ? __('Añadir') : __('Elegir archivo') }}
                            <input type="file" name="ids[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="document.getElementById('id-names').textContent=[...this.files].map(f=>f.name).join(' · ');document.getElementById('id-send').disabled=!this.files.length">
                        </label>
                    </div>
                    <div class="flex justify-end mt-4"><button type="submit" id="id-send" class="auth-btn auth-btn-primary" disabled>{{ __('Enviar documento') }}</button></div>
                </form>
            @endif
            <div class="mt-6 pt-5 border-t border-ink-100 flex flex-col sm:flex-row gap-2 justify-center">
                <a href="/" class="auth-btn auth-btn-ghost">{{ __('Volver a las residencias') }}</a>
                @auth<a href="{{ route('dashboard') }}" class="auth-btn auth-btn-ghost">{{ __('Ir a mi portal') }}</a>@endauth
            </div>
        </div>
    </main>
</div>
</body>
</html>
