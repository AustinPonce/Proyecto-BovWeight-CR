<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $table = 'Medicamento';

    protected $primaryKey = 'id_medicamento';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'unidad',
        'dosis_por_kg',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'dosis_por_kg' => 'float',
            'activo' => 'boolean',
        ];
    }
}
