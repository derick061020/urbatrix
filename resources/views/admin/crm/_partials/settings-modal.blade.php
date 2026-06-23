{{-- ============================================================
     Settings modal — Figma "Configuración de la cuenta"
     3 secciones (Información Personal · Privacidad y Seguridad · Notificaciones)

     Parametrizado: pasar $stProfileRoute (nombre de ruta del POST) y opcional
     $stLogoutRoute. Default → admin.profile.update / logout.
     ============================================================ --}}
@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();
    $sFirst   = $authUser->first_name ?? '';
    $sLast    = $authUser->last_name  ?? '';
    $sName    = trim($sFirst.' '.$sLast) ?: ($authUser->name ?? '');
    $sEmail   = $authUser->email ?? '';
    $sPhone   = $authUser->phone ?? '';
    $sAvatar  = $authUser?->avatar ? asset('storage/'.$authUser->avatar) : null;
    $sInit    = strtoupper(substr($sFirst ?: ($authUser->name ?? 'A'), 0, 1) . substr($sLast, 0, 1)) ?: 'A';

    $stProfileRoute = $stProfileRoute ?? 'admin.profile.update';
    $stLogoutRoute  = $stLogoutRoute  ?? 'logout';

    // Firma del proyecto — sólo para administradores. Se usa para firmar los
    // contratos (promesa de compraventa / plan de pagos) a nombre de Makai.
    $stIsAdmin = (bool) ($authUser?->is_admin);
    $stProjSig    = $stIsAdmin ? (\App\Models\Setting::get('project_signature', []) ?: []) : [];
    $stProjSig    = is_array($stProjSig) ? $stProjSig : [];
    $stSigImage   = $stProjSig['signature_image'] ?? null;
    $stSigName    = $stProjSig['signer_name'] ?? '';
    $stSigEntity  = $stProjSig['signer_entity'] ?? '';

    // Menú del cliente — ítems configurables del navbar (enlaces / documentos).
    $stClientMenu = $stIsAdmin ? (\App\Models\Setting::get('client_menu', []) ?: []) : [];
    $stClientMenu = is_array($stClientMenu) ? array_values($stClientMenu) : [];
@endphp

