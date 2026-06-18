<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Auditoría (app móvil Ionic — solo admin).
 *
 * GET /api/admin/auditoria   → listado paginado con filtros
 *
 * Parámetros opcionales:
 *   accion   — filtra por acción (login, crear, etc.)
 *   modulo   — filtra por módulo (auth, fincas, etc.)
 *   desde    — fecha inicio (Y-m-d)
 *   hasta    — fecha fin    (Y-m-d)
 *   page     — número de página (default 1)
 */
class AuditoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Auditoria::with('usuario:cedula,nombre')
            ->deModulo($request->input('modulo'))
            ->entreFechas($request->input('desde'), $request->input('hasta'))
            ->conBusqueda($request->input('buscar'))
            ->orderByDesc('created_at');

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        $paginado = $query->paginate(20);

        $data = collect($paginado->items())->map(fn ($r) => [
            'id_auditoria'   => $r->id_auditoria,
            'cedula_usuario' => $r->cedula_usuario,
            'usuario'        => $r->usuario?->nombre,
            'accion'         => $r->accion,
            'modulo'         => $r->modulo,
            'descripcion'    => $r->descripcion,
            'ip'             => $r->ip,
            'created_at'     => $r->created_at?->toISOString(),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $paginado->total(),
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
            ],
        ]);
    }
}
