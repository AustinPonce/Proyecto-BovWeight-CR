<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\ComentarioVeterinario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Comentarios Veterinarios (app móvil Ionic).
 *
 * GET    /api/animales/{animal}/comentarios  → index
 * POST   /api/animales/{animal}/comentarios  → store (solo veterinarios)
 * DELETE /api/animales/{animal}/comentarios/{comentario} → destroy
 */
class ComentarioController extends Controller
{
    public function index(Request $request, Animal $animal): JsonResponse
    {
        $this->autorizarVer($request->user(), $animal);

        $comentarios = $animal->comentariosVeterinario()
            ->with('veterinario:cedula,nombre')
            ->orderByDesc('fecha')
            ->get()
            ->map(fn ($c) => [
                'id_comentario'     => $c->id_comentario,
                'comentario'        => $c->comentario,
                'fecha'             => $c->fecha,
                'cedula_veterinario'=> $c->cedula_veterinario,
                'veterinario'       => $c->veterinario?->nombre,
            ]);

        return response()->json(['data' => $comentarios]);
    }

    public function store(Request $request, Animal $animal): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario->esVeterinario()) {
            return response()->json(['mensaje' => 'Solo los veterinarios pueden dejar comentarios.'], 403);
        }

        $this->autorizarVer($usuario, $animal);

        $datos = $request->validate([
            'comentario' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $comentario = ComentarioVeterinario::create([
            'arete'              => $animal->arete,
            'cedula_veterinario' => $usuario->cedula,
            'comentario'         => $datos['comentario'],
            'fecha'              => Carbon::now(),
        ]);

        return response()->json([
            'mensaje' => 'Comentario guardado.',
            'data'    => $comentario,
        ], 201);
    }

    public function destroy(Request $request, Animal $animal, ComentarioVeterinario $comentario): JsonResponse
    {
        $u = $request->user();

        if (! $u->esAdmin() && $comentario->cedula_veterinario !== $u->cedula) {
            return response()->json(['mensaje' => 'Solo el veterinario autor o un admin puede eliminar este comentario.'], 403);
        }

        $comentario->delete();

        return response()->json(['mensaje' => 'Comentario eliminado.']);
    }

    private function autorizarVer($u, Animal $animal): void
    {
        if ($u->esAdmin()) return;

        if ($u->esGanadero() && $animal->finca->cedula === $u->cedula) return;

        if ($u->esVeterinario()
            && $animal->finca->veterinarios()->where('Veterinario_Finca.cedula', $u->cedula)->exists()) {
            return;
        }

        abort(response()->json(['mensaje' => 'No tenés acceso a los comentarios de este animal.'], 403));
    }
}
