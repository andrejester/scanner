<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // \App\Http\Middleware\TwoFactorMiddleware::class,
            // \App\Http\Middleware\Visitors::class,

        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\Role::class,
            'checkUserRole' => \App\Http\Middleware\CheckUserRoleLog::class,
            '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'env' => \App\Http\Middleware\CheckDevelopmentEnvironment::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
