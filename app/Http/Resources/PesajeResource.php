<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * JSON de un Pesaje. Si tiene imagen, devuelve la URL pública.
 * Incluye campos de corrección por raza (RF13).
 */
class PesajeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => (int) $this->id_pesaje,
            'arete'          => $this->arete,

            // Peso final (= peso_corregido si aplica, o el peso bruto si no hay factor).
            'peso'           => (float) $this->peso,

            // RF13 — Campos de corrección por raza (null para pesajes históricos).
            'peso_original'  => $this->peso_original  !== null ? (float) $this->peso_original  : null,
            'factor_raza'    => $this->factor_raza     !== null ? (float) $this->factor_raza    : null,
            'peso_corregido' => $this->peso_corregido  !== null ? (float) $this->peso_corregido : null,

            'fecha'          => $this->fecha,
            'sincronizado'   => (bool) $this->sincronizado,
            'tipo_pesaje_id' => (int) $this->id_tipo_pesaje,
            'imagen_url'     => $this->imagen
                ? Storage::disk('public')->url($this->imagen)
                : null,

            // Si vino con animal cargado, lo incluimos resumido.
            'animal' => $this->whenLoaded('animal', fn () => [
                'arete'  => $this->animal->arete,
                'nombre' => $this->animal->nombre,
                'raza'   => $this->animal->raza?->raza,
            ]),
        ];
    }
}

