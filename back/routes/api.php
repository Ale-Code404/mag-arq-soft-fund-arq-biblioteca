<?php

use App\Infraestructura\Http\Controladores\AuthController;
use App\Infraestructura\Http\Controladores\CapacidadController;
use App\Infraestructura\Http\Controladores\ConfiguracionController;
use App\Infraestructura\Http\Controladores\EjemplarController;
use App\Infraestructura\Http\Controladores\EstanteriaController;
use App\Infraestructura\Http\Controladores\LectorController;
use App\Infraestructura\Http\Controladores\LibroController;
use App\Infraestructura\Http\Controladores\MultaController;
use App\Infraestructura\Http\Controladores\PrestamoController;
use App\Infraestructura\Http\Controladores\SaludController;
use App\Infraestructura\Http\Controladores\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('salud', SaludController::class);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/yo', [AuthController::class, 'yo']);

    // CU-01
    Route::middleware('permiso:ESTANTERIA_GESTIONAR')->group(function () {
        Route::get('estanterias', [EstanteriaController::class, 'index']);
        Route::post('estanterias', [EstanteriaController::class, 'store']);
    });

    // CU-02
    Route::post('libros', [LibroController::class, 'store'])->middleware('permiso:LIBRO_REGISTRAR');

    // CU-03 y CU-04
    Route::middleware('permiso:EJEMPLAR_GESTIONAR')->group(function () {
        Route::post('libros/{isbn}/ejemplares', [EjemplarController::class, 'store']);
        Route::put('ejemplares/{codigo}/ubicacion', [EjemplarController::class, 'ubicar']);
    });

    // CU-05
    Route::get('capacidad', CapacidadController::class)->middleware('permiso:CAPACIDAD_CONSULTAR');

    // CU-06
    Route::post('lectores', [LectorController::class, 'store'])->middleware('permiso:LECTOR_REGISTRAR');

    // CU-07, CU-08 y CU-09 (con CU-14 y CU-15)
    Route::middleware('permiso:PRESTAMO_GESTIONAR')->group(function () {
        Route::post('prestamos', [PrestamoController::class, 'store']);
        Route::post('prestamos/{codigoEjemplar}/renovacion', [PrestamoController::class, 'renovar']);
        Route::post('devoluciones', [PrestamoController::class, 'devolver']);
    });

    // CU-10
    Route::post('multas/{id}/pago', [MultaController::class, 'pagar'])
        ->whereNumber('id')
        ->middleware('permiso:MULTA_REGISTRAR_PAGO');

    // CU-11
    Route::get('lectores/{identificacion}/historial', [LectorController::class, 'historial'])
        ->middleware('permiso:PRESTAMO_CONSULTAR');

    // CU-12
    Route::middleware('permiso:USUARIO_GESTIONAR')->group(function () {
        Route::post('usuarios', [UsuarioController::class, 'store']);
        Route::put('usuarios/{id}', [UsuarioController::class, 'update'])->whereNumber('id');
        Route::put('usuarios/{id}/roles', [UsuarioController::class, 'roles'])->whereNumber('id');
    });

    // CU-13
    Route::middleware('permiso:CONFIGURACION_MODIFICAR')->group(function () {
        Route::get('configuracion', [ConfiguracionController::class, 'show']);
        Route::put('configuracion', [ConfiguracionController::class, 'update']);
    });
});
