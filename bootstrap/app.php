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
        // Route middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'customer.auth' => \App\Http\Middleware\CustomerAuth::class,
        ]);

        // Exclude customer authentication routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'customer/*',
        ]);

        // Apply active-user enforcement to all web requests
        if (method_exists($middleware, 'web')) {
            $middleware->web([
                \App\Http\Middleware\EnsureUserIsActive::class,
            ]);
        } else {
            // Fallback for older signatures: append globally
            $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
