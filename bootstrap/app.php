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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\LogAccess::class);
        $middleware->append(\App\Http\Middleware\ReplaceKutenMiddleware::class);

        // $middleware->append(\App\Http\Middleware\EncryptCookies::class);
        // $middleware->append(\Illuminate\Session\Middleware\StartSession::class);
        // $middleware->append(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);
        // $middleware->append(\Illuminate\View\Middleware\ShareErrorsFromSession::class);
        // $middleware->append(\App\Http\Middleware\VerifyCsrfToken::class);
        // $middleware->append(\App\Http\Middleware\CheckDomain::class);
        // $middleware->append(\Illuminate\Routing\Middleware\SubstituteBindings::class);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

