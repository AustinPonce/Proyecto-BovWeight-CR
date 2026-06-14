<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Animal extends Model {
    use HasFactory;
    protected $table = 'Animal';
    protected $primaryKey = 'arete';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['arete', 'nombre', 'id_raza', 'id_sexo', 'id_estado', 'id_finca'];

    public function reportes() {
        return $this->belongsToMany(Reporte::class, 'Reporte_Animal', 'arete', 'id_reporte', 'arete', 'id_reporte');
    }
}