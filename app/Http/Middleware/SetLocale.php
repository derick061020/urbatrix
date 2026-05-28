<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Determina el idioma a usar para esta request en base a:
     *   1. Sesión (?locale en sesión por ruta /locale)
     *   2. Cookie 'app_locale'
     *   3. Locale por defecto del config
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['es', 'en']);
        $default   = config('app.locale', 'es');

        $locale = $request->session()->get('locale')
            ?? $request->cookie('app_locale')
            ?? $default;

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        // Carbon hereda el locale del helper App si está disponible.
        try { \Carbon\Carbon::setLocale($locale); } catch (\Throwable $e) {}

        return $next($request);
    }
}
