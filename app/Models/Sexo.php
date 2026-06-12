<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    use HasFactory;

    protected $table = 'Sexo';

    protected $primaryKey = 'id_sexo';

    public $timestamps = false;

    protected $fillable = ['sexo'];
}
