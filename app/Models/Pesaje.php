<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pesaje extends Model {
    use HasFactory;
    protected $table = 'Pesaje';
    protected $primaryKey = 'id_pesaje';
    public $timestamps = false;
    protected $fillable = ['fecha', 'peso', 'imagen', 'sincronizado', 'arete', 'id_tipo_pesaje'];
}