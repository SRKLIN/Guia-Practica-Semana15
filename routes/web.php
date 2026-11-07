<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComercioController;
use App\Http\Controllers\TransaccionController;
use Illuminate\Support\Facades\Route;

// guest = solo para quien NO tiene sesión iniciada
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])
        ->name('registro');

    Route::post('/registro', [AuthController::class, 'registrar'])
        ->name('registro.store');

    Route::get('/login', [AuthController::class, 'mostrarLogin'])
        ->name('login');

    // throttle:5,1 = máximo 5 intentos por minuto
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
});

// auth = solo para quien SÍ tiene sesión. Sin ella, Laravel
// redirige a la ruta llamada 'login'.
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/', function () {
        return redirect()->route('comercios.index');
    });

    Route::get('/comercios', [ComercioController::class, 'index'])
        ->name('comercios.index');

    Route::get('/comercios/{comercio}', [ComercioController::class, 'show'])
        ->name('comercios.show');

    // Formulario de nueva transacción El POST no lleva {comercio}

    Route::get('/comercios/{comercio}/transacciones/nueva',
        [TransaccionController::class, 'create']
    )->name('transacciones.create');

    Route::post('/transacciones',
        [TransaccionController::class, 'store']
    )->name('transacciones.store');

    // Editar y eliminar. PUT y DELETE
    Route::get('/transacciones/{transaccion}/editar',
        [TransaccionController::class, 'edit']
    )->name('transacciones.edit');

    Route::put('/transacciones/{transaccion}',
        [TransaccionController::class, 'update']
    )->name('transacciones.update');

    Route::delete('/transacciones/{transaccion}',
        [TransaccionController::class, 'destroy']
    )->name('transacciones.destroy');

    // Cambiar solo el estado
    Route::patch('/transacciones/{transaccion}/mover/{estado}',
        [TransaccionController::class, 'moverEstado']
    )->name('transacciones.mover');

    // Rutas de práctica
    Route::get('/practica/formulario-demo', function () {
        return view('practica.formulario_demo');
    });

    Route::post('/practica/enviar', function () {
        return 'Formulario recibido correctamente.';
    });
});

// Ruta del reto (el mismo Form Request en otro método). Ya probada,
// se deja comentada como pide la guía.
// Route::post('/practica/duplicar',
//     [TransaccionController::class, 'duplicarUltima']
// )->name('practica.duplicar');
