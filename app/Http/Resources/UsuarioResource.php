<?php

namespace App\Http\Resources;

use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma un Usuario en JSON apto para enviar al cliente móvil.
 *
 * NUNCA incluye la contraseña (hash o texto) — la regla de oro.
 * Tampoco expone el id_tipo_usuario raw: en su lugar mandamos un objeto
 * `rol` con id + slug + nombre, más cómodo para el frontend.
 */
class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'rol'    => [
                'id'     => (int) $this->id_tipo_usuario,
                'slug'   => $this->slugRol(),
                'nombre' => $this->whenLoaded(
                    'tipoUsuario',
                    fn () => $this->tipoUsuario->nombre_tipo,
                    fn () => TipoUsuario::find($this->id_tipo_usuario)?->nombre_tipo
                ),
            ],
        ];
    }

    /** Slug legible del rol para que el frontend no tenga que comparar ids mágicos. */
    private function slugRol(): string
    {
        return match ((int) $this->id_tipo_usuario) {
            1 => 'admin',
            2 => 'ganadero',
            3 => 'veterinario',
            default => 'desconocido',
        };
    }
}
