<?php

namespace App\Observers;

use App\Models\Pesaje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Observer (patrón GoF Observer) — reacciona cuando se crea un Pesaje.
 *
 * Inserta una notificación "informativa" en la tabla Notificacion para que
 * el dueño del animal vea actividad reciente en su panel.
 *
 * NOTA: la tabla Notificacion tiene FK obligatoria a Recordatorio. Si el
 * animal todavía no tiene ningún Recordatorio asociado, este observer
 * NO crea la notificación (registra solo en log). Cuando el módulo de
 * Recordatorios se implemente, las notificaciones se generarán automáticamente
 * sin que haya que tocar este código.
 */
class CrearNotificacionObserver
{
    public function created(Pesaje $pesaje): void
    {
        $idRecordatorio = DB::table('Recordatorio')
            ->where('arete', $pesaje->arete)
            ->value('id_recordatorio');

        if (! $idRecordatorio) {
            Log::info("Pesaje {$pesaje->id_pesaje} creado pero no hay Recordatorio "
                ."para arete {$pesaje->arete} — notificación omitida.");

            return;
        }

        DB::table('Notificacion')->insert([
            'mensaje' => "Se ha registrado un nuevo pesaje para el animal con arete: {$pesaje->arete}.",
            'fecha_envio' => Carbon::now(),
            'id_recordatorio' => $idRecordatorio,
        ]);
    }
}
