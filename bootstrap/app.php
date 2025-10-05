<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware globales - Deshabilitar headers de seguridad temporalmente para desarrollo
        // $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->append(\App\Http\Middleware\AuditMiddleware::class);
        
        // Registrar middleware de seguridad personalizados
        $middleware->alias([
            'auth.api' => \App\Http\Middleware\AuthenticateApiSimple::class,
            'rate.limit' => \App\Http\Middleware\RateLimitingMiddleware::class,
            'csrf.api' => \App\Http\Middleware\CsrfApiProtection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
