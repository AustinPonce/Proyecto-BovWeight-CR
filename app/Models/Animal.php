<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'Animal';
    protected $primaryKey = 'arete';
    public $incrementing = false; // Llave primaria es un String (Formato SENASA)
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['arete', 'nombre', 'id_raza', 'id_sexo', 'id_estado', 'id_finca'];

    public function finca()
{
    return $this->belongsTo(Finca::class, 'id_finca', 'id_finca');
}

public function raza()
{
    return $this->belongsTo(Raza::class, 'id_raza', 'id_raza');
}

public function sexo()
{
    return $this->belongsTo(Sexo::class, 'id_sexo', 'id_sexo');
}

public function estado()
{
    return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
}

public function pesajes()
{
    // Un animal tiene muchos pesajes históricos
    return $this->hasMany(Pesaje::class, 'arete', 'arete');
}
}