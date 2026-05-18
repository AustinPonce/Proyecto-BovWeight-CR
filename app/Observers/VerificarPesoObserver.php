<?php

namespace App\Observers;

use App\Models\Pesaje;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarPesoObserver
{
    public function created(Pesaje $pesaje): void
    {
        // Regla de negocio analizada para alertas tempranas de desnutrición o bajo peso
        if ($pesaje->peso < 100.00) {
            DB::table('Notificacion')->insert([
                'mensaje' => "ALERTA SANITARIA: El animal {$pesaje->arete} registra un peso crítico de {$pesaje->peso} kg.",
                'fecha_envio' => Carbon::now(),
                'id_recordatorio' => 1
            ]);
            
            Log::warning("Condición corporal baja detectada en animal: {$pesaje->arete}");
        }
    }
}