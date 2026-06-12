<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaccion extends Model
{
    protected $table = 'Transaccion';

    protected $primaryKey = 'id_transaccion';

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'arete',
        'cedula_usuario',
        'nombre_contraparte',
        'cedula_contraparte',
        'precio_por_kg',
        'peso_negociado',
        'monto_total',
        'fecha',
        'notas',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'arete', 'arete');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }
}
