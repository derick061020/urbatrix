<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrokerMiddleware
{
    /**
     * Permite el portal /broker a usuarios con rol "broker".
     * El admin también puede entrar (vista previa).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login');
        }

        if ($user->role !== 'broker' && ! $user->is_admin) {
            abort(403, 'Acceso exclusivo para brokers.');
        }

        return $next($request);
    }
}
