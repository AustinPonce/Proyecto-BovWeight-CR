<?php

namespace App\Observers;

use App\Models\Pesaje;
use Illuminate\Support\Facades\Log;

class AuditoriaPesajeObserver
{
    public function created(Pesaje $pesaje): void
    {
        // Registra de forma agnóstica el rastro de auditoría en los archivos de Log del sistema
        Log::info("AUDITORÍA SISTEMA: El usuario del sistema registró el pesaje 
        ID {$pesaje->id_pesaje} 
        con un valor de {$pesaje->peso}kg.");
    }
}