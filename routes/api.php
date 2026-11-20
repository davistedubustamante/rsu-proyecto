<?php

use App\Http\Controllers\Api\Ciudadano\AccesoController as AccesoCiudadano;
use App\Http\Controllers\Api\Ciudadano\ProgramacionController as ProgramacionCiudadano;
use App\Http\Controllers\Api\Ciudadano\RecorridoController as RecorridoCiudadano;
use App\Http\Controllers\Api\Ciudadano\ZonaController as ZonaCiudadano;
use App\Http\Controllers\Api\Conductor\AccesoController;
use App\Http\Controllers\Api\Conductor\ProgramacionController;
use App\Http\Controllers\Api\Conductor\RecorridoController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        DB::select('SELECT 1');

        return response()->json([
            'status' => 'ok',
            'database' => 'ok',
        ]);
    } catch (\Throwable) {
        return response()->json([
            'status' => 'degraded',
            'database' => 'error',
        ], 503);
    }
});

/*
| API de la app móvil del conductor (Avance 6). Todas las rutas quedan bajo /api/conductor.
| Autenticación: token de Sanctum en la cabecera «Authorization: Bearer <token>».
*/

Route::prefix('conductor')->name('api.conductor.')->group(function () {
    Route::post('login', [AccesoController::class, 'login'])->middleware('throttle:6,1')->name('login');

    Route::middleware(['auth:sanctum', 'conductor', 'throttle:120,1'])->group(function () {
        Route::get('perfil', [AccesoController::class, 'perfil'])->name('perfil');
        Route::post('logout', [AccesoController::class, 'logout'])->name('logout');

        Route::get('programaciones', [ProgramacionController::class, 'index'])->name('programaciones.index');
        Route::get('programaciones/hoy', [ProgramacionController::class, 'hoy'])->name('programaciones.hoy');
        Route::get('programaciones/{programacion}', [ProgramacionController::class, 'show'])->whereNumber('programacion')->name('programaciones.show');

        Route::post('programaciones/{programacion}/recorrido', [RecorridoController::class, 'iniciar'])->whereNumber('programacion')->name('recorridos.iniciar');
        Route::get('recorridos', [RecorridoController::class, 'index'])->name('recorridos.index');
        Route::get('recorridos/actual', [RecorridoController::class, 'actual'])->name('recorridos.actual');
        Route::get('recorridos/{recorrido}', [RecorridoController::class, 'show'])->whereNumber('recorrido')->name('recorridos.show');
        Route::post('recorridos/{recorrido}/finalizar', [RecorridoController::class, 'finalizar'])->whereNumber('recorrido')->name('recorridos.finalizar');
    });

    // El GPS manda un punto cada 5 s (o un lote si estuvo sin señal): límite propio por minuto.
    Route::middleware(['auth:sanctum', 'conductor', 'throttle:60,1'])
        ->post('recorridos/{recorrido}/puntos', [RecorridoController::class, 'puntos'])->whereNumber('recorrido')->name('recorridos.puntos');
});

/*
| API de la app móvil del ciudadano (Avance 7). Todas las rutas quedan bajo /api/ciudadano.
| El token del ciudadano (habilidad «ciudadano») no sirve en la API del conductor ni al revés.
*/

Route::prefix('ciudadano')->name('api.ciudadano.')->group(function () {
    Route::post('registro', [AccesoCiudadano::class, 'registro'])->middleware('throttle:10,1')->name('registro');
    Route::post('login', [AccesoCiudadano::class, 'login'])->middleware('throttle:6,1')->name('login');

    // Públicas: se usan al registrarse, cuando todavía no hay sesión.
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('zonas', [ZonaCiudadano::class, 'index'])->name('zonas.index');
        Route::get('zonas/ubicacion', [ZonaCiudadano::class, 'ubicacion'])->name('zonas.ubicacion');
    });

    Route::middleware(['auth:sanctum', 'ciudadano', 'throttle:120,1'])->group(function () {
        Route::get('perfil', [AccesoCiudadano::class, 'perfil'])->name('perfil');
        Route::put('perfil/zona', [AccesoCiudadano::class, 'cambiarZona'])->name('perfil.zona');
        Route::post('logout', [AccesoCiudadano::class, 'logout'])->name('logout');

        Route::get('programaciones', [ProgramacionCiudadano::class, 'index'])->name('programaciones.index');
        Route::get('programaciones/{programacion}', [ProgramacionCiudadano::class, 'show'])->whereNumber('programacion')->name('programaciones.show');
        Route::get('programaciones/{programacion}/seguimiento', [ProgramacionCiudadano::class, 'seguimiento'])->whereNumber('programacion')->name('programaciones.seguimiento');

        Route::get('recorridos', [RecorridoCiudadano::class, 'index'])->name('recorridos.index');
        Route::get('recorridos/{recorrido}', [RecorridoCiudadano::class, 'show'])->whereNumber('recorrido')->name('recorridos.show');
        Route::post('recorridos/{recorrido}/calificacion', [RecorridoCiudadano::class, 'calificar'])->whereNumber('recorrido')->name('recorridos.calificar');
    });
});
