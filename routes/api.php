<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\TransaccionApiController;
use App\Http\Resources\ComercioResource;
use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Rutas de la API. Llevan el prefijo /api y no usan sesión ni cookies:
| quien quiera entrar manda su token en el encabezado Authorization.
*/

// Única ruta pública: sin ella nadie podría obtener un token.
// throttle:5,1 = máximo 5 intentos de login por minuto.
Route::post('/login', [AuthApiController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/yo', [AuthApiController::class, 'yo']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Reto: dispositivos con sesión abierta
    Route::get('/tokens', [AuthApiController::class, 'tokens']);
    Route::delete('/tokens/{id}', [AuthApiController::class, 'revocarToken']);

    // Solo los comercios del dueño del token.
    // El usuario sale del token, nunca de un parámetro del cliente.
    Route::get('/comercios', function (Request $request) {
        return ComercioResource::collection(
            $request->user()->comercios()->orderBy('nombre_comercio')->get()
        );
    });

    // Listar lo propio no basta: también hay que revisar el id pedido
    Route::get('/comercios/{comercio}', function (Request $request, Comercio $comercio) {
        abort_unless($comercio->user_id === $request->user()->id, 404);

        return new ComercioResource($comercio->load('transacciones'));
    });

    // apiResource crea 5 rutas: index, store, show, update y destroy.
    // No crea create ni edit porque una API no tiene formularios.
    //
    // parameters() renombra el parámetro: por su cuenta Laravel lo llama
    // {transaccione} y entonces no coincide con el $transaccion del
    // controlador, así que el modelo nunca se carga.
    //
    // names() es obligatorio aquí: por defecto estas rutas se llamarían
    // transacciones.store, transacciones.index..., los mismos nombres que
    // las rutas web de la Semana 11. El último registrado gana, así que
    // route('transacciones.store') en las vistas Blade apuntaría a la API
    // y el formulario del navegador dejaría de funcionar.
    Route::apiResource('transacciones', TransaccionApiController::class)
        ->parameters(['transacciones' => 'transaccion'])
        ->names('api.transacciones');
});
