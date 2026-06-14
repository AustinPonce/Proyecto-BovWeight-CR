<?php

use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\FincaController;
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
    // apiResource genera solo los 5 endpoints REST (sin create/edit
    // que son las vistas web). El middleware rol asegura que solo
    // los roles correctos puedan llegar a estos controllers.
    Route::apiResource('fincas',   FincaController::class)
        ->middleware('rol:admin,ganadero,veterinario');

    Route::apiResource('animales', AnimalController::class)
        ->parameters(['animales' => 'animal'])
        ->middleware('rol:admin,ganadero,veterinario');

});
