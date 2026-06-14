<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FincaController;
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

    // ------------------------------------------------------------------
    // FINCAS (CRUD)
    // ------------------------------------------------------------------
    // Admin, Ganadero y Veterinario pueden ENTRAR a la sección (el filtrado
    // por dueño/asignación se hace dentro del controller).
    // El veterinario ve la sección pero el controller lo limita a lectura
    // y le devuelve solo las fincas a las que está asignado.
    //
    // Route::resource genera 7 rutas RESTful:
    //   GET    /fincas              → index
    //   GET    /fincas/create       → create
    //   POST   /fincas              → store
    //   GET    /fincas/{finca}      → show
    //   GET    /fincas/{finca}/edit → edit
    //   PUT    /fincas/{finca}      → update
    //   DELETE /fincas/{finca}      → destroy
    Route::resource('fincas', FincaController::class)
        ->middleware('rol:admin,ganadero,veterinario');

});
