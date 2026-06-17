<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Raza — catálogo de razas bovinas con factor de corrección de peso (RF13).
 *
 * El `factor_correccion` es un multiplicador aplicado al peso estimado (bruto)
 * antes de guardarlo como peso final. DEFAULT 1.0000 = sin corrección.
 *
 * Ejemplos de factores:
 *   Holstein   = 1.08  (razas más pesadas y voluminosas)
 *   Jersey     = 0.92  (razas más livianas)
 *   Brahman    = 1.12  (razas zebuínas, mayor masa muscular)
 *   Angus      = 1.00  (referencia base)
 *   Simmental  = 1.05
 */
class Raza extends Model
{
    protected $table      = 'Raza';
    protected $primaryKey = 'id_raza';
    public    $timestamps = false;

    protected $fillable = ['raza', 'factor_correccion'];

    protected function casts(): array
    {
        return [
            'factor_correccion' => 'decimal:4',
        ];
    }
}