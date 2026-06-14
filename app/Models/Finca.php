<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Finca extends Model {
    use HasFactory;
    protected $table = 'Finca';
    protected $primaryKey = 'id_finca';
    public $timestamps = false;

    protected $fillable = ['nombre', 'ubicacion', 'cedula'];

    public function veterinarios() {
        return $this->belongsToMany(Usuario::class, 'Veterinario_Finca', 'id_finca', 'cedula', 'id_finca', 'cedula');
    }
}