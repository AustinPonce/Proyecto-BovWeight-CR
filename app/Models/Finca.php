<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finca extends Model
{
    protected $table = 'Finca';
    protected $primaryKey = 'id_finca';
    public $timestamps = false;

    protected $fillable = ['nombre', 'ubicacion', 'cedula'];
}