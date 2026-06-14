<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web (sesión + Blade)
|--------------------------------------------------------------------------
| Estas rutas usan el guard 'web' (cookies + sesión). La app móvil consume
| las de routes/api.php con token Sanctum.
*/

// Raíz: si está logueado va al dashboard, si no a login.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ----------------------------------------------------------------------
// Rutas públicas (solo accesibles si NO hay sesión activa)
// ----------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro.mostrar');
    Route::post('/registro',[AuthController::class, 'registrar'])->name('registro');
});

// ----------------------------------------------------------------------
// Rutas protegidas (requieren sesión)
// ----------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard común a todos los roles (cada rol ve sus propios bloques).
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Logout.
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---- Ejemplos de rutas restringidas por rol (se implementarán en Bloque 2) ----
    //
    // Solo administradores pueden gestionar usuarios:
    // Route::get('/admin/usuarios', [UsuarioController::class, 'index'])
    //     ->middleware('rol:admin')
    //     ->name('admin.usuarios');
    //
    // Ganaderos y veterinarios pueden ver fincas (cada uno las suyas):
    // Route::get('/fincas', [FincaController::class, 'index'])
    //     ->middleware('rol:ganadero,veterinario')
    //     ->name('fincas.index');

});
