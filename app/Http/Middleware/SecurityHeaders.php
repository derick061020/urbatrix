<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad en todas las respuestas, con la Content Security Policy.
 *
 * La política sale del inventario de lo que las tres webs cargan de verdad
 * (octubre 2026). No puede ser estricta del todo: el panel usa el CDN de
 * Tailwind, cientos de onclick y <script> en línea, y Alpine (que necesita
 * eval). Aun así corta lo que más importa: scripts de dominios que no están en
 * la lista, envío de datos por fetch a terceros, <object>/<base> inyectados,
 * formularios hacia http y que otra web nos meta en un iframe (clickjacking).
 *
 * Antes de añadir una librería de un CDN nuevo hay que sumarlo aquí, o el
 * navegador la bloqueará. Si algo se rompe en producción, CSP_REPORT_ONLY=true
 * en el .env deja la política en modo aviso (consola) sin bloquear nada.
 */
class SecurityHeaders
{
    private const CSP = [
        'default-src'     => ["'self'"],
        'script-src'      => ["'self'", "'unsafe-inline'", "'unsafe-eval'",
                              'https://cdn.tailwindcss.com', 'https://cdn.jsdelivr.net',
                              'https://cdnjs.cloudflare.com', 'https://js.stripe.com'],
        'style-src'       => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com',
                              'https://cdn.jsdelivr.net', 'https://cdnjs.cloudflare.com'],
        'font-src'        => ["'self'", 'data:', 'https://fonts.gstatic.com',
                              'https://cdn.jsdelivr.net', 'https://cdnjs.cloudflare.com'],
        // Las imágenes de unidades y del chat vienen de varios almacenes (Google
        // Storage, Firebase…) y algunas URL las guarda el admin: cualquier https.
        'img-src'         => ["'self'", 'data:', 'blob:', 'https:'],
        'media-src'       => ["'self'", 'blob:', 'https:'],
        'connect-src'     => ["'self'", 'https://api.stripe.com', 'https://cdn.jsdelivr.net'],
        // Stripe y los documentos que el admin enlaza en el menú del cliente.
        'frame-src'       => ["'self'", 'https:'],
        'worker-src'      => ["'self'", 'blob:'],
        'object-src'      => ["'none'"],
        'base-uri'        => ["'self'"],
        // https: y no sólo 'self': varios envíos acaban en una redirección a
        // Google, Apple o Stripe, y form-action también se aplica a esas.
        'form-action'     => ["'self'", 'https:'],
        'frame-ancestors' => ["'self'"],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $https = $request->isSecure() || str_starts_with((string) config('app.url'), 'https://');
        $csp = collect(self::CSP)->map(fn ($v, $k) => $k.' '.implode(' ', $v))->implode('; ')
             .($https ? '; upgrade-insecure-requests' : '');   // en local (http) rompería todo
        $response->headers->set(
            config('app.csp_report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy',
            $csp
        );

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self "https://js.stripe.com")');
        // Detrás de Traefik isSecure() no siempre se entera del https: se mira
        // también APP_URL (arriba, $https).
        if ($https) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        // PHP anunciaba su versión exacta (X-Powered-By: PHP/8.4.x).
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }
}
