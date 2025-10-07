<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // Trust proxies for things like load balancers and proxies
        \App\Http\Middleware\TrustProxies::class,
        // Handle CORS
        \Fruitcake\Cors\HandleCors::class,
        // Prevent requests when the app is in maintenance mode
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // Validate size of POST requests
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // Trim input strings
        \App\Http\Middleware\TrimStrings::class,
        // Convert empty strings to null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // Encrypt cookies in web requests
            \App\Http\Middleware\EncryptCookies::class,
            // Add queued cookies to response
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // Start session for web
            \Illuminate\Session\Middleware\StartSession::class,
            // Share errors from session to views
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // CSRF protection
            \App\Http\Middleware\VerifyCsrfToken::class,
            // Substitute route bindings
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // Apply API rate limiting
            'throttle:api',
            // Substitute route bindings
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     * Note: In Laravel 11, middleware aliases are configured in bootstrap/app.php
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // Moved to bootstrap/app.php in Laravel 11
    ];
}