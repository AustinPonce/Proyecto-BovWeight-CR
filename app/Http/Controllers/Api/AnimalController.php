<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnimalRequest;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use App\Models\Finca;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API REST de Animales (consumida por la app móvil Ionic).
 *
 * Endpoints (prefijo /api):
 *   GET    /api/animales               → index (paginado, ?finca=ID opcional)
 *   POST   /api/animales               → store
 *   GET    /api/animales/{animal}      → show con historial de pesajes
 *   PUT    /api/animales/{animal}      → update
 *   DELETE /api/animales/{animal}      → destroy
 *
 * Todas requieren token Sanctum + rol admin/ganadero/veterinario.
 * El Veterinario es bloqueado en escritura por AnimalRequest::authorize().
 */
class AnimalController extends Controller
{
    // ==================================================================
    // GET /api/animales
    // ==================================================================
    public function index(Request $request): JsonResponse
    {
        $query = Animal::visibleFor($request->user())
            ->with(['finca', 'raza', 'sexo', 'estado'])
            ->withCount('pesajes')
            ->orderBy('arete');

        // Filtro opcional por finca: ?finca=5
        if ($request->filled('finca')) {
            $query->where('id_finca', $request->integer('finca'));
        }

        $animales = $query->paginate(20);

        return AnimalResource::collection($animales)->response();
    }

    // ==================================================================
    // POST /api/animales
    // ==================================================================
    public function store(AnimalRequest $request): JsonResponse
    {
        $datos = $request->validated();

        $this->validarFincaPermitida($request->user(), (int) $datos['id_finca']);

        $animal = Animal::create($datos);

        return AnimalResource::make(
            $animal->load(['finca', 'raza', 'sexo', 'estado'])
        )->response()->setStatusCode(201);
    }

    // ==================================================================
    // GET /api/animales/{animal}
    // ==================================================================
    public function show(Request $request, Animal $animal): JsonResponse
    {
        $this->autorizarAcceso($request->user(), $animal);

        $animal->load(['finca', 'raza', 'sexo', 'estado', 'pesajes']);

        return AnimalResource::make($animal)->response();
    }

    // ==================================================================
    // PUT /api/animales/{animal}
    // ==================================================================
    public function update(AnimalRequest $request, Animal $animal): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $animal);

        $datos = $request->validated();
        $this->validarFincaPermitida($request->user(), (int) $datos['id_finca']);

        unset($datos['arete']); // PK no editable
        $animal->update($datos);

        return AnimalResource::make(
            $animal->load(['finca', 'raza', 'sexo', 'estado'])
        )->response();
    }

    // ==================================================================
    // DELETE /api/animales/{animal}
    // ==================================================================
    public function destroy(Request $request, Animal $animal): JsonResponse
    {
        $this->autorizarEdicion($request->user(), $animal);

        $animal->delete();

        return response()->json(['mensaje' => 'Animal eliminado'], 200);
    }

    // ==================================================================
    // Helpers privados
    // ==================================================================

    private function validarFincaPermitida(Usuario $u, int $idFinca): void
    {
        if (! Finca::visibleFor($u)->whereKey($idFinca)->exists()) {
            abort(response()->json(['mensaje' => 'No tenés permiso para usar esa finca'], 403));
        }
    }

    private function autorizarAcceso(Usuario $u, Animal $animal): void
    {
        if (Animal::visibleFor($u)->whereKey($animal->getKey())->exists()) {
            return;
        }

        abort(response()->json(['mensaje' => 'No tenés permiso para ver este animal'], 403));
    }

    private function autorizarEdicion(Usuario $u, Animal $animal): void
    {
        if ($u->esVeterinario()) {
            abort(response()->json(['mensaje' => 'Los veterinarios no pueden modificar animales'], 403));
        }

        $this->autorizarAcceso($u, $animal);
    }
}
