<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MAKAI · Activar cuenta</title>
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
      #auth-panel { position: relative; z-index: 10; background:#fff; width: 100vw; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
      .auth-input { width:100%; height:40px; padding:0 14px; border:1px solid #ebebeb; border-radius:10px; background:#fff; color:#171717; font-size:14px; transition: border-color .15s, box-shadow .15s; }
      .auth-input:focus { outline:none; border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.15); }
      .auth-input::placeholder { color:#a3a3a3; }
      .has-icon  { padding-left:38px; }
      .has-trail { padding-right:40px; }
      .auth-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:40px; padding:0 16px; border-radius:10px; font-weight:500; font-size:14px; line-height:1; cursor:pointer; transition: background-color .15s, border-color .15s, color .15s, transform .12s; }
      .auth-btn:active { transform: translateY(1px); }
      .auth-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; box-shadow: 0 1px 2px 0 rgba(10,13,20,.06); }
      .auth-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
      .auth-btn-primary:disabled { background:#a3a3a3; border-color:#a3a3a3; cursor:not-allowed; }
      .auth-btn-ghost { background:#f5f5f5; color:#171717; border:1px solid transparent; }
      .auth-btn-ghost:hover { background:#ebebeb; }
      .pw-bar { height:4px; flex:1; border-radius:999px; background:#ebebeb; }
      .pw-bar.weak { background:#fb3748; } .pw-bar.medium { background:#f6b51e; } .pw-bar.strong { background:#1fc16b; }
      .panel-scroll { overflow-y: auto; overflow-x: hidden; }
      .panel-scroll::-webkit-scrollbar { width: 6px; }
      .panel-scroll::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:6px; }
    </style>
</head>
<body class="bg-white">

<section id="auth-panel">
    <div class="flex-1 flex flex-col relative panel-scroll">

        <header class="relative z-10 flex items-center justify-between px-7 py-6 lg:px-11">
            <div class="relative">@include('auth._logo')</div>
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-[14px] text-ink-600">¿Ya tienes cuenta?</span>
                <a href="{{ route('login') }}" class="auth-btn auth-btn-ghost">Iniciar sesión</a>
            </div>
        </header>

        <main class="flex-1 pt-7 pb-10 relative z-10">
            <div class="w-full max-w-[452px] mx-auto px-5">

                @if(! ($valid ?? false))
                    {{-- Token inválido / expirado --}}
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full bg-err-soft mx-auto flex items-center justify-center mb-5">
                            <i class="pi pi-times text-err text-[28px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Enlace no válido</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Esta invitación ha caducado o ya fue utilizada. Solicita un nuevo enlace al equipo o restablece tu contraseña.</p>
                    </div>
                    <a href="{{ route('password.request') }}" class="auth-btn auth-btn-primary w-full">Restablecer contraseña</a>
                @else
                    <div class="text-center mb-7">
                        <div class="w-20 h-20 rounded-full border border-ink-200 mx-auto flex items-center justify-center mb-5 bg-white shadow-sm">
                            <i class="pi pi-user-plus text-ink-600 text-[26px]"></i>
                        </div>
                        <h1 class="font-display text-[26px] font-medium text-ink-950 leading-8">Activa tu cuenta</h1>
                        <p class="text-[14px] text-ink-500 mt-2">Hola{{ $name ? ' '.$name : '' }}, crea una contraseña para acceder a tu portal y dar seguimiento a tu reserva.</p>
                        <p class="text-[12px] text-ink-400 mt-1">{{ $email }}</p>
                    </div>
                    <div class="h-px bg-ink-200/70 mb-6"></div>

                    <div id="err-box" class="hidden mb-4 px-3 py-2 rounded-lg bg-err-soft border border-err/30 text-[12px] text-err"></div>

                    <form id="form-activate" class="space-y-4" onsubmit="return submitActivate(event)">
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="pw1" type="password" name="password" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail" oninput="updatePwStrength()">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('pw1', this)"><i class="pi pi-eye text-[14px]"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-ink-950 mb-1.5">Confirmar contraseña <span class="text-err">*</span></label>
                            <div class="relative">
                                <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-[14px]"></i>
                                <input id="pw2" type="password" name="password_confirmation" required minlength="8" placeholder="••••••••••••" class="auth-input has-icon has-trail">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-700 p-1" onclick="togglePassword('pw2', this)"><i class="pi pi-eye text-[14px]"></i></button>
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

                        <button type="submit" class="auth-btn auth-btn-primary w-full mt-2">Activar cuenta e ingresar</button>
                    </form>
                @endif

            </div>
        </main>

        <footer class="relative z-10 flex items-center justify-between px-7 lg:px-11 py-5 text-[12px] text-ink-500 mt-auto">
            <span>© 2026 MAKAI RESIDENCES</span>
            @include('auth._lang_select')
        </footer>
    </div>
</section>

@if($valid ?? false)
<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const ACTION = @json(route('invitation.accept', $token) . '?email=' . urlencode($email));

    window.togglePassword = (id, btn) => {
        const i = document.getElementById(id);
        i.type = i.type === 'password' ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) icon.className = i.type === 'password' ? 'pi pi-eye text-[14px]' : 'pi pi-eye-slash text-[14px]';
    };
    window.updatePwStrength = () => {
        const v = document.getElementById('pw1').value;
        const has = { upper: /[A-Z]/.test(v), num: /\d/.test(v), len: v.length >= 8 };
        const map = { upper: 'pw-rule-upper', num: 'pw-rule-num', len: 'pw-rule-len' };
        Object.entries(has).forEach(([k, ok]) => {
            const li = document.getElementById(map[k]); const ic = li.querySelector('i');
            if (ok) { li.classList.remove('text-ink-500'); li.classList.add('text-ink-950'); ic.className = 'pi pi-check-circle text-ok'; }
            else    { li.classList.add('text-ink-500'); li.classList.remove('text-ink-950'); ic.className = 'pi pi-times-circle text-ink-300'; }
        });
        const count = Object.values(has).filter(Boolean).length;
        ['pw-bar-0','pw-bar-1','pw-bar-2'].map(id => document.getElementById(id)).forEach((b, i) => {
            b.classList.remove('weak','medium','strong');
            if (count > i) b.classList.add(count === 1 ? 'weak' : count === 2 ? 'medium' : 'strong');
        });
    };

    window.submitActivate = async (e) => {
        e.preventDefault();
        const p1 = document.getElementById('pw1').value;
        const p2 = document.getElementById('pw2').value;
        const errBox = document.getElementById('err-box');
        const ok = /[A-Z]/.test(p1) && /\d/.test(p1) && p1.length >= 8;
        if (!ok)       { errBox.textContent = 'La contraseña no cumple las reglas.'; errBox.classList.remove('hidden'); return false; }
        if (p1 !== p2) { errBox.textContent = 'Las contraseñas no coinciden.';       errBox.classList.remove('hidden'); return false; }

        const fd = new FormData();
        fd.append('password', p1);
        fd.append('password_confirmation', p2);
        try {
            const res = await fetch(ACTION, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!res.ok) { errBox.textContent = json.message || 'No se pudo activar la cuenta'; errBox.classList.remove('hidden'); return false; }
            errBox.classList.add('hidden');
            window.location.href = json.redirect || '/dashboard';
        } catch (err) {
            errBox.textContent = 'Error de red. Intenta de nuevo.';
            errBox.classList.remove('hidden');
        }
        return false;
    };
})();
</script>
@endif
</body>
</html>
