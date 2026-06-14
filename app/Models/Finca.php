<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Finca — propiedad de un Usuario (Ganadero).
 *
 * Mapea a la tabla `Finca` (PK autoincremental `id_finca`).
 *
 * Relaciones:
 *   - usuario()      → dueño de la finca (Ganadero)
 *   - animales()     → ganado registrado en la finca
 *   - veterinarios() → veterinarios asignados a esta finca (pivote Veterinario_Finca)
 */
class Finca extends Model
{
    protected $table = 'Finca';
    protected $primaryKey = 'id_finca';
    public $timestamps = false;

    protected $fillable = ['nombre', 'ubicacion', 'cedula'];

    /** Dueño de la finca (relación inversa de Usuario::fincas()). */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula', 'cedula');
    }

    /** Animales registrados en esta finca. */
    public function animales(): HasMany
    {
        return $this->hasMany(Animal::class, 'id_finca', 'id_finca');
    }

    /** Veterinarios asignados a esta finca (tabla pivote Veterinario_Finca). */
    public function veterinarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'Veterinario_Finca',
            'id_finca',
            'cedula',
            'id_finca',
            'cedula'
        );
    }

    // ------------------------------------------------------------------
    // Local Scope: filtrado por rol del usuario
    // ------------------------------------------------------------------
    //
    // Un "local scope" es un método del modelo que extiende la query builder.
    // Se invoca quitándole el prefijo "scope":
    //
    //   Finca::visibleFor($usuario)->get();
    //   Finca::visibleFor($usuario)->where('nombre', 'like', '%X%')->get();
    //
    // Esto centraliza la lógica de control de acceso. Si mañana cambian las
    // reglas (ej. "el comprador también ve fincas marcadas como públicas"),
    // se toca acá y aplica a TODOS los lugares que usan el scope: web, API,
    // reportes, exports, etc.

    /**
     * Devuelve solo las fincas que el usuario tiene permiso de ver.
     *   - Admin       → todas
     *   - Ganadero    → solo las suyas (cedula propia)
     *   - Veterinario → solo asignadas vía pivote Veterinario_Finca
     */
    public function scopeVisibleFor(Builder $query, Usuario $usuario): Builder
    {
        if ($usuario->esAdmin()) {
            return $query;
        }

        if ($usuario->esGanadero()) {
            return $query->where('cedula', $usuario->cedula);
        }

        if ($usuario->esVeterinario()) {
            return $query->whereHas('veterinarios', fn ($q) =>
                $q->where('Veterinario_Finca.cedula', $usuario->cedula)
            );
        }

        // Rol desconocido: por seguridad, nada.
        return $query->whereRaw('1 = 0');
    }
}
