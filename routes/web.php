<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FincaController;
use App\Http\Controllers\PesajeController;
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

    // ------------------------------------------------------------------
    // ANIMALES (CRUD)
    // ------------------------------------------------------------------
    // Mismos roles que fincas. El control fino (scope por finca propia,
    // veterinario sin escritura) se hace dentro del AnimalController.
    Route::resource('animales', AnimalController::class)
        ->parameters(['animales' => 'animal'])    // {animal} en vez de {animale}
        ->middleware('rol:admin,ganadero,veterinario');

    // ------------------------------------------------------------------
    // PESAJES (CRUD parcial — sin edit/update, los pesajes no se editan)
    // ------------------------------------------------------------------
    // Los pesajes son inmutables: si está mal, se borra y se vuelve a registrar.
    // Eso es estándar en sistemas zootécnicos por trazabilidad.
    Route::resource('pesajes', PesajeController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->middleware('rol:admin,ganadero,veterinario');

    // ------------------------------------------------------------------
    // STUBS — vistas planeadas, implementación en próximas fases.
    // Estas rutas existen para que el dashboard no tire "Route not defined".
    // Cada feature se reemplaza con su controller real cuando se complete.
    // ------------------------------------------------------------------
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/usuarios', 'proximamente', [
            'titulo'      => 'Gestión de usuarios',
            'descripcion' => 'Crear, editar y desactivar cuentas del sistema.',
            'fase'        => 'Fase 3 (item #8)',
        ])->name('usuarios.index');

        Route::view('/catalogos', 'proximamente', [
            'titulo'      => 'Catálogos del sistema',
            'descripcion' => 'Razas, estados, tipos de pesaje y medicamentos.',
            'fase'        => 'Fase 3 (item #9)',
        ])->name('catalogos.index');

        Route::view('/reportes', 'proximamente', [
            'titulo'      => 'Reportes globales',
            'descripcion' => 'Estadísticas de todas las fincas y pesajes.',
            'fase'        => 'Fase 4 (item #12)',
        ])->name('reportes.index');
    });

    Route::middleware('rol:veterinario,admin')->group(function () {
        Route::view('/dosis', 'proximamente', [
            'titulo'      => 'Calculadora de dosis',
            'descripcion' => 'Calcular dosis de medicamentos según peso del animal.',
            'fase'        => 'Fase 3 (item #6)',
        ])->name('dosis.calcular');
    });

});
