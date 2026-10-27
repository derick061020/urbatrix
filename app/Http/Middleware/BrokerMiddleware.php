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
            abort(403, __('Acceso exclusivo para brokers.'));
        }

        // Quien se registró solo como broker (pendiente de verificar) rellena
        // primero la ficha de alta; los que da de alta el admin ya están aprobados.
        if ($user->role === 'broker' && ! $user->is_admin
            && in_array($user->verification_status ?? null, ['pending', 'rejected'], true)
            && ! $request->routeIs('broker.alta', 'broker.alta.store')
            && ! optional($user->brokerProfile)->isSubmitted()) {
            return redirect()->route('broker.alta');
        }

        return $next($request);
    }
}
