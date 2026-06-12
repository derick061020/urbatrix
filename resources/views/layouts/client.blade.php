<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Propiedad — MAKAI · Duna Development')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/primeicons/primeicons.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/favicon-urbatrix.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        important: '#cli-root',
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
            },
            boxShadow: {
              xs:    '0 1px 2px 0 rgba(10,13,20,0.04)',
              card:  '0 1px 2px 0 rgba(10,13,20,0.06), 0 1px 3px 0 rgba(10,13,20,0.04)',
              panel: '0 2px 8px -2px rgba(10,13,20,0.06), 0 1px 3px 0 rgba(10,13,20,0.05)',
            }
          }
        }
      }
    </script>
    <style>
      html, body { font-family: 'Inter', system-ui, sans-serif; background:#f4f4f4; }
      ::-webkit-scrollbar { width:8px; height:8px; }
      ::-webkit-scrollbar-thumb { background:#cacfd8; border-radius:8px; }
      ::-webkit-scrollbar-track { background:transparent; }
      .pi { font-size:14px; line-height:1; }
      .cli-nav-link {
          display:flex; align-items:center; gap:10px;
          padding:9px 12px; border-radius:8px;
          color:#525866; font-size:13px; font-weight:500;
          text-decoration:none; transition:background-color .15s, color .15s;
          position:relative;
      }
      .cli-nav-link:hover { background:rgba(255,255,255,.6); color:#222530; }
      .cli-nav-link.active { background:#ffffff; color:#222530; font-weight:600; box-shadow:0 1px 2px rgba(10,13,20,.06); border:1px solid #eaecf0; }
      .cli-nav-link.active::after {
          content:""; position:absolute; right:0px; top:6px; bottom:6px;
          width:3px; border-radius:3px 0 0 3px; background:#5c7c68;
      }
      .cli-nav-link.active .pi { color:#5c7c68; }
      .cli-nav-section { font-size:10px; font-weight:600; color:#99a0ae; letter-spacing:.08em; text-transform:uppercase; padding:16px 12px 6px; }
      .badge-count {
          display:inline-flex; align-items:center; justify-content:center;
          min-width:18px; height:18px; padding:0 5px;
          background:#fb3748; color:#fff; font-size:10px; font-weight:600;
          border-radius:999px; margin-left:auto;
      }
      .cli-pill {
          display:inline-flex; align-items:center; gap:4px;
          padding:3px 8px; border-radius:999px;
          font-size:10px; font-weight:600; line-height:1; letter-spacing:.04em;
          text-transform:uppercase; white-space:nowrap;
      }
      .cli-btn {
          display:inline-flex; align-items:center; justify-content:center; gap:6px;
          padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600;
          line-height:1; cursor:pointer; transition:background-color .15s, border-color .15s;
      }
      .cli-btn-primary { background:#5c7c68; color:#fff; border:1px solid #5c7c68; }
      .cli-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
      .cli-btn-ghost { background:#fff; color:#525866; border:1px solid #eaecf0; }
      .cli-btn-ghost:hover { background:#f5f7fa; }
      .cli-card { background:#fff; border:1px solid #eaecf0; border-radius:12px; }
      .cli-input {
          height:36px; padding:0 14px 0 38px;
          border:1px solid #eaecf0; border-radius:8px;
          font-size:13px; color:#222530; background:#fff;
          width:100%; outline:none; transition:border-color .15s, box-shadow .15s;
      }
      .cli-input:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }
      .cli-avatar {
          width:36px; height:36px; border-radius:999px;
          display:inline-flex; align-items:center; justify-content:center;
          font-weight:600; font-size:13px; color:#fff; flex-shrink:0;
      }
      .cli-avatar-sm { width:28px; height:28px; font-size:11px; }
      .cli-progress { height:6px; border-radius:999px; background:#f2f5f8; overflow:hidden; }
      .cli-progress > span { display:block; height:100%; border-radius:999px; }
      .dot { width:8px; height:8px; border-radius:999px; display:inline-block; }

      .topbar-icon-btn {
          width:38px; height:38px;
          background:#fff; border:1px solid #eaecf0; border-radius:10px;
          display:inline-flex; align-items:center; justify-content:center;
          color:#525866; cursor:pointer; transition:background-color .15s, border-color .15s;
          position:relative;
      }
      .topbar-icon-btn:hover { background:#f5f7fa; border-color:#cacfd8; }
      .topbar-icon-btn .pi { font-size:16px; }
      .topbar-icon-btn .dot-indicator { position:absolute; top:7px; right:7px; width:8px; height:8px; border-radius:999px; background:#fb3748; border:2px solid #fff; }

      .topbar-search {
          height:38px; padding:0 14px 0 38px;
          border:1px solid #eaecf0; border-radius:10px;
          font-size:13px; color:#222530; background:#fff;
          width:100%; outline:none; transition:border-color .15s, box-shadow .15s;
      }
      .topbar-search:focus { border-color:#5c7c68; box-shadow:0 0 0 3px rgba(92,124,104,.18); }

      /* Search dropdown */
      .search-dropdown {
          position:absolute; top: calc(100% + 8px); left:0; right:0;
          background:#fff; border:1px solid #ebebeb; border-radius:12px;
          box-shadow: 0 20px 40px -10px rgba(0,0,0,.15);
          max-height: 420px; overflow-y:auto;
          z-index: 60; display:none;
      }
      .search-dropdown.open { display:block; }
      .search-dropdown__group + .search-dropdown__group { border-top:1px solid #f2f5f8; }
      .search-dropdown__title {
          padding: 10px 14px 4px; font-size:10px; font-weight:600;
          text-transform:uppercase; letter-spacing:.08em; color:#a3a3a3;
      }
      .search-dropdown__item {
          display:flex; align-items:center; gap:10px;
          padding: 9px 14px; cursor:pointer; transition: background-color .12s;
          text-decoration:none; color:#171717;
      }
      .search-dropdown__item:hover,
      .search-dropdown__item.is-active { background:#f5f7f6; }
      .search-dropdown__icon {
          width:30px; height:30px; border-radius:8px; background:#f2f5f8;
          display:flex; align-items:center; justify-content:center;
          color:#5c5c5c; font-size:13px; flex-shrink:0;
      }
      .search-dropdown__label {
          font-size:13px; font-weight:500; color:#171717; line-height:1.2;
          white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
      }
      .search-dropdown__sub {
          font-size:11px; color:#717784; line-height:1.2; margin-top:2px;
          white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
      }
      .search-dropdown__empty,
      .search-dropdown__spinner {
          padding: 22px 14px; text-align:center; font-size:12px; color:#a3a3a3;
      }
      .search-dropdown mark { background:#fff5cf; color:#171717; padding:0 1px; border-radius:2px; }

      /* ============ RESPONSIVE ============ */
      #sidebar-backdrop { display:none; }

      @media (max-width: 1023px) {
          #cli-sidebar {
              display: none !important;
          }
          #cli-sidebar.open {
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
          #sidebar-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:40; }
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
          #cli-topbar { padding-left: 16px !important; padding-right: 16px !important; gap: 12px !important; }
      }
    </style>
    @stack('styles')
</head>
<body id="cli-root">
<div class="min-h-screen flex p-2 sm:p-3 gap-2 sm:gap-3">

    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" onclick="document.getElementById('cli-sidebar').classList.remove('open'); this.classList.remove('open');"></div>

    {{-- ============= SIDEBAR (transparent) ============= --}}
    <aside id="cli-sidebar" class="w-[220px] shrink-0 flex flex-col h-[calc(100vh-24px)] sticky top-3">
        {{-- Logo card --}}
        <a href="{{ url('/') }}" class="rounded-xl bg-white border border-ink-200 px-3 py-2.5 flex items-center gap-2.5 hover:bg-ink-50 transition-colors cursor-pointer no-underline">
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
        </a>

        <nav class="flex-1 overflow-y-auto pt-3 pb-3 pr-1">
            @php
                $uid = auth()->id();
                $reservation = \App\Models\Reservation::where('user_id', $uid)->latest()->first();
                
                // Mis documentos: total de documentos que se muestran en la vista (contratos firmados + KYC)
                $reservationDocs = $reservation ? $reservation->documents : collect();
                $userDocs = \App\Models\Document::whereNull('reservation_id')
                    ->where(function($q) use ($uid) {
                        $q->where('metadata->user_id', $uid)
                          ->orWhere('metadata->source', 'register');
                    })
                    ->get();
                $allDocs = $reservationDocs->merge($userDocs)->unique('id');
                
                $signedDocs = $allDocs->filter(function($d) {
                    return in_array($d->status, ['signed', 'approved', 'completed'])
                        && in_array($d->document_type, ['payment_plan', 'purchase_promise', 'contract']);
                })->count();
                
                $kycDocs = $allDocs->whereIn('document_type', ['id_front', 'id_back', 'kyc'])->count();
                
                $totalDocs = $signedDocs + $kycDocs;
                
                // Acuerdos: cuenta solo los documentos que realmente se muestran como pendientes
                // en la vista de Acuerdos.
                $pendingAgreements = 0;
                if ($reservation) {
                    $acuerdoTypes = ['budget', 'payment_plan', 'purchase_promise', 'contract'];
                    $budgetSent = $reservation->isBudgetSent()
                        || $reservation->budget_status === 'approved'
                        || ! empty($reservation->budget_observations);
                    $paymentPlanSigned = $reservation->documents
                        ->firstWhere('document_type', 'payment_plan')?->status === 'signed';

                    $pendingAgreements = $reservation->documents
                        ->filter(fn($d) => in_array($d->document_type, $acuerdoTypes))
                        ->reject(fn($d) => $d->status === 'signed')
                        ->reject(fn($d) => $d->document_type === 'payment_plan' && ! $budgetSent)
                        ->reject(fn($d) => $d->document_type === 'purchase_promise' && ! $paymentPlanSigned)
                        ->filter(fn($d) => in_array($d->status, ['pending', 'generated', 'awaiting_signature', 'in_review']))
                        ->count();
                }
                
                $pendingPays = \App\Models\Payment::whereHas('reservation', fn($q) => $q->where('user_id', $uid))->where('status', 'pending')->count();
                $savedCount = \App\Models\Wishlist::where('user_id', $uid)->count();
                $unreadMsgs = \App\Models\Message::whereHas('reservation', fn($q) => $q->where('user_id', $uid))
                    ->where('sender_role', 'admin')->whereNull('read_at')->count();
            @endphp

            <a href="{{ route('dashboard') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'mi-propiedad' ? 'active' : '' }}">
                <i class="pi pi-home"></i> {{ __('Mi propiedad') }}
            </a>

            <div class="cli-nav-section">{{ __('Mi cuenta') }}</div>
            <a href="{{ route('dashboard.documents') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'documents' ? 'active' : '' }}">
                <i class="pi pi-folder-open"></i> {{ __('Mis documentos') }}
                @if($totalDocs > 0)<span class="badge-count">{{ $totalDocs }}</span>@endif
            </a>
            <a href="{{ route('dashboard.acuerdos') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'acuerdos' ? 'active' : '' }}">
                <i class="pi pi-check-square"></i> {{ __('Acuerdos') }}
                @if($pendingAgreements > 0)<span class="badge-count">{{ $pendingAgreements }}</span>@endif
            </a>
            <a href="{{ route('dashboard.payments') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'payments' ? 'active' : '' }}">
                <i class="pi pi-credit-card"></i> {{ __('Plan de pagos') }}
                @if($pendingPays > 0)<span class="badge-count">{{ $pendingPays }}</span>@endif
            </a>
            <a href="{{ route('dashboard.guardados') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'guardados' ? 'active' : '' }}">
                <i class="pi pi-heart"></i> {{ __('Guardados') }}
                @if($savedCount > 0)<span class="badge-count" style="background:#5c7c68">{{ $savedCount }}</span>@endif
            </a>

            <div class="cli-nav-section">{{ __('Comunicación') }}</div>
            <a href="{{ route('dashboard.messages') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'messages' ? 'active' : '' }}">
                <i class="pi pi-comments"></i> {{ __('Mensajes') }}
                @if($unreadMsgs > 0)<span class="badge-count">{{ $unreadMsgs }}</span>@endif
            </a>
            <a href="{{ route('dashboard.progress') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'progress' ? 'active' : '' }}">
                <i class="pi pi-chart-line"></i> {{ __('Avance de obra') }}
            </a>
            <a href="{{ route('dashboard.calendario') }}" class="cli-nav-link {{ ($activeRoute ?? '') === 'calendario' ? 'active' : '' }}">
                <i class="pi pi-calendar"></i> {{ __('Calendario') }}
            </a>
        </nav>

        {{-- User --}}
        <div class="mt-2 rounded-xl bg-white border border-ink-200">
            <div class="flex items-center gap-2.5 px-3 py-2.5">
                <button type="button" class="cli-avatar shrink-0 border-0 p-0 cursor-pointer" style="background:#5c7c68; {{ Auth::user()->avatar ? 'background-image:url('.asset('storage/'.Auth::user()->avatar).');background-size:cover;background-position:center;color:transparent;' : '' }}" title="{{ __('Editar') }}" onclick="openSettingsModal()">
                    @if(!Auth::user()->avatar){{ strtoupper(substr(Auth::user()->name ?? 'SU', 0, 2)) }}@endif
                </button>
                <button type="button" class="flex-1 min-w-0 leading-tight no-underline text-ink-950 text-left bg-transparent border-0 p-0 cursor-pointer" title="{{ __('Editar') }}" onclick="openSettingsModal()">
                    <div class="text-[13px] font-bold text-ink-950 truncate">{{ Auth::user()->name ?? __('Cliente') }}</div>
                    <div class="text-[11px] text-ink-500">{{ Auth::user()->role === 'admin' ? __('Administrador') : __('Cliente') }}</div>
                </button>
                <form method="POST" action="{{ route('logout') }}" class="m-0" data-logout-confirm>
                    @csrf
                    <button type="submit" class="text-ink-400 hover:text-ink-700 p-1" title="{{ __('Cerrar sesión') }}"><i class="pi pi-arrow-up-right text-xs"></i></button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============= MAIN CARD ============= --}}
    <div class="flex-1 min-w-0 flex flex-col rounded-2xl bg-white shadow-panel border border-ink-200 overflow-hidden">

        <header id="cli-topbar" class="h-[72px] bg-white border-b border-ink-100 flex items-center px-7 gap-3 shrink-0">
            <button id="mobile-toggle" type="button" class="topbar-icon-btn shrink-0"
                    onclick="document.getElementById('cli-sidebar').classList.toggle('open'); document.getElementById('sidebar-backdrop').classList.toggle('open');">
                <i class="pi pi-bars"></i>
            </button>
            <div class="flex-1 min-w-0">
                <h1 class="font-display text-[18px] sm:text-[22px] font-semibold text-ink-950 leading-tight tracking-tight truncate">@yield('page_title', 'Mi Propiedad')</h1>
                <p class="text-[11px] sm:text-[12px] text-ink-500 leading-tight mt-0.5 truncate">@yield('page_breadcrumb', 'Mi Propiedad')</p>
            </div>
            <div class="topbar-date hidden md:flex items-center text-[12px] text-ink-500 whitespace-nowrap">
                {{ \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat(app()->getLocale() === 'es' ? 'dddd, D [de] MMMM [de] YYYY' : 'dddd, MMMM D, YYYY') }}
            </div>
            <div class="topbar-search-wrap relative w-64 hidden md:block" data-search="client">
                <i class="pi pi-search absolute top-1/2 -translate-y-1/2 left-3 text-ink-400"></i>
                <input id="global-search-input" type="text" placeholder="{{ __('Buscar documentos, pagos, secciones…') }}" class="topbar-search pr-3" autocomplete="off" />
                <div id="global-search-dropdown" class="search-dropdown" role="listbox"></div>
            </div>

            @include('partials.notifications-dropdown', ['endpoint' => 'dashboard.notifications', 'readRoute' => 'dashboard.notifications.read'])
            <button type="button" class="topbar-icon-btn shrink-0" title="{{ __('Configuración') }}" onclick="openSettingsModal()">
                <i class="pi pi-cog"></i>
            </button>
        </header>

        <main class="flex-1 overflow-auto bg-white">
            @yield('content')
        </main>
    </div>
</div>

@include('admin.crm._partials.settings-modal', [
    'stProfileRoute' => 'dashboard.profile.update',
    'stLogoutRoute'  => 'logout',
])
@include('partials.logout-modal')
@include('partials.confirm-dialog')

@if (session('settings_success'))
<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof openSettingsModal === 'function') openSettingsModal();
    });
</script>
@endif

@include('partials.global-search', ['endpoint' => route('dashboard.search')])

@stack('scripts')
</body>
</html>
