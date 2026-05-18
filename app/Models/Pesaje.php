<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesaje extends Model
{
    protected $table = 'Pesaje';
    protected $primaryKey = 'id_pesaje';
    public $timestamps = false;

    protected $fillable = ['fecha', 'peso', 'imagen', 'sincronizado', 'arete', 'id_tipo_pesaje'];
}