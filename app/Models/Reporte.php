<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'Reporte';
    protected $primaryKey = 'id_reporte';
    public $timestamps = false;

    protected $fillable = ['fecha_generacion', 'id_Tipo_Formato'];
}