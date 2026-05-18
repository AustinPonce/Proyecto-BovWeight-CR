<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'Tipo_usuario';
    protected $primaryKey = 'id_tipo_usuario';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];
}