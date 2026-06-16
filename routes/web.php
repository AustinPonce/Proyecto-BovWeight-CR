<?php

use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\ReporteGlobalController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ComentarioVeterinarioController;
use App\Http\Controllers\DosisController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FincaController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\VeterinarioFincaController;
use Illuminate\Support\Facades\Route;

// Raíz: si está logueado va al dashboard, si no a login.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ----------------------------------------------------------------------
// RECUPERACIÓN DE CONTRASEÑA (rutas públicas)
// ----------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro.mostrar');
    Route::post('/registro',[AuthController::class, 'registrar'])->name('registro');

    // Olvidé mi contraseña
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'mostrar'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'enviar'])->name('password.email');

    // Restablecer contraseña
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'mostrar'])->name('password.reset');
    Route::post('/reset-password',        [ResetPasswordController::class, 'resetear'])->name('password.update');
});

// ----------------------------------------------------------------------
// RUTAS PROTEGIDAS (requieren sesión)
// ----------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ------------------------------------------------------------------
    // FINCAS
    // ------------------------------------------------------------------
    Route::resource('fincas', FincaController::class)
        ->middleware('rol:admin,ganadero,veterinario');

    // Asignación de veterinarios a una finca
    Route::middleware('rol:admin,ganadero')->group(function () {
        Route::post('fincas/{finca}/veterinarios',           [VeterinarioFincaController::class, 'store'])
            ->name('fincas.veterinarios.store');
        Route::delete('fincas/{finca}/veterinarios/{cedula}', [VeterinarioFincaController::class, 'destroy'])
            ->name('fincas.veterinarios.destroy');
    });

    // Búsqueda de veterinarios (AJAX — disponible para admin y ganadero)
    Route::get('/veterinarios/buscar', [VeterinarioFincaController::class, 'buscar'])
        ->middleware('rol:admin,ganadero')
        ->name('veterinarios.buscar');

    // ------------------------------------------------------------------
    // ANIMALES
    // ------------------------------------------------------------------
    Route::resource('animales', AnimalController::class)
        ->parameters(['animales' => 'animal'])
        ->middleware('rol:admin,ganadero,veterinario');

    // Comentarios del veterinario en un animal
    Route::get('animales/{animal}/comentarios',        [ComentarioVeterinarioController::class, 'index'])
        ->middleware('rol:admin,ganadero,veterinario')
        ->name('animales.comentarios');

    Route::post('animales/{animal}/comentarios',       [ComentarioVeterinarioController::class, 'store'])
        ->middleware('rol:veterinario')
        ->name('animales.comentarios.store');

    Route::delete('animales/{animal}/comentarios/{comentario}', [ComentarioVeterinarioController::class, 'destroy'])
        ->middleware('rol:admin,veterinario')
        ->name('animales.comentarios.destroy');

    // ------------------------------------------------------------------
    // PESAJES
    // ------------------------------------------------------------------
    Route::resource('pesajes', PesajeController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->middleware('rol:admin,ganadero,veterinario');

    // ------------------------------------------------------------------
    // EXPORTACIONES (PDF y CSV)
    // ------------------------------------------------------------------
    Route::middleware('rol:admin,ganadero,veterinario')->prefix('exportar')->name('export.')->group(function () {
        Route::get('pesajes/pdf', [ExportController::class, 'pesajesPdf'])->name('pesajes.pdf');
        Route::get('pesajes/csv', [ExportController::class, 'pesajesCsv'])->name('pesajes.csv');
        Route::get('animales/pdf',[ExportController::class, 'animalesPdf'])->name('animales.pdf');
        Route::get('animales/csv',[ExportController::class, 'animalesCsv'])->name('animales.csv');
    });

    // ------------------------------------------------------------------
    // CALCULADORA DE DOSIS
    // ------------------------------------------------------------------
    Route::match(['get', 'post'], '/dosis', [DosisController::class, 'calcular'])
        ->middleware('rol:veterinario,admin,ganadero')
        ->name('dosis.calcular');

    // ------------------------------------------------------------------
    // TRANSACCIONES (compradores / vendedores)
    // ------------------------------------------------------------------
    Route::middleware('rol:admin,ganadero')->group(function () {
        Route::get('transacciones',        [TransaccionController::class, 'index'])->name('transacciones.index');
        Route::get('transacciones/create', [TransaccionController::class, 'create'])->name('transacciones.create');
        Route::post('transacciones',       [TransaccionController::class, 'store'])->name('transacciones.store');
        Route::get('transacciones/pdf',    [TransaccionController::class, 'exportarPdf'])->name('transacciones.pdf');
        Route::get('transacciones/csv',    [TransaccionController::class, 'exportarCsv'])->name('transacciones.csv');
    });

    // ------------------------------------------------------------------
    // ADMIN — Gestión de usuarios, catálogos y reportes globales
    // ------------------------------------------------------------------
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {

        // Usuarios
        Route::get('usuarios',              [UsuarioAdminController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/create',       [UsuarioAdminController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios',             [UsuarioAdminController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/edit',   [UsuarioAdminController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}',        [UsuarioAdminController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}',     [UsuarioAdminController::class, 'destroy'])->name('usuarios.destroy');

        // Catálogos
        Route::get('catalogos',                          [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::post('catalogos/medicamentos',            [CatalogoController::class, 'storeMedicamento'])->name('catalogos.medicamentos.store');
        Route::put('catalogos/medicamentos/{medicamento}',[CatalogoController::class, 'updateMedicamento'])->name('catalogos.medicamentos.update');
        Route::delete('catalogos/medicamentos/{medicamento}',[CatalogoController::class, 'destroyMedicamento'])->name('catalogos.medicamentos.destroy');
        Route::post('catalogos/razas',              [CatalogoController::class, 'storeRaza'])->name('catalogos.razas.store');
        Route::put('catalogos/razas/{raza}',        [CatalogoController::class, 'updateRaza'])->name('catalogos.razas.update');
        Route::delete('catalogos/razas/{raza}',     [CatalogoController::class, 'destroyRaza'])->name('catalogos.razas.destroy');
        Route::post('catalogos/estados',            [CatalogoController::class, 'storeEstado'])->name('catalogos.estados.store');
        Route::delete('catalogos/estados/{estado}', [CatalogoController::class, 'destroyEstado'])->name('catalogos.estados.destroy');

        // Reportes globales
        Route::get('reportes',     [ReporteGlobalController::class, 'index'])->name('reportes.index');
        Route::get('reportes/pdf', [ReporteGlobalController::class, 'exportarPdf'])->name('reportes.pdf');
        Route::get('reportes/csv', [ReporteGlobalController::class, 'exportarCsv'])->name('reportes.csv');
    });
});
