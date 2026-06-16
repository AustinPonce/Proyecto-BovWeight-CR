<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\ComentarioVeterinario;
use App\Models\Medicamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Calculadora de Dosis (app móvil Ionic).
 *
 * GET  /api/medicamentos       → lista de medicamentos activos
 * POST /api/dosis/calcular     → calcula dosis y opcionalmente guarda comentario vet
 */
class DosisController extends Controller
{
    public function medicamentos(): JsonResponse
    {
        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get(['id_medicamento', 'nombre', 'unidad', 'dosis_por_kg', 'descripcion']);

        return response()->json(['data' => $medicamentos]);
    }

    public function calcular(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'arete'           => ['required', 'string', 'exists:Animal,arete'],
            'id_medicamento'  => ['required', 'integer', 'exists:Medicamento,id_medicamento'],
            'peso_referencia' => ['nullable', 'numeric', 'min:10', 'max:3000'],
            'comentario'      => ['nullable', 'string', 'max:1000'],
        ]);

        $animal = Animal::with(['pesajes' => fn ($q) => $q->orderByDesc('fecha')])->find($datos['arete']);
        $medicamento = Medicamento::find($datos['id_medicamento']);

        $peso = filled($datos['peso_referencia'])
            ? (float) $datos['peso_referencia']
            : (float) optional($animal->pesajes->first())->peso;

        if (! $peso) {
            return response()->json([
                'mensaje' => 'Este animal no tiene pesajes. Ingresá el peso manualmente.',
            ], 422);
        }

        $dosisTotal = round($medicamento->dosis_por_kg * $peso, 4);

        if ($usuario->esVeterinario() && filled($datos['comentario'] ?? null)) {
            ComentarioVeterinario::create([
                'arete'              => $animal->arete,
                'cedula_veterinario' => $usuario->cedula,
                'comentario'         => "[Dosis {$medicamento->nombre}: {$dosisTotal} {$medicamento->unidad}]\n{$datos['comentario']}",
                'fecha'              => Carbon::now(),
            ]);
        }

        return response()->json([
            'data' => [
                'animal'      => ['arete' => $animal->arete, 'nombre' => $animal->nombre],
                'medicamento' => ['nombre' => $medicamento->nombre, 'unidad' => $medicamento->unidad],
                'peso_kg'     => $peso,
                'dosis_total' => $dosisTotal,
                'unidad'      => $medicamento->unidad,
                'formula'     => "{$medicamento->dosis_por_kg} {$medicamento->unidad}/kg × {$peso} kg = {$dosisTotal} {$medicamento->unidad}",
            ],
        ]);
    }
}
