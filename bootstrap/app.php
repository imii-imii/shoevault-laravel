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
            'customer.session' => \App\Http\Middleware\CustomerSession::class,
            'staff.session' => \App\Http\Middleware\StaffSession::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'operating.hours' => \App\Http\Middleware\OperatingHoursMiddleware::class,
            'session.lifetime' => \App\Http\Middleware\SessionLifetimeMiddleware::class,
        ]);

        // Exclude customer authentication routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'customer/*',
        ]);

        // Prepend session-detection middleware BEFORE session starts
        // This detects whether this is a customer or staff route and sets appropriate cookie name
        $middleware->prepend(\App\Http\Middleware\DetectSessionType::class);

        // Apply active-user enforcement and session lifetime management to all web requests
        if (method_exists($middleware, 'web')) {
            $middleware->web([
                \App\Http\Middleware\EnsureUserIsActive::class,
                \App\Http\Middleware\SessionLifetimeMiddleware::class,
            ]);
        } else {
            // Fallback for older signatures: append globally
            $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);
            $middleware->append(\App\Http\Middleware\SessionLifetimeMiddleware::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
