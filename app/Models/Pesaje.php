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
    protected $table = 'Pesaje';
    protected $primaryKey = 'id_pesaje';
    public $timestamps = false;

    protected $fillable = ['fecha', 'peso', 'imagen', 'sincronizado', 'arete', 'id_tipo_pesaje'];

    public function animal()
{
    return $this->belongsTo(Animal::class, 'arete', 'arete');
}
}