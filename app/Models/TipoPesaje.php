<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPesaje extends Model {
    use HasFactory;
    protected $table = 'Tipo_Pesaje';
    protected $primaryKey = 'id_tipo_pesaje';
    public $timestamps = false;
    protected $fillable = ['tipo_pesaje'];
}