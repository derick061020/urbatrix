@php
    // El logo llevaba a url('/'), la propia plataforma: desde el login te
    // dejaba en la home y el botón «atrás» te devolvía al login, en bucle.
    // Ahora apunta a la web pública del proyecto (ajuste «site_url»); si no
    // está configurada, se queda en la plataforma como antes.
    $logoHref = trim((string) \App\Models\Setting::get('site_url', '')) ?: url('/');
    $logoExt  = \Illuminate\Support\Str::startsWith($logoHref, ['http://', 'https://'])
        && ! \Illuminate\Support\Str::contains($logoHref, request()->getHost());
@endphp
<a href="{{ $logoHref }}" @if($logoExt) rel="noopener" @endif class="flex items-center gap-3 select-none">
    {{-- Logo mark: green rounded square with white concentric circles --}}
    <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 shadow-sm" style="background:#5c7c68">
        <span class="block w-6 h-6">
            <img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full" />
        </span>
    </span>
    <span class="flex flex-col leading-none">
        <span class="font-display text-[14px] font-bold text-ink-950 tracking-tight">MAKAI</span>
        <span class="text-[9px] font-semibold text-ink-500 tracking-[0.18em] uppercase mt-1">{{ __('Duna Development') }}</span>
    </span>
</a>
