<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * JSON de un Pesaje. Si tiene imagen, devuelve la URL pública.
 */
class PesajeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (int) $this->id_pesaje,
            'arete'        => $this->arete,
            'peso'         => (float) $this->peso,
            'fecha'        => $this->fecha,
            'sincronizado' => (bool) $this->sincronizado,
            'tipo_pesaje_id' => (int) $this->id_tipo_pesaje,
            'imagen_url'   => $this->imagen
                ? Storage::disk('public')->url($this->imagen)
                : null,

            // Si vino con animal cargado, lo incluimos resumido.
            'animal' => $this->whenLoaded('animal', fn () => [
                'arete'  => $this->animal->arete,
                'nombre' => $this->animal->nombre,
            ]),
        ];
    }
}
