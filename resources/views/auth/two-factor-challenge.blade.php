<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MAKAI') }} · Verificación 2FA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/primeicons/primeicons.css') }}" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
        colors: { brand: { DEFAULT:'#5c7c68', dark:'#4a6354' } } } } }
    </script>
    <style>
      html, body { font-family:'Inter', system-ui, sans-serif; }
      .tf-input { width:100%; height:46px; padding:0 14px; border:1px solid #ebebeb; border-radius:10px;
        background:#fff; color:#171717; font-size:15px; transition:border-color .15s, box-shadow .15s; }
      .tf-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
    </style>
</head>
<body class="min-h-screen bg-[#f8f8f8] flex items-center justify-center p-5">
    <div class="w-full max-w-[420px] bg-white rounded-2xl border border-[#ebebeb] shadow-[0_18px_50px_-20px_rgba(10,13,20,.25)] p-7">
        <div class="w-12 h-12 rounded-xl bg-brand/10 text-brand flex items-center justify-center mb-4">
            <i class="pi pi-shield text-[22px]"></i>
        </div>
        <h1 class="text-[22px] font-semibold text-[#171717]">Verificación en dos pasos</h1>
        <p class="text-[14px] text-[#717784] mt-1.5" id="tf-sub">
            Ingresá el código de 6 dígitos de tu app de autenticación.
        </p>

        @if ($errors->any())
            <div class="mt-4 flex items-start gap-2 text-[13px] text-[#fb3748] bg-[#ffebec] rounded-lg px-3 py-2">
                <i class="pi pi-exclamation-circle mt-[2px]"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Código del authenticator --}}
        <form method="POST" action="{{ route('2fa.challenge.verify') }}" class="mt-5" id="tf-code-form">
            @csrf
            <label class="text-[12px] font-semibold text-[#2b303b]">Código de autenticación</label>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                   class="tf-input mt-1.5 tracking-[0.3em] text-center" placeholder="000000" autofocus>
            <button type="submit"
                    class="w-full h-[46px] mt-4 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-[14px] transition">
                Verificar
            </button>
        </form>

        {{-- Código de respaldo --}}
        <form method="POST" action="{{ route('2fa.challenge.verify') }}" class="mt-5 hidden" id="tf-recovery-form">
            @csrf
            <label class="text-[12px] font-semibold text-[#2b303b]">Código de respaldo</label>
            <input type="text" name="recovery_code"
                   class="tf-input mt-1.5 text-center uppercase" placeholder="XXXX-XXXX">
            <button type="submit"
                    class="w-full h-[46px] mt-4 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-[14px] transition">
                Verificar
            </button>
        </form>

        <div class="mt-5 text-center">
            <button type="button" id="tf-toggle" class="text-[13px] font-medium text-brand hover:underline">
                Usar un código de respaldo
            </button>
        </div>

        <div class="mt-4 pt-4 border-t border-[#f2f5f8] text-center">
            <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
                <button type="submit" class="text-[12px] text-[#a3a3a3] hover:text-[#717784]">Cancelar e iniciar sesión de nuevo</button>
            </form>
        </div>
    </div>

    <script>
        const codeForm = document.getElementById('tf-code-form');
        const recForm  = document.getElementById('tf-recovery-form');
        const toggle   = document.getElementById('tf-toggle');
        const sub      = document.getElementById('tf-sub');
        let recovery = false;
        toggle.addEventListener('click', () => {
            recovery = !recovery;
            codeForm.classList.toggle('hidden', recovery);
            recForm.classList.toggle('hidden', !recovery);
            toggle.textContent = recovery ? 'Usar el código de la app' : 'Usar un código de respaldo';
            sub.textContent = recovery
                ? 'Ingresá uno de tus códigos de respaldo de un solo uso.'
                : 'Ingresá el código de 6 dígitos de tu app de autenticación.';
            (recovery ? recForm : codeForm).querySelector('input').focus();
        });
    </script>
</body>
</html>
