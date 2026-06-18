<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Notificaciones (app móvil Ionic).
 *
 * GET /api/notificaciones → notificaciones asociadas a los animales
 *                           que el usuario autenticado puede ver.
 */
class NotificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $aretesVisibles = Animal::visibleFor($request->user())->pluck('arete');

        $notificaciones = Notificacion::with([
                'recordatorio.animal:arete,nombre',
            ])
            ->whereHas('recordatorio', fn ($q) => $q->whereIn('arete', $aretesVisibles))
            ->orderByDesc('fecha_envio')
            ->get();

        $data = $notificaciones->map(fn ($n) => [
            'id_notificacion' => $n->id_notificacion,
            'mensaje'         => $n->mensaje,
            'fecha_envio'     => $n->fecha_envio?->toISOString(),
            'id_recordatorio' => $n->id_recordatorio,
            'recordatorio'    => $n->recordatorio ? [
                'arete'      => $n->recordatorio->arete,
                'frecuencia' => $n->recordatorio->frecuencia,
                'animal'     => $n->recordatorio->animal
                    ? ['nombre' => $n->recordatorio->animal->nombre]
                    : null,
            ] : null,
        ]);

        return response()->json(['data' => $data]);
    }
}
