<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autorización por rol.
 *
 * Uso típico en rutas:
 *   Route::get('/admin', ...)->middleware(['auth', 'rol:admin']);
 *   Route::get('/pesajes', ...)->middleware(['auth', 'rol:ganadero,veterinario']);
 *
 * Acepta los slugs: admin, ganadero, veterinario.
 *
 * Si el usuario no está autenticado → 401 (API) o redirect a /login (web).
 * Si está autenticado pero no tiene el rol → 403 (Forbidden).
 */
class RoleMiddleware
{
    /**
     * Mapeo slug → id_tipo_usuario (debe coincidir con CatalogosSeeder).
     * Centralizado aquí para no repetir números mágicos en las rutas.
     */
    private const MAPA_ROLES = [
        'admin'        => Usuario::ROL_ADMIN,
        'ganadero'     => Usuario::ROL_GANADERO,
        'veterinario'  => Usuario::ROL_VETERINARIO,
    ];

    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        $user = $request->user();

        // 1) No autenticado: el middleware 'auth' debería haberlo cortado antes,
        //    pero por defensa devolvemos respuesta consistente.
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'No autenticado'], 401)
                : redirect()->route('login');
        }

        // 2) Convertimos los slugs ('admin','ganadero') a ids numéricos.
        $idsPermitidos = array_map(
            fn (string $slug) => self::MAPA_ROLES[$slug]
                ?? abort(500, "Rol desconocido en middleware: {$slug}"),
            $rolesPermitidos
        );

        // 3) Chequeo: ¿el rol del usuario está en la lista permitida?
        if (! $user->tieneRol(...$idsPermitidos)) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'No tienes permiso para esta acción'], 403)
                : abort(403, 'No tienes permiso para ver esta página.');
        }

        return $next($request);
    }
}
