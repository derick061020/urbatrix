{{-- ====== Toggle de idioma (ES/EN) ======
     Reusable. Inclúyelo dentro del menú hamburguesa o de cualquier sidebar/menú.
     Persiste el idioma en la sesión + cookie y recarga la página al cambiar. --}}
@php
    $currentLocale = app()->getLocale();
    $variant = $variant ?? 'compact'; // 'compact' | 'pill'
@endphp

@if ($variant === 'pill')
<div class="lang-toggle-pill" data-lang-toggle>
    <div class="lang-pill-indicator" data-lang-indicator
         style="left: {{ $currentLocale === 'es' ? '4px' : 'calc(50% + 2px)' }};"></div>
    <button type="button" data-lang="es" class="lang-pill-btn {{ $currentLocale === 'es' ? 'is-active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="20" height="20" rx="10" fill="#AA151B"/>
            <rect y="6.67" width="20" height="6.66" fill="#F1BF00"/>
        </svg>
        <span>{{ __('Español') }}</span>
    </button>
    <button type="button" data-lang="en" class="lang-pill-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="20" height="20" rx="10" fill="#B22234"/>
            <rect y="1.54" width="20" height="1.54" fill="white"/>
            <rect y="4.62" width="20" height="1.54" fill="white"/>
            <rect y="7.69" width="20" height="1.54" fill="white"/>
            <rect y="10.77" width="20" height="1.54" fill="white"/>
            <rect y="13.85" width="20" height="1.54" fill="white"/>
            <rect y="16.92" width="20" height="1.54" fill="white"/>
            <rect width="8.46" height="10.77" fill="#3C3B6E"/>
        </svg>
        <span>{{ __('English') }}</span>
    </button>
</div>
@else
<div class="lang-toggle-compact" data-lang-toggle>
    <button type="button" data-lang="es" class="lang-compact-btn {{ $currentLocale === 'es' ? 'is-active' : '' }}" title="{{ __('Español') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="20" height="20" rx="10" fill="#AA151B"/>
            <rect y="6.67" width="20" height="6.66" fill="#F1BF00"/>
        </svg>
        ES
    </button>
    <button type="button" data-lang="en" class="lang-compact-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}" title="{{ __('English') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="20" height="20" rx="10" fill="#B22234"/>
            <rect y="1.54" width="20" height="1.54" fill="white"/>
            <rect y="4.62" width="20" height="1.54" fill="white"/>
            <rect y="7.69" width="20" height="1.54" fill="white"/>
            <rect y="10.77" width="20" height="1.54" fill="white"/>
            <rect y="13.85" width="20" height="1.54" fill="white"/>
            <rect y="16.92" width="20" height="1.54" fill="white"/>
            <rect width="8.46" height="10.77" fill="#3C3B6E"/>
        </svg>
        EN
    </button>
</div>
@endif

@once
@push('styles')
<style>
    .lang-toggle-compact { display: inline-flex; background: #f2f5f8; padding: 4px; border-radius: 999px; gap: 2px; }
    .lang-compact-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px;
        background: transparent; border: 0; cursor: pointer; font: 600 11px/1 'Inter', sans-serif;
        color: #717784; transition: background .15s, color .15s; }
    .lang-compact-btn.is-active { background: #fff; color: #1f2937; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .lang-compact-btn svg { flex-shrink: 0; }

    .lang-toggle-pill { background: #f2f5f8; display: flex; gap: 4px; align-items: center; justify-content: center;
        overflow: hidden; padding: 4px; border-radius: 12px; width: 308px; position: relative; }
    .lang-pill-indicator { position: absolute; background: #fff; height: 32px; border-radius: 8px; top: 4px;
        width: calc(50% - 6px); transition: left .2s ease; }
    .lang-pill-btn { display: flex; gap: 6px; align-items: center; justify-content: center; padding: 6px;
        border-radius: 8px; flex: 1; min-width: 0; position: relative; z-index: 1; background: transparent;
        border: 0; cursor: pointer; font: 600 12px/20px 'Inter', sans-serif; color: #717784; opacity: .52; }
    .lang-pill-btn.is-active { color: #525866; opacity: 1; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (window.__langToggleBound) return;
    window.__langToggleBound = true;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function applyActive(root, lang) {
        root.querySelectorAll('[data-lang]').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.lang === lang);
        });
        const ind = root.querySelector('[data-lang-indicator]');
        if (ind) ind.style.left = (lang === 'es') ? '4px' : 'calc(50% + 2px)';
    }

    function changeLang(lang) {
        const serverLang = (document.documentElement.getAttribute('lang') || 'es').toLowerCase().split('-')[0];
        if (serverLang === lang) return;
        document.querySelectorAll('[data-lang-toggle]').forEach(r => applyActive(r, lang));
        fetch('{{ route("locale.update") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ locale: lang }),
        }).then(r => r.ok ? r.json() : Promise.reject(r))
          .then(() => window.location.reload())
          .catch(() => {});
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-lang-toggle] [data-lang]');
        if (!btn) return;
        e.preventDefault();
        changeLang(btn.dataset.lang);
    });
})();
</script>
@endpush
@endonce
