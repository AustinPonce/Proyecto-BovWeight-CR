<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma un Animal en JSON para la API.
 *
 * Combina cada catálogo (raza/sexo/estado) en un objeto `{id, nombre}` —
 * mucho más cómodo para el cliente que un id sin contexto.
 */
class AnimalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'arete'  => $this->arete,
            'nombre' => $this->nombre,

            'raza' => $this->whenLoaded('raza', fn () => [
                'id'     => (int) $this->raza->id_raza,
                'nombre' => $this->raza->raza,
            ]),

            'sexo' => $this->whenLoaded('sexo', fn () => [
                'id'     => (int) $this->sexo->id_sexo,
                'nombre' => $this->sexo->sexo,
            ]),

            'estado' => $this->whenLoaded('estado', fn () => [
                'id'     => (int) $this->estado->id_estado,
                'nombre' => $this->estado->estado,
            ]),

            // Cuando solo necesitamos saber a qué finca pertenece, mandamos id+nombre.
            'finca' => $this->whenLoaded('finca', fn () => [
                'id'     => (int) $this->finca->id_finca,
                'nombre' => $this->finca->nombre,
            ]),

            // Conteo de pesajes (rápido).
            'pesajes_count' => $this->whenCounted('pesajes'),

            // Último peso registrado, útil para listados en la app móvil.
            'ultimo_peso' => $this->when(
                $this->relationLoaded('pesajes') && $this->pesajes->isNotEmpty(),
                fn () => [
                    'peso'  => (float) $this->pesajes->sortByDesc('fecha')->first()->peso,
                    'fecha' => $this->pesajes->sortByDesc('fecha')->first()->fecha,
                ]
            ),
        ];
    }
}
