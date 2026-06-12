<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Recordatorio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Recordatorios de re-pesaje (app móvil Ionic).
 *
 * GET    /api/animales/{animal}/recordatorios          → index
 * POST   /api/animales/{animal}/recordatorios          → store
 * DELETE /api/animales/{animal}/recordatorios/{rec}   → destroy
 */
class RecordatorioController extends Controller
{
    public function index(Request $request, Animal $animal): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $animal);

        $recordatorios = $animal->recordatorios()
            ->orderByDesc('fecha_inicio')
            ->get(['id_recordatorio', 'arete', 'frecuencia', 'fecha_inicio']);

        return response()->json(['data' => $recordatorios]);
    }

    public function store(Request $request, Animal $animal): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $animal);

        $datos = $request->validate([
            'frecuencia' => ['required', 'in:semanal,quincenal,mensual,trimestral'],
            'fecha_inicio' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $recordatorio = Recordatorio::create([
            'arete' => $animal->arete,
            'frecuencia' => $datos['frecuencia'],
            'fecha_inicio' => $datos['fecha_inicio'],
        ]);

        return response()->json(['data' => $recordatorio], 201);
    }

    public function destroy(Request $request, Animal $animal, Recordatorio $recordatorio): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $animal);

        if ($recordatorio->arete !== $animal->arete) {
            return response()->json(['mensaje' => 'Este recordatorio no pertenece al animal indicado.'], 403);
        }

        $recordatorio->delete();

        return response()->json(['mensaje' => 'Recordatorio eliminado.']);
    }

    private function autorizarAcceso($usuario, Animal $animal): void
    {
        if ($usuario->esAdmin()) {
            return;
        }

        if ($usuario->esGanadero() && $animal->finca->cedula === $usuario->cedula) {
            return;
        }

        if ($usuario->esVeterinario()
            && $animal->finca->veterinarios()->where('Veterinario_Finca.cedula', $usuario->cedula)->exists()) {
            return;
        }

        abort(response()->json(['mensaje' => 'No tenés permiso para gestionar este animal.'], 403));
    }
}
