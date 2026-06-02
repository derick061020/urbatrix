<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pago de reserva {{ $reservation->reservation_code ?? '' }} · MAKAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/primeicons/primeicons.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans:    ['Inter', 'system-ui', 'sans-serif'],
              display: ['"Inter Tight"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
              brand: { DEFAULT:'#5c7c68', dark:'#4a6354', soft:'#5c7c6833', tint:'#eef2ef' },
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
      .auth-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
      .auth-input::placeholder { color:#a3a3a3; }
      .auth-input.is-invalid { border-color:#fb3748 !important; box-shadow:0 0 0 3px rgba(251,55,72,.14) !important; }
      .auth-select { appearance:none; padding-right:36px; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a3a3a3' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat:no-repeat; background-position: right 12px center; }

      /* Stripe Elements styled to match .auth-input — the iframe lives inside */
      .stripe-field {
        min-height:40px; padding:11px 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; transition: border-color .15s, box-shadow .15s;
      }
      .stripe-field.StripeElement--focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
      .stripe-field.StripeElement--invalid { border-color:#fb3748; box-shadow:0 0 0 3px rgba(251,55,72,.14); }

      .auth-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        height:40px; padding:0 16px; border-radius:10px;
        font-weight:500; font-size:14px; line-height:1; cursor:pointer;
        transition: background-color .15s, border-color .15s, color .15s, transform .12s;
      }
      .auth-btn:active { transform: translateY(1px); }
      .auth-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; box-shadow: 0 1px 2px 0 rgba(10,13,20,.06); }
      .auth-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
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
      .pay-card.selected { border-color:#5c7c68; background:#fff; box-shadow:0 0 0 1px #5c7c68; }

      .check-circle {
          width:64px; height:64px; border-radius:999px;
          background:#5c7c68; color:#fff;
          display:flex; align-items:center; justify-content:center;
          margin: 0 auto 18px;
      }

      @media (max-width: 640px) {
          #step-indicator { display:none !important; }
      }
    </style>
</head>
<body>

@php
    $fee   = (float) ($reservation->reservation_fee ?? 5000);
    $img   = optional(optional($unit)->images)->first();
    $unitName = $reservation->unit_name ?? optional($unit)->custom_id ?? optional($unit)->name ?? '—';
@endphp

<div class="min-h-screen flex flex-col">

    {{-- ============= HEADER ============= --}}
    <header class="flex items-center justify-between px-7 lg:px-11 py-6 border-b border-ink-100 bg-white">
        <a href="/" class="flex items-center gap-3 select-none">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 shadow-sm" style="background:#5c7c68">
                <span class="block w-6 h-6"><img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full"></span>
            </span>
            <span class="flex flex-col leading-none">
                <span class="font-display text-[14px] font-bold text-ink-950 tracking-tight">MAKAI</span>
                <span class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">Duna Development</span>
            </span>
        </a>

        {{-- Step indicator: 1. Datos · 2. Pago · 3. Confirmar --}}
        <div id="step-indicator" class="hidden lg:flex items-center gap-5">
            <div class="step-pill done"><span class="num"><i class="pi pi-check text-[9px]"></i></span><span>Datos</span></div>
            <i class="pi pi-angle-right text-ink-300 text-[12px]"></i>
            <div class="step-pill active"><span class="num">2</span><span>Pago</span></div>
            <i class="pi pi-angle-right text-ink-300 text-[12px]"></i>
            <div class="step-pill"><span class="num">3</span><span>Confirmar</span></div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right hidden md:block">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-ink-400">Reserva</div>
                <div class="text-[11px] font-bold text-ink-950">{{ $reservation->reservation_code ?? '—' }}</div>
            </div>
            <a href="/" class="auth-btn auth-btn-ghost w-10 px-0" title="Cerrar"><i class="pi pi-times text-[12px]"></i></a>
        </div>
    </header>

    {{-- ============= BODY ============= --}}
    <main class="flex-1 flex items-start justify-center px-5 py-8 relative">
        <div class="absolute inset-x-0 top-0 h-[300px] bg-pattern opacity-50 pointer-events-none" aria-hidden="true"></div>

        {{-- ====== CHECKOUT (payment) ====== --}}
        <div id="checkout-view" class="w-full max-w-[960px] relative">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6">

                {{-- LEFT: payment form --}}
                <div>
                    <div class="mb-6">
                        <h1 class="font-display text-[24px] font-medium text-ink-950 leading-8">Completá tu reserva</h1>
                        <p class="text-[14px] text-ink-500 mt-1">Abona la seña para asegurar la unidad. El cobro se realiza una sola vez.</p>
                    </div>

                    {{-- Buyer summary --}}
                    <div class="flex items-center justify-between p-4 rounded-xl border border-ink-200 bg-ink-50/50 mb-6">
                        <div class="min-w-0">
                            <div class="text-[14px] font-semibold text-ink-950 truncate">{{ trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: 'Adquiriente' }}</div>
                            <div class="text-[12px] text-ink-500 truncate">{{ $reservation->email ?? '' }}{{ $reservation->phone ? ' · '.$reservation->phone : '' }}</div>
                        </div>
                        <a href="/dashboard" class="text-[12px] text-brand font-semibold hover:underline shrink-0 ml-3">Editar</a>
                    </div>

                    {{-- Card --}}
                    <div class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider mb-3">Tarjeta de crédito / débito</div>

                    <div id="pay-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="field-label">Número de tarjeta <span class="field-required">*</span></label>
                            <div id="card-number" class="stripe-field"></div>
                        </div>
                        <div>
                            <label class="field-label">Vencimiento <span class="field-required">*</span></label>
                            <div id="card-expiry" class="stripe-field"></div>
                        </div>
                        <div>
                            <label class="field-label">CVC <span class="field-required">*</span></label>
                            <div id="card-cvc" class="stripe-field"></div>
                        </div>
                        <div>
                            <label class="field-label">Código postal <span class="field-required">*</span></label>
                            <input type="text" id="billing_zip" class="auth-input" placeholder="10102" autocomplete="postal-code">
                        </div>
                        <div>
                            <label class="field-label">Titular de la tarjeta <span class="field-required">*</span></label>
                            <input type="text" id="card_name" class="auth-input" placeholder="Nombre como aparece en la tarjeta" value="{{ trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) }}">
                        </div>
                    </div>

                    {{-- Billing address --}}
                    <div class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider mt-8 mb-3">Dirección de facturación</div>

                    <div id="billing-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="field-label">País</label>
                            <select id="billing_country" class="auth-input auth-select">
                                <option value="CR">Costa Rica</option>
                                <option value="MX">México</option>
                                <option value="US">Estados Unidos</option>
                                <option value="CO">Colombia</option>
                                <option value="PA">Panamá</option>
                                <option value="OT">Otro</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label">Dirección</label>
                            <input type="text" id="billing_line1" class="auth-input" placeholder="Calle y número">
                        </div>
                        <div>
                            <label class="field-label">Ciudad</label>
                            <input type="text" id="billing_city" class="auth-input" placeholder="Ciudad">
                        </div>
                        <div>
                            <label class="field-label">Código postal</label>
                            <input type="text" id="billing_postal" class="auth-input" placeholder="Código postal">
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="button" id="pay-btn" onclick="payNow()" class="auth-btn auth-btn-primary w-full">
                            <span id="pay-btn-label">Confirmar y pagar · USD ${{ number_format($fee, 0, '.', ',') }}</span>
                            <i id="pay-spinner" class="pi pi-spin pi-spinner hidden"></i>
                        </button>
                        <p class="text-[11px] text-ink-400 text-center mt-3">
                            <i class="pi pi-lock text-[10px]"></i>
                            Cifrado TLS 1.3 · PCI DSS Level 1 · No almacenamos tu CVC
                        </p>
                    </div>
                </div>

                {{-- RIGHT: order summary --}}
                <aside>
                    <div class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sticky top-8">
                        @if($img)
                            <div class="rounded-xl overflow-hidden mb-4 bg-ink-100">
                                <img src="{{ $img->path }}" alt="{{ $unitName }}" class="w-full h-36 object-cover" onerror="this.style.display='none'">
                            </div>
                        @endif
                        <div class="text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Proyecto Duna · Playa del Carmen</div>
                        <div class="font-display text-[18px] font-bold text-ink-950 mt-1">Unidad {{ $unitName }}</div>

                        <ul class="mt-4 space-y-2 text-[12px] text-ink-600">
                            <li class="flex items-center gap-2"><i class="pi pi-lock text-brand text-[12px]"></i> Bloqueo exclusivo por 30 días</li>
                            <li class="flex items-center gap-2"><i class="pi pi-id-card text-brand text-[12px]"></i> Acceso al portal del comprador</li>
                            <li class="flex items-center gap-2"><i class="pi pi-file text-brand text-[12px]"></i> Recibo oficial</li>
                            <li class="flex items-center gap-2"><i class="pi pi-user text-brand text-[12px]"></i> Asesor dedicado</li>
                            <li class="flex items-center gap-2"><i class="pi pi-shield text-brand text-[12px]"></i> Seguridad cifrada</li>
                        </ul>

                        <div class="h-px bg-ink-200/70 my-4"></div>

                        <div class="flex items-center justify-between text-[13px] text-ink-600">
                            <span>Precio total</span>
                            <span class="font-semibold text-ink-950">${{ number_format((float) (optional($unit)->price ?? $reservation->unit_price ?? 0), 0, '.', ',') }} USD</span>
                        </div>
                        <div class="flex items-center justify-between text-[14px] mt-2">
                            <span class="font-semibold text-ink-950">Monto de reserva</span>
                            <span class="font-display font-bold text-brand text-[18px]">${{ number_format($fee, 0, '.', ',') }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        {{-- ====== THANK YOU (hidden until paid) ====== --}}
        <div id="success-view" class="hidden w-full max-w-[640px] relative text-center py-12">
            <div class="check-circle"><i class="pi pi-check text-[28px] font-bold"></i></div>
            <h2 class="font-display text-[28px] font-bold text-ink-950">$<span>{{ number_format($fee, 0, '.', ',') }}</span> USD</h2>
            <h3 class="font-display text-[20px] font-medium text-ink-950 mt-2">¡Reserva confirmada!</h3>
            <p class="text-[14px] text-ink-500 mt-2 max-w-[420px] mx-auto">
                Pago procesado. La Unidad {{ $unitName }} quedó reservada a tu nombre por los próximos 30 días.
            </p>
            <p class="text-[12px] text-ink-400 mt-1">Ref: <span id="success-ref">{{ $reservation->reservation_code }}</span></p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
                <a href="/form" class="auth-btn auth-btn-primary w-full sm:w-auto px-6"><i class="pi pi-id-card text-[13px]"></i> Completar KYC</a>
                <a href="/dashboard" class="auth-btn auth-btn-ghost w-full sm:w-auto px-6">Ir a mi portal <i class="pi pi-arrow-right text-[12px]"></i></a>
            </div>
        </div>
    </main>

    {{-- ============= FOOTER ============= --}}
    <footer class="flex items-center justify-between px-7 lg:px-11 py-5 text-[12px] text-ink-500 border-t border-ink-100 bg-white">
        <span>© 2026 MAKAI RESIDENCES</span>
        <span class="flex items-center gap-1.5"><i class="pi pi-lock text-[12px]"></i> Pago seguro con Stripe</span>
    </footer>
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const STRIPE_KEY = @json($stripeKey);
    const errBox = document.getElementById('pay-error');
    const payBtn = document.getElementById('pay-btn');
    const paySpinner = document.getElementById('pay-spinner');

    function showError(msg) {
        errBox.textContent = msg;
        errBox.classList.remove('hidden');
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearError() { errBox.classList.add('hidden'); errBox.textContent = ''; }
    function setLoading(on) {
        payBtn.disabled = on;
        paySpinner.classList.toggle('hidden', !on);
    }

    if (!STRIPE_KEY) {
        showError('Stripe no está configurado. Falta la clave pública (STRIPE_KEY) en .env.');
        setLoading(true);
        return;
    }

    const stripe = Stripe(STRIPE_KEY);
    const elements = stripe.elements({ fonts: [{ cssSrc: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap' }] });
    const style = {
        base: {
            color: '#171717', fontFamily: 'Inter, system-ui, sans-serif', fontSize: '14px',
            '::placeholder': { color: '#a3a3a3' },
        },
        invalid: { color: '#fb3748' },
    };
    const cardNumber = elements.create('cardNumber', { style, showIcon: true, placeholder: '4242 4242 4242 4242' });
    const cardExpiry = elements.create('cardExpiry', { style });
    const cardCvc    = elements.create('cardCvc', { style });
    cardNumber.mount('#card-number');
    cardExpiry.mount('#card-expiry');
    cardCvc.mount('#card-cvc');

    [cardNumber, cardExpiry, cardCvc].forEach(el => el.on('change', (e) => { if (e.error) showError(e.error.message); else clearError(); }));

    window.payNow = async function () {
        clearError();
        const name = document.getElementById('card_name').value.trim();
        if (!name) { showError('Ingresá el nombre del titular de la tarjeta.'); return; }

        setLoading(true);
        try {
            // 1) Create the PaymentIntent server-side
            const piRes = await fetch('/checkout/payment-intent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            });
            const piData = await piRes.json();
            if (!piRes.ok || !piData.success) { showError(piData.message || 'No se pudo iniciar el pago.'); setLoading(false); return; }

            // 2) Confirm the card payment with Stripe
            const billing_details = { name };
            const zip = document.getElementById('billing_zip').value.trim();
            billing_details.address = {
                country: document.getElementById('billing_country').value || undefined,
                line1:   document.getElementById('billing_line1').value.trim() || undefined,
                city:    document.getElementById('billing_city').value.trim() || undefined,
                postal_code: document.getElementById('billing_postal').value.trim() || zip || undefined,
            };

            const result = await stripe.confirmCardPayment(piData.client_secret, {
                payment_method: { card: cardNumber, billing_details },
            });

            if (result.error) { showError(result.error.message || 'El pago fue rechazado.'); setLoading(false); return; }
            if (!result.paymentIntent || result.paymentIntent.status !== 'succeeded') {
                showError('El pago no se completó. Intenta de nuevo.'); setLoading(false); return;
            }

            // 3) Confirm server-side (verifies status + holds the unit)
            const confRes = await fetch('/checkout/confirm', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ payment_intent_id: result.paymentIntent.id }),
            });
            const confData = await confRes.json();
            if (!confRes.ok || !confData.success) { showError(confData.message || 'No se pudo verificar el pago.'); setLoading(false); return; }

            // 4) Show the thank-you state
            if (confData.reservation_code) document.getElementById('success-ref').textContent = confData.reservation_code;
            document.getElementById('checkout-view').classList.add('hidden');
            document.getElementById('success-view').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (e) {
            console.error(e);
            showError('Error de red. Intenta de nuevo.');
            setLoading(false);
        }
    };
})();
</script>
</body>
</html>
