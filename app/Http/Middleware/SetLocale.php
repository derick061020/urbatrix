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
     *   3. Idioma del navegador (cabecera Accept-Language)
     *   4. Locale por defecto del config
     *
     * Los dos primeros son una elección explícita del usuario (el selector
     * ES/EN), así que mandan sobre el navegador: quien cambia el idioma a mano
     * no quiere que se lo volvamos a cambiar en la siguiente visita.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['es', 'en']);
        $default   = config('app.locale', 'es');

        $locale = $this->deSesion($request, 'locale')
            ?? $request->cookie('app_locale')
            ?? $this->fromBrowser($request, $supported)
            ?? $default;

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        // Carbon hereda el locale del helper App si está disponible.
        try { \Carbon\Carbon::setLocale($locale); } catch (\Throwable $e) {}

        // Zona horaria: sesión -> cookie -> config. Se aplica a PHP/Carbon para
        // que todas las fechas/horas se muestren en el huso elegido por el usuario.
        $timezone = $this->deSesion($request, 'timezone')
            ?? $request->cookie('app_timezone')
            ?? config('app.timezone', 'UTC');

        if (in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }

    /**
     * Un valor de la sesión, si es que hay sesión.
     *
     * Este middleware corre también en la pila global, para que las páginas de
     * error —que no pasan por el grupo `web`— salgan en el idioma del
     * visitante. Allí la sesión todavía no está arrancada y pedirla lanza una
     * excepción, así que se comprueba antes. Cuando vuelve a correr dentro del
     * grupo `web` la sesión ya está y manda ella, que es la elección explícita
     * del usuario en el selector ES/EN.
     */
    private function deSesion(Request $request, string $clave)
    {
        return $request->hasSession() ? $request->session()->get($clave) : null;
    }

    /**
     * Idioma preferido del navegador, si es uno de los que soportamos.
     *
     * Accept-Language llega como «es-419,es;q=0.9,en;q=0.8»: se compara sólo
     * la parte principal, para que es-419, es-DO o es-AR caigan todos en «es».
     * Devuelve null si el navegador no manda la cabecera o pide un idioma que
     * no tenemos, y entonces decide el locale por defecto.
     */
    private function fromBrowser(Request $request, array $supported): ?string
    {
        $header = (string) $request->headers->get('Accept-Language');
        if ($header === '') {
            return null;
        }

        // [idioma => calidad], ordenado de mayor a menor preferencia.
        $ranked = [];
        foreach (explode(',', $header) as $part) {
            $bits = explode(';q=', trim($part));
            $tag  = strtolower(trim($bits[0]));
            if ($tag === '' || $tag === '*') {
                continue;
            }
            $base = explode('-', $tag)[0];
            $q    = isset($bits[1]) ? (float) $bits[1] : 1.0;
            // Nos quedamos con la mayor calidad vista para cada idioma base.
            if (! isset($ranked[$base]) || $q > $ranked[$base]) {
                $ranked[$base] = $q;
            }
        }
        arsort($ranked);

        foreach (array_keys($ranked) as $base) {
            if (in_array($base, $supported, true)) {
                return $base;
            }
        }

        return null;
    }
}
