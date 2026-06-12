<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ICalculadorPeso;
use App\Exceptions\NoBovinoDetectadoException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesajeRequest;
use App\Http\Resources\PesajeResource;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\TipoPesaje;
use App\Services\CalculadorPesoContext;
use App\Strategies\FormulaManualStrategy;
use App\Strategies\FotoIAWeightStrategy;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API REST de Pesajes (app móvil Ionic).
 *
 * Endpoints:
 *   GET    /api/pesajes              → index (?animal=arete, ?finca=id)
 *   POST   /api/pesajes              → store (manual o foto)
 *   GET    /api/pesajes/{pesaje}     → show
 *   DELETE /api/pesajes/{pesaje}     → destroy
 *
 * Igual que en web, usamos el patrón Strategy (FormulaManualStrategy o
 * FotoIAWeightStrategy) para el cálculo y los Observers se disparan en
 * el save.
 */
class PesajeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $aretesVisibles = Animal::visibleFor($request->user())->pluck('arete');

        $query = Pesaje::query()
            ->whereIn('arete', $aretesVisibles)
            ->with(['animal'])
            ->orderByDesc('fecha');

        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }
        if ($request->filled('finca')) {
            $aretesFinca = Animal::where('id_finca', $request->integer('finca'))->pluck('arete');
            $query->whereIn('arete', $aretesFinca);
        }

        return PesajeResource::collection($query->paginate(20))->response();
    }

    public function store(PesajeRequest $request): JsonResponse
    {
        $datos = $request->validated();

        // Seguridad: el animal debe ser uno permitido para el usuario.
        if (! Animal::visibleFor($request->user())->whereKey($datos['arete'])->exists()) {
            return response()->json(['mensaje' => 'No tenés permiso para ese animal'], 403);
        }

        // Guardar imagen primero para tener el path antes de calcular.
        $pathImagen = null;
        if ($request->hasFile('imagen')) {
            $pathImagen = $request->file('imagen')->store('pesajes', 'public');
        }

        // Strategy.
        $strategy = $this->resolverStrategy($datos['tipo']);
        $context = new CalculadorPesoContext($strategy);

        $datosCalculo = $datos['tipo'] === 'manual'
            ? [
                'largo_cuerpo' => (float) $datos['largo_cuerpo'],
                'altura' => (float) $datos['altura'],
                'perimetro_toracico' => (float) $datos['perimetro_toracico'],
            ]
            : [
                'imagen' => $pathImagen,
                'categoria' => $datos['tipo_animal'] ?? 'auto',
            ];

        // Si ML detecta que NO es una vaca: borrar imagen y devolver 422.
        try {
            $pesoOriginal = $context->calcular($datosCalculo);
        } catch (NoBovinoDetectadoException $e) {
            if ($pathImagen) {
                Storage::disk('public')->delete($pathImagen);
            }

            return response()->json([
                'mensaje' => $e->mensajeUsuario,
                'detalles' => $e->detalles,
            ], 422);
        }

        // RF13 — Factor de corrección por raza.
        $animal = Animal::with('raza')->whereKey($datos['arete'])->first();
        $factorRaza = (float) ($animal->raza->factor_correccion ?? 1.0);
        $pesoCorregido = round($pesoOriginal * $factorRaza, 2);

        $pesaje = Pesaje::create([
            'fecha' => Carbon::now(),
            'peso' => $pesoCorregido,
            'peso_original' => round($pesoOriginal, 2),
            'factor_raza' => $factorRaza,
            'peso_corregido' => $pesoCorregido,
            'imagen' => $pathImagen,
            'sincronizado' => 1,
            'arete' => $datos['arete'],
            'id_tipo_pesaje' => $this->resolverTipoPesajeId($datos['tipo']),
        ]);

        return PesajeResource::make($pesaje->load('animal'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Pesaje $pesaje): JsonResponse
    {
        if (! Animal::visibleFor($request->user())->whereKey($pesaje->arete)->exists()) {
            return response()->json(['mensaje' => 'No tenés permiso para ver este pesaje'], 403);
        }

        return PesajeResource::make($pesaje->load('animal'))->response();
    }

    public function destroy(Request $request, Pesaje $pesaje): JsonResponse
    {
        if ($request->user()->esVeterinario()) {
            return response()->json(['mensaje' => 'Los veterinarios no pueden eliminar pesajes'], 403);
        }
        if (! Animal::visibleFor($request->user())->whereKey($pesaje->arete)->exists()) {
            return response()->json(['mensaje' => 'No tenés permiso para este pesaje'], 403);
        }

        if ($pesaje->imagen) {
            Storage::disk('public')->delete($pesaje->imagen);
        }
        $pesaje->delete();

        return response()->json(['mensaje' => 'Pesaje eliminado']);
    }

    private function resolverStrategy(string $tipo): ICalculadorPeso
    {
        return match ($tipo) {
            'manual' => new FormulaManualStrategy,
            'foto' => new FotoIAWeightStrategy,
        };
    }

    private function resolverTipoPesajeId(string $tipo): int
    {
        $nombreBuscado = $tipo === 'foto'
            ? 'Estimación por Fotografía'
            : 'Pesaje Manual con Báscula';

        return (int) TipoPesaje::where('tipo_pesaje', $nombreBuscado)->value('id_tipo_pesaje');
    }
}
