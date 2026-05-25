<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LastActivity;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            LastActivity::class,
        ]);

        $middleware->api(prepend: [
            ThrottleRequests::class.':60,1',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->trustProxies(at: [
            '192.168.0.0/16',
            '10.0.0.0/8',
            '172.16.0.0/12',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
