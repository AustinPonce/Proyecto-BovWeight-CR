<?php

use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\DosisController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\FincaController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PesajeController;
use App\Http\Controllers\Api\RazaController;
use App\Http\Controllers\Api\RecordatorioController;
use App\Http\Controllers\Api\TransaccionController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeterinarioFincaController;
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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// ----------------------------------------------------------------------
// Protegidas (requieren header `Authorization: Bearer {token}`)
// ----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // ---- Sesión y perfil ----
    Route::get('/usuario', fn (Request $r) => $r->user()->load('tipoUsuario'));
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---- Recursos del negocio ----
    // IMPORTANTE: les ponemos prefijo 'api.' al nombre de las rutas
    // (api.fincas.index, api.animales.index, etc.) para que NO choquen
    // con las rutas web del mismo nombre. Sin esto, route('fincas.index')
    // en una vista Blade resolvería a /api/fincas en vez de /fincas.
    Route::apiResource('fincas', FincaController::class)
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

    // ---- Comentarios veterinarios + Recordatorios ----
    Route::prefix('animales/{animal}')->group(function () {
        Route::get('comentarios', [ComentarioController::class, 'index'])->name('api.comentarios.index');
        Route::post('comentarios', [ComentarioController::class, 'store'])->name('api.comentarios.store');
        Route::delete('comentarios/{comentario}', [ComentarioController::class, 'destroy'])->name('api.comentarios.destroy');

        Route::get('recordatorios', [RecordatorioController::class, 'index'])->name('api.recordatorios.index');
        Route::post('recordatorios', [RecordatorioController::class, 'store'])->name('api.recordatorios.store');
        Route::delete('recordatorios/{recordatorio}', [RecordatorioController::class, 'destroy'])->name('api.recordatorios.destroy');
    });

    // ---- Veterinarios de una finca ----
    Route::get('veterinarios/buscar', [VeterinarioFincaController::class, 'buscar'])->name('api.veterinarios.buscar');
    Route::prefix('fincas/{finca}/veterinarios')->group(function () {
        Route::get('/', [VeterinarioFincaController::class, 'index'])->name('api.fincas.veterinarios.index');
        Route::post('/', [VeterinarioFincaController::class, 'store'])->name('api.fincas.veterinarios.store');
        Route::delete('/{cedula}', [VeterinarioFincaController::class, 'destroy'])->name('api.fincas.veterinarios.destroy');
    });

    // ---- Transacciones ----
    Route::apiResource('transacciones', TransaccionController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->names('api.transacciones');

    // ---- Calculadora de dosis ----
    Route::get('medicamentos', [DosisController::class, 'medicamentos'])->name('api.medicamentos.index');
    Route::post('dosis/calcular', [DosisController::class, 'calcular'])->name('api.dosis.calcular');

    // ---- Medicamentos CRUD (solo admin) ----
    Route::post('medicamentos', [MedicamentoController::class, 'store'])
        ->name('api.medicamentos.store')->middleware('rol:admin');
    Route::put('medicamentos/{medicamento}', [MedicamentoController::class, 'update'])
        ->name('api.medicamentos.update')->middleware('rol:admin');
    Route::delete('medicamentos/{medicamento}', [MedicamentoController::class, 'destroy'])
        ->name('api.medicamentos.destroy')->middleware('rol:admin');

    // ---- Razas (lectura todos, CRUD solo admin) ----
    Route::get('razas', [RazaController::class, 'index'])->name('api.razas.index');
    Route::post('razas', [RazaController::class, 'store'])
        ->name('api.razas.store')->middleware('rol:admin');
    Route::put('razas/{raza}', [RazaController::class, 'update'])
        ->name('api.razas.update')->middleware('rol:admin');
    Route::delete('razas/{raza}', [RazaController::class, 'destroy'])
        ->name('api.razas.destroy')->middleware('rol:admin');

    // ---- Gestión de usuarios (solo admin, excluye admins del listado) ----
    Route::get('usuarios', [UsuarioController::class, 'index'])
        ->name('api.usuarios.index')->middleware('rol:admin');
    Route::post('usuarios', [UsuarioController::class, 'store'])
        ->name('api.usuarios.store')->middleware('rol:admin');
    Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])
        ->name('api.usuarios.update')->middleware('rol:admin');
    Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])
        ->name('api.usuarios.destroy')->middleware('rol:admin');
    Route::patch('usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])
        ->name('api.usuarios.toggle-activo')->middleware('rol:admin');

    // ---- Estados de animal ----
    Route::get('estados', [EstadoController::class, 'index'])->name('api.estados.index');

    // ---- Notificaciones del usuario autenticado ----
    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('api.notificaciones.index');

    // ---- Todos los comentarios veterinarios visibles para el usuario ----
    Route::get('comentarios', [ComentarioController::class, 'indexGlobal'])->name('api.comentarios.global');

    // ---- Auditoría (solo admin) ----
    Route::get('admin/auditoria', [AuditoriaController::class, 'index'])
        ->name('api.admin.auditoria.index')->middleware('rol:admin');

});
