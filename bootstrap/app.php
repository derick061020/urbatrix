<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        // SetLocale corre en todas las rutas web (resuelve sesión/cookie -> App::setLocale).
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        // SignNow posts to this webhook with its own signature; exempt from CSRF.
        $middleware->validateCsrfTokens(except: [
            'webhooks/signnow',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
