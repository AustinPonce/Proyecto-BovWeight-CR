<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notificacion extends Model {
    use HasFactory;
    protected $table = 'Notificacion';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;
    protected $fillable = ['mensaje', 'fecha_envio', 'id_recordatorio'];
}