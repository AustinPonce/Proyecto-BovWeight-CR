<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finca extends Model
{
    protected $table = 'Finca';
    protected $primaryKey = 'id_finca';
    public $timestamps = false;

    protected $fillable = ['nombre', 'ubicacion', 'cedula'];

    public function fincas()
{
    // Un usuario tiene muchas fincas. Especificamos la llave foránea y la local.
    return $this->hasMany(Finca::class, 'cedula', 'cedula');
}

public function animales()
{
    return $this->hasMany(Animal::class, 'id_finca', 'id_finca');
}
}