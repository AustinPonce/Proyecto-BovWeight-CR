<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recordatorio extends Model {
    use HasFactory;
    protected $table = 'Recordatorio';
    protected $primaryKey = 'id_recordatorio';
    public $timestamps = false;
    protected $fillable = ['frecuencia', 'fecha_inicio', 'arete'];
}