<?php

use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\FincaController;
use App\Http\Controllers\Api\PesajeController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API (token Sanctum + JSON)
|--------------------------------------------------------------------------
| Consumidas por la app móvil Ionic. Prefijo /api se aplica automáticamente.
|
| Convención de respuestas:
|   - 200 OK         → operación exitosa
|   - 201 Created    → recurso creado (store)
|   - 401 Unauth     → falta o expiró el token Sanctum
|   - 403 Forbidden  → token válido pero sin permiso de rol
|   - 404 Not Found  → recurso inexistente
|   - 422 Unprocessable → falló la validación (form request)
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

    // ---- Sesión y perfil ----
    Route::get('/usuario',  fn (Request $r) => $r->user()->load('tipoUsuario'));
    Route::post('/logout',  [AuthController::class, 'logout']);

    // ---- Recursos del negocio ----
    // IMPORTANTE: les ponemos prefijo 'api.' al nombre de las rutas
    // (api.fincas.index, api.animales.index, etc.) para que NO choquen
    // con las rutas web del mismo nombre. Sin esto, route('fincas.index')
    // en una vista Blade resolvería a /api/fincas en vez de /fincas.
    Route::apiResource('fincas',   FincaController::class)
        ->names('api.fincas')
        ->middleware('rol:admin,ganadero,veterinario');

    Route::apiResource('animales', AnimalController::class)
        ->parameters(['animales' => 'animal'])
        ->names('api.animales')
        ->middleware('rol:admin,ganadero,veterinario');

    // Pesajes: index/store/show/destroy (no editan, son inmutables).
    Route::apiResource('pesajes', PesajeController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->names('api.pesajes')
        ->middleware('rol:admin,ganadero,veterinario');

});
