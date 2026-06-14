<?php

namespace App\Models;

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
}
