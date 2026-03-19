<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use \App\Http\Middleware\EnsureBusinessAndOutlet;
use \App\Http\Middleware\EnsureActiveShift;
use \Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'ensure.business.outlet' => EnsureBusinessAndOutlet::class,
            'ensure.shift.active' => EnsureActiveShift::class,
        ]);

        $middleware->trustProxies(
            at: '*',
            headers:
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
