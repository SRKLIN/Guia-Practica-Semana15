<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // En /api siempre se responde JSON, aunque el cliente olvide
        // el encabezado Accept. Antes devolvía una redirección o HTML.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Una falla de base de datos es culpa del servidor: al cliente
        // solo un mensaje genérico, y el detalle completo al log.
        // Con APP_DEBUG=true se vería el SQL, la tabla y rutas internas.
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'No se pudo guardar la información. Intenta de nuevo más tarde.',
                ], 500);
            }
        });
    })->create();
