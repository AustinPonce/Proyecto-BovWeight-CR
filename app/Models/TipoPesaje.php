<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPesaje extends Model
{
    use HasFactory;

    protected $table = 'Tipo_Pesaje';

    protected $primaryKey = 'id_tipo_pesaje';

    public $timestamps = false;

    protected $fillable = ['tipo_pesaje'];
}
