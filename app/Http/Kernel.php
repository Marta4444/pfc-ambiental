<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     */
    protected $middleware = [
        // Puedes agregar aquí middleware global si los necesitas
    ];

    /**
     * Middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // Laravel Breeze ya maneja la autenticación y CSRF internamente
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route middleware.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class, // viene con Breeze desde vendor
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class, // viene con Breeze
        'admin' => \App\Http\Middleware\AdminMiddleware::class, // tu middleware de admin
    ];
}
