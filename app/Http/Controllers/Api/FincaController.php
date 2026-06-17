<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FincaRequest;
use App\Http\Resources\FincaResource;
use App\Models\Finca;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Fincas (consumida por la app móvil Ionic).
 *
 * Endpoints (prefijo /api):
 *   GET    /api/fincas               → index   (lista paginada)
 *   POST   /api/fincas               → store   (crear)
 *   GET    /api/fincas/{finca}       → show    (detalle con animales)
 *   PUT    /api/fincas/{finca}       → update  (modificar)
 *   DELETE /api/fincas/{finca}       → destroy
 *
 * Todas requieren `Authorization: Bearer {token}` (Sanctum).
 *
 * Reglas de acceso (delegadas al scope visibleFor del modelo):
 *   - Admin       → todas
 *   - Ganadero    → solo las propias
 *   - Veterinario → solo asignadas (LECTURA — el FincaRequest bloquea escritura)
 */
class FincaController extends Controller
{
    // ==================================================================
    // GET /api/fincas
    // ==================================================================
    public function index(Request $request): JsonResponse
    {
        $fincas = Finca::visibleFor($request->user())
            ->with('usuario')
            ->withCount('animales')
            ->orderBy('nombre')
            ->paginate(20);

        // FincaResource::collection() respeta la paginación y agrega meta/links.
        return FincaResource::collection($fincas)->response();
    }

    // ==================================================================
    // POST /api/fincas
    // ==================================================================
    public function store(FincaRequest $request): JsonResponse
    {
        // FincaRequest::authorize() ya bloqueó al Veterinario (devolvió 403 JSON).
        $datos = $request->validated();

        // Si es ganadero, dueño = él mismo. Si es admin y mandó cedula, la respeta.
        $datos['cedula'] = $request->user()->esAdmin() && $request->filled('cedula')
            ? $request->input('cedula')
            : $request->user()->cedula;

        $finca = Finca::create($datos);

        return FincaResource::make($finca->load('usuario'))
            ->response()
            ->setStatusCode(201);
    }

    // ==================================================================
    // GET /api/fincas/{finca}
    // ==================================================================
    public function show(Request $request, Finca $finca): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $finca);

        $finca->load(['usuario', 'veterinarios'])
            ->loadCount('animales');

        return FincaResource::make($finca)->response();
    }

    // ==================================================================
    // PUT /api/fincas/{finca}
    // ==================================================================
    public function update(FincaRequest $request, Finca $finca): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $finca);

        $datos = $request->validated();

        if ($request->user()->esAdmin() && $request->filled('cedula')) {
            $datos['cedula'] = $request->input('cedula');
        }

        $finca->update($datos);

        return FincaResource::make($finca->load('usuario'))->response();
    }

    // ==================================================================
    // DELETE /api/fincas/{finca}
    // ==================================================================
    public function destroy(Request $request, Finca $finca): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $finca);

        $finca->delete();

        return response()->json(['mensaje' => 'Finca eliminada'], 200);
    }

    // ==================================================================
    // Helpers privados — chequeos finos sobre una instancia específica
    // ==================================================================

    private function autorizarAcceso(Usuario $u, Finca $finca): void
    {
        if (Finca::visibleFor($u)->whereKey($finca->getKey())->exists()) {
            return;
        }

        abort(response()->json(['mensaje' => 'No tenés permiso para ver esta finca'], 403));
    }

    private function autorizarEdicion(Usuario $u, Finca $finca): void
    {
        if ($u->esAdmin()) return;
        if ($u->esGanadero() && $finca->cedula === $u->cedula) return;

        abort(response()->json(['mensaje' => 'No tenés permiso para modificar esta finca'], 403));
    }
}
