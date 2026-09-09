<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Both deploy targets (Render, Vercel) front every request with a
        // reverse proxy. Trusting it makes $request->ip() the real visitor
        // IP — without this, the contact-form throttle keys on the EDGE IP
        // and the whole site shares one 5-per-minute budget.
        $middleware->trustProxies(at: '*');

        // Locale resolution for the bilingual (EN/ES) storefront. Must run after
        // StartSession so a stored preference is honored. See ADR-003.
        $middleware->web(append: [
            SetSecurityHeaders::class,
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