<style>
    /* ===== Settings modal ===== */
    .st-overlay {
        position: fixed; inset: 0; z-index: 200;
        background: rgba(15, 17, 24, 0.45);
        display: none; align-items: center; justify-content: center;
        padding: 24px;
        animation: stFadeIn .18s ease-out;
    }
    .st-overlay.open { display: flex; }
    @keyframes stFadeIn { from { opacity: 0; } to { opacity: 1; } }

    .st-shell {
        width: 100%; max-width: 1040px;
        height: 640px; max-height: calc(100vh - 48px);
        background: #fff; border-radius: 18px; overflow: hidden;
        box-shadow: 0 30px 80px -20px rgba(10,13,20,.35);
        display: grid; grid-template-columns: 260px 1fr;
        animation: stSlideIn .22s cubic-bezier(.4,0,.2,1);
    }
    @keyframes stSlideIn { from { transform: translateY(12px) scale(.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

    .st-sidebar {
        background: #fafbfc; border-right: 1px solid #eaecf0;
        padding: 22px 14px; display: flex; flex-direction: column; gap: 4px;
    }
    .st-section-label {
        font-size: 10px; font-weight: 700; color: #99a0ae;
        text-transform: uppercase; letter-spacing: .08em;
        padding: 4px 10px 10px;
    }
    .st-nav-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 9px;
        font-size: 13px; font-weight: 500; color: #525866;
        cursor: pointer; border: none; background: transparent;
        text-align: left; transition: background .15s, color .15s;
        position: relative;
    }
    .st-nav-item:hover { background: rgba(92,124,104,.06); color: #222530; }
    .st-nav-item .pi { font-size: 14px; color: #717784; }
    .st-nav-item.active {
        background: #fff; color: #222530; font-weight: 600;
        box-shadow: 0 1px 2px rgba(10,13,20,.05);
        border: 1px solid #eaecf0;
    }
    .st-nav-item.active .pi { color: #5c7c68; }
    .st-nav-item .chev {
        margin-left: auto; opacity: 0; transition: opacity .15s;
        font-size: 11px;
    }
    .st-nav-item.active .chev { opacity: 1; }

    /* Cerrar sesión — empujado al fondo del sidebar */
    .st-logout {
        margin-top: auto;
        color: #e93544;
        font-weight: 600;
    }
    .st-logout .pi { color: #e93544; }
    .st-logout:hover { background: rgba(233,53,68,.07); color: #c81e2c; }
    .st-logout:hover .pi { color: #c81e2c; }

    .st-main { display: flex; flex-direction: column; min-width: 0; min-height: 0; height: 100%; max-height: calc(100vh - 48px); }
    .st-head {
        padding: 22px 28px 0;
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 12px; flex-wrap: wrap;
    }
    .st-head-title { font-family: 'Inter Tight', Inter, sans-serif; font-size: 18px; font-weight: 700; color: #171717; line-height: 1.25; }
    .st-head-sub   { font-size: 12px; color: #717784; margin-top: 4px; }
    .st-head-actions { display: flex; gap: 8px; align-items: center; }

    .st-btn {
        display:inline-flex; align-items:center; gap:6px;
        padding: 9px 16px; border-radius: 9px;
        font-size: 13px; font-weight: 600; line-height: 1;
        cursor: pointer; border: 1px solid transparent;
        transition: background .15s, border-color .15s;
    }
    .st-btn-ghost { background:#fff; color:#525866; border-color:#eaecf0; }
    .st-btn-ghost:hover { background:#f5f7fa; }
    .st-btn-primary { background:#5c7c68; color:#fff; border-color:#5c7c68; }
    .st-btn-primary:hover { background:#4a6354; border-color:#4a6354; }
    .st-btn-link { background:transparent; color:#5c7c68; border:none; padding:6px 8px; font-weight:600; font-size:12px; cursor:pointer; }
    .st-btn-link:hover { color:#4a6354; }

    .st-tabs {
        margin: 14px 28px 0;
        display: flex; gap: 24px;
        border-bottom: 1px solid #eaecf0;
    }
    .st-tab {
        background: transparent; border: none; padding: 12px 2px;
        font-size: 14px; font-weight: 500; color: #717784;
        cursor: pointer; border-bottom: 2px solid transparent;
        transition: color .15s, border-color .15s;
    }
    .st-tab:hover { color: #222530; }
    .st-tab.active { color: #222530; font-weight: 600; border-color: #5c7c68; }

    .st-body { padding: 8px 28px 24px; overflow-y: auto; flex: 1; }
    .st-pane { display: none; }
    .st-pane.active { display: block; }

    .st-row {
        display: grid; grid-template-columns: minmax(220px, 1fr) minmax(280px, 1.2fr);
        gap: 20px; align-items: center;
        padding: 18px 0;
        border-bottom: 1px dashed #eaecf0;
    }
    .st-row:last-child { border-bottom: 0; }
    .st-row-label { font-size: 13px; font-weight: 600; color: #171717; line-height: 1.3; }
    .st-row-desc  { font-size: 12px; color: #717784; margin-top: 4px; }

    .st-row-right { display: flex; justify-content: flex-end; align-items: center; gap: 10px; }

    /* Inline display value with edit icon */
    .st-value {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 12px; border-radius: 8px;
        background: #f4f5f7; color: #2b303b; font-size: 13px;
        width: 100%; max-width: 320px; justify-content: space-between;
    }
    .st-value-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .st-edit-btn { background: transparent; border: none; cursor: pointer; color: #717784; padding: 2px 4px; }
    .st-edit-btn:hover { color: #222530; }

    /* Editing input */
    .st-edit-wrap {
        display: none; align-items: center; gap: 8px;
        width: 100%; max-width: 360px;
        background:#fff; border:1px solid #5c7c68;
        box-shadow: 0 0 0 3px rgba(92,124,104,.18);
        border-radius: 8px; padding: 4px 4px 4px 12px;
    }
    .st-edit-wrap.open { display: flex; }
    .st-edit-wrap input {
        flex:1; min-width:0; border:none; outline:none; background:transparent;
        font-size:13px; color:#222530; padding: 6px 0;
    }
    .st-row.editing .st-value { display: none; }

    /* Avatar block */
    .st-avatar-row { display: flex; align-items: center; gap: 14px; justify-content: flex-end; }
    .st-avatar {
        width: 56px; height: 56px; border-radius: 999px;
        background: #5c7c68; color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 18px;
        background-size: cover; background-position: center;
        position: relative; flex-shrink: 0;
    }
    .st-avatar-del {
        position: absolute; top: -4px; right: -4px;
        width: 20px; height: 20px; border-radius: 999px;
        background: #fb3748; color: #fff; border: 2px solid #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 10px; cursor: pointer;
    }
    .st-avatar-del .pi { font-size: 9px; }

    /* Toggle */
    .st-toggle {
        width: 38px; height: 22px; border-radius: 999px;
        background: #cacfd8; position: relative; cursor: pointer;
        transition: background .15s; flex-shrink: 0;
        border: none; padding: 0;
    }
    .st-toggle::after {
        content: ""; position: absolute; top: 2px; left: 2px;
        width: 18px; height: 18px; border-radius: 999px;
        background: #fff; transition: left .15s; box-shadow: 0 1px 2px rgba(0,0,0,.18);
    }
    .st-toggle.on { background: #5c7c68; }
    .st-toggle.on::after { left: 18px; }

    .st-row.compact { padding: 14px 0; align-items: center; }
    .st-row.compact .st-row-right { justify-content: flex-end; }

    .st-alert {
        margin: 0 28px;
        padding: 10px 14px; border-radius: 10px;
        font-size: 12px; font-weight: 500;
        display: flex; align-items: center; gap: 8px;
    }
    .st-alert-ok  { background:#e3f7ec; color:#1daf61; border:1px solid rgba(31,193,107,.25); }
    .st-alert-err { background:#ffebec; color:#e93544; border:1px solid rgba(251,55,72,.25); }

    /* Hidden sections (Password change inline) */
    .st-collapse { display: none; padding: 16px 0 4px; }
    .st-collapse.open { display: block; }

    /* ===== 2FA ===== */
    .st-2fa-badge {
        display:inline-block; margin-left:6px; padding:1px 8px; border-radius:999px;
        font-size:10px; font-weight:700; letter-spacing:.03em; text-transform:uppercase;
        background:#f2f5f8; color:#99a0ae; vertical-align:middle;
    }
    .st-2fa-badge.on { background:#e3f7ec; color:#1daf61; }
    .st-2fa-grid { display:flex; gap:18px; flex-wrap:wrap; align-items:flex-start; }
    .st-2fa-qr {
        width:168px; height:168px; flex-shrink:0; border:1px solid #eaecf0; border-radius:12px;
        background:#fff; display:flex; align-items:center; justify-content:center; padding:8px;
    }
    .st-2fa-qr img, .st-2fa-qr canvas { width:100%; height:100%; }
    .st-2fa-steps { flex:1; min-width:240px; display:flex; flex-direction:column; gap:8px; }
    .st-2fa-help { font-size:12.5px; color:#525866; line-height:1.5; margin:0; }
    .st-2fa-secret {
        display:inline-block; font-family:ui-monospace,Menlo,monospace; font-size:13px; letter-spacing:.12em;
        background:#f5f7fa; border:1px solid #eaecf0; border-radius:8px; padding:6px 10px; color:#171717; word-break:break-all;
    }
    .st-2fa-codes {
        display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px;
    }
    .st-2fa-codes span {
        font-family:ui-monospace,Menlo,monospace; font-size:14px; letter-spacing:.08em; text-align:center;
        background:#f5f7fa; border:1px solid #eaecf0; border-radius:8px; padding:8px 6px; color:#171717;
    }
    .st-2fa-codes span.used { text-decoration:line-through; opacity:.45; }

    @media (max-width: 860px) {
        .st-shell { grid-template-columns: 1fr; height: calc(100vh - 24px); max-height: calc(100vh - 24px); }
        .st-sidebar { display: flex; flex-direction: row; gap: 4px; overflow-x: auto; padding: 12px; }
        .st-sidebar .st-section-label { display: none; }
        .st-nav-item .chev { display: none; }
        .st-row { grid-template-columns: 1fr; }
        .st-row-right { justify-content: flex-start; }
    }
</style>

<div id="settingsModal" class="st-overlay" role="dialog" aria-modal="true" aria-label="{{ __('Configuración de la cuenta') }}">
    <div class="st-shell" id="settingsShell">

        {{-- Sidebar --}}
        <aside class="st-sidebar">
            <div class="st-section-label">{{ __('Configuración Personal') }}</div>
            <button type="button" class="st-nav-item active" data-st-pane="profile">
                <i class="pi pi-user"></i> Información Personal
                <i class="pi pi-angle-right chev"></i>
            </button>
            <button type="button" class="st-nav-item" data-st-pane="security">
                <i class="pi pi-shield"></i> Privacidad y Seguridad
                <i class="pi pi-angle-right chev"></i>
            </button>
            <button type="button" class="st-nav-item" data-st-pane="notifications">
                <i class="pi pi-bell"></i> Notificaciones
                <i class="pi pi-angle-right chev"></i>
            </button>

            @if($stIsAdmin)
            <div class="st-section-label" style="margin-top:14px;">{{ __('Configuración del proyecto') }}</div>
            <button type="button" class="st-nav-item" data-st-pane="signature">
                <i class="pi pi-pencil"></i> {{ __('Firma del proyecto') }}
                <i class="pi pi-angle-right chev"></i>
            </button>
            <button type="button" class="st-nav-item" data-st-pane="menu">
                <i class="pi pi-bars"></i> {{ __('Menú del cliente') }}
                <i class="pi pi-angle-right chev"></i>
            </button>
            @endif

            {{-- Cerrar sesión — anclado al fondo del sidebar --}}
            <button type="button" class="st-nav-item st-logout" onclick="(window.openLogoutModal ? openLogoutModal() : document.getElementById('stLogoutFallback')?.submit())">
                <i class="pi pi-sign-out"></i> Cerrar sesión
            </button>
            <form id="stLogoutFallback" method="POST" action="{{ route($stLogoutRoute) }}" class="hidden">@csrf</form>
        </aside>

        {{-- Main --}}
        <div class="st-main">

            {{-- Header --}}
            <div class="st-head">
                <div>
                    <div class="st-head-title" data-st-title>{{ __('Configuración de la cuenta') }}</div>
                    <div class="st-head-sub"   data-st-sub>{{ __('Administra y colabora en la configuración de tu cuenta') }}</div>
                </div>
                <div class="st-head-actions">
                    <button type="button" class="st-btn st-btn-ghost" onclick="closeSettingsModal()">{{ __('Descartar') }}</button>
                    <button type="button" class="st-btn st-btn-primary" id="stSaveBtn" onclick="submitSettingsProfile()">
                        <span id="stSaveLabel">{{ __('Guardar cambios') }}</span>
                    </button>
                </div>
            </div>

            {{-- Inline alerts --}}
            <div id="stAlert" class="st-alert" style="display:none; margin-top:14px;"></div>

            {{-- PROFILE TABS --}}
            <div class="st-tabs" data-st-tabs="profile">
                <button type="button" class="st-tab active" data-st-tab="profile-perfil">{{ __('Perfil') }}</button>
                <button type="button" class="st-tab" data-st-tab="profile-region">{{ __('Idioma y región') }}</button>
            </div>
            {{-- SECURITY TABS --}}
            <div class="st-tabs" data-st-tabs="security" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="security-pwd">{{ __('Contraseña y 2FA') }}</button>
                <button type="button" class="st-tab" data-st-tab="security-session">{{ __('Sesión Activa') }}</button>
            </div>
            {{-- NOTIFICATIONS TABS --}}
            <div class="st-tabs" data-st-tabs="notifications" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="notifications-main">{{ __('Notificaciones') }}</button>
            </div>
            @if($stIsAdmin)
            {{-- SIGNATURE TABS --}}
            <div class="st-tabs" data-st-tabs="signature" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="signature-main">{{ __('Firma del proyecto') }}</button>
            </div>
            {{-- CLIENT MENU TABS --}}
            <div class="st-tabs" data-st-tabs="menu" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="menu-main">{{ __('Menú del cliente') }}</button>
            </div>
            @endif

            <div class="st-body">

                <form id="stProfileForm" method="POST" action="{{ route($stProfileRoute) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="redirect_settings" value="1">

                    {{-- =========== PROFILE PANE — PERFIL =========== --}}
                    <div class="st-pane active" data-st-pane="profile-perfil">

                        {{-- Foto --}}
                        <div class="st-row">
                            <div>
                                <div class="st-row-label">{{ __('Foto de perfil') }}</div>
                                <div class="st-row-desc">{{ __('Mínimo 400x400px, formatos PNG o JPEG.') }}</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-avatar-row">
                                    <div class="st-avatar" id="stAvatar" @if($sAvatar) style="background-image:url('{{ $sAvatar }}'); color:transparent;" @endif>
                                        @if(!$sAvatar){{ $sInit }}@endif
                                        @if($sAvatar)
                                            <button type="button" class="st-avatar-del" onclick="stRemoveAvatar()" title="{{ __('Eliminar foto') }}"><i class="pi pi-times"></i></button>
                                        @endif
                                    </div>
                                    <label class="st-btn st-btn-ghost" style="cursor:pointer;">
                                        Cambiar
                                        <input type="file" name="avatar" id="stAvatarInput" accept="image/png,image/jpeg,image/webp" class="hidden" style="display:none;">
                                    </label>
                                    <input type="hidden" name="remove_avatar" id="stAvatarRemove" value="0">
                                </div>
                            </div>
                        </div>

                        {{-- Nombre completo --}}
                        <div class="st-row" data-st-field>
                            <div>
                                <div class="st-row-label">{{ __('Nombre completo') }}</div>
                                <div class="st-row-desc">{{ __('Tu nombre será visible para tus contactos.') }}</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sName ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="{{ __('Editar') }}"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap">
                                    <input type="text" name="name" value="{{ $sName }}" placeholder="{{ __('Tu nombre completo') }}" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">{{ __('Cancelar') }}</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">{{ __('Guardar') }}</button>
                                </div>
                            </div>
                        </div>

                        {{-- Correo --}}
                        <div class="st-row" data-st-field>
                            <div>
                                <div class="st-row-label">{{ __('Correo electrónico') }}</div>
                                <div class="st-row-desc">{{ __('Se recomienda correo electrónico empresarial.') }}</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sEmail ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="{{ __('Editar') }}"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap">
                                    <input type="email" name="email" value="{{ $sEmail }}" placeholder="tu@correo.com" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">{{ __('Cancelar') }}</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">{{ __('Guardar') }}</button>
                                </div>
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div class="st-row editing" data-st-field>
                            <div>
                                <div class="st-row-label">{{ __('Número de teléfono') }}</div>
                                <div class="st-row-desc">{{ __('Se recomienda número de teléfono empresarial.') }}</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sPhone ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="{{ __('Editar') }}"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap open">
                                    <input type="tel" name="phone" value="{{ $sPhone ?: '+1 (123) 456-6789' }}" placeholder="+57 300 000 0000" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">{{ __('Cancelar') }}</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">{{ __('Guardar') }}</button>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden split-name fields kept in sync --}}
                        <input type="hidden" name="first_name" id="stFirstName" value="{{ $sFirst }}">
                        <input type="hidden" name="last_name"  id="stLastName"  value="{{ $sLast }}">
                    </div>

                    {{-- =========== PROFILE PANE — IDIOMA Y REGIÓN =========== --}}
                    <div class="st-pane" data-st-pane="profile-region">
                        <div class="st-row compact">
                            <div>
                                <div class="st-row-label">{{ __('Idioma') }}</div>
                                <div class="st-row-desc">{{ __('Idioma para la interfaz del CRM.') }}</div>
                            </div>
                            <div class="st-row-right">
                                <select name="locale" class="st-value" style="border:none; outline:none;">
                                    <option value="es" @selected(app()->getLocale() === 'es')>{{ __('Español') }}</option>
                                    <option value="en" @selected(app()->getLocale() === 'en')>{{ __('English') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="st-row compact">
                            <div>
                                <div class="st-row-label">{{ __('Zona horaria') }}</div>
                                <div class="st-row-desc">{{ __('Las fechas y horas se muestran en esta zona.') }}</div>
                            </div>
                            <div class="st-row-right">
                                @php
                                    $stTz = session('timezone', request()->cookie('app_timezone', 'America/Santo_Domingo'));
                                    // Husos horarios disponibles. El offset GMT se calcula en vivo
                                    // (respeta horario de verano) y se muestra como "(GMT-04:00) Ciudad".
                                    $stTzList = [
                                        'America/Santo_Domingo'           => 'Santo Domingo',
                                        'America/New_York'                => 'Nueva York',
                                        'America/Bogota'                  => 'Bogotá / Lima',
                                        'America/Mexico_City'             => 'Ciudad de México',
                                        'America/Chicago'                 => 'Chicago',
                                        'America/Los_Angeles'             => 'Los Ángeles',
                                        'America/Caracas'                 => 'Caracas',
                                        'America/Santiago'                => 'Santiago',
                                        'America/Argentina/Buenos_Aires'  => 'Buenos Aires',
                                        'America/Sao_Paulo'               => 'São Paulo',
                                        'Europe/Madrid'                   => 'Madrid',
                                        'Europe/London'                   => 'Londres',
                                        'UTC'                             => 'UTC',
                                    ];
                                    // Si la zona guardada no está en la lista, la agregamos para no perderla.
                                    if ($stTz && !array_key_exists($stTz, $stTzList)) {
                                        $stTzList = [$stTz => \Illuminate\Support\Str::of($stTz)->afterLast('/')->replace('_', ' ')] + $stTzList;
                                    }
                                @endphp
                                <select name="timezone" class="st-value" style="border:none; outline:none;">
                                    @foreach($stTzList as $tzId => $tzCity)
                                        @php
                                            try {
                                                $tzOffset = (new \DateTime('now', new \DateTimeZone($tzId)))->format('P');
                                            } catch (\Throwable $e) { $tzOffset = '+00:00'; }
                                        @endphp
                                        <option value="{{ $tzId }}" @selected($stTz === $tzId)>(GMT{{ $tzOffset }}) {{ $tzCity }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden inline password fields used by Privacidad → Cambiar contraseña --}}
                    <input type="hidden" name="password" id="stPwdInput">
                    <input type="hidden" name="password_confirmation" id="stPwdConfirmInput">
                    <input type="hidden" name="current_password" id="stCurrentPwdInput">
                </form>

                {{-- =========== SECURITY PANE — CONTRASEÑA Y 2FA =========== --}}
                <div class="st-pane" data-st-pane="security-pwd">
                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">{{ __('Cambiar Contraseña') }}</div>
                            <div class="st-row-desc">{{ __('Actualiza la contraseña para mejorar la seguridad de tu cuenta.') }}</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stTogglePwdPanel()">{{ __('Cambiar Contraseña') }}</button>
                        </div>
                    </div>

                    <div class="st-collapse" id="stPwdPanel">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">{{ __('Contraseña actual') }}</label>
                                <input type="password" id="stPwdCurrent" class="crm-input pl-3 mt-1" autocomplete="current-password" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">{{ __('Nueva contraseña') }}</label>
                                <input type="password" id="stPwdNew" class="crm-input pl-3 mt-1" autocomplete="new-password" placeholder="{{ __('Mín. 8 caracteres') }}">
                            </div>
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">{{ __('Confirmar') }}</label>
                                <input type="password" id="stPwdConfirm" class="crm-input pl-3 mt-1" autocomplete="new-password" placeholder="{{ __('Repite la contraseña') }}">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 justify-end">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stTogglePwdPanel(false)">{{ __('Cancelar') }}</button>
                            <button type="button" class="st-btn st-btn-primary" onclick="submitSettingsPassword()">{{ __('Confirmar cambio') }}</button>
                        </div>
                    </div>

                    @php $st2faOn = $authUser?->hasTwoFactorEnabled() ?? false; @endphp

                    {{-- =========== AUTENTICACIÓN 2FA =========== --}}
                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">
                                Autenticación 2FA
                                <span id="st2faBadge" class="st-2fa-badge {{ $st2faOn ? 'on' : '' }}">{{ $st2faOn ? 'Activa' : 'Inactiva' }}</span>
                            </div>
                            <div class="st-row-desc">{{ __('Agrega una capa extra de protección a tu cuenta.') }}</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" id="st2faManageBtn" onclick="st2faTogglePanel()">{{ __('Administrar Autenticación') }}</button>
                        </div>
                    </div>

                    <div class="st-collapse" id="st2faPanel">
                        {{-- (A) Activar: QR + secreto + confirmación --}}
                        <div id="st2faSetup" style="display:none;">
                            <div class="st-2fa-grid">
                                <div class="st-2fa-qr" id="st2faQr"></div>
                                <div class="st-2fa-steps">
                                    <p class="st-2fa-help">{{ __('1. Escaneá el código QR con Google Authenticator, Authy, 1Password o similar.') }}</p>
                                    <p class="st-2fa-help">{{ __('2. ¿No podés escanear? Cargá esta clave manualmente:') }}</p>
                                    <code class="st-2fa-secret" id="st2faSecret">—</code>
                                    <p class="st-2fa-help">{{ __('3. Ingresá el código de 6 dígitos que muestra la app:') }}</p>
                                    <div class="flex items-center gap-2">
                                        <input type="text" id="st2faConfirmCode" inputmode="numeric" maxlength="6"
                                               class="crm-input pl-3" style="max-width:160px; letter-spacing:.25em; text-align:center;" placeholder="000000">
                                        <button type="button" class="st-btn st-btn-primary" onclick="st2faConfirm()">{{ __('Confirmar y activar') }}</button>
                                        <button type="button" class="st-btn st-btn-ghost" onclick="st2faTogglePanel(false)">{{ __('Cancelar') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- (B) Ya activa: desactivar --}}
                        <div id="st2faActive" style="display:none;">
                            <p class="st-2fa-help" style="margin-bottom:10px;">
                                <i class="pi pi-check-circle" style="color:#1daf61;"></i>
                                La 2FA está activa. Cada vez que inicies sesión te pediremos un código.
                            </p>
                            <div class="flex items-center gap-2">
                                <input type="password" id="st2faDisablePwd" class="crm-input pl-3" style="max-width:220px;" placeholder="{{ __('Tu contraseña actual') }}">
                                <button type="button" class="st-btn st-btn-ghost" style="color:#e93544;" onclick="st2faDisable()">{{ __('Desactivar 2FA') }}</button>
                            </div>
                        </div>
                    </div>

                    {{-- =========== CÓDIGOS DE RESPALDO =========== --}}
                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">{{ __('Códigos de Respaldo') }}</div>
                            <div class="st-row-desc">{{ __('Genera códigos de respaldo para tu dispositivo 2FA.') }}</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" onclick="st2faShowRecoveryCodes()">{{ __('Generar Códigos') }}</button>
                        </div>
                    </div>

                    <div class="st-collapse" id="st2faCodesPanel">
                        <p class="st-2fa-help" style="margin-bottom:8px;">
                            Guardá estos códigos en un lugar seguro. Cada uno sirve una sola vez para entrar si perdés tu dispositivo.
                        </p>
                        <div class="st-2fa-codes" id="st2faCodesList"></div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="button" class="st-btn st-btn-ghost" onclick="st2faCopyCodes()"><i class="pi pi-copy"></i> {{ __('Copiar') }}</button>
                            <button type="button" class="st-btn st-btn-ghost" onclick="st2faRegenerate()"><i class="pi pi-refresh"></i> {{ __('Regenerar') }}</button>
                        </div>
                    </div>
                </div>

                <div class="st-pane" data-st-pane="security-session">
                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">{{ __('Sesión actual') }}</div>
                            <div class="st-row-desc">Última actividad: {{ now()->isoFormat('D MMMM YYYY · HH:mm') }}</div>
                        </div>
                        <div class="st-row-right">
                            <form method="POST" action="{{ route($stLogoutRoute) }}" class="m-0" data-logout-confirm>@csrf
                                <button type="submit" class="st-btn st-btn-ghost" style="color:#e93544;">{{ __('Cerrar sesión') }}</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- =========== NOTIFICATIONS PANE =========== --}}
                <div class="st-pane" data-st-pane="notifications-main">
                    @php
                        $notiPrefs = [
                            ['doc_pending',  'Documentos pendientes',     'Cuando hay un documento que requiere tu firma', true],
                            ['new_offer',    'Nuevas propuestas',         'Cuando tu asesor envía una oferta o presupuesto', true],
                            ['video_remind', 'Recordatorio de videollamada', '24 h antes de tu cita programada', false],
                            ['proj_updates', 'Actualizaciones del proyecto', 'Avances de construcción, fechas, noticias', true],
                            ['promos',       'Promociones',               'Unidades nuevas, descuentos especiales', true],
                        ];
                    @endphp
                    @foreach($notiPrefs as $pref)
                        <div class="st-row compact">
                            <div>
                                <div class="st-row-label">{{ $pref[1] }}</div>
                                <div class="st-row-desc">{{ $pref[2] }}</div>
                            </div>
                            <div class="st-row-right">
                                <button type="button"
                                        class="st-toggle"
                                        data-st-noti="{{ $pref[0] }}"
                                        data-st-default="{{ $pref[3] ? '1' : '0' }}"
                                        onclick="stToggleNoti(this)"
                                        aria-pressed="{{ $pref[3] ? 'true' : 'false' }}"></button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($stIsAdmin)
                {{-- =========== SIGNATURE PANE — FIRMA DEL PROYECTO =========== --}}
                <div class="st-pane" data-st-pane="signature-main">
                    <div class="st-row" style="display:block;">
                        <div style="margin-bottom:14px;">
                            <div class="st-row-label">{{ __('Firma del proyecto') }}</div>
                            <div class="st-row-desc">
                                {{ __('Esta firma se estampa automáticamente en el recuadro del Desarrollador/Vendedora de los contratos (promesa de compraventa y plan de pagos), para que salgan firmados a nombre de Makai.') }}
                            </div>
                        </div>

                        {{-- Firma manuscrita actual --}}
                        <div style="margin-bottom:16px;">
                            <div class="st-row-label" style="font-size:12px; margin-bottom:6px;">{{ __('Firma manuscrita') }}</div>
                            <div id="psCurrentWrap" style="{{ $stSigImage ? '' : 'display:none;' }} margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="border:1px solid #eaecf0; border-radius:10px; background:#fff; padding:8px; width:220px; height:96px; display:flex; align-items:center; justify-content:center;">
                                        <img id="psCurrentImg" src="{{ $stSigImage }}" alt="{{ __('Firma actual') }}" style="max-height:100%; max-width:100%; object-fit:contain;">
                                    </div>
                                    <button type="button" class="st-btn-link" style="color:#e93544;" onclick="psRemoveSignature()">{{ __('Eliminar firma') }}</button>
                                </div>
                            </div>

                            <div class="ps-canvas-wrap" id="ps-canvas-wrap" style="position:relative; width:100%; max-width:420px;">
                                <canvas id="ps-sig-canvas" style="width:100%; height:150px; border:1.5px dashed #cdd2da; border-radius:10px; background:#fff; touch-action:none; cursor:crosshair;"></canvas>
                                <div class="ps-empty-canvas" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#99a0ae; font-size:12px; pointer-events:none;">{{ __('Dibujá la firma con el mouse o el dedo') }}</div>
                            </div>
                            <div style="margin-top:8px;">
                                <button type="button" class="st-btn-link" onclick="psClearSig()"><i class="pi pi-eraser"></i> {{ __('Limpiar') }}</button>
                            </div>
                        </div>

                        {{-- Nombre del firmante --}}
                        <div style="margin-bottom:14px;">
                            <div class="st-row-label" style="font-size:12px; margin-bottom:6px;">{{ __('Nombre del firmante') }}</div>
                            <input type="text" id="psSignerName" value="{{ $stSigName }}" placeholder="Ej: JOSE ANTONIO GONZALEZ DIAZ"
                                   style="width:100%; max-width:420px; border:1px solid #eaecf0; border-radius:9px; padding:9px 12px; font-size:13px; color:#222530;">
                        </div>

                        {{-- Entidad / cargo --}}
                        <div style="margin-bottom:6px;">
                            <div class="st-row-label" style="font-size:12px; margin-bottom:6px;">{{ __('Entidad / representación') }}</div>
                            <input type="text" id="psSignerEntity" value="{{ $stSigEntity }}" placeholder="Ej: En Rep. De IGUANAS LAKE CONDO & RESIDENCES, S.R.L."
                                   style="width:100%; max-width:420px; border:1px solid #eaecf0; border-radius:9px; padding:9px 12px; font-size:13px; color:#222530;">
                            <div class="st-row-desc" style="margin-top:6px;">{{ __('Si dejás estos campos vacíos, el contrato usa los datos por defecto del documento.') }}</div>
                        </div>
                    </div>
                </div>

                {{-- =========== CLIENT MENU PANE — MENÚ DEL CLIENTE =========== --}}
                <div class="st-pane" data-st-pane="menu-main">

                    {{-- Ítems fijos: Sitio web (URL configurable) y FAQs --}}
                    <div class="st-row" style="display:block; margin-bottom:18px; padding-bottom:18px; border-bottom:1px solid #eaecf0;">
                       
                        <div>
                            <label class="st-row-label" style="font-size:12px; display:block; margin-bottom:6px;">{{ __('URL del sitio web') }}</label>
                            <input type="url" id="cmSiteUrl" value="{{ \App\Models\Setting::get('site_url', '') }}" placeholder="https://makairesidences.com"
                                   style="width:100%; max-width:480px; border:1px solid #eaecf0; border-radius:9px; padding:9px 12px; font-size:13px; color:#222530;">
                        </div>
                    </div>

                    <div class="st-row" style="display:block;">
                        <div style="margin-bottom:14px;">
                            <div class="st-row-label">{{ __('Ítems del menú del cliente') }}</div>
                            <div class="st-row-desc">
                                {{ __('Estos ítems aparecen en el menú desplegable del cliente (Brochure, Plantas, ROIs, etc.). Vos decidís cuáles mostrar: cada ítem puede ser un enlace externo o un documento que se abre en una ventana.') }}
                            </div>
                        </div>

                        <div id="cmList" style="display:flex; flex-direction:column; gap:12px;"></div>

                        <button type="button" class="st-btn st-btn-ghost" style="margin-top:14px;" onclick="cmAddItem()">
                            <i class="pi pi-plus"></i> {{ __('Agregar ítem') }}
                        </button>

                        <div id="cmEmpty" class="st-row-desc" style="margin-top:10px; display:none;">{{ __('Todavía no hay ítems. Agregá el primero para que aparezca en el menú del cliente.') }}</div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@if($stIsAdmin)
<style>
    /* ===== Selector de íconos (menú del cliente) ===== */
    .cm-icon-picker { position: relative; }
    .cm-icon-trigger {
        width: 100%; height: 36px;
        display: flex; align-items: center; justify-content: space-between; gap: 6px;
        border: 1px solid #eaecf0; border-radius: 9px; background: #fff;
        padding: 0 10px; cursor: pointer; color: #5c5c5c;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .cm-icon-trigger:hover { border-color: #d6dae1; }
    .cm-icon-picker.open .cm-icon-trigger { border-color: #c7ccd4; box-shadow: 0 0 0 3px rgba(199,204,212,.25); }
    .cm-icon-current { display: inline-flex; align-items: center; justify-content: center; color: #475160; }
    .cm-icon-caret { color: #99a0ae; flex-shrink: 0; }

    .cm-icon-menu {
        position: absolute; z-index: 30; top: calc(100% + 6px); left: 0;
        display: none; grid-template-columns: repeat(3, 1fr); gap: 4px;
        padding: 6px; width: 148px;
        background: #fff; border: 1px solid #eaecf0; border-radius: 12px;
        box-shadow: 0 16px 40px -12px rgba(10,13,20,.28);
    }
    .cm-icon-picker.open .cm-icon-menu { display: grid; }
    .cm-icon-opt {
        display: flex; align-items: center; justify-content: center;
        width: 42px; height: 38px; border: 1px solid transparent; border-radius: 9px;
        background: #fff; cursor: pointer; color: #5c5c5c;
        transition: background .12s ease, color .12s ease, border-color .12s ease;
    }
    .cm-icon-opt:hover { background: #f4f5f7; color: #222530; }
    .cm-icon-opt.active { background: #eef2ef; border-color: #d8e3da; color: #222530; }
</style>

{{-- Plantilla de fila para el editor del menú del cliente --}}
<template id="cmRowTemplate">
    <div class="cm-row" style="border:1px solid #eaecf0; border-radius:12px; padding:14px; background:#fff;">
        <div style="display:flex; gap:10px; align-items:flex-start;">
            <div style="flex:1; display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:160px;">
                        <label class="st-row-label" style="font-size:11px; display:block; margin-bottom:4px;">{{ __('Nombre') }}</label>
                        <input type="text" class="cm-label" placeholder="Ej: Brochure" style="width:100%; border:1px solid #eaecf0; border-radius:9px; padding:8px 11px; font-size:13px;">
                    </div>
                    <div style="width:140px;">
                        <label class="st-row-label" style="font-size:11px; display:block; margin-bottom:4px;">{{ __('Tipo') }}</label>
                        <select class="cm-type" onchange="cmSyncType(this)" style="width:100%; border:1px solid #eaecf0; border-radius:9px; padding:8px 11px; font-size:13px; background:#fff;">
                            <option value="link">{{ __('Enlace') }}</option>
                            <option value="document">{{ __('Documento') }}</option>
                        </select>
                    </div>
                    <div style="width:88px;">
                        <label class="st-row-label" style="font-size:11px; display:block; margin-bottom:4px;">{{ __('Ícono') }}</label>
                        <div class="cm-icon-picker" data-icon="file">
                            <button type="button" class="cm-icon-trigger" onclick="cmToggleIconMenu(this)">
                                <span class="cm-icon-current"></span>
                                <svg class="cm-icon-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <div class="cm-icon-menu"></div>
                        </div>
                    </div>
                </div>

                {{-- Enlace --}}
                <div class="cm-link-wrap">
                    <label class="st-row-label" style="font-size:11px; display:block; margin-bottom:4px;">{{ __('URL') }}</label>
                    <input type="url" class="cm-url" placeholder="https://..." style="width:100%; border:1px solid #eaecf0; border-radius:9px; padding:8px 11px; font-size:13px;">
                </div>

                {{-- Documento --}}
                <div class="cm-doc-wrap" style="display:none;">
                    <label class="st-row-label" style="font-size:11px; display:block; margin-bottom:4px;">{{ __('Archivo') }} <span style="color:#99a0ae; font-weight:400;">(PDF, imagen, Office · máx 50&nbsp;MB)</span></label>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <label class="st-btn st-btn-ghost" style="cursor:pointer;">
                            <i class="pi pi-upload"></i> {{ __('Subir archivo') }}
                            <input type="file" class="cm-file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx" style="display:none;" onchange="cmFilePicked(this)">
                        </label>
                        <span class="cm-file-name" style="font-size:12px; color:#5c5c5c;"></span>
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:4px;">
                <button type="button" class="cm-mv" title="{{ __('Subir') }}" onclick="cmMove(this,-1)" style="background:#f4f5f7; border:none; border-radius:7px; width:30px; height:28px; cursor:pointer; color:#5c5c5c;"><i class="pi pi-chevron-up"></i></button>
                <button type="button" class="cm-mv" title="{{ __('Bajar') }}" onclick="cmMove(this,1)" style="background:#f4f5f7; border:none; border-radius:7px; width:30px; height:28px; cursor:pointer; color:#5c5c5c;"><i class="pi pi-chevron-down"></i></button>
                <button type="button" title="{{ __('Eliminar') }}" onclick="cmRemove(this)" style="background:#fff0f1; border:none; border-radius:7px; width:30px; height:28px; cursor:pointer; color:#e93544;"><i class="pi pi-trash"></i></button>
            </div>
        </div>
    </div>
</template>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
    let activePane = 'profile';

    window.openSettingsModal = function() {
        document.getElementById('settingsModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        stRestoreNoti();
    };
    window.closeSettingsModal = function() {
        document.getElementById('settingsModal').classList.remove('open');
        document.body.style.overflow = '';
        stHideAlert();
    };

    // Close on backdrop click + Escape
    document.getElementById('settingsModal').addEventListener('click', function(e){
        if (e.target === this) closeSettingsModal();
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && document.getElementById('settingsModal').classList.contains('open')) {
            closeSettingsModal();
        }
    });

    // Sidebar nav
    document.querySelectorAll('.st-nav-item[data-st-pane]').forEach(btn => {
        btn.addEventListener('click', () => {
            activePane = btn.dataset.stPane;
            document.querySelectorAll('.st-nav-item').forEach(n => n.classList.remove('active'));
            btn.classList.add('active');

            // Toggle tabs visibility
            document.querySelectorAll('[data-st-tabs]').forEach(t => {
                t.style.display = t.dataset.stTabs === activePane ? 'flex' : 'none';
            });

            // Show first pane of section
            const firstTab = document.querySelector(`[data-st-tabs="${activePane}"] .st-tab`);
            if (firstTab) stActivateTab(firstTab);

            // Title + Save button visibility
            const titles = {
                profile:       ['Configuración de la cuenta', 'Administra y colabora en la configuración de tu cuenta', 'Guardar cambios', true],
                security:      ['Privacidad y Seguridad',    'Personaliza tus configuraciones de privacidad y seguridad', 'Guardar Cambios', false],
                notifications: ['Configuración de la cuenta', 'Elige qué notificaciones quieres recibir', 'Guardar cambios', false],
                signature:     ['Firma del proyecto',        'Esta firma se usa para firmar los contratos a nombre de Makai', 'Guardar firma', true],
                menu:          ['Menú del cliente',          'Configurá los ítems (enlaces y documentos) que ve el cliente en su menú', 'Guardar menú', true],
            };
            const t = titles[activePane];
            document.querySelector('[data-st-title]').textContent = t[0];
            document.querySelector('[data-st-sub]').textContent   = t[1];
            document.getElementById('stSaveLabel').textContent    = t[2];
            document.getElementById('stSaveBtn').style.display    = t[3] || activePane === 'notifications' ? '' : 'none';

            if (activePane === 'signature' && typeof window.psInitCanvas === 'function') {
                // Esperar a que el pane sea visible para medir el canvas correctamente.
                setTimeout(() => window.psInitCanvas(), 30);
            }
            if (activePane === 'menu' && typeof window.cmRender === 'function') {
                window.cmRender();
            }
        });
    });

    // Tabs
    document.querySelectorAll('.st-tab').forEach(tab => tab.addEventListener('click', () => stActivateTab(tab)));
    function stActivateTab(tab){
        const tabsRow = tab.closest('.st-tabs');
        tabsRow.querySelectorAll('.st-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const target = tab.dataset.stTab;
        document.querySelectorAll('.st-pane').forEach(p => p.classList.remove('active'));
        document.querySelector(`.st-pane[data-st-pane="${target}"]`)?.classList.add('active');
    }

    // Inline edit toggles
    window.stToggleEdit = function(btn){
        const row = btn.closest('[data-st-field]');
        row.classList.add('editing');
        row.querySelector('.st-edit-wrap').classList.add('open');
        row.querySelector('[data-st-input]').focus();
    };
    window.stCancelEdit = function(btn){
        const row = btn.closest('[data-st-field]');
        row.classList.remove('editing');
        row.querySelector('.st-edit-wrap').classList.remove('open');
    };
    window.stConfirmEdit = function(btn){
        const row = btn.closest('[data-st-field]');
        const input = row.querySelector('[data-st-input]');
        const display = row.querySelector('[data-st-display]');
        if (display) display.textContent = input.value || '—';
        row.classList.remove('editing');
        row.querySelector('.st-edit-wrap').classList.remove('open');
        stShowAlert('Cambio listo. Recordá tocar "Guardar cambios" para aplicar.', 'ok', 2200);
    };

    // Avatar
    document.getElementById('stAvatarInput').addEventListener('change', function(e){
        const file = e.target.files?.[0]; if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            const avatar = document.getElementById('stAvatar');
            avatar.style.backgroundImage = `url('${ev.target.result}')`;
            avatar.style.backgroundSize = 'cover';
            avatar.style.backgroundPosition = 'center';
            avatar.style.color = 'transparent';
            avatar.textContent = '';
            document.getElementById('stAvatarRemove').value = '0';
        };
        reader.readAsDataURL(file);
    });
    window.stRemoveAvatar = function(){
        if (!confirm('{{ __("¿Eliminar la foto de perfil?") }}')) return;
        const avatar = document.getElementById('stAvatar');
        avatar.style.backgroundImage = '';
        avatar.style.color = '#fff';
        avatar.textContent = '{{ $sInit }}';
        document.getElementById('stAvatarRemove').value = '1';
        document.getElementById('stAvatarInput').value = '';
    };

    // Password panel
    window.stTogglePwdPanel = function(force){
        const panel = document.getElementById('stPwdPanel');
        if (typeof force === 'boolean') panel.classList.toggle('open', force);
        else panel.classList.toggle('open');
    };

    /* ======================= 2FA ======================= */
    let st2faEnabled = {{ ($authUser?->hasTwoFactorEnabled() ?? false) ? 'true' : 'false' }};
    let st2faCurrentCodes = [];

    const st2faCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function st2faPost(url, body){
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': st2faCsrf(), 'Accept':'application/json' },
            body: JSON.stringify(body || {}),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Ocurrió un error. Intentá de nuevo.');
        return data;
    }
    async function st2faGet(url){
        const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': st2faCsrf(), 'Accept':'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Ocurrió un error.');
        return data;
    }

    function st2faSyncBadge(){
        const badge = document.getElementById('st2faBadge');
        if (badge){ badge.classList.toggle('on', st2faEnabled); badge.textContent = st2faEnabled ? 'Activa' : 'Inactiva'; }
    }

    // Mostrar/ocultar el panel de gestión 2FA (activar o desactivar según estado)
    window.st2faTogglePanel = async function(force){
        const panel = document.getElementById('st2faPanel');
        const willOpen = typeof force === 'boolean' ? force : !panel.classList.contains('open');
        panel.classList.toggle('open', willOpen);
        if (!willOpen) return;

        document.getElementById('st2faSetup').style.display  = st2faEnabled ? 'none' : 'block';
        document.getElementById('st2faActive').style.display = st2faEnabled ? 'block' : 'none';
        if (st2faEnabled) return;

        // Generar secreto + QR
        try {
            const data = await st2faPost('{{ route('2fa.enable') }}');
            document.getElementById('st2faSecret').textContent = data.secret;
            const qrBox = document.getElementById('st2faQr');
            qrBox.innerHTML = '';
            if (window.QRCode) {
                new QRCode(qrBox, { text: data.otpauth_uri, width: 152, height: 152, correctLevel: QRCode.CorrectLevel.M });
            } else {
                qrBox.innerHTML = '<span style="font-size:11px;color:#99a0ae;text-align:center;">No se pudo cargar el QR. Usá la clave manual.</span>';
            }
        } catch (e) { stShowAlert(e.message, 'err'); }
    };

    window.st2faConfirm = async function(){
        const code = document.getElementById('st2faConfirmCode').value.trim();
        if (code.length < 6) { stShowAlert('Ingresá el código de 6 dígitos.', 'err'); return; }
        try {
            const data = await st2faPost('{{ route('2fa.confirm') }}', { code });
            st2faEnabled = true;
            st2faSyncBadge();
            document.getElementById('st2faPanel').classList.remove('open');
            stShowAlert('¡2FA activada! Guardá tus códigos de respaldo.', 'ok', 3000);
            st2faRenderCodes(data.recovery_codes);
        } catch (e) { stShowAlert(e.message, 'err'); }
    };

    // Confirmación con la línea gráfica de la web (partials/confirm-dialog).
    // Fallback al confirm() nativo solo si el diálogo no estuviera disponible.
    function st2faConfirm2(opts){
        if (typeof window.confirmDialog === 'function') { window.confirmDialog(opts); return; }
        if (confirm(opts.text || opts.title || '¿Continuar?')) (opts.onConfirm || function(){})();
    }

    window.st2faDisable = function(){
        const password = document.getElementById('st2faDisablePwd').value;
        if (!password) { stShowAlert('Ingresá tu contraseña para desactivar la 2FA.', 'err'); return; }
        st2faConfirm2({
            title: '{{ __("Desactivar autenticación 2FA") }}',
            text: '{{ __("Tu cuenta quedará protegida únicamente por la contraseña. Podés volver a activarla cuando quieras.") }}',
            confirmLabel: 'Desactivar',
            icon: 'pi pi-shield',
            onConfirm: async () => {
                try {
                    await st2faPost('{{ route('2fa.disable') }}', { password });
                    st2faEnabled = false;
                    st2faSyncBadge();
                    document.getElementById('st2faPanel').classList.remove('open');
                    document.getElementById('st2faCodesPanel').classList.remove('open');
                    document.getElementById('st2faDisablePwd').value = '';
                    stShowAlert('La 2FA fue desactivada.', 'ok', 2500);
                } catch (e) { stShowAlert(e.message, 'err'); }
            },
        });
    };

    function st2faRenderCodes(codes){
        st2faCurrentCodes = codes || [];
        const list = document.getElementById('st2faCodesList');
        list.innerHTML = '';
        st2faCurrentCodes.forEach(c => { const s = document.createElement('span'); s.textContent = c; list.appendChild(s); });
        document.getElementById('st2faCodesPanel').classList.add('open');
    }

    window.st2faShowRecoveryCodes = async function(){
        if (!st2faEnabled) { stShowAlert('Activá la 2FA antes de generar códigos de respaldo.', 'err'); return; }
        try {
            const data = await st2faGet('{{ route('2fa.recovery') }}');
            st2faRenderCodes(data.recovery_codes);
        } catch (e) { stShowAlert(e.message, 'err'); }
    };

    window.st2faRegenerate = function(){
        st2faConfirm2({
            title: '{{ __("Regenerar códigos de respaldo") }}',
            text: '{{ __("Esto invalidará tus códigos anteriores: los que hayas guardado dejarán de funcionar.") }}',
            confirmLabel: 'Regenerar',
            tone: 'brand',
            icon: 'pi pi-refresh',
            onConfirm: async () => {
                try {
                    const data = await st2faPost('{{ route('2fa.recovery.regen') }}');
                    st2faRenderCodes(data.recovery_codes);
                    stShowAlert('Códigos regenerados.', 'ok', 2200);
                } catch (e) { stShowAlert(e.message, 'err'); }
            },
        });
    };

    window.st2faCopyCodes = function(){
        if (!st2faCurrentCodes.length) return;
        navigator.clipboard?.writeText(st2faCurrentCodes.join('\n'))
            .then(() => stShowAlert('Códigos copiados al portapapeles.', 'ok', 1800))
            .catch(() => stShowAlert('No se pudieron copiar.', 'err'));
    };

    // Sync name → first/last + form submit
    window.submitSettingsProfile = function(){
        if (activePane === 'notifications') {
            stPersistNoti();
            stShowAlert('Preferencias de notificaciones guardadas.', 'ok', 2200);
            return;
        }
        if (activePane === 'signature') {
            if (typeof window.psSaveSignature === 'function') window.psSaveSignature();
            return;
        }
        if (activePane === 'menu') {
            if (typeof window.cmSave === 'function') window.cmSave();
            return;
        }
        if (activePane === 'security') {
            stShowAlert('Usá los botones de cada apartado para aplicar cambios.', 'ok', 2200);
            return;
        }
        // Profile pane: split name → first/last
        const nameInput = document.querySelector('input[name="name"]');
        const full = (nameInput?.value || '').trim();
        const parts = full.split(/\s+/);
        document.getElementById('stFirstName').value = parts.shift() || '';
        document.getElementById('stLastName').value  = parts.join(' ');

        // Ensure password hidden inputs are empty (separate flow)
        document.getElementById('stPwdInput').value = '';
        document.getElementById('stPwdConfirmInput').value = '';
        document.getElementById('stCurrentPwdInput').value = '';

        document.getElementById('stProfileForm').submit();
    };

    window.submitSettingsPassword = function(){
        const cur  = document.getElementById('stPwdCurrent').value;
        const nw   = document.getElementById('stPwdNew').value;
        const cnf  = document.getElementById('stPwdConfirm').value;
        if (!cur || !nw || !cnf) { stShowAlert('Completá los tres campos.', 'err'); return; }
        if (nw.length < 8)       { stShowAlert('La nueva contraseña debe tener al menos 8 caracteres.', 'err'); return; }
        if (nw !== cnf)          { stShowAlert('La confirmación no coincide.', 'err'); return; }
        document.getElementById('stCurrentPwdInput').value = cur;
        document.getElementById('stPwdInput').value        = nw;
        document.getElementById('stPwdConfirmInput').value = cnf;
        document.getElementById('stProfileForm').submit();
    };

    // Notifications (client-side persistence)
    window.stToggleNoti = function(btn){
        btn.classList.toggle('on');
        btn.setAttribute('aria-pressed', btn.classList.contains('on') ? 'true' : 'false');
    };
    function stPersistNoti(){
        const prefs = {};
        document.querySelectorAll('[data-st-noti]').forEach(b => { prefs[b.dataset.stNoti] = b.classList.contains('on'); });
        try { localStorage.setItem('crm-noti-prefs', JSON.stringify(prefs)); } catch(_) {}
    }
    function stRestoreNoti(){
        let prefs = {};
        try { prefs = JSON.parse(localStorage.getItem('crm-noti-prefs') || '{}'); } catch(_) {}
        document.querySelectorAll('[data-st-noti]').forEach(b => {
            const key = b.dataset.stNoti;
            const def = b.dataset.stDefault === '1';
            const on  = (key in prefs) ? prefs[key] : def;
            b.classList.toggle('on', !!on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    // Alerts
    function stShowAlert(msg, type, autoMs){
        const el = document.getElementById('stAlert');
        el.className = 'st-alert ' + (type === 'err' ? 'st-alert-err' : 'st-alert-ok');
        el.innerHTML = (type === 'err'
            ? '<i class="pi pi-exclamation-circle"></i> '
            : '<i class="pi pi-check-circle"></i> ') + msg;
        el.style.display = 'flex';
        if (autoMs) setTimeout(stHideAlert, autoMs);
    }
    function stHideAlert(){ document.getElementById('stAlert').style.display = 'none'; }
    window.stShowAlert = stShowAlert;
    window.stHideAlert = stHideAlert;

    // Restore notifications on first paint
    stRestoreNoti();

    @if($stIsAdmin)
    /* ======================= Firma del proyecto ======================= */
    let psCtx = null, psDrawing = false, psHasStroke = false, psRemove = false;

    window.psInitCanvas = function(){
        const canvas = document.getElementById('ps-sig-canvas');
        if (!canvas) return;
        const ratio = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        if (!rect.width) return;
        canvas.width  = Math.round(rect.width * ratio);
        canvas.height = Math.round(rect.height * ratio);
        psCtx = canvas.getContext('2d');
        psCtx.scale(ratio, ratio);
        psCtx.lineCap = 'round'; psCtx.lineJoin = 'round';
        psCtx.strokeStyle = '#171717'; psCtx.lineWidth = 2;
    };

    function psPt(e, canvas){
        const r = canvas.getBoundingClientRect();
        const cx = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
        const cy = (e.touches ? e.touches[0].clientY : e.clientY) - r.top;
        return [cx, cy];
    }
    function psStart(e){
        e.preventDefault();
        const canvas = document.getElementById('ps-sig-canvas');
        if (!psCtx) window.psInitCanvas();
        psDrawing = true; psHasStroke = true; psRemove = false;
        document.querySelector('#ps-canvas-wrap .ps-empty-canvas')?.style.setProperty('display', 'none');
        const [x, y] = psPt(e, canvas);
        psCtx.beginPath(); psCtx.moveTo(x, y);
    }
    function psMove(e){
        if (!psDrawing) return;
        e.preventDefault();
        const canvas = document.getElementById('ps-sig-canvas');
        const [x, y] = psPt(e, canvas);
        psCtx.lineTo(x, y); psCtx.stroke();
    }
    function psEnd(){ psDrawing = false; }

    (function bindPsCanvas(){
        const canvas = document.getElementById('ps-sig-canvas');
        if (!canvas) return;
        canvas.addEventListener('mousedown', psStart);
        canvas.addEventListener('mousemove', psMove);
        window.addEventListener('mouseup', psEnd);
        canvas.addEventListener('touchstart', psStart, { passive: false });
        canvas.addEventListener('touchmove', psMove, { passive: false });
        window.addEventListener('touchend', psEnd);
    })();

    window.psClearSig = function(){
        const canvas = document.getElementById('ps-sig-canvas');
        if (canvas && psCtx) psCtx.clearRect(0, 0, canvas.width, canvas.height);
        psHasStroke = false;
        document.querySelector('#ps-canvas-wrap .ps-empty-canvas')?.style.setProperty('display', 'flex');
    };

    window.psRemoveSignature = function(){
        psRemove = true;
        psHasStroke = false;
        window.psClearSig();
        document.getElementById('psCurrentWrap').style.display = 'none';
        stShowAlert('La firma se eliminará al guardar.', 'ok', 2200);
    };

    window.psSaveSignature = function(){
        const name   = document.getElementById('psSignerName').value.trim();
        const entity = document.getElementById('psSignerEntity').value.trim();

        let sigData = '';
        if (psHasStroke) {
            const canvas = document.getElementById('ps-sig-canvas');
            sigData = canvas.toDataURL('image/png');
        }

        const btn = document.getElementById('stSaveBtn');
        btn.disabled = true;

        const fd = new FormData();
        fd.append('signer_name', name);
        fd.append('signer_entity', entity);
        if (sigData)  fd.append('signature_image', sigData);
        if (psRemove) fd.append('remove_signature', '1');
        fd.append('_token', st2faCsrf());

        fetch('{{ route('admin.project-signature.update') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            credentials: 'same-origin',
        })
        .then(r => r.json().catch(() => ({})))
        .then(d => {
            btn.disabled = false;
            if (d.success === false) { stShowAlert(d.message || 'No se pudo guardar la firma.', 'err'); return; }
            stShowAlert(d.message || 'Firma del proyecto guardada.', 'ok', 2600);
            // Reflejar la firma recién dibujada en la vista previa.
            if (sigData) {
                document.getElementById('psCurrentImg').src = sigData;
                document.getElementById('psCurrentWrap').style.display = '';
            }
            psHasStroke = false; psRemove = false;
        })
        .catch(() => { btn.disabled = false; stShowAlert('Error de red al guardar la firma.', 'err'); });
    };

    /* ======================= Menú del cliente ======================= */
    // Estado inicial cargado desde el backend.
    let cmData = @json($stClientMenu);
    if (!Array.isArray(cmData)) cmData = [];
    let cmRendered = false;

    // Íconos disponibles (mismo set que el navbar del cliente). Sólo el ícono,
    // sin texto: es exactamente el que se mostrará en el menú.
    const cmIconSvgs = {
        globe:    '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
        file:     '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>',
        image:    '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline>',
        chart:    '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>',
        list:     '<line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line>',
        help:     '<circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>',
        book:     '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>',
        building: '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="6" x2="9.01" y2="6"></line><line x1="15" y1="6" x2="15.01" y2="6"></line><line x1="9" y1="10" x2="9.01" y2="10"></line><line x1="15" y1="10" x2="15.01" y2="10"></line><line x1="9" y1="14" x2="15" y2="14"></line>',
        map:      '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
    };
    function cmIconSvg(key){
        const inner = cmIconSvgs[key] || cmIconSvgs.file;
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + inner + '</svg>';
    }
    function cmSetIcon(picker, key){
        picker.dataset.icon = (key in cmIconSvgs) ? key : 'file';
        picker.querySelector('.cm-icon-current').innerHTML = cmIconSvg(picker.dataset.icon);
        picker.querySelectorAll('.cm-icon-opt').forEach(o => o.classList.toggle('active', o.dataset.icon === picker.dataset.icon));
    }
    function cmFillIconMenu(picker){
        const menu = picker.querySelector('.cm-icon-menu');
        menu.innerHTML = '';
        Object.keys(cmIconSvgs).forEach(key => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'cm-icon-opt';
            b.dataset.icon = key;
            b.innerHTML = cmIconSvg(key);
            b.addEventListener('click', () => { cmSetIcon(picker, key); cmCloseIconMenus(); });
            menu.appendChild(b);
        });
    }
    window.cmToggleIconMenu = function(btn){
        const picker = btn.closest('.cm-icon-picker');
        const wasOpen = picker.classList.contains('open');
        cmCloseIconMenus();
        if (!wasOpen) picker.classList.add('open');
    };
    function cmCloseIconMenus(){
        document.querySelectorAll('.cm-icon-picker.open').forEach(p => p.classList.remove('open'));
    }
    document.addEventListener('click', function(e){
        if (!e.target.closest('.cm-icon-picker')) cmCloseIconMenus();
    });

    // Render del editor (sólo la primera vez que se abre el pane).
    window.cmRender = function(){
        if (cmRendered) return;
        cmRendered = true;
        const list = document.getElementById('cmList');
        list.innerHTML = '';
        cmData.forEach(item => list.appendChild(cmBuildRow(item)));
        cmUpdateEmpty();
    };

    function cmBuildRow(item){
        item = item || {};
        const tpl  = document.getElementById('cmRowTemplate');
        const node = tpl.content.firstElementChild.cloneNode(true);
        node.dataset.id   = item.id || ('item' + Math.random().toString(36).slice(2, 8));
        node.dataset.file = item.file || '';
        node.dataset.format = item.format || '';
        node.querySelector('.cm-label').value = item.label || '';
        node.querySelector('.cm-type').value  = (item.type === 'document') ? 'document' : 'link';
        const picker = node.querySelector('.cm-icon-picker');
        cmFillIconMenu(picker);
        cmSetIcon(picker, item.icon || 'file');
        node.querySelector('.cm-url').value   = item.url || '';
        if (item.type === 'document' && item.file) {
            const parts = item.file.split('/');
            node.querySelector('.cm-file-name').textContent = parts[parts.length - 1];
        }
        cmSyncType(node.querySelector('.cm-type'));
        return node;
    }

    window.cmAddItem = function(){
        const list = document.getElementById('cmList');
        list.appendChild(cmBuildRow({ type: 'link', icon: 'globe' }));
        cmUpdateEmpty();
    };

    window.cmRemove = function(btn){
        btn.closest('.cm-row').remove();
        cmUpdateEmpty();
    };

    window.cmMove = function(btn, dir){
        const row = btn.closest('.cm-row');
        if (dir < 0 && row.previousElementSibling) row.parentNode.insertBefore(row, row.previousElementSibling);
        if (dir > 0 && row.nextElementSibling)     row.parentNode.insertBefore(row.nextElementSibling, row);
    };

    window.cmSyncType = function(sel){
        const row = sel.closest('.cm-row');
        const isDoc = sel.value === 'document';
        row.querySelector('.cm-link-wrap').style.display = isDoc ? 'none' : '';
        row.querySelector('.cm-doc-wrap').style.display  = isDoc ? '' : 'none';
    };

    window.cmFilePicked = function(input){
        const row = input.closest('.cm-row');
        const f = input.files && input.files[0];
        if (!f) return;
        if (f.size > 50 * 1024 * 1024) {
            stShowAlert('El archivo supera los 50 MB.', 'err');
            input.value = '';
            return;
        }
        cmUploadFile(row, f);
    };

    // Sube el archivo en trozos de ~1 MB para evitar el límite de post_max_size.
    async function cmUploadFile(row, file){
        const nameEl  = row.querySelector('.cm-file-name');
        const saveBtn = document.getElementById('stSaveBtn');
        // 512 KB por chunk: queda por debajo del client_max_body_size por defecto
        // de nginx (1 MB), contando el overhead del multipart, para evitar el 413.
        const chunkSize = 512 * 1024;
        const total = Math.ceil(file.size / chunkSize) || 1;
        const uploadId = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);

        row.dataset.uploading = '1';
        saveBtn.disabled = true;

        try {
            for (let i = 0; i < total; i++) {
                const chunk = file.slice(i * chunkSize, (i + 1) * chunkSize);
                const fd = new FormData();
                fd.append('chunk', chunk);
                fd.append('upload_id', uploadId);
                fd.append('index', i);
                fd.append('total', total);
                fd.append('name', file.name);
                fd.append('_token', st2faCsrf());

                const res = await fetch('{{ route('admin.client-menu.upload') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                    credentials: 'same-origin',
                });
                if (res.status === 413) throw new Error('El servidor rechazó el envío por tamaño. Subí el límite de subida de nginx (client_max_body_size).');
                const d = await res.json().catch(() => ({}));
                if (!res.ok || d.success === false) throw new Error(d.message || 'No se pudo subir el archivo.');

                nameEl.textContent = file.name + ' — ' + Math.round(((i + 1) / total) * 100) + '%';

                if (d.done) {
                    row.dataset.file   = d.path || '';
                    row.dataset.format = d.format || '';
                    nameEl.textContent = file.name;
                }
            }
        } catch (e) {
            row.dataset.file = '';
            row.dataset.format = '';
            nameEl.textContent = '';
            stShowAlert(e.message || 'No se pudo subir el archivo.', 'err');
        } finally {
            row.dataset.uploading = '';
            saveBtn.disabled = false;
        }
    }

    function cmUpdateEmpty(){
        const hasRows = document.querySelectorAll('#cmList .cm-row').length > 0;
        document.getElementById('cmEmpty').style.display = hasRows ? 'none' : '';
    }

    window.cmSave = function(){
        const rows = Array.from(document.querySelectorAll('#cmList .cm-row'));
        const meta = [];
        const fd = new FormData();

        if (rows.some(r => r.dataset.uploading === '1')) {
            stShowAlert('Esperá a que termine la subida del archivo.', 'err');
            return;
        }

        for (const row of rows) {
            const label = row.querySelector('.cm-label').value.trim();
            const type  = row.querySelector('.cm-type').value;
            const icon  = row.querySelector('.cm-icon-picker').dataset.icon || 'file';
            const id    = row.dataset.id;
            if (!label) { stShowAlert('Cada ítem necesita un nombre.', 'err'); return; }

            const entry = { id, label, type, icon };
            if (type === 'link') {
                const url = row.querySelector('.cm-url').value.trim();
                if (!url) { stShowAlert('El enlace "' + label + '" necesita una URL.', 'err'); return; }
                entry.url = url;
            } else {
                // El archivo ya se subió por chunks; aquí sólo viaja su ruta.
                if (!row.dataset.file) {
                    stShowAlert('El documento "' + label + '" necesita un archivo.', 'err'); return;
                }
                entry.file   = row.dataset.file || null;
                entry.format = row.dataset.format || null;
            }
            meta.push(entry);
        }

        fd.append('items', JSON.stringify(meta));
        fd.append('site_url', (document.getElementById('cmSiteUrl')?.value || '').trim());
        fd.append('_token', st2faCsrf());

        const btn = document.getElementById('stSaveBtn');
        btn.disabled = true;

        fetch('{{ route('admin.client-menu.update') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            credentials: 'same-origin',
        })
        .then(r => r.json().catch(() => ({})))
        .then(d => {
            btn.disabled = false;
            if (d.success === false) { stShowAlert(d.message || 'No se pudo guardar el menú.', 'err'); return; }
            // Refrescar el estado local con las rutas de archivo devueltas.
            if (Array.isArray(d.items)) {
                cmData = d.items;
                cmRendered = false;
                document.getElementById('cmList').innerHTML = '';
                window.cmRender();
            }
            stShowAlert(d.message || 'Menú del cliente guardado.', 'ok', 2600);
        })
        .catch(() => { btn.disabled = false; stShowAlert('Error de red al guardar el menú.', 'err'); });
    };
    @endif
})();
</script>
