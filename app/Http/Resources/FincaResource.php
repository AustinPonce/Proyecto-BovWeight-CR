<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma una Finca en JSON para la API.
 *
 * `whenLoaded()` solo incluye el campo si la relación fue cargada con
 * `->with('xxx')` o `->load('xxx')`. Esto evita el problema N+1: si Austin
 * pide `GET /api/fincas` sin querer todos los animales, no los traemos.
 */
class FincaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (int) $this->id_finca,
            'nombre'    => $this->nombre,
            'ubicacion' => $this->ubicacion,

            // Cantidad de animales (rápido, sin traerlos todos).
            'animales_count' => $this->whenCounted('animales'),

            // Dueño completo solo si se pidió.
            'dueno' => UsuarioResource::make($this->whenLoaded('usuario')),

            // Animales completos solo si se pidió (con ?include=animales por ej).
            'animales' => AnimalResource::collection($this->whenLoaded('animales')),

            // Veterinarios asignados — útil en /fincas/{id}.
            'veterinarios' => UsuarioResource::collection($this->whenLoaded('veterinarios')),
        ];
    }
}
