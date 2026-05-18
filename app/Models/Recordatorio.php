<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recordatorio extends Model
{
    protected $table = 'Recordatorio';
    protected $primaryKey = 'id_recordatorio';
    public $timestamps = false;

    protected $fillable = ['frecuencia', 'fecha_inicio', 'arete'];
}