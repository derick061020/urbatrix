<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM Duna - Makai')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/primeicons/primeicons.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        important: '#crm-root',
        theme: {
          extend: {
            fontFamily: {
              sans:    ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
              display: ['"Inter Tight"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
              brand: { DEFAULT:'#5c7c68', dark:'#4a6354', soft:'#5c7c6833', tint:'#eef2ef' },
              ink: { 950:'#171717', 900:'#222530', 700:'#2b303b', 600:'#525866', 500:'#717784', 400:'#99a0ae', 300:'#cacfd8', 200:'#eaecf0', 100:'#f2f5f8', 50:'#f5f7fa' },
              ok:    { DEFAULT:'#1fc16b', soft:'#e3f7ec', dark:'#1daf61' },
              warn:  { DEFAULT:'#fa7319', soft:'#fff3eb', dark:'#e16614' },
              err:   { DEFAULT:'#fb3748', soft:'#ffebec', dark:'#e93544' },
              info:  { DEFAULT:'#335cff', soft:'#ebf1ff', dark:'#3559e9' },
              page:  '#ededed',
            },
            boxShadow: {
              xs:    '0 1px 2px 0 rgba(10,13,20,0.04)',
              card:  '0 1px 2px 0 rgba(10,13,20,0.06), 0 1px 3px 0 rgba(10,13,20,0.04)',
              panel: '0 2px 8px -2px rgba(10,13,20,0.06), 0 1px 3px 0 rgba(10,13,20,0.05)',
            },
            fontSize: {
              xxs: ['10px', '14px'],
              '11': ['11px', '14px'],
            },
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; background:#f4f4f4; }
      ::-webkit-scrollbar { width: 8px; height: 8px; }
      ::-webkit-scrollbar-thumb { background: #cacfd8; border-radius: 8px; }
      ::-webkit-scrollbar-track { background: transparent; }
      .pi { font-size: 14px; line-height: 1; }

      .crm-nav-link {
          display: flex; align-items: center; gap: 10px;
          padding: 9px 12px; border-radius: 8px;
          color: #525866; font-size: 13px; font-weight: 500;
          text-decoration: none; transition: background-color .15s, color .15s;
          position: relative;
      }
      .crm-nav-link:hover { background:rgba(255,255,255,.6); color:#222530; }
      .crm-nav-link.active { background:#ffffff; color:#222530; font-weight:600; box-shadow:0 1px 2px rgba(10,13,20,.06); border:1px solid #eaecf0; }
      .crm-nav-link.active::after {
          content:""; position:absolute; right:0px; top:6px; bottom:6px;
          width:3px; border-radius:3px 0 0 3px; background:#5c7c68;
      }
      .crm-nav-link.active .pi { color:#5c7c68; }
      .crm-nav-section {
          font-size: 10px; font-weight: 600; color:#99a0ae;
          letter-spacing: .08em; text-transform: uppercase;
          padding: 16px 12px 6px;
      }
      .badge-count {
          display:inline-flex; align-items:center; justify-content:center;
          min-width:18px; height:18px; padding:0 5px;
          background:#fb3748; color:#fff; font-size:10px; font-weight:600;
          border-radius:999px; margin-left:auto;
      }
      .crm-pill {
          display:inline-flex; align-items:center; gap:4px;
          padding:3px 8px; border-radius:999px;
          font-size:10px; font-weight:600; line-height:1; letter-spacing:.04em;
          text-transform:uppercase; white-space:nowrap;
      }
      .crm-tab {
          padding: 8px 16px; border-radius: 999px; font-size:12px; font-weight:600;
          color:#525866; cursor:pointer;
      }
      .crm-tab.active { background:#5c7c68; color:#fff; }
      .crm-tab-line {
          padding: 14px 4px; font-size:14px; font-weight:500; color:#717784;
          border-bottom:2px solid transparent; cursor:pointer;
      }
      .crm-tab-line.active { color:#222530; border-color:#5c7c68; font-weight:600; }
      .crm-btn {
          display:inline-flex; align-items:center; gap:6px;
          padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600;
          line-height:1; cursor:pointer; transition: background-color .15s, border-color .15s;
      }
      .crm-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; }
      .crm-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
      .crm-btn-ghost { background:#fff; color:#525866; border:1px solid #eaecf0; }
      .crm-btn-ghost:hover { background:#f5f7fa; }
      .crm-card { background:#fff; border:1px solid #eaecf0; border-radius:12px; }
      .crm-table th {
          padding: 12px 16px; font-size: 11px; font-weight: 600;
          color: #717784; text-transform: uppercase; letter-spacing: .04em;
          text-align: left; border-bottom:1px solid #eaecf0; white-space:nowrap;
      }
      .crm-table td {
          padding: 14px 16px; font-size: 13px; color: #2b303b;
          border-bottom: 1px solid #f2f5f8; vertical-align: middle;
      }
      .crm-table tr:last-child td { border-bottom: 0; }
      .crm-table tbody tr:hover td { background:#fafbfc; }
      .crm-input {
          height: 36px; padding: 0 14px 0 38px;
          border:1px solid #eaecf0; border-radius:8px;
          font-size: 13px; color:#222530; background:#fff;
          width: 100%; outline:none; transition: border-color .15s, box-shadow .15s;
      }
      .crm-input:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
      .crm-avatar {
          width:36px; height:36px; border-radius:999px;
          display:inline-flex; align-items:center; justify-content:center;
          font-weight:600; font-size:13px; color:#fff; flex-shrink:0;
      }
      .crm-avatar-sm { width:28px; height:28px; font-size:11px; }
      .crm-progress {
          height:6px; border-radius:999px; background:#f2f5f8; overflow:hidden;
      }
      .crm-progress > span { display:block; height:100%; border-radius:999px; }
      .dot { width:8px; height:8px; border-radius:999px; display:inline-block; }
      .crm-toggle { width:36px; height:20px; border-radius:999px; background:#cacfd8; position:relative; cursor:pointer; transition:background .15s; }
      .crm-toggle::after { content:""; position:absolute; top:2px; left:2px; width:16px; height:16px; border-radius:999px; background:#fff; transition: left .15s; }
      .crm-toggle.on { background:#1fc16b; }
      .crm-toggle.on::after { left:18px; }

      /* Top icon button (notifications, settings, etc.) */
      .topbar-icon-btn {
          width: 38px; height: 38px;
          background:#fff; border:1px solid #eaecf0; border-radius:10px;
          display:inline-flex; align-items:center; justify-content:center;
          color:#525866; cursor:pointer; transition: background-color .15s, border-color .15s;
          position: relative;
      }
      .topbar-icon-btn:hover { background:#f5f7fa; border-color:#cacfd8; }
      .topbar-icon-btn .pi { font-size: 16px; }
      .topbar-icon-btn .dot-indicator {
          position:absolute; top:7px; right:7px;
          width:8px; height:8px; border-radius:999px;
          background:#fb3748; border:2px solid #fff;
      }

      /* Topbar search */
      .topbar-search {
          height: 38px; padding: 0 14px 0 38px;
          border:1px solid #eaecf0; border-radius:10px;
          font-size: 13px; color:#222530; background:#fff;
          width: 100%; outline:none; transition: border-color .15s, box-shadow .15s;
      }
      .topbar-search:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }

      /* ============ RESPONSIVE ============ */
      #sidebar-backdrop { display:none; }

      @media (max-width: 1023px) {
          /* Sidebar collapses out of layout on mobile/tablet */
          #crm-sidebar {
              display: none !important;
          }
          #crm-sidebar.open {
              display: flex !important;
              position: fixed !important;
              top: 12px; left: 12px; bottom: 12px;
              z-index: 50;
              width: 240px !important;
              height: auto !important;
              max-height: calc(100vh - 24px);
              padding: 12px;
              background: #fdfdfd;
              border-radius: 14px;
              box-shadow: 0 20px 40px -10px rgba(0,0,0,.25);
              overflow-y: auto;
              animation: slideInLeft .25s cubic-bezier(.4,0,.2,1);
          }
          #sidebar-backdrop {
              display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:40;
          }
          #sidebar-backdrop.open { display:block; }
      }
      @keyframes slideInLeft {
          from { transform: translateX(-20px); opacity: 0; }
          to   { transform: translateX(0); opacity: 1; }
      }
      @media (min-width: 1024px) {
          #mobile-toggle { display: none !important; }
      }
      @media (max-width: 767px) {
          .topbar-date { display: none !important; }
          .topbar-search-wrap { display: none !important; }
          #crm-topbar { padding-left: 16px !important; padding-right: 16px !important; gap: 12px !important; }
      }
    </style>
    @stack('styles')
</head>
<body id="crm-root">

<div class="min-h-screen flex p-2 sm:p-3 gap-2 sm:gap-3">

    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" onclick="document.getElementById('crm-sidebar').classList.remove('open'); this.classList.remove('open');"></div>

    {{-- ============= SIDEBAR (transparent on gray bg) ============= --}}
    <aside id="crm-sidebar" class="w-[220px] shrink-0 flex flex-col h-[calc(100vh-24px)] sticky top-3">
        {{-- Logo card --}}
        <div class="rounded-xl bg-white border border-ink-200 px-3 py-2.5 flex items-center gap-2.5 hover:bg-ink-50 transition-colors cursor-pointer">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 shadow-xs" style="background:#5c7c68">
                <span class="block w-6 h-6">
                    <img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full">
                </span>
            </span>
            <div class="flex-1 min-w-0 leading-none">
                <div class="font-display text-[13px] font-bold text-ink-950 tracking-tight">MAKAI</div>
                <div class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">Duna Development</div>
            </div>
            <span class="text-ink-400 text-xs"><i class="pi pi-angle-down"></i></span>
        </div>

        {{-- Nav --}}
        @php $isBroker = Auth::user()->role === 'broker'; @endphp
        <nav class="flex-1 overflow-y-auto pt-3 pb-3 pr-1">
            <a href="{{ route('admin.crm.dashboard') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.dashboard' ? 'active' : '' }}">
                <i class="pi pi-th-large"></i> Dashboard
            </a>

            <div class="crm-nav-section">Gestión</div>
            @php
                $brokerUnitIds = $isBroker
                    ? Auth::user()->assignedUnits()->pluck('units.id')->all()
                    : [];
                $brokerUnitIdStrings = array_map('strval', $brokerUnitIds);

                $expedientesQuery = \App\Models\Reservation::query();
                if ($isBroker) $expedientesQuery->whereIn('unit_id', $brokerUnitIdStrings);
                $expedientesCount = $expedientesQuery->count();

                $docsQuery = \App\Models\Document::whereIn('status', ['pending', 'generated']);
                if ($isBroker) {
                    $docsQuery->whereHas('reservation', function($q) use ($brokerUnitIdStrings) {
                        $q->whereIn('unit_id', $brokerUnitIdStrings);
                    });
                }
                $docsPendientesCount = $docsQuery->count();

                $contratosQuery = \App\Models\Reservation::where(function($q){
                    $q->where('budget_status', 'draft')->orWhereNull('budget_status');
                });
                if ($isBroker) $contratosQuery->whereIn('unit_id', $brokerUnitIdStrings);
                $contratosPendientesCount = $contratosQuery->count();

                $pendingUsersCount = (!$isBroker && \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status'))
                    ? \App\Models\User::where('verification_status', 'pending')->count()
                    : 0;
                $pendingKycDocsCount = $isBroker ? 0 : \App\Models\Document::where('document_type', 'kyc')->where('status', 'pending')->whereNotNull('reservation_id')->count();
                $aprobacionesCount = $isBroker ? 0 : (\App\Models\Approval::where('status', 'pendiente')->count() + $pendingUsersCount + $pendingKycDocsCount);
                $tareasCount = $isBroker ? 0 : \App\Models\Task::whereIn('status', ['pendiente', 'en_proceso', 'vencida'])->count();
            @endphp
            <a href="{{ route('admin.crm.expedientes') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.expedientes' ? 'active' : '' }}">
                <i class="pi pi-folder"></i> Expedientes @if($expedientesCount > 0)<span class="badge-count">{{ $expedientesCount }}</span>@endif
            </a>
            <a href="{{ route('admin.crm.documentos') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.documentos' ? 'active' : '' }}">
                <i class="pi pi-file"></i> Documentos @if($docsPendientesCount > 0)<span class="badge-count">{{ $docsPendientesCount }}</span>@endif
            </a>
            <a href="{{ route('admin.crm.contratos') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.contratos' ? 'active' : '' }}">
                <i class="pi pi-id-card"></i> Reservas y Contratos @if($contratosPendientesCount > 0)<span class="badge-count">{{ $contratosPendientesCount }}</span>@endif
            </a>
            <a href="{{ route('admin.transactions-report') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'transactions-report' ? 'active' : '' }}">
                <i class="pi pi-credit-card"></i> Transacciones
            </a>

            @unless($isBroker)
                <div class="crm-nav-section">Proyectos</div>
                <a href="{{ route('admin.crm.proyectos') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.proyectos' ? 'active' : '' }}">
                    <i class="pi pi-building"></i> Proyectos
                </a>
                <a href="{{ route('admin.units') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'units' ? 'active' : '' }}">
                    <i class="pi pi-home"></i> Unidades
                </a>
                <a href="{{ route('admin.crm.avance-obra') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.avance-obra' ? 'active' : '' }}">
                    <i class="pi pi-chart-line"></i> Avance de Obra
                </a>

                <div class="crm-nav-section">Comunicación</div>
                @php
                    $mensajesCount = \App\Models\Message::where('sender_role', 'client')->whereNull('read_at')->count();
                @endphp
                <a href="{{ route('admin.communication') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'communication' ? 'active' : '' }}">
                    <i class="pi pi-comments"></i> Mensajes @if($mensajesCount > 0)<span class="badge-count">{{ $mensajesCount }}</span>@endif
                </a>
                <a href="{{ route('admin.crm.plantillas') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.plantillas' ? 'active' : '' }}">
                    <i class="pi pi-envelope"></i> Plantilla y Automatiz…
                </a>
                <a href="{{ route('admin.crm.anuncios') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.anuncios' ? 'active' : '' }}">
                    <i class="pi pi-megaphone"></i> Anuncios
                </a>

                <div class="crm-nav-section">Equipo</div>
                <a href="{{ route('admin.profiles') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'profiles' ? 'active' : '' }}">
                    <i class="pi pi-user"></i> Usuarios
                </a>
                <a href="{{ route('admin.agents') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'agents' ? 'active' : '' }}">
                    <i class="pi pi-briefcase"></i> Brokers
                </a>
                <a href="{{ route('admin.crm.aprobaciones') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.aprobaciones' ? 'active' : '' }}">
                    <i class="pi pi-check-square"></i> Aprobaciones @if($aprobacionesCount > 0)<span class="badge-count">{{ $aprobacionesCount }}</span>@endif
                </a>
                <a href="{{ route('admin.crm.tareas') }}" class="crm-nav-link {{ ($activeRoute ?? '') === 'crm.tareas' ? 'active' : '' }}">
                    <i class="pi pi-check"></i> Tareas @if($tareasCount > 0)<span class="badge-count">{{ $tareasCount }}</span>@endif
                </a>
            @endunless
        </nav>

        {{-- User --}}
        <div class="mt-2 rounded-xl bg-white border border-ink-200">
            <div class="flex items-center gap-2.5 px-3 py-2.5">
                <a href="{{ route('admin.profile.edit') }}" class="crm-avatar shrink-0" style="background:#5c7c68; {{ Auth::user()->avatar ? 'background-image:url('.asset('storage/'.Auth::user()->avatar).');background-size:cover;background-position:center;color:transparent;' : '' }}" title="Editar perfil">
                    @if(!Auth::user()->avatar){{ strtoupper(substr(Auth::user()->name ?? 'SU', 0, 2)) }}@endif
                </a>
                <a href="{{ route('admin.profile.edit') }}" class="flex-1 min-w-0 leading-tight no-underline text-ink-950" title="Editar perfil">
                    <div class="text-[13px] font-bold text-ink-950 truncate">{{ Auth::user()->name ?? 'Samuel Urbina' }}</div>
                    <div class="text-[11px] text-ink-500">{{ Auth::user()->role === 'broker' ? 'Broker' : 'Administrador' }}</div>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="text-ink-400 hover:text-ink-700 p-1" title="Cerrar sesión">
                        <i class="pi pi-arrow-up-right text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============= MAIN CONTENT CARD (white, rounded) ============= --}}
    <div class="flex-1 min-w-0 flex flex-col rounded-2xl bg-white shadow-panel border border-ink-200 overflow-hidden">

        {{-- Topbar --}}
        <header id="crm-topbar" class="h-[72px] bg-white border-b border-ink-100 flex items-center px-7 gap-5 shrink-0">
            <button id="mobile-toggle" type="button" class="topbar-icon-btn shrink-0"
                    onclick="document.getElementById('crm-sidebar').classList.toggle('open'); document.getElementById('sidebar-backdrop').classList.toggle('open');">
                <i class="pi pi-bars"></i>
            </button>
            <div class="flex-1 min-w-0">
                <h1 class="font-display text-[18px] sm:text-[22px] font-semibold text-ink-950 leading-tight tracking-tight truncate">@yield('page_title', 'Dashboard')</h1>
                <p class="text-[11px] sm:text-[12px] text-ink-500 leading-tight mt-0.5 truncate">@yield('page_breadcrumb', 'Vista general')</p>
            </div>
            <div class="topbar-date hidden md:flex items-center text-[12px] text-ink-500 whitespace-nowrap">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </div>
            <div class="topbar-search-wrap relative w-64 hidden md:block">
                <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                <input type="text" placeholder="Buscar cliente…" class="topbar-search pr-3" />
            </div>
            <button type="button" class="topbar-icon-btn shrink-0" title="Notificaciones">
                <i class="pi pi-bell"></i>
                <span class="dot-indicator"></span>
            </button>
            <button type="button" class="topbar-icon-btn shrink-0" title="Configuración" onclick="openSettingsModal()">
                <i class="pi pi-cog"></i>
            </button>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-auto bg-white">
            @yield('content')
        </main>
    </div>
</div>

@include('admin.crm._partials.settings-modal')

@if (session('settings_success'))
<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof openSettingsModal === 'function') openSettingsModal();
    });
</script>
@endif

@stack('scripts')
</body>
</html>
