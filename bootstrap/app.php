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
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'role.rt' => \App\Http\Middleware\EnsureUserIsRtStaff::class,
            'role.kelurahan' => \App\Http\Middleware\EnsureUserIsKelurahan::class,
            'role.admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login.hub'));
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => $request->user()?->dashboardRoute() ?? route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
