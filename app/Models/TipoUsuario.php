<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoUsuario extends Model {
    use HasFactory;
    protected $table = 'Tipo_usuario';
    protected $primaryKey = 'id_tipo_usuario';
    public $timestamps = false;
    protected $fillable = ['nombre_tipo'];
}