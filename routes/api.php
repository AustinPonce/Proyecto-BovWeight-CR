<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API (token Sanctum + JSON)
|--------------------------------------------------------------------------
| Estas rutas son las que consume la app móvil Ionic (Austin) y cualquier
| otro cliente HTTP. Todas devuelven JSON.
|
| El prefijo /api se aplica automáticamente. Es decir:
|   POST /api/login         (no /login)
|   GET  /api/usuario       (no /usuario)
*/

// ----------------------------------------------------------------------
// Públicas (sin token)
// ----------------------------------------------------------------------
Route::post('/registro', [AuthController::class, 'registrar']);
Route::post('/login',    [AuthController::class, 'login']);

// ----------------------------------------------------------------------
// Protegidas (requieren header `Authorization: Bearer {token}`)
// ----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Devuelve el usuario autenticado actual (útil para el splash de la app móvil).
    Route::get('/usuario', fn (Request $r) => $r->user()->load('tipoUsuario'));

    Route::post('/logout', [AuthController::class, 'logout']);

    // ---- Endpoints de negocio (Bloque 2 y 3) ----
    //
    // Route::apiResource('fincas', FincaController::class)
    //     ->middleware('rol:ganadero,veterinario,admin');
    //
    // Route::post('/pesajes/estimar', [PesajeController::class, 'estimar'])
    //     ->middleware('rol:ganadero,admin');

});
