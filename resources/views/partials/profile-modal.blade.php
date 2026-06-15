@auth
@php
    $pmUser   = auth()->user();
    $pmIsAdmin = $pmUser->role === 'admin';
    $pmAction = $pmIsAdmin ? route('admin.profile.update') : route('dashboard.profile.update');
    $pmFull   = trim(($pmUser->first_name ?? '') . ' ' . ($pmUser->last_name ?? '')) ?: ($pmUser->name ?? '');
    $pmInit   = strtoupper(substr($pmUser->first_name ?? $pmUser->name ?? 'U', 0, 1) . substr($pmUser->last_name ?? '', 0, 1));
    $pmAvatar = $pmUser->avatar ? asset('storage/' . $pmUser->avatar) : null;
@endphp
<style>
    .profile-modal-overlay {
        position: fixed; inset: 0; z-index: 410; display: none;
        align-items: center; justify-content: center; padding: 24px;
        background: rgba(15, 17, 24, 0.48);
    }
    .profile-modal-overlay.open { display: flex; }
    .profile-modal-card {
        width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto;
        border-radius: 18px; background: #fff;
        box-shadow: 0 30px 80px -20px rgba(10, 13, 20, .4);
        font-family: 'Poppins', sans-serif;
        animation: profileModalIn .18s ease-out;
    }
    @keyframes profileModalIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .profile-modal-head {
        display: flex; align-items: center; gap: 12px;
        padding: 18px 22px; border-bottom: 1px solid #ececec;
    }
    .profile-modal-title { margin: 0; font-size: 16px; font-weight: 700; color: #171717; flex: 1; letter-spacing: -0.02em; }
    .profile-modal-x { border: none; background: transparent; color: #98a2b3; cursor: pointer; padding: 4px; font-size: 16px; }
    .profile-modal-x:hover { color: #475467; }
    .profile-modal-body { padding: 22px; }
    .profile-modal-section { margin-bottom: 18px; }
    .pm-label { display: block; font-size: 12px; font-weight: 600; color: #344054; margin-bottom: 5px; }
    .pm-input {
        width: 100%; height: 42px; border: 1px solid #e5e7eb; border-radius: 10px;
        padding: 0 12px; font-size: 13px; color: #171717; font-family: inherit; background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }
    .pm-input:focus { outline: none; border-color: #5c7c68; box-shadow: 0 0 0 3px rgba(92,124,104,.15); }
    .pm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .pm-grid .pm-col-span-2 { grid-column: 1 / -1; }
    .pm-divider { height: 1px; background: #ececec; margin: 4px 0 18px; }
    .pm-section-title { font-size: 13px; font-weight: 700; color: #171717; margin-bottom: 2px; }
    .pm-section-sub { font-size: 11px; color: #667085; margin-bottom: 12px; }
    .profile-modal-foot {
        display: flex; justify-content: flex-end; gap: 10px;
        padding: 16px 22px; border-top: 1px solid #ececec; background: #fafafa;
        border-radius: 0 0 18px 18px;
    }
    .pm-btn { height: 40px; border-radius: 10px; padding: 0 16px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #e5e7eb; transition: background .15s, transform .15s; font-family: inherit; }
    .pm-btn:hover { transform: translateY(-1px); }
    .pm-btn-ghost { background: #fff; color: #344054; }
    .pm-btn-ghost:hover { background: #f4f4f5; }
    .pm-btn-primary { background: #5c7c68; border-color: #5c7c68; color: #fff; }
    .pm-btn-primary:hover { background: #4a6354; border-color: #4a6354; }
    .pm-avatar { width: 72px; height: 72px; border-radius: 999px; overflow: hidden; background: #5c7c68; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; border: 1px solid #e5e7eb; }
    .pm-upload { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #5c7c68; cursor: pointer; }
    .pm-alert { padding: 10px 12px; border-radius: 10px; font-size: 12px; margin-bottom: 16px; }
    .pm-alert-ok { background: #e8f5ee; border: 1px solid rgba(31,193,107,.3); color: #0f7a45; }
    .pm-alert-err { background: #fde8e8; border: 1px solid rgba(220,38,38,.3); color: #b42318; }
    @media (max-width: 520px) {
        .pm-grid { grid-template-columns: 1fr; }
        .profile-modal-foot { flex-direction: column-reverse; }
        .pm-btn { width: 100%; }
    }
</style>

<div id="profileModal" class="profile-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
    <div class="profile-modal-card">
        <div class="profile-modal-head">
            <h2 id="profileModalTitle" class="profile-modal-title">{{ __('Mi perfil') }}</h2>
            <button type="button" class="profile-modal-x" onclick="closeProfileModal()" aria-label="{{ __('Cerrar') }}"><i class="pi pi-times"></i></button>
        </div>

        <form method="POST" action="{{ $pmAction }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="profile_modal" value="1">
            <div class="profile-modal-body">

                @if (session('success'))
                    <div class="pm-alert pm-alert-ok"><i class="pi pi-check-circle"></i> {{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="pm-alert pm-alert-err">
                        <div style="font-weight:600;margin-bottom:4px;"><i class="pi pi-exclamation-circle"></i> {{ __('Revisa los datos:') }}</div>
                        <ul style="margin:0;padding-left:18px;">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                {{-- Avatar --}}
                <div class="profile-modal-section" style="display:flex;align-items:center;gap:16px;">
                    <div id="pm-avatar-preview" class="pm-avatar"
                         @if($pmAvatar) style="background-image:url('{{ $pmAvatar }}');background-size:cover;background-position:center;" @endif>
                        @if(!$pmAvatar){{ $pmInit ?: 'U' }}@endif
                    </div>
                    <div>
                        <label class="pm-upload">
                            <i class="pi pi-upload"></i> Subir nueva foto
                            <input type="file" name="avatar" id="pm-avatar-input" accept="image/png,image/jpeg,image/webp" style="display:none;">
                        </label>
                        @if($pmUser->avatar)
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#dc2626;cursor:pointer;margin-top:6px;">
                                <input type="checkbox" name="remove_avatar" value="1"> Eliminar foto actual
                            </label>
                        @endif
                        <div style="font-size:11px;color:#667085;margin-top:4px;">{{ __('JPG, PNG o WebP · máx 4 MB') }}</div>
                    </div>
                </div>

                {{-- Basic info --}}
                <div class="profile-modal-section">
                    <div class="pm-grid">
                        <div>
                            <label class="pm-label">{{ __('Nombre') }}</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $pmUser->first_name) }}" class="pm-input" placeholder="{{ __('Nombre') }}">
                        </div>
                        <div>
                            <label class="pm-label">{{ __('Apellido') }}</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $pmUser->last_name) }}" class="pm-input" placeholder="{{ __('Apellido') }}">
                        </div>
                        <div class="pm-col-span-2">
                            <label class="pm-label">{{ __('Nombre para mostrar') }} <span style="color:#98a2b3;font-weight:400;">(opcional)</span></label>
                            <input type="text" name="name" value="{{ old('name', $pmUser->name) }}" class="pm-input" placeholder="{{ $pmFull }}">
                        </div>
                        <div>
                            <label class="pm-label">{{ __('Correo electrónico') }}</label>
                            <input type="email" name="email" required value="{{ old('email', $pmUser->email) }}" class="pm-input" placeholder="tu@correo.com">
                        </div>
                        <div>
                            <label class="pm-label">{{ __('Teléfono') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $pmUser->phone) }}" class="pm-input" placeholder="+57 300 000 0000">
                        </div>
                        <div class="pm-col-span-2">
                            <label class="pm-label">{{ __('País') }}</label>
                            <input type="text" name="country" value="{{ old('country', $pmUser->country) }}" class="pm-input" placeholder="{{ __('Colombia') }}">
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="pm-divider"></div>
                <div class="profile-modal-section">
                    <div class="pm-section-title">{{ __('Cambiar contraseña') }}</div>
                    <div class="pm-section-sub">{{ __('Déjalo en blanco si no quieres cambiarla.') }}</div>
                    <div class="pm-grid">
                        <div class="pm-col-span-2">
                            <label class="pm-label">{{ __('Contraseña actual') }}</label>
                            <input type="password" name="current_password" autocomplete="current-password" class="pm-input" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="pm-label">{{ __('Nueva contraseña') }}</label>
                            <input type="password" name="password" autocomplete="new-password" class="pm-input" placeholder="{{ __('Mín. 8 caracteres') }}">
                        </div>
                        <div>
                            <label class="pm-label">{{ __('Confirmar') }}</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password" class="pm-input" placeholder="{{ __('Repite la contraseña') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-modal-foot">
                <button type="button" class="pm-btn pm-btn-ghost" onclick="closeProfileModal()">{{ __('Cancelar') }}</button>
                <button type="submit" class="pm-btn pm-btn-primary"><i class="pi pi-check"></i> {{ __('Guardar cambios') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('profileModal');
    if (!modal) return;
    let prevOverflow = '';

    window.openProfileModal = function () {
        if (typeof closeProfileMenu === 'function') closeProfileMenu();
        prevOverflow = document.body.style.overflow;
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    };
    window.closeProfileModal = function () {
        modal.classList.remove('open');
        document.body.style.overflow = prevOverflow;
    };

    modal.addEventListener('click', function (e) { if (e.target === modal) window.closeProfileModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) window.closeProfileModal();
    });

    // Avatar live preview
    document.getElementById('pm-avatar-input')?.addEventListener('change', function (e) {
        const file = e.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            const p = document.getElementById('pm-avatar-preview');
            p.style.backgroundImage = `url('${ev.target.result}')`;
            p.style.backgroundSize = 'cover';
            p.style.backgroundPosition = 'center';
            p.textContent = '';
        };
        reader.readAsDataURL(file);
    });

    // Reopen automatically after a validation error so the user sees what failed
    @if(old('profile_modal'))
        document.addEventListener('DOMContentLoaded', window.openProfileModal);
    @endif
})();
</script>
@endauth
