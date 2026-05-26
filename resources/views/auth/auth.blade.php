<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MAKAI · Duna Development</title>
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

      /* ---- Background hero (always full screen) ---- */
      .auth-bg {
        position: fixed; inset: 0;
        background: #efefef;
        z-index: 0;
      }
      .auth-bg img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover; object-position: center;
      }
      /* Decorative isotipo in the hero top-right area */
      .auth-bg::after {
        content:""; position:absolute; top:-10px; right:-10px;
        width: 450px; height: 450px;
        background: url('/images/isotipo-makai.png') no-repeat center/contain;
        opacity: 1;
        pointer-events:none;
      }

      /* ---- Floating panel ---- */
      #auth-panel {
        position: relative; z-index: 10;
        background:#fff;
        margin: 10px;
        width: 664px; height: calc(100vh - 20px);
        border-radius: 20px;
        box-shadow: 0 30px 60px -20px rgba(0,0,0,.25);
        overflow: hidden;
        display:flex; flex-direction: column;
        transition: width .7s cubic-bezier(.65,.05,.36,1), height .7s cubic-bezier(.65,.05,.36,1), margin .7s cubic-bezier(.65,.05,.36,1), border-radius .55s cubic-bezier(.65,.05,.36,1);
        will-change: width, height, margin, border-radius;
      }
      body[data-mode="register"] #auth-panel {
        width: calc(100vw - 0px);
        height: 100vh;
        margin: 0;
        border-radius: 0;
      }
      /* RESPONSIVE: full-screen panel on tablets and mobile, hide hero */
      @media (max-width: 1023px) {
        #auth-panel {
          width: 100vw; height: 100vh;
          margin: 0; border-radius: 0;
        }
        #hero { display: none !important; }
      }
      @media (max-width: 640px) {
        /* Reduce content padding on phones */
        .login-view, .register-view { padding-left: 16px !important; padding-right: 16px !important; }
        .register-view header, .register-view footer { padding-left: 16px !important; padding-right: 16px !important; }
        #step-indicator { display: none !important; }
        .reg-step h1 { font-size: 22px !important; }
        .code-input { width: 44px !important; height: 52px !important; font-size: 22px !important; }
      }

      /* ---- Inputs ---- */
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

      /* ---- Buttons ---- */
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
      .auth-btn-social {
        background:#fff; color:#171717; border:1px solid #ebebeb;
        flex:1; height:48px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
      }
      .auth-btn-social:hover { background:#f8f8f8; }
      .auth-btn-ghost { background:#f5f5f5; color:#171717; border:1px solid transparent; }
      .auth-btn-ghost:hover { background:#ebebeb; }
      .auth-link { color:#171717; font-weight:500; font-size:14px; border-bottom:1px solid #171717; padding-bottom:1px; }
      .auth-link:hover { color:#5c7c68; border-color:#5c7c68; }

      /* ---- View toggles ---- */
      .login-view,
      .register-view { transition: opacity .25s ease; }
      body[data-mode="register"] .login-view    { display: none; }
      body[data-mode="login"]    .register-view { display: none; }

      /* ---- Step transitions ---- */
      @keyframes reg-step-in-forward {
        from { opacity: 0; transform: translateX(28px); }
        to   { opacity: 1; transform: translateX(0); }
      }
      @keyframes reg-step-in-back {
        from { opacity: 0; transform: translateX(-28px); }
        to   { opacity: 1; transform: translateX(0); }
      }
      .reg-step { display: none; }
      .reg-step.active {
        display: block;
        animation: reg-step-in-forward .42s cubic-bezier(.22,1,.36,1) both!important;
      }
      body[data-step-direction="back"] .reg-step.active {
        animation-name: reg-step-in-back!important;
      }
      @media (prefers-reduced-motion: reduce) {
        .reg-step.active { animation: none!important; }
      }

      /* ---- 6-digit code inputs ---- */
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

      /* ---- Step indicator ---- */
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

      /* ---- Role card ---- */
      .role-card {
        display:flex; align-items:center; gap:14px;
        padding: 16px; border:1px solid #ebebeb; border-radius:14px;
        background:#fff; cursor:pointer; position:relative;
        transition: border-color .15s, background-color .15s;
      }
      .role-card:hover { background:#f8f8f8; }
      .role-card.selected { border-color:#5c7c68; background:#fff; box-shadow: 0 0 0 1px #5c7c68; }
      .role-card .radio-dot {
        width:18px; height:18px; border-radius:999px;
        border: 1.5px solid #cacfd8;
        margin-left:auto; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
      }
      .role-card.selected .radio-dot { border-color:#5c7c68; }
      .role-card.selected .radio-dot::after { content:""; width:9px; height:9px; border-radius:999px; background:#5c7c68; }
      .verif-tag {
        position:absolute; right:14px; top:-9px;
        background:#fff; border:1px solid #ebebeb;
        padding: 3px 8px; border-radius: 999px;
        font-size: 9px; font-weight:600; letter-spacing:.1em;
        color:#a3a3a3;
      }

      /* ---- Document row ---- */
      .doc-row { display:flex; align-items:center; gap:14px; padding: 10px 0; }
      .doc-row + .doc-row { border-top:1px dashed #ebebeb; }

      /* ---- Password strength bars ---- */
      .pw-bar { height:4px; flex:1; border-radius:999px; background:#ebebeb; }
      .pw-bar.weak   { background:#fb3748; }
      .pw-bar.medium { background:#f6b51e; }
      .pw-bar.strong { background:#1fc16b; }
      .resend-strong { font-weight:700; color:#171717; }

      /* Scrollable panel content when register has long steps */
      .panel-scroll { overflow-y: auto; overflow-x: hidden; }
      .panel-scroll::-webkit-scrollbar { width: 6px; }
      .panel-scroll::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:6px; }
    </style>
</head>
<body data-mode="{{ ($mode ?? 'login') === 'register' ? 'register' : 'login' }}" class="bg-white">

{{-- Full-screen hero background --}}
<div class="auth-bg">
    <img src="{{ asset('images/brand/login-hero.jpg') }}" alt="Makai Residences">
</div>

{{-- Floating panel --}}
<section id="auth-panel">

    {{-- ====== LOGIN VIEW ====== --}}
    <div class="login-view flex-1 flex flex-col p-7 panel-scroll">

        <header class="flex items-center justify-between">
            @include('auth._logo')
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-[14px] text-ink-600">¿No tienes una cuenta?</span>
                <button type="button" onclick="goToRegister()" class="auth-btn auth-btn-ghost">Regístrate</button>
            </div>
        </header>

        <div class="flex-1 flex items-center justify-center py-6">
            <div class="w-full max-w-[392px]">

                <div class="text-center mb-7">
                    <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 shadow-sm">
                        <i class="pi pi-user text-ink-600 text-[26px]"></i>
                    </div>
                    <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Inicia sesión en tu cuenta</h1>
                    <p class="text-[14px] text-ink-500 mt-2">Introduce tus datos para iniciar sesión.</p>
                </div>

                {{-- Social buttons — icon only --}}
                <div class="flex gap-3">
                    <a href="#" class="auth-btn-social" title="Iniciar sesión con Apple">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#000"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                    </a>
                    <a href="{{ route('auth.google') }}" class="auth-btn-social" title="Iniciar sesión con Google">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M5.27 9.76A7.08 7.08 0 0 1 16.42 6.5l3.27-3.27A11.97 11.97 0 0 0 1.31 7.41z"/>
                            <path fill="#34A853" d="M16.04 18.01A7.36 7.36 0 0 1 12 19.1a7.08 7.08 0 0 1-6.72-4.82L1.29 17.41A12 12 0 0 0 12 24c2.93 0 5.73-1.04 7.83-3z"/>
                            <path fill="#4A90E2" d="M19.83 21c2.2-2.05 3.62-5.1 3.62-9 0-.72-.11-1.49-.27-2.18H12v4.43h6.43c-.32 1.6-1.2 2.83-2.39 3.64z"/>
                            <path fill="#FBBC05" d="M5.28 14.27a7.12 7.12 0 0 1 0-4.51L1.31 6.59a12.01 12.01 0 0 0 0 10.82z"/>
                        </svg>
                    </a>
                </div>

                <div class="flex items-center gap-3 my-6">
                    <span class="h-px flex-1 bg-ink-200"></span>
                    <span class="text-[11px] uppercase tracking-[0.16em] text-ink-400 font-medium">O</span>
                    <span class="h-px flex-1 bg-ink-200"></span>
                </div>

                @if($errors->any() && ($mode ?? 'login') === 'login')
                    <div class="mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/login" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Dirección de correo electrónico <span class="text-err">*</span></label>
                        <div class="relative">
                            <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="samuel@gmail.com" class="auth-input has-icon" autocomplete="email">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Contraseña <span class="text-err">*</span></label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                            <input id="login-pass" type="password" name="password" required placeholder="••••••••••••" class="auth-input has-icon has-trail" autocomplete="current-password">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('login-pass', this)"><i class="pi pi-eye text-[14px]"></i></button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-brand">
                            <span class="text-[13px] text-ink-600">Mantenerme conectado</span>
                        </label>
                        <a href="#" class="auth-link text-[13px]">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Iniciar sesión</button>
                </form>
            </div>
        </div>

        <footer class="flex items-center justify-between text-[12px] text-ink-500">
            <span>© 2026 MAKAI RESIDENCES</span>
            <button class="flex items-center gap-1.5 hover:text-ink-700">
                <i class="pi pi-globe text-[12px]"></i><span>ESP</span><i class="pi pi-angle-down text-[10px]"></i>
            </button>
        </footer>
    </div>

    {{-- ====== REGISTER VIEW ====== --}}
    <div class="register-view flex-1 flex flex-col relative panel-scroll">

        {{-- Header --}}
        <header class="relative z-10 flex items-center justify-between px-7 py-6 lg:px-11">
            <div class="relative">
                @include('auth._logo')
            </div>

            <div id="step-indicator" class="hidden lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:flex items-center gap-5">
                @php
                    $steps = [
                        ['Confirmación', 1],
                        ['Perfil',       2],
                        ['Documentación',3],
                        ['Contraseña',   4],
                        ['Resumen',      5],
                    ];
                @endphp
                @foreach($steps as $idx => $s)
                    <div class="step-pill" data-step="{{ $s[1] }}">
                        <span class="num">{{ $s[1] }}</span>
                        <span>{{ $s[0] }}</span>
                    </div>
                    @if(!$loop->last)
                        <i class="pi pi-angle-right text-ink-300 text-[12px] step-chevron" data-after-step="{{ $s[1] }}"></i>
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-[14px] text-ink-600 reg-initial-only">¿Necesitas ayuda?</span>
                <a href="#" class="auth-btn auth-btn-ghost reg-initial-only"><i class="pi pi-headphones text-[14px]"></i> Contáctanos</a>
                <button type="button" onclick="goToLogin()" class="auth-btn auth-btn-ghost w-10 px-0" title="Volver al login">
                    <i class="pi pi-times text-[12px]"></i>
                </button>
            </div>
        </header>

        {{-- "Volver" button (only steps 1-5) --}}
        <button type="button" onclick="prevStep()" id="back-btn" class="hidden absolute top-[100px] left-7 lg:left-11 z-20 auth-btn auth-btn-ghost"><i class="pi pi-angle-left text-[12px]"></i> Volver</button>

        <main class="flex-1 pt-7 pb-10 relative z-10">
            <div class="w-full max-w-[452px] mx-auto px-5">

                {{-- ========= STEP 0 — Crea tu cuenta ========= --}}
                <div class="reg-step active" data-step="0">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-user-plus text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Crea tu cuenta</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Accede a Duna Sales Platform de forma gratuita</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step0-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    {{-- Social buttons — icon only --}}
                    <div class="flex gap-3 mb-5">
                        <a href="#" class="auth-btn-social" title="Registrarse con Apple">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#000"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                        </a>
                        <a href="{{ route('auth.google') }}" class="auth-btn-social" title="Registrarse con Google">
                            <svg width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#EA4335" d="M5.27 9.76A7.08 7.08 0 0 1 16.42 6.5l3.27-3.27A11.97 11.97 0 0 0 1.31 7.41z"/>
                                <path fill="#34A853" d="M16.04 18.01A7.36 7.36 0 0 1 12 19.1a7.08 7.08 0 0 1-6.72-4.82L1.29 17.41A12 12 0 0 0 12 24c2.93 0 5.73-1.04 7.83-3z"/>
                                <path fill="#4A90E2" d="M19.83 21c2.2-2.05 3.62-5.1 3.62-9 0-.72-.11-1.49-.27-2.18H12v4.43h6.43c-.32 1.6-1.2 2.83-2.39 3.64z"/>
                                <path fill="#FBBC05" d="M5.28 14.27a7.12 7.12 0 0 1 0-4.51L1.31 6.59a12.01 12.01 0 0 0 0 10.82z"/>
                            </svg>
                        </a>
                    </div>

                    <div class="flex items-center gap-3 mb-5">
                        <span class="h-px flex-1 bg-ink-200"></span>
                        <span class="text-[11px] uppercase tracking-[0.16em] text-ink-400 font-medium">O</span>
                        <span class="h-px flex-1 bg-ink-200"></span>
                    </div>

                    <form id="form-step0" class="space-y-4" onsubmit="return submitStep0(event)">
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Nombre completo <span class="text-err">*</span></label>
                            <input type="text" name="full_name" required placeholder="Samuel Urbina" class="auth-input" autocomplete="name">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Dirección de correo electrónico <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input type="email" name="email" required placeholder="samuel@gmail.com" class="auth-input has-icon" autocomplete="email">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Número de teléfono <span class="text-err">*</span></label>
                            <div class="flex gap-2">
                                <div class="relative">
                                    <select name="country" class="auth-input pr-7 w-[120px] appearance-none">
                                        <option value="DO+1">🇩🇴 +1</option>
                                        <option value="US+1">🇺🇸 +1</option>
                                        <option value="ES+34">🇪🇸 +34</option>
                                        <option value="MX+52">🇲🇽 +52</option>
                                        <option value="FR+33">🇫🇷 +33</option>
                                        <option value="BR+55">🇧🇷 +55</option>
                                    </select>
                                    <i class="pi pi-angle-down absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 text-[10px] pointer-events-none"></i>
                                </div>
                                <input type="tel" name="phone" required placeholder="(612) 000-0000" class="auth-input flex-1" autocomplete="tel">
                            </div>
                        </div>
                        <label class="flex items-start gap-2 cursor-pointer pt-1">
                            <input type="checkbox" name="terms" value="1" required checked class="w-4 h-4 rounded accent-brand mt-0.5">
                            <span class="text-[13px] text-ink-600">
                                Acepto los <a href="#" class="text-ink-950 hover:text-brand underline">Términos</a> y la
                                <a href="#" class="text-ink-950 hover:text-brand underline">Política de Privacidad</a>.
                            </span>
                        </label>
                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Comenzar</button>
                        <div class="text-center text-[13px] text-ink-600 mt-4">
                            ¿Ya tienes una cuenta?
                            <button type="button" onclick="goToLogin()" class="auth-link ml-1">Iniciar sesión</button>
                        </div>
                    </form>
                </div>

                {{-- ========= STEP 1 — Verifica tu email ========= --}}
                <div class="reg-step" data-step="1">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-envelope text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Verifica tu email</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Enviamos un código de 6 digitos a tu email.</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step1-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>
                    <div id="dev-code-hint" class="hidden mb-4 px-3 py-2 rounded-lg bg-ok-soft border border-ok/30 text-[12px] text-ink-700">
                        <span class="font-semibold">DEV</span> · Código generado: <code id="dev-code" class="font-mono"></code>
                    </div>

                    <form id="form-step1" onsubmit="return submitStep1(event)">
                        <div class="flex items-center justify-center gap-2 mb-7" id="code-row">
                            <input class="code-input" data-pos="0" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="1" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="2" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <span class="text-ink-300 text-[32px] mx-1">–</span>
                            <input class="code-input" data-pos="3" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="4" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                            <input class="code-input" data-pos="5" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                        </div>
                        <button type="submit" class="auth-btn auth-btn-primary w-full">Continuar</button>
                        <div class="text-center mt-5 text-[13px] text-ink-500">
                            ¿No recibiste el código?  Reenviar en <span class="resend-strong" id="resend-timer">58s</span>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" onclick="resendCode()" id="resend-btn" disabled class="auth-link disabled:opacity-40 disabled:cursor-not-allowed">Volver a enviar el código</button>
                        </div>
                    </form>
                </div>

                {{-- ========= STEP 2 — Perfil / Rol ========= --}}
                <div class="reg-step" data-step="2">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-id-card text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">¿Cómo utilizarás la plataforma?</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Selecciona el perfil que mejor te describa</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <form id="form-step2" class="space-y-3" onsubmit="return submitStep2(event)">
                        <label class="role-card selected" data-role="buyer">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-eye"></i></div>
                            <div class="flex-1">
                                <div class="text-[14px] font-semibold text-ink-950">Soy comprador</div>
                                <div class="text-[12px] text-ink-500">Estoy interesado en adquirir una propiedad</div>
                            </div>
                            <input type="radio" name="role" value="buyer" checked class="sr-only">
                            <span class="radio-dot"></span>
                        </label>
                        <label class="role-card" data-role="broker">
                            <span class="verif-tag">VERIFICACIÓN REQUERIDA</span>
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-id-card"></i></div>
                            <div class="flex-1">
                                <div class="text-[14px] font-semibold text-ink-950">Soy broker independiente</div>
                                <div class="text-[12px] text-ink-500">Intermediario individual de propiedades</div>
                            </div>
                            <input type="radio" name="role" value="broker" class="sr-only">
                            <span class="radio-dot"></span>
                        </label>
                        <label class="role-card" data-role="agency">
                            <span class="verif-tag">VERIFICACIÓN REQUERIDA</span>
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-building"></i></div>
                            <div class="flex-1">
                                <div class="text-[14px] font-semibold text-ink-950">Soy una agencia inmobiliaria</div>
                                <div class="text-[12px] text-ink-500">Express o equipo de intermediación</div>
                            </div>
                            <input type="radio" name="role" value="agency" class="sr-only">
                            <span class="radio-dot"></span>
                        </label>
                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-5">Continuar</button>
                    </form>
                </div>

                {{-- ========= STEP 3 — Documentación ========= --}}
                <div class="reg-step" data-step="3">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-id-card text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Documentación profesional</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Sube los documentos requeridos para activar tu cuenta</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <form id="form-step3" onsubmit="return submitStep3(event)">
                        @php
                            // Each row: [key, label, icon, optional, [roles allowed]]
                            $docs = [
                                ['id_front', 'Cédula / Pasaporte (Frente)',  'id-card', false, ['broker', 'agency']],
                                ['id_back',  'Cédula / Pasaporte (Reverso)', 'id-card', false, ['broker', 'agency']],
                                ['rnc',      'RNC / Registro fiscal',        'file',    false, ['agency']],
                            ];
                        @endphp
                        @foreach($docs as $d)
                            <div class="doc-row" data-doc="{{ $d[0] }}" data-roles="{{ implode(',', $d[4]) }}">
                                <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-{{ $d[2] }}"></i></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[14px] text-ink-950 font-medium">
                                        {{ $d[1] }}
                                        @if(isset($d[3]) && $d[3])
                                            <span class="text-[12px] text-ink-400 font-normal">(Opcional)</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-ink-500 doc-filename hidden mt-0.5"></div>
                                </div>
                                <input type="file" name="docs[{{ $d[0] }}]" accept=".pdf,.jpg,.jpeg,.png" class="hidden doc-input" onchange="onDocSelected(this)">
                                <button type="button" class="auth-btn auth-btn-ghost text-[12px] py-1 px-3 doc-btn" onclick="this.previousElementSibling.click()">Subir</button>
                            </div>
                        @endforeach

                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-6">Continuar</button>

                        <div class="text-center text-[13px] text-ink-600 mt-4">
                            ¿Quieres completar más tarde?
                            <button type="button" onclick="skipDocs()" class="auth-link ml-1">Saltar este paso</button>
                        </div>
                    </form>
                </div>

                {{-- ========= STEP 4 — Contraseña ========= --}}
                <div class="reg-step" data-step="4">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-lock text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Configuración de contraseña</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Configura una contraseña segura para tu cuenta.</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="step4-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <form id="form-step4" class="space-y-4" onsubmit="return submitStep4(event)">
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Crear una contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="reg-pw1" type="password" name="password" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail" oninput="updatePwStrength()">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('reg-pw1', this)"><i class="pi pi-eye text-[14px]"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Confirmar contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="reg-pw2" type="password" name="password_confirmation" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('reg-pw2', this)"><i class="pi pi-eye text-[14px]"></i></button>
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

                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Continuar</button>
                    </form>
                </div>

                {{-- ========= STEP 5 — Resumen ========= --}}
                <div class="reg-step" data-step="5">
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-check text-ink-600 text-[22px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Resumen de incorporación</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Revisa y completa la configuración de tu cuenta.</p>
                    </div>

                    <div id="step5-error" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <div class="border border-ink-200 rounded-2xl divide-y divide-ink-100 overflow-hidden">
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-user"></i></div>
                            <div class="flex-1">
                                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Nombre completo</div>
                                <div class="text-[14px] font-semibold text-ink-950" id="sum-name">—</div>
                            </div>
                            <button type="button" class="text-ink-400 hover:text-ink-700 p-1" onclick="setStep(0)"><i class="pi pi-pencil text-[12px]"></i></button>
                        </div>
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-envelope"></i></div>
                            <div class="flex-1">
                                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Correo electrónico</div>
                                <div class="text-[14px] font-semibold text-ink-950" id="sum-email">—</div>
                            </div>
                            <button type="button" class="text-ink-400 hover:text-ink-700 p-1" onclick="setStep(0)"><i class="pi pi-pencil text-[12px]"></i></button>
                        </div>
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-eye"></i></div>
                            <div class="flex-1">
                                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Rol</div>
                                <div class="text-[14px] font-semibold text-ink-950" id="sum-role">—</div>
                            </div>
                            <button type="button" class="text-ink-400 hover:text-ink-700 p-1" onclick="setStep(2)"><i class="pi pi-pencil text-[12px]"></i></button>
                        </div>
                        <div id="sum-verif-row" class="px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-ink-100 flex items-center justify-center text-ink-600"><i class="pi pi-user-edit"></i></div>
                            <div class="flex-1">
                                <div class="text-[10px] uppercase tracking-wider text-ink-400 font-semibold">Verificación</div>
                                <div class="text-[14px] font-semibold text-ink-950" id="sum-verif">Pendiente</div>
                            </div>
                            <button type="button" class="text-ink-400 hover:text-ink-700 p-1" onclick="setStep(3)"><i class="pi pi-pencil text-[12px]"></i></button>
                        </div>
                    </div>
                    <button type="button" onclick="submitFinal()" class="auth-btn auth-btn-primary w-full mt-5">Completo</button>
                </div>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 flex items-center justify-between px-7 lg:px-11 py-5 text-[12px] text-ink-500 mt-auto">
            <span>© 2026 MAKAI RESIDENCES</span>
            <button class="flex items-center gap-1.5 hover:text-ink-700">
                <i class="pi pi-globe text-[12px]"></i><span>ESP</span><i class="pi pi-angle-down text-[10px]"></i>
            </button>
        </footer>
    </div>
</section>

<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const body = document.body;

    /* ------------ State ------------- */
    const state = {
        full_name: '', email: '', phone: '', country: 'DO+1',
        role: 'buyer',
        password: '',
        code: '',
        docs: {},
        currentStep: 0,
    };

    /* ------------ View toggles ------------- */
    window.goToRegister = () => {
        body.dataset.mode = 'register';
        body.dataset.stepDirection = 'forward';
        window.history.replaceState(null, '', '{{ route('register') }}');
        state.currentStep = null;
        setStep(0);
    };
    window.goToLogin = () => {
        body.dataset.mode = 'login';
        window.history.replaceState(null, '', '{{ route('login') }}');
        setStep(0);
    };

    /* ------------ Step navigation ------------- */
    window.setStep = (n) => {
        // Buyers don't have a docs step — redirect to role step so they can change their role first
        if (n === 3 && state.role === 'buyer') n = 2;
        const prev = state.currentStep ?? 0;
        const direction = (n < prev) ? 'back' : 'forward';
        body.dataset.stepDirection = direction;
        state.currentStep = n;

        const target = document.querySelector('.reg-step[data-step="' + n + '"]');
        document.querySelectorAll('.reg-step').forEach(el => {
            if (el !== target) el.classList.remove('active');
        });

        if (target) {
            target.classList.remove('active');
            target.style.animation = 'none';
            // Force reflow so the next animation assignment restarts the keyframes
            void target.offsetWidth;
            target.style.animation = '';
            target.classList.add('active');
        }
        document.getElementById('step-indicator').classList.toggle('hidden', n === 0);
        document.querySelectorAll('.reg-initial-only').forEach(el => el.classList.toggle('hidden', n !== 0));
        document.getElementById('back-btn').classList.toggle('hidden', n === 0);

        const isBuyer = state.role === 'buyer';
        // Hide the Documentación pill + its trailing chevron for buyers
        document.querySelectorAll('#step-indicator .step-chevron').forEach(c => {
            const after = +c.dataset.afterStep;
            c.style.display = (after === 3 && isBuyer) ? 'none' : '';
        });

        let visibleIndex = 0;
        document.querySelectorAll('#step-indicator .step-pill').forEach(p => {
            const s = +p.dataset.step;
            const hidden = (s === 3 && isBuyer);
            p.style.display = hidden ? 'none' : '';
            p.classList.toggle('active', s === n);
            p.classList.toggle('done',   s <  n);

            const num = p.querySelector('.num');
            if (hidden) return; // don't renumber hidden pills
            visibleIndex++;
            if (s < n) num.innerHTML = '<i class="pi pi-check text-[10px]"></i>';
            else num.textContent = visibleIndex;
        });

        if (n === 5) {
            document.getElementById('sum-name').textContent  = state.full_name || '—';
            document.getElementById('sum-email').textContent = state.email     || '—';
            document.getElementById('sum-role').textContent  = ({ buyer: 'Comprador', broker: 'Broker', agency: 'Agencia' })[state.role] || '—';
            // Hide the Verificación row when no documents were requested (buyer with no uploads)
            const needsVerif = state.role !== 'buyer' || Object.keys(state.docs || {}).length > 0;
            document.getElementById('sum-verif-row').style.display = needsVerif ? '' : 'none';
        }

        const scroll = document.querySelector('.register-view');
        if (scroll) scroll.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.prevStep = () => setStep(Math.max(0, state.currentStep - 1));

    /* ------------ Step 0 — basics + send code ------------- */
    window.submitStep0 = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const data = Object.fromEntries(fd.entries());
        if (!data.full_name || !data.email || !data.phone) return false;

        Object.assign(state, data);

        try {
            const res = await fetch('{{ route('register.init') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) {
                document.getElementById('step0-error').textContent = json.message || 'Error al iniciar registro';
                document.getElementById('step0-error').classList.remove('hidden');
                return false;
            }
            state.code = json.code;
            if (json.code) {
                document.getElementById('dev-code').textContent = json.code;
                document.getElementById('dev-code-hint').classList.remove('hidden');
            }
            setStep(1);
            startResendTimer();
        } catch (err) {
            document.getElementById('step0-error').textContent = 'Error de red. Intenta de nuevo.';
            document.getElementById('step0-error').classList.remove('hidden');
        }
        return false;
    };

    /* ------------ Step 1 — verify code ------------- */
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
        const fd = new FormData();
        fd.append('email', state.email);
        const res = await fetch('{{ route('register.resend') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const json = await res.json();
        if (json.code) {
            state.code = json.code;
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
    window.submitStep1 = async (e) => {
        e.preventDefault();
        const code = Array.from(document.querySelectorAll('#code-row .code-input')).map(i => i.value).join('');
        if (code.length !== 6) {
            document.getElementById('step1-error').textContent = 'Introduce los 6 dígitos.';
            document.getElementById('step1-error').classList.remove('hidden');
            return false;
        }
        const fd = new FormData();
        fd.append('code', code);
        const res = await fetch('{{ route('register.verify') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const json = await res.json();
        if (!res.ok) {
            document.getElementById('step1-error').textContent = json.message || 'Código inválido';
            document.getElementById('step1-error').classList.remove('hidden');
            return false;
        }
        document.getElementById('step1-error').classList.add('hidden');
        setStep(2);
        return false;
    };

    /* ------------ Step 2 — role ------------- */
    document.querySelectorAll('.role-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const input = card.querySelector('input[type=radio]');
            input.checked = true;
            state.role = input.value;
        });
    });
    window.submitStep2 = (e) => {
        e.preventDefault();
        // Buyers don't need verification documents — skip step 3 entirely and drop any stale uploads
        if (state.role === 'buyer') {
            state.docs = {};
            setStep(4);
            return false;
        }
        // Filter doc rows based on selected role; drop uploads for rows not visible to this role
        document.querySelectorAll('.doc-row').forEach(row => {
            const roles = (row.dataset.roles || '').split(',').filter(Boolean);
            const visible = roles.includes(state.role);
            row.style.display = visible ? '' : 'none';
            if (!visible) delete state.docs[row.dataset.doc];
        });
        setStep(3);
        return false;
    };

    /* ------------ Step 3 — docs ------------- */
    window.onDocSelected = (input) => {
        const row = input.closest('.doc-row');
        const file = input.files[0];
        if (!file) return;
        state.docs[row.dataset.doc] = file;
        const fn = row.querySelector('.doc-filename');
        fn.textContent = file.name + ' · ' + (file.size/1024).toFixed(0) + ' KB';
        fn.classList.remove('hidden');
        row.querySelector('.doc-btn').textContent = 'Cambiar';
    };
    window.skipDocs = () => setStep(4);
    window.submitStep3 = (e) => { e.preventDefault(); setStep(4); return false; };

    /* ------------ Step 4 — password ------------- */
    window.togglePassword = (id, btn) => {
        const i = document.getElementById(id);
        i.type = i.type === 'password' ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) icon.className = i.type === 'password' ? 'pi pi-eye text-[14px]' : 'pi pi-eye-slash text-[14px]';
    };
    window.updatePwStrength = () => {
        const v = document.getElementById('reg-pw1').value;
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
    window.submitStep4 = (e) => {
        e.preventDefault();
        const p1 = document.getElementById('reg-pw1').value;
        const p2 = document.getElementById('reg-pw2').value;
        const ok = /[A-Z]/.test(p1) && /\d/.test(p1) && p1.length >= 8;
        const errBox = document.getElementById('step4-error');
        if (!ok)       { errBox.textContent = 'La contraseña no cumple las reglas.'; errBox.classList.remove('hidden'); return false; }
        if (p1 !== p2) { errBox.textContent = 'Las contraseñas no coinciden.';       errBox.classList.remove('hidden'); return false; }
        errBox.classList.add('hidden');
        state.password = p1;
        setStep(5);
        return false;
    };

    /* ------------ Final submit ------------- */
    window.submitFinal = async (e) => {
        if (e) e.preventDefault();
        const fd = new FormData();
        fd.append('full_name', state.full_name);
        fd.append('email',     state.email);
        fd.append('phone',     state.phone);
        fd.append('country',   state.country);
        fd.append('role',      state.role);
        fd.append('password',  state.password);
        fd.append('password_confirmation', state.password);
        Object.entries(state.docs).forEach(([k, f]) => fd.append('docs['+k+']', f));

        try {
            const res = await fetch('{{ route('register.complete') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) {
                document.getElementById('step5-error').textContent = json.message || 'Error al crear la cuenta';
                document.getElementById('step5-error').classList.remove('hidden');
                return;
            }
            window.location.href = json.redirect || '/dashboard';
        } catch (err) {
            document.getElementById('step5-error').textContent = 'Error de red. Intenta de nuevo.';
            document.getElementById('step5-error').classList.remove('hidden');
        }
        return false;
    };
})();
</script>

</body>
</html>
