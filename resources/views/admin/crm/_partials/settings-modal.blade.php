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

    @media (max-width: 860px) {
        .st-shell { grid-template-columns: 1fr; height: calc(100vh - 24px); max-height: calc(100vh - 24px); }
        .st-sidebar { display: flex; flex-direction: row; gap: 4px; overflow-x: auto; padding: 12px; }
        .st-sidebar .st-section-label { display: none; }
        .st-nav-item .chev { display: none; }
        .st-row { grid-template-columns: 1fr; }
        .st-row-right { justify-content: flex-start; }
    }
</style>

<div id="settingsModal" class="st-overlay" role="dialog" aria-modal="true" aria-label="Configuración de la cuenta">
    <div class="st-shell" id="settingsShell">

        {{-- Sidebar --}}
        <aside class="st-sidebar">
            <div class="st-section-label">Configuración Personal</div>
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
        </aside>

        {{-- Main --}}
        <div class="st-main">

            {{-- Header --}}
            <div class="st-head">
                <div>
                    <div class="st-head-title" data-st-title>Configuración de la cuenta</div>
                    <div class="st-head-sub"   data-st-sub>Administra y colabora en la configuración de tu cuenta</div>
                </div>
                <div class="st-head-actions">
                    <button type="button" class="st-btn st-btn-ghost" onclick="closeSettingsModal()">Descartar</button>
                    <button type="button" class="st-btn st-btn-primary" id="stSaveBtn" onclick="submitSettingsProfile()">
                        <span id="stSaveLabel">Guardar cambios</span>
                    </button>
                </div>
            </div>

            {{-- Inline alerts --}}
            <div id="stAlert" class="st-alert" style="display:none; margin-top:14px;"></div>

            {{-- PROFILE TABS --}}
            <div class="st-tabs" data-st-tabs="profile">
                <button type="button" class="st-tab active" data-st-tab="profile-perfil">Perfil</button>
                <button type="button" class="st-tab" data-st-tab="profile-region">Idioma y región</button>
            </div>
            {{-- SECURITY TABS --}}
            <div class="st-tabs" data-st-tabs="security" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="security-pwd">Contraseña y 2FA</button>
                <button type="button" class="st-tab" data-st-tab="security-session">Sesión Activa</button>
            </div>
            {{-- NOTIFICATIONS TABS --}}
            <div class="st-tabs" data-st-tabs="notifications" style="display:none;">
                <button type="button" class="st-tab active" data-st-tab="notifications-main">Notificaciones</button>
            </div>

            <div class="st-body">

                <form id="stProfileForm" method="POST" action="{{ route($stProfileRoute) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="redirect_settings" value="1">

                    {{-- =========== PROFILE PANE — PERFIL =========== --}}
                    <div class="st-pane active" data-st-pane="profile-perfil">

                        {{-- Foto --}}
                        <div class="st-row">
                            <div>
                                <div class="st-row-label">Foto de perfil</div>
                                <div class="st-row-desc">Mínimo 400x400px, formatos PNG o JPEG.</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-avatar-row">
                                    <div class="st-avatar" id="stAvatar" @if($sAvatar) style="background-image:url('{{ $sAvatar }}'); color:transparent;" @endif>
                                        @if(!$sAvatar){{ $sInit }}@endif
                                        @if($sAvatar)
                                            <button type="button" class="st-avatar-del" onclick="stRemoveAvatar()" title="Eliminar foto"><i class="pi pi-times"></i></button>
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
                                <div class="st-row-label">Nombre completo</div>
                                <div class="st-row-desc">Tu nombre será visible para tus contactos.</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sName ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="Editar"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap">
                                    <input type="text" name="name" value="{{ $sName }}" placeholder="Tu nombre completo" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">Cancelar</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">Guardar</button>
                                </div>
                            </div>
                        </div>

                        {{-- Correo --}}
                        <div class="st-row" data-st-field>
                            <div>
                                <div class="st-row-label">Correo electrónico</div>
                                <div class="st-row-desc">Se recomienda correo electrónico empresarial.</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sEmail ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="Editar"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap">
                                    <input type="email" name="email" value="{{ $sEmail }}" placeholder="tu@correo.com" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">Cancelar</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">Guardar</button>
                                </div>
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div class="st-row editing" data-st-field>
                            <div>
                                <div class="st-row-label">Número de teléfono</div>
                                <div class="st-row-desc">Se recomienda número de teléfono empresarial.</div>
                            </div>
                            <div class="st-row-right">
                                <div class="st-value">
                                    <span class="st-value-text" data-st-display>{{ $sPhone ?: '—' }}</span>
                                    <button type="button" class="st-edit-btn" onclick="stToggleEdit(this)" aria-label="Editar"><i class="pi pi-pencil"></i></button>
                                </div>
                                <div class="st-edit-wrap open">
                                    <input type="tel" name="phone" value="{{ $sPhone ?: '+1 (123) 456-6789' }}" placeholder="+57 300 000 0000" data-st-input>
                                    <button type="button" class="st-btn-link" onclick="stCancelEdit(this)">Cancelar</button>
                                    <button type="button" class="st-btn-link" onclick="stConfirmEdit(this)" style="color:#5c7c68;">Guardar</button>
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
                                <div class="st-row-label">Idioma</div>
                                <div class="st-row-desc">Idioma para la interfaz del CRM.</div>
                            </div>
                            <div class="st-row-right">
                                <select name="locale" class="st-value" style="border:none; outline:none;">
                                    <option value="es" @selected(app()->getLocale() === 'es')>Español</option>
                                    <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                                </select>
                            </div>
                        </div>
                        <div class="st-row compact">
                            <div>
                                <div class="st-row-label">Zona horaria</div>
                                <div class="st-row-desc">Las fechas y horas se muestran en esta zona.</div>
                            </div>
                            <div class="st-row-right">
                                @php $stTz = session('timezone', request()->cookie('app_timezone', 'America/Santo_Domingo')); @endphp
                                <select name="timezone" class="st-value" style="border:none; outline:none;">
                                    <option value="America/Santo_Domingo" @selected($stTz === 'America/Santo_Domingo')>America/Santo_Domingo</option>
                                    <option value="America/Bogota" @selected($stTz === 'America/Bogota')>America/Bogota</option>
                                    <option value="America/Mexico_City" @selected($stTz === 'America/Mexico_City')>America/Mexico_City</option>
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
                            <div class="st-row-label">Cambiar Contraseña</div>
                            <div class="st-row-desc">Actualiza la contraseña para mejorar la seguridad de tu cuenta.</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stTogglePwdPanel()">Cambiar Contraseña</button>
                        </div>
                    </div>

                    <div class="st-collapse" id="stPwdPanel">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">Contraseña actual</label>
                                <input type="password" id="stPwdCurrent" class="crm-input pl-3 mt-1" autocomplete="current-password" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">Nueva contraseña</label>
                                <input type="password" id="stPwdNew" class="crm-input pl-3 mt-1" autocomplete="new-password" placeholder="Mín. 8 caracteres">
                            </div>
                            <div>
                                <label class="text-[12px] font-semibold text-ink-700">Confirmar</label>
                                <input type="password" id="stPwdConfirm" class="crm-input pl-3 mt-1" autocomplete="new-password" placeholder="Repite la contraseña">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 justify-end">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stTogglePwdPanel(false)">Cancelar</button>
                            <button type="button" class="st-btn st-btn-primary" onclick="submitSettingsPassword()">Confirmar cambio</button>
                        </div>
                    </div>

                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">Códigos de Respaldo</div>
                            <div class="st-row-desc">Genera códigos de respaldo para tu dispositivo 2FA.</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stShowAlert('Códigos de respaldo: función disponible próximamente.', 'ok')">Generar Códigos</button>
                        </div>
                    </div>

                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">Autenticación 2FA</div>
                            <div class="st-row-desc">Agrega una capa extra de protección a tu cuenta.</div>
                        </div>
                        <div class="st-row-right">
                            <button type="button" class="st-btn st-btn-ghost" onclick="stShowAlert('2FA: función disponible próximamente.', 'ok')">Administrar Autenticación</button>
                        </div>
                    </div>
                </div>

                <div class="st-pane" data-st-pane="security-session">
                    <div class="st-row compact">
                        <div>
                            <div class="st-row-label">Sesión actual</div>
                            <div class="st-row-desc">Última actividad: {{ now()->isoFormat('D MMMM YYYY · HH:mm') }}</div>
                        </div>
                        <div class="st-row-right">
                            <form method="POST" action="{{ route($stLogoutRoute) }}" class="m-0" data-logout-confirm>@csrf
                                <button type="submit" class="st-btn st-btn-ghost" style="color:#e93544;">Cerrar sesión</button>
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

            </div>
        </div>
    </div>
</div>

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
            };
            const t = titles[activePane];
            document.querySelector('[data-st-title]').textContent = t[0];
            document.querySelector('[data-st-sub]').textContent   = t[1];
            document.getElementById('stSaveLabel').textContent    = t[2];
            document.getElementById('stSaveBtn').style.display    = t[3] || activePane === 'notifications' ? '' : 'none';
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
        if (!confirm('¿Eliminar la foto de perfil?')) return;
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

    // Sync name → first/last + form submit
    window.submitSettingsProfile = function(){
        if (activePane === 'notifications') {
            stPersistNoti();
            stShowAlert('Preferencias de notificaciones guardadas.', 'ok', 2200);
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
})();
</script>
