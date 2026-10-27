<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Js;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mete el salvavidas (botón flotante de tickets, public/salvavidas) antes del
 * </body> de todas las páginas HTML: web pública, login, checkout, panel del
 * cliente, del broker y del admin. Así no hay que acordarse de añadirlo a cada
 * layout ni a las páginas sueltas que no heredan de ninguno.
 *
 * No entra en lo que se pinta dentro de un iframe (los documentos del menú del
 * cliente) ni en respuestas que no sean HTML.
 */
class InjectSalvavidas
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('company.salvavidas', true)
            || ! $response instanceof IlluminateResponse
            || $request->expectsJson()
            || $request->header('Sec-Fetch-Dest') === 'iframe'
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        $pos = is_string($html) ? strripos($html, '</body>') : false;
        if ($pos === false || str_contains($html, 'id="salvavidas-cfg"')) {
            return $response;
        }

        $user = $request->user();
        $cfg = [
            'csrf'   => csrf_token(),
            'auth'   => (bool) $user,
            'lang'   => str_starts_with(app()->getLocale(), 'en') ? 'en' : 'es',
            'accent' => config('company.accent', '#2b2b1a'),
            'brand'  => config('company.project'),
            'urls'   => [
                'store'  => route('salvavidas.store'),
                'mine'   => route('salvavidas.mine'),
                'unread' => route('salvavidas.unread'),
                'lib'    => asset('salvavidas/html2canvas-pro.min.js'),
            ],
        ];
        $v = @filemtime(public_path('salvavidas/salvavidas.js'));
        $tag = '<script id="salvavidas-cfg">window.SALVAVIDAS='.Js::from($cfg).';</script>'
             .'<link rel="stylesheet" href="'.asset('salvavidas/salvavidas.css').'?v='.@filemtime(public_path('salvavidas/salvavidas.css')).'">'
             .'<script src="'.asset('salvavidas/salvavidas.js').'?v='.$v.'" defer></script>';

        $response->setContent(substr_replace($html, $tag, $pos, 0));
        $response->headers->remove('Content-Length');

        return $response;
    }
}
