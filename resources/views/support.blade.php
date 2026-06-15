@php
    $user = auth()->user();
    $userInitials = collect(explode(' ', trim($user->name ?? 'Cliente')))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $isAdminUser = $user && ($user->role ?? '') === 'admin';
    $locale = app()->getLocale();
    $isEs = ! str_starts_with($locale, 'en');
@endphp
<!doctype html>
{{--
  MAKAI · Portal del comprador · Soporte
  Adaptación del mockup de soporte a la línea gráfica Makai (verde #5c7c68 · Inter / Inter Tight),
  tomando como referencia las vistas de auth/register. Estructura: hero+búsqueda · soporte
  contextual (atado a la compra) · hub de canales · centro de ayuda (FAQs+guías) · mis solicitudes.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('MAKAI · Soporte') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon-urbatrix.png') }}" type="image/png">
<style>
  :root{
    --bg:#f4f6f4; --card:#ffffff; --ink:#171717; --ink-strong:#222530;
    --muted:#a3a3a3; --muted-2:#5c5c5c; --line:#ebebeb; --line-2:#f2f5f8;
    --brand:#5c7c68; --brand-dark:#4a6354; --brand-bg:#eef2ef; --brand-line:#dde6e0;
    --green:#1fc16b; --green-bg:#e3f7ec;
    --wa:#25d366; --wa-bg:#eafbf0;
    --info-bg:#eef2ef; --info-line:#dde6e0; --info-ink:#4a6354;
    --ff:'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --ff-display:'Inter Tight', 'Inter', system-ui, sans-serif;
    --r:14px;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{ font-family:var(--ff); color:var(--ink); background:var(--bg); -webkit-font-smoothing:antialiased; line-height:1.5; }
  a{ color:inherit; }

  /* topbar */
  .topbar{ background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; z-index:10; }
  .topbar .in{ max-width:1080px; margin:0 auto; padding:13px 22px; display:flex; align-items:center; gap:14px; }
  .back{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; border:1px solid var(--line); color:var(--ink); text-decoration:none; flex:none; transition:background-color .15s, border-color .15s; }
  .back:hover{ background:var(--brand-bg); border-color:var(--brand-line); }
  .back svg{ width:18px; height:18px; }
  .wordmark{ font-family:var(--ff-display); font-weight:700; font-size:18px; letter-spacing:.22em; color:var(--ink-strong); text-transform:uppercase; }
  .crumb{ font-size:12.5px; color:var(--muted-2); border-left:1px solid var(--line); padding-left:14px; }
  .crumb b{ color:var(--ink-strong); font-weight:600; }
  .tright{ margin-left:auto; display:flex; align-items:center; gap:12px; }
  .lang{ display:inline-flex; background:var(--line-2); border-radius:999px; padding:3px; }
  .lang button{ font:inherit; font-size:11px; font-weight:600; border:0; background:transparent; color:var(--muted-2); padding:4px 10px; border-radius:999px; cursor:pointer; }
  .lang button.on{ background:#fff; color:var(--ink-strong); box-shadow:0 1px 2px rgba(16,32,61,.12); }
  .uav{ width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#7b9a86,var(--brand-dark)); display:grid; place-items:center; color:#fff; font-weight:700; font-size:11px; }

  .wrap{ max-width:1080px; margin:0 auto; padding:26px 22px 60px; display:flex; flex-direction:column; gap:18px; }

  /* hero + search */
  .hero{ text-align:center; padding:14px 0 6px; }
  .hero h1{ font-family:var(--ff-display); font-weight:600; font-size:34px; color:var(--ink-strong); letter-spacing:-.01em; }
  .hero p{ font-size:14px; color:var(--muted-2); margin-top:6px; }
  .search{ max-width:560px; margin:18px auto 0; display:flex; align-items:center; gap:10px; background:#fff; border:1px solid var(--line); border-radius:12px; padding:13px 16px; box-shadow:0 6px 20px -14px rgba(16,32,61,.25); }
  .search:focus-within{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(92,124,104,.15); }
  .search svg{ width:18px; height:18px; color:var(--muted); flex:none; }
  .search input{ font:inherit; font-size:14px; border:0; outline:0; width:100%; color:var(--ink-strong); }

  .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--r); padding:20px; }
  .eyebrow{ font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--muted); }
  .sec-title{ font-family:var(--ff-display); font-size:22px; font-weight:600; color:var(--ink-strong); }

  /* contextual */
  .ctx{ background:linear-gradient(180deg,#fff,#f7faf8); border:1px solid var(--brand-line); border-radius:var(--r); padding:20px; }
  .ctx-head{ display:flex; align-items:center; gap:12px; margin-bottom:14px; }
  .ctx-head .ic{ width:40px; height:40px; border-radius:10px; background:var(--brand-bg); display:grid; place-items:center; color:var(--brand-dark); flex:none; } .ctx-head .ic svg{ width:20px; height:20px; }
  .ctx-head .t{ font-size:15px; font-weight:700; color:var(--ink-strong); } .ctx-head .s{ font-size:12.5px; color:var(--muted-2); }
  .ctx-head .badge{ margin-left:auto; font-size:10px; font-weight:700; color:var(--brand-dark); background:var(--brand-bg); border:1px solid var(--brand-line); padding:3px 9px; border-radius:999px; }
  .qa{ display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
  .qa a{ display:flex; align-items:center; gap:10px; text-decoration:none; border:1px solid var(--line); border-radius:11px; padding:12px; color:var(--ink); background:#fff; cursor:pointer; transition:border-color .15s, background-color .15s; }
  .qa a:hover{ border-color:var(--brand); background:var(--brand-bg); }
  .qa a svg{ width:17px; height:17px; color:var(--brand-dark); flex:none; }
  .qa a span{ font-size:12.5px; font-weight:600; }

  /* channels */
  .channels{ display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
  .ch{ background:#fff; border:1px solid var(--line); border-radius:var(--r); padding:16px; display:flex; flex-direction:column; gap:8px; }
  .ch .ci{ width:38px; height:38px; border-radius:10px; display:grid; place-items:center; } .ch .ci svg{ width:19px; height:19px; }
  .ch.wa .ci{ background:var(--wa-bg); color:#1a9e4b; } .ch.mail .ci{ background:var(--brand-bg); color:var(--brand-dark); } .ch.tel .ci{ background:var(--brand-bg); color:var(--brand-dark); } .ch.vid .ci{ background:var(--green-bg); color:#15803d; }
  .ch .cn{ font-size:13.5px; font-weight:700; color:var(--ink-strong); }
  .ch .cv{ font-size:12px; color:var(--muted-2); } .ch .cv b{ color:var(--ink); font-weight:600; }
  .ch .sla{ font-size:11px; color:var(--muted); margin-top:auto; }
  .ch .cta{ font:inherit; font-size:12px; font-weight:700; text-align:center; text-decoration:none; border-radius:9px; padding:9px; cursor:pointer; margin-top:4px; transition:background-color .15s, border-color .15s; }
  .ch.wa .cta{ background:var(--wa); color:#fff; border:0; } .ch.wa .cta:hover{ background:#1fbe5b; }
  .ch .cta.ghost{ background:#fff; border:1px solid var(--line); color:var(--ink); } .ch .cta.ghost:hover{ background:var(--brand-bg); border-color:var(--brand-line); }

  /* two-column */
  .cols{ display:grid; grid-template-columns:1.3fr 1fr; gap:18px; align-items:start; }

  /* FAQ accordion + categorías */
  .cats{ display:flex; flex-wrap:wrap; gap:7px; margin-bottom:14px; }
  .cat{ font-size:11.5px; font-weight:600; color:var(--muted-2); background:#fff; border:1px solid var(--line); border-radius:999px; padding:6px 12px; cursor:pointer; }
  .cat.on{ color:var(--brand-dark); border-color:var(--brand); background:var(--brand-bg); }
  .faq{ border-top:1px solid var(--line-2); }
  .faq:first-of-type{ border-top:0; }
  .faq-q{ width:100%; text-align:left; font:inherit; font-size:13.5px; font-weight:600; color:var(--ink-strong); background:none; border:0; padding:13px 0; cursor:pointer; display:flex; align-items:center; gap:10px; }
  .faq-q .chev{ margin-left:auto; transition:transform .2s; color:var(--muted); } .faq.open .faq-q .chev{ transform:rotate(90deg); }
  .faq-a{ display:none; font-size:13px; color:var(--muted-2); padding:0 0 14px 26px; } .faq.open .faq-a{ display:block; }
  .guias{ margin-top:16px; padding-top:14px; border-top:1px solid var(--line-2); }
  .guia{ display:flex; align-items:center; gap:10px; padding:9px 0; text-decoration:none; color:var(--ink); font-size:13px; font-weight:600; cursor:pointer; }
  .guia:hover{ color:var(--brand-dark); }
  .guia svg{ width:16px; height:16px; color:var(--brand-dark); }

  /* tickets */
  .ticket{ border:1px solid var(--line); border-radius:11px; padding:13px; margin-bottom:10px; }
  .ticket .tt{ display:flex; align-items:center; gap:8px; }
  .ticket .tid{ font-family:ui-monospace,monospace; font-size:11px; color:var(--muted); }
  .ticket .ts{ font-size:10px; font-weight:700; padding:2px 8px; border-radius:999px; margin-left:auto; }
  .ts.open{ color:var(--brand-dark); background:var(--brand-bg); } .ts.proc{ color:var(--info-ink); background:var(--info-bg); } .ts.done{ color:#15803d; background:var(--green-bg); }
  .ticket .tq{ font-size:13px; font-weight:600; color:var(--ink-strong); margin-top:7px; }
  .ticket .tm{ font-size:11px; color:var(--muted); margin-top:3px; }
  .btn-primary{ font:inherit; font-size:13px; font-weight:700; border:0; background:var(--brand); color:#fff; border-radius:10px; padding:11px 14px; cursor:pointer; width:100%; transition:background-color .15s; }
  .btn-primary:hover{ background:var(--brand-dark); }
  .newform{ display:none; margin-top:12px; border-top:1px solid var(--line-2); padding-top:14px; }
  .newform.show{ display:block; }
  .newform label{ display:block; font-size:11.5px; font-weight:600; color:var(--muted-2); margin:0 0 5px; }
  .newform select, .newform textarea{ font:inherit; font-size:13px; width:100%; border:1px solid var(--line); border-radius:9px; padding:10px; outline:none; margin-bottom:11px; color:var(--ink-strong); }
  .newform select:focus, .newform textarea:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(92,124,104,.15); }
  .newform select{ appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235c5c5c' stroke-width='2.4'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; }
  .newform textarea{ resize:vertical; min-height:70px; }

  /* footer status + asistente */
  .statusbar{ display:flex; align-items:center; gap:10px; justify-content:center; font-size:12px; color:var(--muted-2); padding:6px; }
  .dot{ width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 0 3px var(--green-bg); }
  .note{ display:flex; align-items:flex-start; gap:9px; background:var(--brand-bg); border:1px solid var(--brand-line); border-radius:11px; padding:11px 13px; font-size:11.5px; color:var(--brand-dark); }
  .note svg{ width:15px; height:15px; flex:none; color:var(--brand-dark); margin-top:1px; }

  .assistant{ position:fixed; right:22px; bottom:22px; display:flex; align-items:center; gap:9px; background:var(--brand-dark); color:#fff; border-radius:999px; padding:12px 16px; box-shadow:0 12px 30px -10px rgba(16,32,61,.5); cursor:default; font-size:13px; font-weight:600; }
  .assistant svg{ width:18px; height:18px; } .assistant .soon{ font-size:9px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; background:rgba(255,255,255,.18); border-radius:999px; padding:2px 7px; }

  @media (max-width:920px){ .qa{ grid-template-columns:1fr 1fr; } .channels{ grid-template-columns:1fr 1fr; } .cols{ grid-template-columns:1fr; } }
  @media (max-width:560px){ .qa{ grid-template-columns:1fr; } .channels{ grid-template-columns:1fr; } .hero h1{ font-size:27px; } .crumb{ display:none; } }
  @media (prefers-reduced-motion:reduce){ *{transition:none!important;} }
  button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible{ outline:2px solid var(--brand); outline-offset:2px; }

  /* logo + profile trigger */
  .logo-img{ height:28px; width:auto; max-width:150px; object-fit:contain; display:block; }
  .profile-trigger{ display:inline-flex; align-items:center; gap:8px; padding:0; background:transparent; border:none; cursor:pointer; border-radius:9999px; }
  .profile-trigger .pinfo{ display:flex; flex-direction:column; align-items:flex-end; gap:2px; line-height:1; }
  .profile-trigger .pname{ font-weight:600; font-size:12px; color:var(--brand-dark); }
  .profile-trigger .prole{ font-weight:500; font-size:9px; color:#99a0ae; letter-spacing:.72px; text-transform:uppercase; }
  .profile-trigger .pav{ width:34px; height:34px; border-radius:50%; object-fit:cover; flex-shrink:0; display:inline-block; }
  .profile-trigger .pav-i{ width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,#7b9a86,var(--brand-dark)); color:#fff; font-weight:600; font-size:13px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
  @media (max-width:560px){ .profile-trigger .pinfo{ display:none; } }

  /* ===== MENU DROPDOWN (réplica del header del home) ===== */
  .menu-dropdown-container{ position:absolute; z-index:3000; right:0; top:calc(100% + 8px); transform-origin:right top; display:none; }
  .menu-dropdown-container.menu-open{ display:block; }
  #profileDropdown.menu-dropdown-container{
    position:absolute; top:calc(100% + 14px); right:0; width:320px; display:flex; flex-direction:column;
    align-items:flex-start; overflow:hidden; padding:6px; border-radius:18px; background:#fff;
    box-shadow:0 20px 20px -10px rgba(23,23,23,.04),0 10px 10px -5px rgba(23,23,23,.04),0 6px 6px -3px rgba(23,23,23,.04),0 3px 3px -1.5px rgba(23,23,23,.04),0 1px 1px -.5px rgba(23,23,23,.04),0 0 0 1px rgba(23,23,23,.08);
    z-index:3001; visibility:hidden; opacity:0; transform:translateX(8px); transition:opacity .25s cubic-bezier(.4,0,.2,1), transform .25s cubic-bezier(.4,0,.2,1), visibility .25s;
  }
  #profileDropdown.menu-dropdown-container.menu-open{ visibility:visible; opacity:1; transform:translateX(0); }
  #profileDropdown .menu-item, #profileDropdown .logout-item{ transition:all .2s ease; cursor:pointer; font-family:var(--ff); }
  #profileDropdown .menu-item:hover, #profileDropdown .logout-item:hover{ background:#f8fafc !important; transform:translateX(2px); }
  #profileDropdown .logout-item:hover{ background:#fef2f2 !important; }
  #profileDropdown .logout-item:hover svg{ color:#dc2626 !important; }
  #profileDropdown .logout-item:hover > div:last-child{ color:#dc2626 !important; }
  @media (max-width:560px){ #profileDropdown.menu-dropdown-container{ width:288px; } }
</style>
</head>
<body>

  <div class="topbar">
    <div class="in">
      <a href="/" class="back" aria-label="{{ __('Volver al inicio') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      </a>
      <a href="/" style="display:flex;align-items:center;text-decoration:none;flex:none;">
        <img src="/images/makai-logo.png" alt="{{ __('Makai') }}" class="logo-img">
      </a>
      <span class="crumb"><b>{{ __('Soporte') }}</b></span>
      <div class="tright">
        <!-- Toggle de idioma (real · persiste vía /locale) -->
        <div class="lang">
          <button type="button" id="lang-es" class="{{ $isEs ? 'on' : '' }}" onclick="setLanguage('es')">ES</button>
          <button type="button" id="lang-en" class="{{ $isEs ? '' : 'on' }}" onclick="setLanguage('en')">EN</button>
        </div>

        <!-- Foto de perfil → abre el menú del home -->
        <div style="position:relative;">
          <button type="button" class="profile-trigger" onclick="toggleProfileMenu()" aria-label="{{ __('Menú de perfil') }}">
            <span class="pinfo">
              <span class="pname">{{ $user ? explode(' ', $user->name)[0] : 'Cliente' }}</span>
              <span class="prole">{{ $isAdminUser ? __('Admin') : __('Cliente') }}</span>
            </span>
            @if($user && $user->avatar)
              <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="pav">
            @else
              <span class="pav-i">{{ $userInitials }}</span>
            @endif
          </button>

          <!-- PROFILE DROPDOWN (réplica del menú hamburguesa del home) -->
          <div class="menu-dropdown-container" id="profileDropdown">

            <!-- User info -->
            <div style="display:flex;gap:8px;align-items:center;padding:8px;background:white;border-radius:10px;width:100%;flex-shrink:0;">
              <div style="position:relative;border-radius:999px;width:40px;height:40px;flex-shrink:0;overflow:hidden;">
                @if($user && $user->avatar)
                  <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" style="position:absolute;width:100%;height:100%;object-fit:cover;border-radius:999px;" />
                @else
                  <span style="position:absolute;display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;background:var(--brand);color:white;font-family:var(--ff);font-weight:600;font-size:16px;border-radius:999px;">{{ $userInitials }}</span>
                @endif
              </div>
              <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;flex:1;min-width:0;">
                <div style="font-family:var(--ff);font-weight:600;font-size:14px;color:#171717;letter-spacing:-0.084px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user ? $user->name : 'Cliente' }}</div>
                <div style="font-family:var(--ff);font-weight:500;font-size:12px;color:#a3a3a3;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user ? $user->email : '' }}</div>
              </div>
            </div>

            <!-- Divider -->
            <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;"><div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div></div>

            <!-- Menu Items -->
            <a href="{{ $isAdminUser ? route('admin.crm.dashboard') : route('dashboard') }}" style="text-decoration:none;display:block;width:100%">
              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </div>
                <div style="flex:1;min-width:0;font-family:var(--ff);font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Dashboard') }}</div>
              </div>
            </a>

            <a href="{{ route('dashboard.documents') }}" style="text-decoration:none;display:block;width:100%">
              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div style="flex:1;min-width:0;font-family:var(--ff);font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Documentos') }}</div>
              </div>
            </a>

            <!-- Divider -->
            <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;"><div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div></div>

            <a href="{{ route('support') }}" style="text-decoration:none;display:block;width:100%">
              <div class="menu-item" style="background:#eef2ef;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--brand-dark);"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <div style="flex:1;min-width:0;font-family:var(--ff);font-weight:600;font-size:14px;color:var(--brand-dark);letter-spacing:-0.084px;">{{ __('Soporte') }}</div>
              </div>
            </a>

            <a href="{{ $isAdminUser ? route('admin.crm.avance-obra') : route('dashboard.progress') }}" style="text-decoration:none;display:block;width:100%">
              <div class="menu-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;flex-shrink:0;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#5c5c5c;"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                <div style="flex:1;min-width:0;font-family:var(--ff);font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Progreso de la construcción') }}</div>
              </div>
            </a>

            <!-- Divider -->
            <div style="display:flex;align-items:center;justify-content:center;padding:1.5px 0;width:100%;flex-shrink:0;"><div style="background:#ebebeb;flex:1;height:1px;min-width:0;"></div></div>

            <!-- Sign Out -->
            <form method="POST" action="/logout" style="margin:0;width:100%;flex-shrink:0;">
              @csrf
              <button type="submit" class="logout-item" style="background:white;display:flex;gap:8px;align-items:center;overflow:hidden;padding:8px;border-radius:12px;width:100%;border:none;cursor:pointer;">
                <div style="position:relative;width:20px;height:20px;flex-shrink:0;overflow:hidden;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#dc2626;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </div>
                <div style="flex:1;text-align:start;min-width:0;font-family:var(--ff);font-weight:500;font-size:14px;color:#5c5c5c;letter-spacing:-0.084px;">{{ __('Cerrar sesión') }}</div>
              </button>
            </form>

            <!-- Footer -->
            <div style="background:white;display:flex;align-items:center;overflow:hidden;padding:8px;width:100%;flex-shrink:0;">
              <div style="flex:1;min-width:0;font-family:var(--ff);font-weight:500;font-size:12px;color:#a3a3a3;line-height:16px;">v.1.0.1 · {{ __('Términos y condiciones') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="wrap">

    <!-- HERO -->
    <div class="hero">
      <div class="eyebrow">{{ __('Centro de soporte') }}</div>
      <h1>{{ __('¿En qué podemos ayudarte?') }}</h1>
      <p>{{ __('Resuelve dudas sobre tu compra, pagos y documentos — o habla con nosotros.') }}</p>
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input placeholder="{{ __('Busca en la ayuda: reserva, plan de pago, documentos…') }}">
      </div>
    </div>

    <!-- SOPORTE CONTEXTUAL (atado a la compra) -->
    <section class="ctx">
      <div class="ctx-head">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V8l9-5 9 5v13z"/><path d="M9 21v-7h6v7"/></svg></div>
        <div><div class="t">{{ __('Tu compra · Unit C-301') }}</div><div class="s">{{ __('Makai Residences · reserva activa') }}</div></div>
        <span class="badge">{{ __('Ayuda sobre tu unidad') }}</span>
      </div>
      <div class="qa">
        <a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>{{ __('Dudas de mi plan de pago') }}</span></a>
        <a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M15 2v5h5"/></svg><span>{{ __('Estado de mis documentos') }}</span></a>
        <a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span>{{ __('Reprogramar una cuota') }}</span></a>
        <a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg><span>{{ __('Hablar con mi asesor') }}</span></a>
        <a href="{{ route('dashboard.progress') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/></svg><span>{{ __('Ver progreso de obra') }}</span></a>
        <a><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg><span>{{ __('Otra cosa') }}</span></a>
      </div>
    </section>

    <!-- CANALES -->
    <div>
      <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:12px;">
        <span class="sec-title">{{ __('Contáctanos') }}</span>
        <span style="font-size:12px; color:var(--muted);">{{ __('Elige el canal que prefieras') }}</span>
      </div>
      <div class="channels">
        <div class="ch wa">
          <div class="ci"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.8.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2a.4.4 0 0 0 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11 11 0 0 0 4.3 3.8c1.7.7 2 .6 2.4.5a2.4 2.4 0 0 0 1.6-1.1 2 2 0 0 0 .1-1.1c0-.1-.2-.2-.4-.3z"/></svg></div>
          <div class="cn">WhatsApp</div>
          <div class="cv">{{ __('El más rápido') }}</div>
          <div class="sla">{{ __('Atendido en horario laboral (GMT-4)') }}</div>
          <a class="cta" href="https://wa.me/18495854171" target="_blank" rel="noopener">{{ __('Abrir WhatsApp') }}</a>
        </div>
        <div class="ch mail">
          <div class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
          <div class="cn">{{ __('Email') }}</div>
          <div class="cv"><b>soporte@urbatrix.com</b></div>
          <div class="sla">{{ __('Respondemos en hasta 48 h hábiles') }}</div>
          <a class="cta ghost" href="mailto:soporte@urbatrix.com">{{ __('Escribir') }}</a>
        </div>
        <div class="ch tel">
          <div class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></div>
          <div class="cn">{{ __('Teléfono') }}</div>
          <div class="cv"><b>+1 (849) 585-4171</b></div>
          <div class="sla">{{ __('Lun–Vie · horario AST') }}</div>
          <a class="cta ghost" href="tel:+18495854171">{{ __('Llamar') }}</a>
        </div>
        <div class="ch vid">
          <div class="ci"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg></div>
          <div class="cn">{{ __('Videollamada') }}</div>
          <div class="cv">{{ __('Recorrido guiado con tu asesor') }}</div>
          <div class="sla">{{ __('Eliges día y hora') }}</div>
          <a class="cta ghost">{{ __('Agendar') }}</a>
        </div>
      </div>
    </div>

    <!-- AYUDA + SOLICITUDES -->
    <div class="cols">

      <!-- Centro de ayuda -->
      <section class="card">
        <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:14px;">
          <span class="sec-title">{{ __('Centro de ayuda') }}</span>
        </div>
        <div class="cats">
          <span class="cat on">{{ __('Reservas y pagos') }}</span>
          <span class="cat">{{ __('Documentos y KYC') }}</span>
          <span class="cat">{{ __('Entrega y obra') }}</span>
          <span class="cat">{{ __('Mi cuenta') }}</span>
        </div>

        <!-- FAQs (acordeón) -->
        <div class="faq"><button class="faq-q">{{ __('¿La reserva es reembolsable?') }}<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg></button><div class="faq-a">{{ __('Sí. La reserva de US$2,500 es 100% reembolsable bajo las condiciones del contrato de reserva. Puedes consultar los detalles en tus documentos o escribirnos.') }}</div></div>
        <div class="faq"><button class="faq-q">{{ __('¿Cómo pago el inicial y en qué plazos?') }}<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg></button><div class="faq-a">{{ __('El pago inicial (mínimo 20% del valor) puede abonarse de una vez o fraccionado según tu plan de pago. Encuentras tu calendario exacto en “Plan de pagos” de tu portal.') }}</div></div>
        <div class="faq"><button class="faq-q">{{ __('¿En qué moneda pago?') }}<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg></button><div class="faq-a">{{ __('Los precios se fijan en USD durante el ciclo de venta. Puedes ver el equivalente referencial en DOP/EUR, pero la operación se liquida en USD.') }}</div></div>
        <div class="faq"><button class="faq-q">{{ __('¿Cómo funciona la pasarela de pago internacional?') }}<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 6 15 12 9 18"/></svg></button><div class="faq-a">{{ __('Aceptamos tarjeta internacional (con verificación 3D Secure) y pago asistido. Cada pago queda confirmado y reflejado en tu plan de pagos.') }}</div></div>

        <div class="guias">
          <div class="eyebrow" style="margin-bottom:6px;">{{ __('Guías del proceso') }}</div>
          <a class="guia"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h4"/></svg>{{ __('Cómo reservar tu unidad, paso a paso') }}</a>
          <a class="guia"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>{{ __('Qué documentos necesitas (KYC)') }}</a>
          <a class="guia" href="{{ route('dashboard.progress') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/></svg>{{ __('Cómo seguir el avance de tu obra') }}</a>
        </div>
      </section>

      <!-- Mis solicitudes -->
      <section class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px;">
          <span class="sec-title">{{ __('Mis solicitudes') }}</span>
        </div>

        <div class="ticket">
          <div class="tt"><span class="tid">SOP-112</span><span class="ts proc">{{ __('En proceso') }}</span></div>
          <div class="tq">{{ __('Duda sobre la fecha de la cuota de mayo') }}</div>
          <div class="tm">{{ __('Pago · creada hace 1 día · respondemos en hasta 48 h') }}</div>
        </div>
        <div class="ticket">
          <div class="tt"><span class="tid">SOP-108</span><span class="ts done">{{ __('Resuelta') }}</span></div>
          <div class="tq">{{ __('No podía descargar el plano de la unidad') }}</div>
          <div class="tm">{{ __('Técnico · resuelta hace 4 días') }}</div>
        </div>

        <button class="btn-primary" onclick="document.getElementById('nf').classList.toggle('show');">{{ __('+ Nueva solicitud') }}</button>
        <div class="newform" id="nf">
          <label>{{ __('Tema') }}</label>
          <select><option>{{ __('Pago') }}</option><option>{{ __('Documentos') }}</option><option>{{ __('Reserva') }}</option><option>{{ __('Técnico / cuenta') }}</option><option>{{ __('Legal') }}</option><option>{{ __('Otro') }}</option></select>
          <label>{{ __('Cuéntanos') }}</label>
          <textarea placeholder="{{ __('Describe tu solicitud. Si es sobre tu unidad, ya la asociamos a Unit C-301.') }}"></textarea>
          <button class="btn-primary">{{ __('Enviar solicitud') }}</button>
        </div>
      </section>
    </div>

    <!-- estado + nota honesta -->
    <div class="statusbar"><span class="dot"></span> {{ __('Todos los sistemas operativos · estado en vivo') }}</div>
    <div class="note">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
      <span>Los plazos de respuesta son reales según horario laboral; para temas legales o de privacidad se aplican los canales y plazos específicos (legal@ / privacidad@urbatrix.com). El estado del servicio refleja datos reales, no estimaciones.</span>
    </div>

  </div>

  <!-- asistente IA (fase 2) -->
  <div class="assistant" title="{{ __('Próximamente') }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8V4H8"/><rect x="4" y="8" width="16" height="12" rx="2"/><path d="M2 14h2M20 14h2M9 13v2M15 13v2"/></svg>
    Asistente <span class="soon">{{ __('pronto') }}</span>
  </div>

<script>
  // acordeón FAQ
  document.querySelectorAll('.faq-q').forEach(function(b){
    b.addEventListener('click', function(){ b.parentElement.classList.toggle('open'); });
  });
  // categorías (cosmético)
  document.querySelectorAll('.cat').forEach(function(c){
    c.addEventListener('click', function(){ document.querySelectorAll('.cat').forEach(function(x){x.classList.remove('on');}); c.classList.add('on'); });
  });

  // ===== Toggle de idioma (real · persiste en backend y recarga) =====
  function setLanguage(lang){
    // Reflejo inmediato en la UI del toggle
    document.querySelectorAll('.lang button').forEach(function(b){ b.classList.remove('on'); });
    var btn = document.getElementById('lang-' + lang);
    if (btn) btn.classList.add('on');

    var serverLang = (document.documentElement.getAttribute('lang') || 'es').toLowerCase().split('-')[0];
    if (serverLang === lang) return; // ya es el idioma activo

    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch('{{ route("locale.update") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ locale: lang }),
    }).then(function(r){ return r.ok ? r.json() : Promise.reject(r); })
      .then(function(){ window.location.reload(); })
      .catch(function(){ /* la UI local ya quedó actualizada */ });
  }

  // ===== Menú de perfil (mismo comportamiento que el home) =====
  function toggleProfileMenu(){
    var dropdown = document.getElementById('profileDropdown');
    if (!dropdown) return;
    if (dropdown.classList.contains('menu-open')) {
      closeProfileMenu();
    } else {
      dropdown.classList.add('menu-open');
      setTimeout(function(){ document.addEventListener('click', closeProfileMenuOnOutsideClick); }, 10);
    }
  }
  function closeProfileMenu(){
    var dropdown = document.getElementById('profileDropdown');
    if (dropdown) dropdown.classList.remove('menu-open');
    document.removeEventListener('click', closeProfileMenuOnOutsideClick);
  }
  function closeProfileMenuOnOutsideClick(e){
    var dropdown = document.getElementById('profileDropdown');
    if (dropdown && !dropdown.contains(e.target) && !e.target.closest('.profile-trigger')) {
      closeProfileMenu();
    }
  }
</script>
</body>
</html>
