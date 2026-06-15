<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'Reporte';
    protected $primaryKey = 'id_reporte';
    public $timestamps = false;

    protected $fillable = ['fecha_generacion', 'id_Tipo_Formato'];
}