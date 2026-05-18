<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'Usuario';
    protected $primaryKey = 'cedula';
    public $incrementing = false; // Llave primaria es un String (Cédula)
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['cedula', 'nombre', 'correo', 'contrasena', 'id_tipo_usuario'];
}