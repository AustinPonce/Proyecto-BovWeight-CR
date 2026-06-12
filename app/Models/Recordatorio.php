<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Recordatorio de re-pesaje por animal (RF22).
 *
 * Campos BD:
 *   id_recordatorio  PK
 *   frecuencia       Ej.: 'semanal', 'quincenal', 'mensual', 'trimestral'
 *   fecha_inicio     Fecha desde la cual contar los intervalos
 *   arete            FK → Animal
 */
class Recordatorio extends Model
{
    use HasFactory;

    protected $table = 'Recordatorio';

    protected $primaryKey = 'id_recordatorio';

    public $timestamps = false;

    protected $fillable = ['frecuencia', 'fecha_inicio', 'arete'];

    protected $casts = [
        'fecha_inicio' => 'date',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'arete', 'arete');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_recordatorio', 'id_recordatorio')
            ->orderByDesc('fecha_envio');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Días entre repeticiones según la frecuencia configurada.
     */
    public function diasFrecuencia(): int
    {
        return match ($this->frecuencia) {
            'semanal' => 7,
            'quincenal' => 15,
            'mensual' => 30,
            'trimestral' => 90,
            default => 30,
        };
    }

    /**
     * Calcula la próxima fecha de pesaje a partir de fecha_inicio y la frecuencia.
     * Avanza tantos ciclos como sean necesarios para que la fecha sea >= hoy.
     */
    public function proximaFecha(): Carbon
    {
        $dias = $this->diasFrecuencia();
        $fecha = Carbon::parse($this->fecha_inicio);
        $hoy = Carbon::today();

        while ($fecha->lt($hoy)) {
            $fecha->addDays($dias);
        }

        return $fecha;
    }

    /**
     * Devuelve true si hoy es día de recordatorio (o ya venció sin notificación).
     * Usado por el comando artisan `recordatorios:procesar`.
     */
    public function estaVencido(): bool
    {
        return $this->proximaFecha()->lte(Carbon::today());
    }
}
