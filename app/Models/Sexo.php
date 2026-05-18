<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    protected $table = 'Sexo';
    protected $primaryKey = 'id_sexo';
    public $timestamps = false;

    protected $fillable = ['sexo'];
}