<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reporte extends Model {
    use HasFactory;
    protected $table = 'Reporte';
    protected $primaryKey = 'id_reporte';
    public $timestamps = false;
    protected $fillable = ['fecha_generacion', 'id_tipo_formato'];

    public function animales() {
        return $this->belongsToMany(Animal::class, 'Reporte_Animal', 'id_reporte', 'arete', 'id_reporte', 'arete');
    }
}