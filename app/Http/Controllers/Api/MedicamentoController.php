<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'unidad' => ['required', 'string', 'max:20'],
            'dosis_por_kg' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $datos['activo'] = true;
        $medicamento = Medicamento::create($datos);

        return response()->json(['data' => $medicamento], 201);
    }

    public function update(Request $request, Medicamento $medicamento): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'unidad' => ['required', 'string', 'max:20'],
            'dosis_por_kg' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $medicamento->update($datos);

        return response()->json(['data' => $medicamento]);
    }

    public function destroy(Medicamento $medicamento): JsonResponse
    {
        $medicamento->update(['activo' => false]);

        return response()->json(['mensaje' => 'Medicamento desactivado.']);
    }
}
