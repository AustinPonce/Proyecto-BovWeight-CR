<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raza;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazaController extends Controller
{
    public function index(): JsonResponse
    {
        $razas = Raza::orderBy('raza')->get(['id_raza', 'raza']);

        return response()->json(['data' => $razas]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'raza' => ['required', 'string', 'max:100', 'unique:Raza,raza'],
        ]);

        $raza = Raza::create($datos);

        return response()->json(['data' => $raza], 201);
    }

    public function update(Request $request, Raza $raza): JsonResponse
    {
        $datos = $request->validate([
            'raza' => ['required', 'string', 'max:100'],
        ]);

        $raza->update($datos);

        return response()->json(['data' => $raza]);
    }

    public function destroy(Raza $raza): JsonResponse
    {
        $raza->delete();

        return response()->json(['mensaje' => 'Raza eliminada.']);
    }
}
