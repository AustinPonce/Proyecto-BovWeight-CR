<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoFormato extends Model {
    use HasFactory;
    protected $table = 'Tipo_Formato';
    protected $primaryKey = 'id_Tipo_Formato';
    public $timestamps = false;
    protected $fillable = ['tipo'];
}