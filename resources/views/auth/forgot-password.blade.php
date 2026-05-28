<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MAKAI · Recuperar contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/primeicons/primeicons.css" rel="stylesheet" />
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
              brand: { DEFAULT:'#5c7c68', dark:'#4a6354', soft:'#5c7c6833', tint:'#eef2ef' },
              ink: { 950:'#171717', 900:'#222530', 700:'#2b303b', 600:'#5c5c5c', 500:'#717784', 400:'#a3a3a3', 300:'#cacfd8', 200:'#ebebeb', 100:'#f2f5f8', 50:'#f8f8f8' },
              err: { DEFAULT:'#fb3748', soft:'#ffebec' },
              ok:  { DEFAULT:'#1fc16b', soft:'#e3f7ec' },
            },
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; overflow:hidden; }

      /* Full-screen panel matching the register view */
      #auth-panel {
        position: relative; z-index: 10;
        background:#fff;
        width: 100vw; height: 100vh;
        display: flex; flex-direction: column;
        overflow: hidden;
      }

      @media (max-width: 640px) {
        .fp-step h1 { font-size: 22px !important; }
        .code-input { width: 44px !important; height: 52px !important; font-size: 22px !important; }
        #step-indicator { display: none !important; }
      }

      /* Inputs */
      .auth-input {
        width:100%; height:40px; padding:0 14px;
        border:1px solid #ebebeb; border-radius:10px;
        background:#fff; color:#171717; font-size:14px;
        transition: border-color .15s, box-shadow .15s;
      }
      .auth-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
      .auth-input::placeholder { color:#a3a3a3; }
      .has-icon  { padding-left:38px; }
      .has-trail { padding-right:40px; }

      /* Buttons */
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
      .auth-btn-ghost { background:#f5f5f5; color:#171717; border:1px solid transparent; }
      .auth-btn-ghost:hover { background:#ebebeb; }
      .auth-link { color:#171717; font-weight:500; font-size:14px; border-bottom:1px solid #171717; padding-bottom:1px; }
      .auth-link:hover { color:#5c7c68; border-color:#5c7c68; }

      /* Step transitions */
      @keyframes fp-step-in-forward {
        from { opacity: 0; transform: translateX(28px); }
        to   { opacity: 1; transform: translateX(0); }
      }
      @keyframes fp-step-in-back {
        from { opacity: 0; transform: translateX(-28px); }
        to   { opacity: 1; transform: translateX(0); }
      }
      .fp-step { display: none; }
      .fp-step.active {
        display: block;
        animation: fp-step-in-forward .42s cubic-bezier(.22,1,.36,1) both!important;
      }
      body[data-step-direction="back"] .fp-step.active {
        animation-name: fp-step-in-back!important;
      }
      @media (prefers-reduced-motion: reduce) {
        .fp-step.active { animation: none!important; }
      }

      /* 6-digit code inputs */
      .code-input {
        width: 63px; height: 64px;
        text-align: center;
        font-family: 'Inter Tight', 'Inter', sans-serif;
        font-size: 28px; font-weight: 500;
        border: 1px solid #ebebeb; border-radius: 12px;
        background: #fff; color: #171717;
        transition: border-color .15s, box-shadow .15s;
      }
      .code-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }

      /* Step indicator pills (same look as register) */
      .step-pill { display:inline-flex; align-items:center; gap:8px; font-size:14px; color:#a3a3a3; font-weight:500; white-space:nowrap; }
      .step-pill .num {
        width:22px; height:22px; border-radius:999px;
        display:inline-flex; align-items:center; justify-content:center;
        background:#fff; border:1px solid #ebebeb;
        font-size:11px; font-weight:600; color:#a3a3a3;
        transition: background-color .2s, color .2s, border-color .2s;
      }
      .step-pill.active      { color:#171717; }
      .step-pill.active .num { background:#222530; color:#fff; border-color:#222530; }
      .step-pill.done        { color:#171717; }
      .step-pill.done .num   { background:#1fc16b; color:#fff; border-color:#1fc16b; }

      /* Password strength bars */
      .pw-bar { height:4px; flex:1; border-radius:999px; background:#ebebeb; }
      .pw-bar.weak   { background:#fb3748; }
      .pw-bar.medium { background:#f6b51e; }
      .pw-bar.strong { background:#1fc16b; }
      .resend-strong { font-weight:700; color:#171717; }

      .panel-scroll { overflow-y: auto; overflow-x: hidden; }
      .panel-scroll::-webkit-scrollbar { width: 6px; }
      .panel-scroll::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:6px; }

      /* ---- Language dropdown (footer) ---- */
      .auth-lang-wrap { position: relative; display: inline-block; }
      .auth-lang-menu {
        position: absolute; right: 0; bottom: calc(100% + 8px);
        min-width: 140px;
        background: #fff; border: 1px solid #ebebeb; border-radius: 10px;
        box-shadow: 0 10px 30px -6px rgba(0,0,0,.12);
        padding: 4px; z-index: 50;
        animation: auth-lang-fade .15s ease-out;
      }
      @keyframes auth-lang-fade {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
      }
      .auth-lang-item {
        display: flex; align-items: center; gap: 8px;
        width: 100%; padding: 8px 10px;
        background: transparent; border: 0; border-radius: 6px;
        color: #717784; font: 500 13px/1 'Inter', sans-serif;
        cursor: pointer; text-align: left;
        transition: background-color .12s, color .12s;
      }
      .auth-lang-item:hover { background: #f2f5f8; color: #171717; }
      .auth-lang-item.is-active { color: #171717; font-weight: 600; }
      .auth-lang-check { visibility: hidden; color: #5c7c68; }
      .auth-lang-item.is-active .auth-lang-check { visibility: visible; }
    </style>
</head>
<body class="bg-white">

<section id="auth-panel">
    <div class="flex-1 flex flex-col relative panel-scroll">

        <header class="relative z-10 flex items-center justify-between px-7 py-6 lg:px-11">
            <div class="relative">
                @include('auth._logo')
            </div>

            <div id="step-indicator" class="hidden lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:flex items-center gap-5">
                @php
                    $steps = [
                        ['Correo',       1],
                        ['Verificación', 2],
                        ['Nueva clave',  3],
                    ];
                @endphp
                @foreach($steps as $idx => $s)
                    <div class="step-pill" data-step="{{ $s[1] }}">
                        <span class="num">{{ $s[1] }}</span>
                        <span>{{ $s[0] }}</span>
                    </div>
                    @if(!$loop->last)
                        <i class="pi pi-angle-right text-ink-300 text-[12px]"></i>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-[14px] text-ink-600">¿Lo recordaste?</span>
                <a href="{{ route('login') }}" class="auth-btn auth-btn-ghost">Iniciar sesión</a>
            </div>
        </header>

        <button type="button" onclick="prevStep()" id="back-btn"
                class="hidden absolute top-[100px] left-7 lg:left-11 z-20 auth-btn auth-btn-ghost">
            <i class="pi pi-angle-left text-[12px]"></i> Volver
        </button>

        <main class="flex-1 pt-7 pb-10 relative z-10">
            <div class="w-full max-w-[452px] mx-auto px-5">

                {{-- ========= STEP 1 — Email ========= --}}
                <div class="fp-step active" data-step="1">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-key text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Recuperar contraseña</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Introduce tu correo y te enviaremos un código de 6 dígitos para restablecer tu contraseña.</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step1-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <form id="form-step1" class="space-y-4" onsubmit="return submitStep1(event)">
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Dirección de correo electrónico <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="fp-email" type="email" name="email" required placeholder="samuel@gmail.com" class="auth-input has-icon" autocomplete="email" autofocus>
                            </div>
                        </div>
                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Enviar código</button>
                        <div class="text-center text-[13px] text-ink-600 mt-4">
                            ¿No tienes cuenta?
                            <a href="{{ route('register') }}" class="auth-link ml-1">Regístrate</a>
                        </div>
                    </form>
                </div>

                {{-- ========= STEP 2 — Verify code ========= --}}
                <div class="fp-step" data-step="2">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-shield text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Verifica tu identidad</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Enviamos un código de 6 dígitos a <span id="fp-email-echo" class="text-ink-950 font-medium"></span></p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step2-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>
                    <div id="dev-code-hint" class="hidden mb-4 px-3 py-2 rounded-lg bg-ok-soft border border-ok/30 text-[12px] text-ink-700">
                        <span class="font-semibold">DEV</span> · Código generado: <code id="dev-code" class="font-mono"></code>
                    </div>

                    <form id="form-step2" onsubmit="return submitStep2(event)">
                        <div class="flex items-center justify-center gap-2 mb-7" id="code-row">
                            <input class="code-input" data-pos="0" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="1" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="2" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <span class="text-ink-300 text-[32px] mx-1">–</span>
                            <input class="code-input" data-pos="3" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="4" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="5" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                        </div>
                        <button type="submit" class="auth-btn auth-btn-primary w-full">Verificar código</button>
                        <div class="text-center mt-5 text-[13px] text-ink-500">
                            ¿No recibiste el código? Reenviar en <span class="resend-strong" id="resend-timer">58s</span>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" onclick="resendCode()" id="resend-btn" disabled class="auth-link disabled:opacity-40 disabled:cursor-not-allowed">Volver a enviar el código</button>
                        </div>
                    </form>
                </div>

                {{-- ========= STEP 3 — New password ========= --}}
                <div class="fp-step" data-step="3">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-lock text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Nueva contraseña</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Crea una contraseña segura para tu cuenta.</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step3-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <form id="form-step3" class="space-y-4" onsubmit="return submitStep3(event)">
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Nueva contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="fp-pw1" type="password" name="password" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail" oninput="updatePwStrength()">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('fp-pw1', this)"><i class="pi pi-eye text-[14px]"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Confirmar contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="fp-pw2" type="password" name="password_confirmation" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('fp-pw2', this)"><i class="pi pi-eye text-[14px]"></i></button>
                            </div>
                        </div>

                        <div>
                            <div class="flex gap-1.5">
                                <div class="pw-bar" id="pw-bar-0"></div>
                                <div class="pw-bar" id="pw-bar-1"></div>
                                <div class="pw-bar" id="pw-bar-2"></div>
                            </div>
                            <div class="text-[12px] text-ink-500 mt-3 mb-1">Debe contener al menos:</div>
                            <ul class="space-y-1 text-[12px]">
                                <li class="flex items-center gap-2 text-ink-500" id="pw-rule-upper"><i class="pi pi-times-circle text-ink-300"></i> Al menos 1 mayúscula</li>
                                <li class="flex items-center gap-2 text-ink-500" id="pw-rule-num"><i class="pi pi-times-circle text-ink-300"></i> Al menos 1 número</li>
                                <li class="flex items-center gap-2 text-ink-500" id="pw-rule-len"><i class="pi pi-times-circle text-ink-300"></i> Al menos 8 caracteres</li>
                            </ul>
                        </div>

                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Restablecer contraseña</button>
                    </form>
                </div>

                {{-- ========= STEP 4 — Success ========= --}}
                <div class="fp-step" data-step="4">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full bg-ok-soft mx-auto flex items-center justify-center mb-5">
                            <i class="pi pi-check text-ok text-[28px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Contraseña actualizada</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Tu contraseña fue restablecida correctamente. Ya puedes iniciar sesión con tus nuevos datos.</p>
                    </div>
                    <a href="{{ route('login') }}" class="auth-btn auth-btn-primary w-full">Ir al inicio de sesión</a>
                </div>

            </div>
        </main>

        <footer class="relative z-10 flex items-center justify-between px-7 lg:px-11 py-5 text-[12px] text-ink-500 mt-auto">
            <span>© 2026 MAKAI RESIDENCES</span>
            @include('auth._lang_select')
        </footer>
    </div>
</section>

<script>
/* ---- Locale switcher (footer dropdown) ---- */
window.toggleAuthLangMenu = function (e) {
    e.stopPropagation();
    const wrap = e.currentTarget.closest('.auth-lang-wrap');
    if (!wrap) return;
    const menu = wrap.querySelector('.auth-lang-menu');
    const btn  = wrap.querySelector('button[aria-haspopup="true"]');
    const open = !menu.classList.contains('hidden');
    document.querySelectorAll('.auth-lang-menu').forEach(m => m.classList.add('hidden'));
    document.querySelectorAll('.auth-lang-wrap button[aria-haspopup="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
    if (!open) {
        menu.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
    }
};
document.addEventListener('click', function (e) {
    if (e.target.closest('.auth-lang-wrap')) return;
    document.querySelectorAll('.auth-lang-menu').forEach(m => m.classList.add('hidden'));
    document.querySelectorAll('.auth-lang-wrap button[aria-haspopup="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
});
window.setAuthLocale = function (lang) {
    const current = (document.documentElement.getAttribute('lang') || 'es').toLowerCase().split('-')[0];
    if (current === lang) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch('{{ route("locale.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ locale: lang }),
        credentials: 'same-origin',
    }).then(r => r.ok ? r.json() : Promise.reject(r))
      .then(() => window.location.reload())
      .catch(() => {});
};
</script>

<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const body = document.body;

    const state = {
        email: '',
        currentStep: 1,
    };

    /* ---------- Step navigation ---------- */
    window.setStep = (n) => {
        const prev = state.currentStep ?? 1;
        body.dataset.stepDirection = (n < prev) ? 'back' : 'forward';
        state.currentStep = n;

        const target = document.querySelector('.fp-step[data-step="' + n + '"]');
        document.querySelectorAll('.fp-step').forEach(el => {
            if (el !== target) el.classList.remove('active');
        });
        if (target) {
            target.classList.remove('active');
            target.style.animation = 'none';
            void target.offsetWidth;
            target.style.animation = '';
            target.classList.add('active');
        }

        // Back button visible only on steps 2 and 3 (not on success)
        document.getElementById('back-btn').classList.toggle('hidden', n === 1 || n === 4);

        // Step pills
        document.querySelectorAll('#step-indicator .step-pill').forEach(p => {
            const s = +p.dataset.step;
            p.classList.toggle('active', s === n);
            p.classList.toggle('done',   s <  n);
            const num = p.querySelector('.num');
            if (s < n) num.innerHTML = '<i class="pi pi-check text-[10px]"></i>';
            else num.textContent = s;
        });

        const scroll = document.querySelector('.panel-scroll');
        if (scroll) scroll.scrollTo({ top: 0, behavior: 'smooth' });
    };
    window.prevStep = () => setStep(Math.max(1, state.currentStep - 1));

    /* ---------- Step 1 — request code ---------- */
    window.submitStep1 = async (e) => {
        e.preventDefault();
        const email = document.getElementById('fp-email').value.trim();
        const errBox = document.getElementById('step1-error');
        if (!email) return false;

        const fd = new FormData();
        fd.append('email', email);
        try {
            const res = await fetch('{{ route('password.send') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) {
                errBox.textContent = json.message || 'Error al enviar el código';
                errBox.classList.remove('hidden');
                return false;
            }
            errBox.classList.add('hidden');
            state.email = email;
            document.getElementById('fp-email-echo').textContent = email;

            if (json.code) {
                document.getElementById('dev-code').textContent = json.code;
                document.getElementById('dev-code-hint').classList.remove('hidden');
            } else {
                document.getElementById('dev-code-hint').classList.add('hidden');
            }

            setStep(2);
            startResendTimer();
            setTimeout(() => {
                const first = document.querySelector('#code-row .code-input');
                if (first) first.focus();
            }, 250);
        } catch (err) {
            errBox.textContent = 'Error de red. Intenta de nuevo.';
            errBox.classList.remove('hidden');
        }
        return false;
    };

    /* ---------- Step 2 — verify code ---------- */
    let resendTimerId = null;
    function startResendTimer() {
        let s = 58;
        document.getElementById('resend-timer').textContent = s + 's';
        document.getElementById('resend-btn').disabled = true;
        clearInterval(resendTimerId);
        resendTimerId = setInterval(() => {
            s--;
            if (s <= 0) {
                clearInterval(resendTimerId);
                document.getElementById('resend-timer').textContent = '0s';
                document.getElementById('resend-btn').disabled = false;
            } else {
                document.getElementById('resend-timer').textContent = s + 's';
            }
        }, 1000);
    }
    window.resendCode = async () => {
        if (!state.email) return;
        const fd = new FormData();
        fd.append('email', state.email);
        const res = await fetch('{{ route('password.send') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const json = await res.json();
        if (json.code) {
            document.getElementById('dev-code').textContent = json.code;
            document.getElementById('dev-code-hint').classList.remove('hidden');
        }
        startResendTimer();
    };

    document.querySelectorAll('#code-row .code-input').forEach((inp, i, all) => {
        inp.addEventListener('input', (e) => {
            const v = e.target.value.replace(/\D/g, '').slice(0,1);
            e.target.value = v;
            if (v && i < all.length - 1) all[i+1].focus();
        });
        inp.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && i > 0) all[i-1].focus();
        });
        inp.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0,6);
            for (let k = 0; k < text.length && k < all.length; k++) all[k].value = text[k];
            all[Math.min(text.length, all.length-1)].focus();
        });
    });

    window.submitStep2 = async (e) => {
        e.preventDefault();
        const code = Array.from(document.querySelectorAll('#code-row .code-input')).map(i => i.value).join('');
        const errBox = document.getElementById('step2-error');
        if (code.length !== 6) {
            errBox.textContent = 'Introduce los 6 dígitos.';
            errBox.classList.remove('hidden');
            return false;
        }
        const fd = new FormData();
        fd.append('code', code);
        try {
            const res = await fetch('{{ route('password.verify') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) {
                errBox.textContent = json.message || 'Código inválido';
                errBox.classList.remove('hidden');
                return false;
            }
            errBox.classList.add('hidden');
            setStep(3);
            setTimeout(() => document.getElementById('fp-pw1').focus(), 250);
        } catch (err) {
            errBox.textContent = 'Error de red. Intenta de nuevo.';
            errBox.classList.remove('hidden');
        }
        return false;
    };

    /* ---------- Step 3 — set password ---------- */
    window.togglePassword = (id, btn) => {
        const i = document.getElementById(id);
        i.type = i.type === 'password' ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) icon.className = i.type === 'password' ? 'pi pi-eye text-[14px]' : 'pi pi-eye-slash text-[14px]';
    };
    window.updatePwStrength = () => {
        const v = document.getElementById('fp-pw1').value;
        const has = { upper: /[A-Z]/.test(v), num: /\d/.test(v), len: v.length >= 8 };
        const map = { upper: 'pw-rule-upper', num: 'pw-rule-num', len: 'pw-rule-len' };
        Object.entries(has).forEach(([k, ok]) => {
            const li = document.getElementById(map[k]);
            const ic = li.querySelector('i');
            if (ok) { li.classList.remove('text-ink-500'); li.classList.add('text-ink-950');
                     ic.className = 'pi pi-check-circle text-ok'; }
            else    { li.classList.add('text-ink-500'); li.classList.remove('text-ink-950');
                     ic.className = 'pi pi-times-circle text-ink-300'; }
        });
        const count = Object.values(has).filter(Boolean).length;
        const bars = ['pw-bar-0','pw-bar-1','pw-bar-2'].map(id => document.getElementById(id));
        bars.forEach((b, i) => {
            b.classList.remove('weak','medium','strong');
            if (count > i) {
                if (count === 1) b.classList.add('weak');
                else if (count === 2) b.classList.add('medium');
                else b.classList.add('strong');
            }
        });
    };

    window.submitStep3 = async (e) => {
        e.preventDefault();
        const p1 = document.getElementById('fp-pw1').value;
        const p2 = document.getElementById('fp-pw2').value;
        const errBox = document.getElementById('step3-error');
        const ok = /[A-Z]/.test(p1) && /\d/.test(p1) && p1.length >= 8;
        if (!ok)       { errBox.textContent = 'La contraseña no cumple las reglas.'; errBox.classList.remove('hidden'); return false; }
        if (p1 !== p2) { errBox.textContent = 'Las contraseñas no coinciden.';       errBox.classList.remove('hidden'); return false; }

        const fd = new FormData();
        fd.append('password', p1);
        fd.append('password_confirmation', p2);

        try {
            const res = await fetch('{{ route('password.update') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) {
                errBox.textContent = json.message || 'Error al actualizar la contraseña';
                errBox.classList.remove('hidden');
                return false;
            }
            errBox.classList.add('hidden');
            setStep(4);
        } catch (err) {
            errBox.textContent = 'Error de red. Intenta de nuevo.';
            errBox.classList.remove('hidden');
        }
        return false;
    };

    /* Initialize indicator */
    setStep(1);
})();
</script>
</body>
</html>
