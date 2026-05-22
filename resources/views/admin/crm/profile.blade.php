@extends('layouts.admin_crm')
@section('title', 'Mi Perfil — CRM Duna Makai')
@section('page_title', 'Mi Perfil')
@section('page_breadcrumb', 'Cuenta · Editar perfil')
@php $activeRoute = 'crm.profile'; @endphp

@section('content')
@php
    /** @var \App\Models\User $user */
    $user = $user ?? Auth::user();
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? '');
    $initials = strtoupper(
        substr($user->first_name ?? $user->name ?? 'A', 0, 1)
        . substr($user->last_name ?? '', 0, 1)
    );
    $avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : null;
@endphp

<div class="p-6 sm:p-7 max-w-4xl">

    @if (session('success'))
        <div class="mb-5 px-4 py-3 rounded-lg bg-ok-soft border border-ok/30 text-ok-dark text-[13px] font-medium flex items-center gap-2">
            <i class="pi pi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 px-4 py-3 rounded-lg bg-err-soft border border-err/30 text-err-dark text-[13px] font-medium">
            <div class="flex items-center gap-2 mb-1"><i class="pi pi-exclamation-circle"></i> Revisa los datos:</div>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Avatar card --}}
        <div class="crm-card p-5 sm:p-6">
            <div class="text-[14px] font-semibold text-ink-900 mb-1">Foto de perfil</div>
            <div class="text-[12px] text-ink-500 mb-4">Se mostrará en el CRM y en las conversaciones con clientes.</div>

            <div class="flex items-center gap-5 flex-wrap">
                <div class="relative">
                    <div id="avatar-preview"
                         class="w-24 h-24 rounded-full overflow-hidden bg-brand flex items-center justify-center text-white text-[28px] font-bold shadow-card border border-ink-200"
                         @if($avatarUrl) style="background-image:url('{{ $avatarUrl }}');background-size:cover;background-position:center;" @endif>
                        @if(!$avatarUrl) {{ $initials ?: 'A' }} @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="crm-btn crm-btn-primary cursor-pointer">
                        <i class="pi pi-upload"></i> Subir nueva foto
                        <input type="file" name="avatar" id="avatar-input" accept="image/png,image/jpeg,image/webp" class="hidden">
                    </label>
                    @if($user->avatar)
                        <label class="text-[12px] text-err inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="remove_avatar" value="1" class="accent-err">
                            Eliminar foto actual
                        </label>
                    @endif
                    <div class="text-[11px] text-ink-500">JPG, PNG o WebP · máx 4 MB</div>
                </div>
            </div>
        </div>

        {{-- Personal info --}}
        <div class="crm-card p-5 sm:p-6">
            <div class="text-[14px] font-semibold text-ink-900 mb-1">Información básica</div>
            <div class="text-[12px] text-ink-500 mb-4">Estos datos identifican al administrador en el sistema.</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Nombre</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="crm-input pl-3 mt-1" placeholder="Nombre">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Apellido</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="crm-input pl-3 mt-1" placeholder="Apellido">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[12px] font-semibold text-ink-700">Nombre para mostrar <span class="text-ink-400 font-normal">(opcional)</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="crm-input pl-3 mt-1" placeholder="{{ $fullName }}">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Correo electrónico</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="crm-input pl-3 mt-1" placeholder="tu@correo.com">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="crm-input pl-3 mt-1" placeholder="+57 300 000 0000">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[12px] font-semibold text-ink-700">País</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="crm-input pl-3 mt-1" placeholder="Colombia">
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="crm-card p-5 sm:p-6">
            <div class="text-[14px] font-semibold text-ink-900 mb-1">Cambiar contraseña</div>
            <div class="text-[12px] text-ink-500 mb-4">Déjalo en blanco si no quieres cambiarla.</div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Contraseña actual</label>
                    <input type="password" name="current_password" autocomplete="current-password" class="crm-input pl-3 mt-1" placeholder="••••••••">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Nueva contraseña</label>
                    <input type="password" name="password" autocomplete="new-password" class="crm-input pl-3 mt-1" placeholder="Mín. 8 caracteres">
                </div>
                <div>
                    <label class="text-[12px] font-semibold text-ink-700">Confirmar</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" class="crm-input pl-3 mt-1" placeholder="Repite la contraseña">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.crm.dashboard') }}" class="crm-btn crm-btn-ghost">Cancelar</a>
            <button type="submit" class="crm-btn crm-btn-primary"><i class="pi pi-check"></i> Guardar cambios</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('avatar-input')?.addEventListener('change', function (e) {
        const file = e.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            const preview = document.getElementById('avatar-preview');
            preview.style.backgroundImage = `url('${ev.target.result}')`;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
            preview.textContent = '';
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
@endsection
