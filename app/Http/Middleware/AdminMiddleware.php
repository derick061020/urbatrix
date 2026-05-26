<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Routes a broker is allowed to access inside the admin panel.
     * Brokers see only the "Gestión" section (expedientes, documentos,
     * contratos, transacciones) plus their dashboard and profile.
     */
    private const BROKER_ALLOWED_ROUTES = [
        'admin.crm.dashboard',
        'admin.crm.expedientes',
        'admin.crm.expediente.detalle',
        'admin.crm.documentos',
        'admin.crm.contratos',
        'admin.transactions-report',
        'admin.crm.pagos',
        'admin.crm.budget.save',
        'admin.crm.budget.revert',
        'admin.crm.contract.upload',
        'admin.crm.contract.reply',
        'admin.crm.contract.generate',
        'admin.crm.contract.payment-plan',
        'admin.crm.contract.purchase-promise',
        'admin.crm.document.upload',
        'admin.crm.message.send',
        'admin.profile.edit',
        'admin.profile.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || auth()->user()->role === 'user') {
            return redirect('/');
        }

        if (auth()->user()->role === 'broker') {
            $routeName = $request->route()?->getName();
            if ($routeName && ! in_array($routeName, self::BROKER_ALLOWED_ROUTES, true)) {
                abort(403, 'No tienes acceso a esta sección.');
            }
        }

        return $next($request);
    }
}
