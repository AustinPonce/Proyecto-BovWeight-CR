<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'Estado';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = ['estado'];
}