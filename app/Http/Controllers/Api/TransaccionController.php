<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Transaccion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Transacciones (app móvil Ionic).
 *
 * GET  /api/transacciones          → index (lista paginada, filtros: tipo, animal)
 * POST /api/transacciones          → store
 * GET  /api/transacciones/{id}     → show
 * DELETE /api/transacciones/{id}   → destroy
 */
class TransaccionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Transaccion::with(['animal:arete,nombre,id_finca', 'animal.finca:id_finca,nombre'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'tipo'               => ['required', 'in:compra,venta'],
            'arete'              => ['required', 'string', 'exists:Animal,arete'],
            'nombre_contraparte' => ['required', 'string', 'max:150'],
            'cedula_contraparte' => ['nullable', 'string', 'max:30'],
            'precio_por_kg'      => ['required', 'numeric', 'min:1', 'max:99999'],
            'peso_negociado'     => ['required', 'numeric', 'min:1', 'max:3000'],
            'notas'              => ['nullable', 'string', 'max:1000'],
        ]);

        if (! Animal::visibleFor($usuario)->where('arete', $datos['arete'])->exists()) {
            return response()->json(['mensaje' => 'No tenés permiso sobre este animal.'], 403);
        }

        $datos['monto_total']    = round($datos['precio_por_kg'] * $datos['peso_negociado'], 2);
        $datos['fecha']          = Carbon::now();
        $datos['cedula_usuario'] = $usuario->cedula;

        $transaccion = Transaccion::create($datos);

        return response()->json([
            'mensaje' => 'Transacción registrada correctamente.',
            'data'    => $transaccion,
        ], 201);
    }

    public function show(Request $request, Transaccion $transaccion): JsonResponse
    {
        $usuario = $request->user();

        if (! Animal::visibleFor($usuario)->whereKey($transaccion->arete)->exists()) {
            return response()->json(['mensaje' => 'No tenés permiso para ver esta transacción.'], 403);
        }

        return response()->json(['data' => $transaccion->load(['animal.finca', 'usuario'])]);
    }

    public function destroy(Request $request, Transaccion $transaccion): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario->esAdmin() && $transaccion->cedula_usuario !== $usuario->cedula) {
            return response()->json(['mensaje' => 'No tenés permiso para eliminar esta transacción.'], 403);
        }

        $transaccion->delete();

        return response()->json(['mensaje' => 'Transacción eliminada.']);
    }
}
