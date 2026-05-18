<?php

namespace App\Observers;

use App\Models\Pesaje;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrearNotificacionObserver
{
    // Reacciona inmediatamente después de que se inserta el pesaje en la BD
    public function created(Pesaje $pesaje): void
    {
        DB::table('Notificacion')->insert([
            'mensaje' => "Se ha registrado un nuevo pesaje para el animal con arete: {$pesaje->arete}.",
            'fecha_envio' => Carbon::now(),
            'id_recordatorio' => 1 // ID por defecto 
        ]);
    }
}