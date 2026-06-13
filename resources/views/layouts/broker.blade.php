<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Broker — MAKAI · Duna Development')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/primeicons/primeicons.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/favicon-urbatrix.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        important: '#brk-root',
        theme: { extend: {
          fontFamily: { sans:['Inter','system-ui','sans-serif'], display:['"Inter Tight"','Inter','sans-serif'] },
          colors: {
            brand: { DEFAULT:'#5c7c68', dark:'#4a6354', soft:'#5c7c6833', tint:'#eef2ef' },
            ink: { 950:'#171717', 900:'#222530', 700:'#2b303b', 600:'#525866', 500:'#717784', 400:'#99a0ae', 300:'#cacfd8', 200:'#eaecf0', 100:'#f2f5f8', 50:'#f5f7fa' },
            ok:{DEFAULT:'#1fc16b',soft:'#e3f7ec',dark:'#1daf61'}, warn:{DEFAULT:'#fa7319',soft:'#fff3eb',dark:'#e16614'},
            err:{DEFAULT:'#fb3748',soft:'#ffebec',dark:'#e93544'}, info:{DEFAULT:'#335cff',soft:'#ebf1ff',dark:'#3559e9'},
          },
        } }
      }
    </script>
    <style>
      html, body { font-family:'Inter', system-ui, sans-serif; background:#f4f4f4; }
      ::-webkit-scrollbar { width:8px; height:8px; }
      ::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:8px; }
      .pi { font-size:14px; line-height:1; }
      .brk-nav-link { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; color:#525866; font-size:13px; font-weight:500; text-decoration:none; transition:.15s; position:relative; }
      .brk-nav-link:hover { background:rgba(255,255,255,.6); color:#222530; }
      .brk-nav-link.active { background:#fff; color:#222530; font-weight:600; box-shadow:0 1px 2px rgba(10,13,20,.06); border:1px solid #eaecf0; }
      .brk-nav-link.active::after { content:""; position:absolute; right:0; top:6px; bottom:6px; width:3px; border-radius:3px 0 0 3px; background:#5c7c68; }
      .brk-nav-link.active .pi { color:#5c7c68; }
      .brk-nav-section { font-size:10px; font-weight:600; color:#99a0ae; letter-spacing:.08em; text-transform:uppercase; padding:16px 12px 6px; }
      .brk-card { background:#fff; border:1px solid #eaecf0; border-radius:12px; }
      .brk-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; line-height:1; cursor:pointer; transition:.15s; text-decoration:none; }
      .brk-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; } .brk-btn-primary:hover { background:#4a6354; }
      .brk-btn-ghost { background:#fff; color:#525866; border:1px solid #eaecf0; } .brk-btn-ghost:hover { background:#f5f7fa; }
      .brk-input { height:36px; padding:0 14px; border:1px solid #eaecf0; border-radius:8px; font-size:13px; color:#222530; background:#fff; width:100%; outline:none; }
      .brk-input:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
      .brk-pill { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:999px; font-size:10px; font-weight:600; line-height:1; letter-spacing:.04em; text-transform:uppercase; }
      .brk-avatar { width:36px; height:36px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:13px; color:#fff; background:#5c7c68; }
      @media (max-width:1023px){ #brk-sidebar{ display:none !important; } }
    </style>
    @stack('styles')
</head>
<body id="brk-root">
<div class="min-h-screen flex p-2 sm:p-3 gap-2 sm:gap-3">

    {{-- SIDEBAR --}}
    <aside id="brk-sidebar" class="w-[220px] shrink-0 flex flex-col h-[calc(100vh-24px)] sticky top-3">
        <div class="rounded-xl bg-white border border-ink-200 px-3 py-2.5 flex items-center gap-2.5">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#5c7c68">
                <span class="block w-6 h-6"><img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full"></span>
            </span>
            <div class="flex-1 min-w-0 leading-none">
                <div class="font-display text-[13px] font-bold text-ink-950 tracking-tight">MAKAI</div>
                <div class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">Portal Broker</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto pt-3 pb-3 pr-1">
            <a href="{{ route('broker.dashboard') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'dashboard' ? 'active' : '' }}">
                <i class="pi pi-th-large"></i> Dashboard
            </a>

            <div class="brk-nav-section">Clientes</div>
            <a href="{{ route('broker.cartera') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'cartera' ? 'active' : '' }}">
                <i class="pi pi-users"></i> Mi cartera
            </a>
            <a href="{{ route('broker.registro') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'registro' ? 'active' : '' }}">
                <i class="pi pi-user-plus"></i> Registrar cliente
            </a>

            <div class="brk-nav-section">Ventas</div>
            <a href="{{ route('broker.inventario') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'inventario' ? 'active' : '' }}">
                <i class="pi pi-building"></i> Inventario en vivo
            </a>
            <a href="{{ route('broker.herramientas') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'herramientas' ? 'active' : '' }}">
                <i class="pi pi-wrench"></i> Herramientas de venta
            </a>
            <a href="{{ route('broker.material') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'material' ? 'active' : '' }}">
                <i class="pi pi-folder-open"></i> Material de ventas
            </a>

            <div class="brk-nav-section">Comisiones</div>
            <a href="{{ route('broker.comisiones') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'comisiones' ? 'active' : '' }}">
                <i class="pi pi-dollar"></i> Estado de cuenta
            </a>
            <a href="{{ route('broker.calculadora') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'calculadora' ? 'active' : '' }}">
                <i class="pi pi-calculator"></i> Calculadora
            </a>
            <a href="{{ route('broker.simulador') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'simulador' ? 'active' : '' }}">
                <i class="pi pi-chart-line"></i> Simulador de cobro
            </a>

            <div class="brk-nav-section">Crecimiento</div>
            <a href="{{ route('broker.metas') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'metas' ? 'active' : '' }}">
                <i class="pi pi-star"></i> Metas e incentivos
            </a>

            <div class="brk-nav-section">Cuenta</div>
            <a href="{{ route('broker.contrato') }}" class="brk-nav-link {{ ($activeRoute ?? '') === 'contrato' ? 'active' : '' }}">
                <i class="pi pi-file-edit"></i> Mi contrato
            </a>
        </nav>

        <div class="rounded-xl bg-white border border-ink-200 p-3 flex items-center gap-2.5">
            <span class="brk-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'B', 0, 1)) }}</span>
            <div class="flex-1 min-w-0 leading-tight">
                <div class="text-[12px] font-semibold text-ink-900 truncate">{{ auth()->user()->name }}</div>
                <div class="text-[10px] text-ink-500 truncate">Broker</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="text-ink-400 hover:text-err" title="Salir"><i class="pi pi-sign-out"></i></button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header id="brk-topbar" class="rounded-xl bg-white border border-ink-200 px-4 sm:px-6 py-3 flex items-center gap-4 mb-3">
            <div class="min-w-0">
                <div class="font-display text-[16px] font-bold text-ink-950 leading-tight">@yield('page_title', 'Portal Broker')</div>
                <div class="text-[11px] text-ink-500">@yield('page_breadcrumb', 'Duna Development')</div>
            </div>
            @if(($previewAdmin ?? false))
                <span class="ml-auto brk-pill bg-warn-soft text-warn-dark">Vista admin</span>
            @endif
        </header>

        <main class="flex-1 rounded-xl bg-white border border-ink-200 overflow-hidden">
            @if(session('success'))
                <div class="m-4 px-4 py-2 rounded-lg bg-ok-soft text-ok-dark text-[12px]">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="m-4 px-4 py-2 rounded-lg bg-err-soft text-err text-[12px]">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
