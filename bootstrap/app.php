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
    // Default Broadcast::routes() guards /broadcasting/auth with the "web" (session) middleware —
    // the mobile app authenticates with a Sanctum bearer token instead, so it needs its own group.
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        attributes: ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias(['admin' => \App\Http\Middleware\EnsureUserIsAdmin::class]);
        $middleware->redirectUsersTo('/admin');
        $middleware->redirectGuestsTo('/signin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
