<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

    $exceptions->shouldRenderJsonWhen(
        fn (Request $request, Throwable $e) =>
            $request->is('api/*') || $request->expectsJson()
    );
    
    // Handle JWT-related exceptions centrally.
    $exceptions->render(function (
        JWTException $e,
        Request $request
    ) {
        if (! $request->is('api/*')) {
            return null;
        }

        return response()->json([
            'message' => 'Unauthenticated.',
            'error' => 'invalid_token',
        ], 401);
    });

})->create();
