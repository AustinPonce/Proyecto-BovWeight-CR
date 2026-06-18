<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use Illuminate\Http\JsonResponse;

/**
 * API REST de Estados de Animal (app móvil Ionic).
 *
 * GET /api/estados → lista todos los estados disponibles
 */
class EstadoController extends Controller
{
    public function index(): JsonResponse
    {
        $estados = Estado::orderBy('id_estado')->get(['id_estado', 'estado']);

        return response()->json(['data' => $estados]);
    }
}
