<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'Notificacion';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = ['mensaje', 'fecha_envio', 'id_recordatorio'];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];

    public function recordatorio()
    {
        return $this->belongsTo(Recordatorio::class, 'id_recordatorio', 'id_recordatorio');
    }
}
