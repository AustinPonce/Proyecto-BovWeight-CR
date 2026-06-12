<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComentarioVeterinario extends Model
{
    protected $table = 'ComentarioVeterinario';

    protected $primaryKey = 'id_comentario';

    public $timestamps = false;

    protected $fillable = [
        'arete',
        'cedula_veterinario',
        'comentario',
        'fecha',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'arete', 'arete');
    }

    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_veterinario', 'cedula');
    }
}
