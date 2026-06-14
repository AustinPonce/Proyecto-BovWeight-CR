<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Model {
    use HasFactory;
    protected $table = 'Usuario';
    protected $primaryKey = 'cedula';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['cedula', 'nombre', 'correo', 'contrasena', 'id_tipo_usuario'];

    public function fincas() {
        return $this->hasMany(Finca::class, 'cedula', 'cedula');
    }
    public function fincasAsignadas() {
        return $this->belongsToMany(Finca::class, 'Veterinario_Finca', 'cedula', 'id_finca', 'cedula', 'id_finca');
    }
}