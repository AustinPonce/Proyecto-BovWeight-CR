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
}