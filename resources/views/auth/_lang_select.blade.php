@php
    $currentLocale = app()->getLocale();
    $label = $currentLocale === 'en' ? 'ENG' : 'ESP';
@endphp
<div class="auth-lang-wrap">
    <button type="button" class="flex items-center gap-1.5 hover:text-ink-700" onclick="toggleAuthLangMenu(event)" aria-haspopup="true" aria-expanded="false">
        <i class="pi pi-globe text-[12px]"></i><span data-auth-lang-label>{{ $label }}</span><i class="pi pi-angle-down text-[10px]"></i>
    </button>
    <div class="auth-lang-menu hidden" role="menu">
        <button type="button" role="menuitem" data-auth-lang="es" onclick="setAuthLocale('es')" class="auth-lang-item {{ $currentLocale === 'es' ? 'is-active' : '' }}">
            <i class="pi pi-check text-[10px] auth-lang-check"></i>
            <span>{{ __('Español') }}</span>
        </button>
        <button type="button" role="menuitem" data-auth-lang="en" onclick="setAuthLocale('en')" class="auth-lang-item {{ $currentLocale === 'en' ? 'is-active' : '' }}">
            <i class="pi pi-check text-[10px] auth-lang-check"></i>
            <span>{{ __('English') }}</span>
        </button>
    </div>
</div>
