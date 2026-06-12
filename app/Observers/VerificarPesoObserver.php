<?php

namespace App\Observers;

use App\Models\Pesaje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Observer (GoF Observer) — alerta de bajo peso.
 *
 * Si el peso registrado es < 100 kg, eso indica una posible condición
 * corporal crítica (desnutrición, enfermedad). Se loguea siempre y se
 * intenta crear una notificación de alerta si existe un Recordatorio
 * para el animal.
 */
class VerificarPesoObserver
{
    /** Umbral mínimo de peso considerado saludable (en kg). */
    private const UMBRAL_PESO_CRITICO = 100.00;

    public function created(Pesaje $pesaje): void
    {
        if ((float) $pesaje->peso >= self::UMBRAL_PESO_CRITICO) {
            return; // peso normal, nada que hacer
        }

        Log::warning("Condición corporal baja detectada en animal: {$pesaje->arete} "
            ."(peso registrado: {$pesaje->peso} kg)");

        $idRecordatorio = DB::table('Recordatorio')
            ->where('arete', $pesaje->arete)
            ->value('id_recordatorio');

        if (! $idRecordatorio) {
            // Sin recordatorio configurado, solo queda el log de warning de arriba.
            return;
        }

        DB::table('Notificacion')->insert([
            'mensaje' => "ALERTA SANITARIA: El animal {$pesaje->arete} registra "
                                ."un peso crítico de {$pesaje->peso} kg.",
            'fecha_envio' => Carbon::now(),
            'id_recordatorio' => $idRecordatorio,
        ]);
    }
}
