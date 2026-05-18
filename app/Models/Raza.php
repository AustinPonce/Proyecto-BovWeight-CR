<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    protected $table = 'Raza';
    protected $primaryKey = 'id_raza';
    public $timestamps = false;

    protected $fillable = ['raza'];
}