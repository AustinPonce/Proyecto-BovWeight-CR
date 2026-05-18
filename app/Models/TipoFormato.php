<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoFormato extends Model
{
    protected $table = 'Tipo_Formato';
    protected $primaryKey = 'id_Tipo_Formato';
    public $timestamps = false;

    protected $fillable = ['tipo'];
}