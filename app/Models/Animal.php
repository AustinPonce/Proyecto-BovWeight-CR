<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Animal — cabeza de ganado registrada en una Finca.
 *
 * La PK es `arete` (string, número SENASA), no autoincremental.
 *
 * Relaciones: finca(), raza(), sexo(), estado(), pesajes().
 */
class Animal extends Model
{
    protected $table = 'Animal';
    protected $primaryKey = 'arete';
    public $incrementing = false;       // PK es string (formato SENASA)
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['arete', 'nombre', 'id_raza', 'id_sexo', 'id_estado', 'id_finca'];

    // ------------------------------------------------------------------
    // Relaciones
    // ------------------------------------------------------------------

    public function finca(): BelongsTo
    {
        return $this->belongsTo(Finca::class, 'id_finca', 'id_finca');
    }

    public function raza(): BelongsTo
    {
        return $this->belongsTo(Raza::class, 'id_raza', 'id_raza');
    }

    public function sexo(): BelongsTo
    {
        return $this->belongsTo(Sexo::class, 'id_sexo', 'id_sexo');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class, 'arete', 'arete');
    }

    // ------------------------------------------------------------------
    // Local Scope: filtrado por rol — delega en el scope de Finca
    // ------------------------------------------------------------------
    //
    // Un animal "visible" para un usuario es aquel cuyo id_finca está entre
    // las fincas visibles para ese usuario. Reusamos Finca::scopeVisibleFor()
    // para no duplicar la lógica de roles.

    public function scopeVisibleFor(Builder $query, Usuario $usuario): Builder
    {
        $idsFincas = Finca::visibleFor($usuario)->pluck('id_finca');

        return $query->whereIn('id_finca', $idsFincas);
    }
}