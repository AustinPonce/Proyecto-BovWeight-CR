<?php

namespace App\Console\Commands;

use App\Models\Notificacion;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * RF22 — Comando artisan que procesa los recordatorios de re-pesaje vencidos.
 *
 * Uso manual:
 *   php artisan recordatorios:procesar
 *
 * Programado automáticamente en routes/console.php para correr cada día a las 8 am.
 *
 * Lógica:
 *   1. Carga todos los Recordatorios con su animal.
 *   2. Para cada uno calcula si hoy >= proxima fecha de pesaje.
 *   3. Si está vencido, inserta una Notificacion en la BD y avanza la fecha_inicio
 *      al siguiente ciclo para que no se dispare de nuevo hasta el próximo intervalo.
 */
class ProcesarRecordatorios extends Command
{
    protected $signature   = 'recordatorios:procesar';
    protected $description = 'Genera notificaciones para los recordatorios de re-pesaje vencidos (RF22)';

    public function handle(): int
    {
        $hoy         = Carbon::today();
        $recordatorios = Recordatorio::with('animal.finca')->get();
        $procesados  = 0;

        foreach ($recordatorios as $rec) {
            if (! $rec->estaVencido()) {
                continue;
            }

            $animal   = $rec->animal;
            $nombre   = $animal->nombre ? "\"{$animal->nombre}\"" : "arete {$animal->arete}";
            $finca    = $animal->finca->nombre ?? 'finca desconocida';

            // Crear la notificación
            Notificacion::create([
                'mensaje'         => "Recordatorio: es momento de volver a pesar al animal {$nombre} de {$finca}. "
                                   . "Frecuencia configurada: {$rec->frecuencia}.",
                'fecha_envio'     => Carbon::now(),
                'id_recordatorio' => $rec->id_recordatorio,
            ]);

            // Avanzar fecha_inicio al próximo ciclo para evitar re-disparo
            $rec->fecha_inicio = $rec->proximaFecha()->addDays($rec->diasFrecuencia());
            $rec->save();

            $procesados++;

            Log::info("RecordatoriosProcesar: notificación creada para animal {$animal->arete} "
                . "(recordatorio #{$rec->id_recordatorio}, frecuencia: {$rec->frecuencia}).");

            $this->line("  ✓ Animal {$animal->arete} — {$rec->frecuencia}");
        }

        $this->info("Procesados: {$procesados} recordatorio(s) vencido(s).");

        return Command::SUCCESS;
    }
}
