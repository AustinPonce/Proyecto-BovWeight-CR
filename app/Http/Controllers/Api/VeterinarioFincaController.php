<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finca;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de asignación de veterinarios a fincas (app móvil Ionic).
 *
 * GET    /api/veterinarios/buscar             → buscar por nombre o cédula
 * GET    /api/fincas/{finca}/veterinarios     → lista de vets asignados
 * POST   /api/fincas/{finca}/veterinarios     → asignar vet
 * DELETE /api/fincas/{finca}/veterinarios/{cedula} → desasignar vet
 */
class VeterinarioFincaController extends Controller
{
    public function buscar(Request $request): JsonResponse
    {
        $term = $request->input('q', '');

        $vets = Usuario::where('id_tipo_usuario', Usuario::ROL_VETERINARIO)
            ->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('cedula', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['cedula', 'nombre', 'correo']);

        return response()->json(['data' => $vets]);
    }

    public function index(Request $request, Finca $finca): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $finca);

        $vets = $finca->veterinarios()->get(['cedula', 'nombre', 'correo']);

        return response()->json(['data' => $vets]);
    }

    public function store(Request $request, Finca $finca): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $finca);

        $request->validate([
            'cedula_veterinario' => ['required', 'string', 'exists:Usuario,cedula'],
        ]);

        $vet = Usuario::where('cedula', $request->input('cedula_veterinario'))
            ->where('id_tipo_usuario', Usuario::ROL_VETERINARIO)
            ->firstOrFail();

        if (! $finca->veterinarios()->where('Veterinario_Finca.cedula', $vet->cedula)->exists()) {
            $finca->veterinarios()->attach($vet->cedula);
        }

        return response()->json([
            'mensaje' => "Veterinario {$vet->nombre} asignado correctamente.",
            'data'    => $vet->only(['cedula', 'nombre', 'correo']),
        ], 201);
    }

    public function destroy(Request $request, Finca $finca, string $cedula): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $finca);

        $finca->veterinarios()->detach($cedula);

        return response()->json(['mensaje' => 'Veterinario desasignado.']);
    }

    private function autorizarAcceso($u, Finca $finca): void
    {
        if (Finca::visibleFor($u)->whereKey($finca->getKey())->exists()) return;
        abort(response()->json(['mensaje' => 'No tenés permiso para ver esta finca.'], 403));
    }

    private function autorizarEdicion($u, Finca $finca): void
    {
        if ($u->esAdmin()) return;
        if ($u->esGanadero() && $finca->cedula === $u->cedula) return;
        abort(response()->json(['mensaje' => 'No tenés permiso para gestionar veterinarios de esta finca.'], 403));
    }
}
