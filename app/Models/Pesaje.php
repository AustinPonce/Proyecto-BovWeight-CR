<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\CrearNotificacionObserver;
use App\Observers\VerificarPesoObserver;
use App\Observers\AuditoriaPesajeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// atributo nativo de Laravel para asignarle los 3 observadores GoF
#[ObservedBy([CrearNotificacionObserver::class, VerificarPesoObserver::class, AuditoriaPesajeObserver::class])]
class Pesaje extends Model
{
    protected $table      = 'Pesaje';
    protected $primaryKey = 'id_pesaje';
    public    $timestamps = false;

    /**
     * Campos del pesaje base + campos de corrección por raza (RF13).
     *
     *   peso           → peso final que se muestra al usuario (= peso_corregido si aplica, o peso_original)
     *   peso_original  → peso calculado antes del factor de raza
     *   factor_raza    → snapshot del factor de la raza al momento del pesaje
     *   peso_corregido → peso_original × factor_raza (= peso guardado en `peso`)
     */
    protected $fillable = [
        'fecha',
        'peso',
        'peso_original',
        'factor_raza',
        'peso_corregido',
        'imagen',
        'sincronizado',
        'arete',
        'id_tipo_pesaje',
    ];

    protected function casts(): array
    {
        return [
            'peso'           => 'decimal:2',
            'peso_original'  => 'decimal:2',
            'factor_raza'    => 'decimal:4',
            'peso_corregido' => 'decimal:2',
            'sincronizado'   => 'boolean',
        ];
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'arete', 'arete');
    }
}